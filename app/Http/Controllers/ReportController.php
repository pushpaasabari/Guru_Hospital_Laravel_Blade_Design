<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\AnesthesiaType;
use App\Models\ClinicalStaff;
use App\Models\Newborn;
use App\Services\DocumentPackPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Report index redirects to month-wise census report.
     */
    public function index()
    {
        return redirect()->route('reports.month-wise');
    }

    /**
     * Day Wise / Complete Daily Clinical Register Report
     * Covers: 1. Admissions, 2. Discharges, 3. Inpatients (Active Census)
     */
    public function day(Request $request, DocumentPackPdfService $pdfService)
    {
        $allDates = $request->boolean('all_dates') || $request->input('date') === 'all';
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $selectedDate = (!$allDates && !$dateFrom && !$dateTo)
            ? ($request->input('date') ?: now()->toDateString())
            : (string) $request->input('date', '');

        $dateBasis = (string) $request->input('date_basis', 'any');
        if (!in_array($dateBasis, ['any', 'admission', 'procedure', 'discharge'], true)) {
            $dateBasis = 'any';
        }

        $section = (string) $request->input('section', 'all');
        if (!in_array($section, ['all', 'admissions', 'discharges', 'inpatients'], true)) {
            $section = 'all';
        }

        $search = trim((string) $request->input('search'));
        $workflow = (string) $request->input('surgery_type', '');
        $format = (string) $request->input('format', '');
        $surgeon = (string) $request->input('surgeon', '');

        $applyCommonFilters = function ($q) use ($search, $workflow, $format, $surgeon) {
            if ($search !== '') {
                $q->filterSearch($search, true);
            }
            if ($workflow !== '') {
                $q->where('surgery_type', $workflow);
            }
            if ($format !== '') {
                $q->where('format', $format);
            }
            if ($surgeon !== '') {
                $q->where(function ($sub) use ($surgeon) {
                    $sub->where('surgeon', $surgeon)
                        ->orWhere('consultant', $surgeon);
                });
            }
        };

        $applyDateWindow = function ($q, array $columns) use ($allDates, $dateFrom, $dateTo, $selectedDate) {
            if ($dateFrom || $dateTo) {
                $q->where(function ($outer) use ($columns, $dateFrom, $dateTo) {
                    foreach ($columns as $col) {
                        $outer->orWhere(function ($sub) use ($col, $dateFrom, $dateTo) {
                            if ($dateFrom) {
                                $sub->whereDate($col, '>=', $dateFrom);
                            }
                            if ($dateTo) {
                                $sub->whereDate($col, '<=', $dateTo);
                            }
                        });
                    }
                });
            } elseif (!$allDates && $selectedDate !== '') {
                $q->where(function ($outer) use ($columns, $selectedDate) {
                    foreach ($columns as $col) {
                        $outer->orWhereDate($col, $selectedDate);
                    }
                });
            }
        };

        // Combined operative/day register query preserved for date-basis filtering & pagination compatibility
        $query = Admission::query()
            ->with('newborns')
            ->latest('admission_at')
            ->latest('id');

        $dateColumns = match ($dateBasis) {
            'admission' => ['admission_at'],
            'procedure' => ['procedure_at'],
            'discharge' => ['discharge_at'],
            default => ['admission_at', 'procedure_at', 'discharge_at'],
        };

        $applyDateWindow($query, $dateColumns);
        $applyCommonFilters($query);

        // 1. Admissions in selected period
        $admissionsQuery = Admission::query()
            ->with('newborns')
            ->latest('admission_at')
            ->latest('id');
        $applyDateWindow($admissionsQuery, ['admission_at']);
        $applyCommonFilters($admissionsQuery);

        // 2. Discharges in selected period
        $dischargesQuery = Admission::query()
            ->with('newborns')
            ->whereNotNull('discharge_at')
            ->latest('discharge_at')
            ->latest('id');
        $applyDateWindow($dischargesQuery, ['discharge_at']);
        $applyCommonFilters($dischargesQuery);

        // 3. Active Inpatients (ward census as of selected/end date, or live in-admission when All Dates)
        $censusDate = (!$allDates && ($dateTo || $selectedDate !== ''))
            ? ($dateTo ?: $selectedDate)
            : now()->toDateString();

        $inpatientsQuery = Admission::query()
            ->with('newborns')
            ->latest('admission_at')
            ->latest('id');
        $applyCommonFilters($inpatientsQuery);
        if ($allDates) {
            $inpatientsQuery->whereNull('discharge_at');
        } else {
            $inpatientsQuery
                ->whereDate('admission_at', '<=', $censusDate)
                ->where(function ($q) use ($censusDate) {
                    $q->whereNull('discharge_at')
                        ->orWhereDate('discharge_at', '>', $censusDate);
                });
        }

        $admissionsList = $this->enrichDayAdmissions($admissionsQuery->get(), $censusDate);
        $dischargesList = $this->enrichDayAdmissions($dischargesQuery->get(), $censusDate);
        $inpatientsList = $this->enrichDayAdmissions($inpatientsQuery->get(), $censusDate);
        $allMatches = $this->enrichDayAdmissions((clone $query)->get(), $censusDate);

        $sections = [
            'admissions' => $admissionsList->values(),
            'discharges' => $dischargesList->values(),
            'inpatients' => $inpatientsList->values(),
        ];

        if ($request->input('export') === 'csv') {
            $label = $allDates
                ? 'all_dates'
                : (($dateFrom || $dateTo) ? trim(($dateFrom ?: 'start') . '_to_' . ($dateTo ?: 'end')) : $selectedDate);
            return $this->exportDayCsv($sections, $section, $label);
        }

        $deliveries = $allMatches->filter(fn ($a) => $a->surgery_type === 'delivery' || $a->newborns->isNotEmpty());
        $censusDeliveries = $inpatientsList->merge($admissionsList)->merge($dischargesList)
            ->unique('id')
            ->filter(fn ($a) => $a->surgery_type === 'delivery' || $a->newborns->isNotEmpty());

        $metrics = [
            'total_cases' => $allMatches->count(),
            'admissions_count' => $admissionsList->count(),
            'discharges_section_count' => $dischargesList->count(),
            'inpatients_count' => $inpatientsList->count(),
            'census_date' => $censusDate,
            'surgeries_count' => $allMatches->where('surgery_type', 'other')->count(),
            'deliveries_count' => $deliveries->count(),
            'babies_count' => $deliveries->flatMap->newborns->count(),
            'census_deliveries_count' => $censusDeliveries->count(),
            'census_babies_count' => $censusDeliveries->flatMap->newborns->count(),
            'medical_count' => $allMatches->where('surgery_type', 'medical')->count(),
            'discharges_count' => $dischargesList->count(),
        ];

        if (in_array($request->input('export'), ['image', 'pdf'], true)) {
            $exportFormat = (string) $request->input('export');
            $label = $allDates
                ? 'all_dates'
                : (($dateFrom || $dateTo) ? trim(($dateFrom ?: 'start') . '_to_' . ($dateTo ?: 'end')) : $selectedDate);
            $periodLabel = $allDates
                ? 'All Dates (Live Ward Census)'
                : (($dateFrom || $dateTo)
                    ? trim(($dateFrom ? Carbon::parse($dateFrom)->format('d.m.Y') : 'Start') . ' to ' . ($dateTo ? Carbon::parse($dateTo)->format('d.m.Y') : 'End'))
                    : 'Date: ' . Carbon::parse($selectedDate)->format('d.m.Y'));
            $sectionLabel = match ($section) {
                'admissions' => '1. Admissions Only',
                'discharges' => '2. Discharges Only',
                'inpatients' => '3. Inpatients Census Only',
                default => 'Complete Daily Register (Admissions • Discharges • Inpatients)',
            };

            return $this->streamPlaywrightReport(
                'pdf.day-report-image',
                [
                    'sections' => $sections,
                    'sectionFilter' => $section,
                    'records' => $allMatches,
                    'metrics' => $metrics,
                    'periodLabel' => $periodLabel,
                    'basisLabel' => $sectionLabel,
                ],
                "day_report_{$label}",
                "GURU HOSPITAL — Day Report ({$periodLabel})",
                $exportFormat,
                $pdfService
            );
        }

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage < 5 || $perPage > 200) {
            $perPage = 50;
        }

        $records = $query->paginate($perPage)->withQueryString();
        $this->enrichDayAdmissions($records->getCollection(), $censusDate);

        $filters = [
            'date' => $selectedDate,
            'date_from' => $dateFrom ?: '',
            'date_to' => $dateTo ?: '',
            'date_basis' => $dateBasis,
            'section' => $section,
            'all_dates' => $allDates,
            'search' => $request->input('search', ''),
            'surgery_type' => $request->input('surgery_type', ''),
            'format' => $request->input('format', ''),
            'surgeon' => $request->input('surgeon', ''),
            'per_page' => $perPage,
        ];

        $clinicalStaff = ClinicalStaff::cachedActive();

        return Inertia::render('Reports/DayReport', compact('records', 'sections', 'metrics', 'filters', 'clinicalStaff'));
    }

    /**
     * Attach computed MRD, payment summary, review date, and stay-day counts for Day Report views and exports.
     */
    protected function enrichDayAdmissions($collection, string $censusDate)
    {
        $censusEnd = Carbon::parse($censusDate)->startOfDay();

        return $collection->each(function (Admission $adm) use ($censusEnd) {
            $adm->append(['resolved_mrd_no', 'payment_summary']);

            if ($adm->admission_at) {
                $admitDay = Carbon::parse($adm->admission_at)->startOfDay();
                $dischDay = $adm->discharge_at ? Carbon::parse($adm->discharge_at)->startOfDay() : null;
                $adm->setAttribute(
                    'stay_days',
                    $dischDay
                        ? max(1, (int) $admitDay->diffInDays($dischDay))
                        : max(1, (int) $admitDay->diffInDays($censusEnd) + 1)
                );
                $adm->setAttribute(
                    'census_stay_days',
                    $admitDay->lte($censusEnd) ? max(1, (int) $admitDay->diffInDays($censusEnd) + 1) : 1
                );
            } else {
                $adm->setAttribute('stay_days', null);
                $adm->setAttribute('census_stay_days', null);
            }

            $review = $adm->formatted_advice_and_review;
            $adm->setAttribute('resolved_review_date', $review['review_date'] ?? null);
        });
    }

    /**
     * MRD Report (Medical Records Department Register)
     */
    public function mrd(Request $request, DocumentPackPdfService $pdfService)
    {
        $query = Admission::query()
            ->latest('discharge_at')
            ->latest('admission_at')
            ->latest('id');

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $dateField = $request->input('date_basis') === 'discharge' ? 'discharge_at' : 'admission_at';

        if ($dateFrom) {
            $query->whereDate($dateField, '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate($dateField, '<=', $dateTo);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('uhid', 'like', "%{$search}%")
                    ->orWhere('ip_no', 'like', "%{$search}%")
                    ->orWhere('mrd_no', 'like', "%{$search}%")
                    ->orWhere('icd_code', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%");
            });
        }

        if ($paymentMode = $request->input('payment_mode')) {
            if (strtolower((string) $paymentMode) === 'insurance') {
                $query->where(function ($q) {
                    $q->where('payment_mode', 'insurance')
                        ->orWhereIn('format', ['cmchistn', 'private']);
                });
            } elseif (strtolower((string) $paymentMode) === 'cash') {
                $query->where(function ($q) {
                    $q->whereNull('payment_mode')
                        ->orWhereIn('payment_mode', ['cash', 'self', 'Cash', 'Self']);
                });
            }
        }

        if ($workflow = $request->input('surgery_type')) {
            $query->where('surgery_type', $workflow);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($handoverStatus = $request->input('handover_status')) {
            if ($handoverStatus === 'signed') {
                $query->whereNotNull('mrd_incharge_name')->where('mrd_incharge_name', '!=', '');
            } elseif ($handoverStatus === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('mrd_incharge_name')->orWhere('mrd_incharge_name', '');
                });
            }
        }

        if ($request->input('export') === 'csv') {
            return $this->exportMrdCsv($query->get());
        }

        $allMatches = (clone $query)->get();
        $insuranceCount = $allMatches->filter(fn ($a) => $a->isInsurance() || in_array($a->format, ['cmchistn', 'private'], true))->count();

        $metrics = [
            'total_files' => $allMatches->count(),
            'discharged_count' => $allMatches->filter(fn ($a) => !empty($a->discharge_at))->count(),
            'icd_coded_count' => $allMatches->filter(fn ($a) => trim((string) $a->icd_code) !== '')->count(),
            'mrd_signed_count' => $allMatches->filter(fn ($a) => trim((string) $a->mrd_incharge_name) !== '')->count(),
            'generated_count' => $allMatches->where('status', 'generated')->count(),
            'insurance_count' => $insuranceCount,
            'cash_count' => $allMatches->count() - $insuranceCount,
        ];

        if (in_array($request->input('export'), ['image', 'pdf'], true)) {
            $exportFormat = (string) $request->input('export');
            $periodLabel = ($dateFrom || $dateTo)
                ? trim(($dateFrom ? Carbon::parse($dateFrom)->format('d.m.Y') : 'Start') . ' to ' . ($dateTo ? Carbon::parse($dateTo)->format('d.m.Y') : 'End'))
                : 'All MRD Records';
            $rangeSlug = ($dateFrom || $dateTo) ? trim(($dateFrom ?: 'start') . '_to_' . ($dateTo ?: 'end')) : 'all';

            return $this->streamPlaywrightReport(
                'pdf.mrd-report-image',
                [
                    'records' => $allMatches,
                    'metrics' => $metrics,
                    'periodLabel' => $periodLabel,
                ],
                "mrd_report_{$rangeSlug}",
                "GURU HOSPITAL — MRD Report ({$periodLabel})",
                $exportFormat,
                $pdfService
            );
        }

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage < 5 || $perPage > 200) {
            $perPage = 50;
        }

        $records = $query->paginate($perPage)->withQueryString();
        $records->through(function (Admission $adm) {
            return $adm->append(['resolved_mrd_no', 'payment_summary']);
        });
        $filters = $request->only([
            'date_from',
            'date_to',
            'date_basis',
            'search',
            'payment_mode',
            'surgery_type',
            'status',
            'handover_status',
            'per_page',
        ]);
        $filters['date_basis'] = $request->input('date_basis', 'admission');
        $filters['per_page'] = $perPage;

        return Inertia::render('Reports/MrdReport', compact('records', 'metrics', 'filters'));
    }

    /**
     * 1. Birth Report
     */
    public function birth(Request $request)
    {
        $query = Admission::query()
            ->where(function ($q) {
                $q->where('surgery_type', 'delivery')
                  ->orWhereHas('newborns');
            })
            ->with('newborns')
            ->latest('admission_at');

        // Date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('admission_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('admission_at', '<=', $dateTo);
        }

        // Search
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('uhid', 'like', "%{$search}%")
                  ->orWhere('ip_no', 'like', "%{$search}%")
                  ->orWhere('procedure_name', 'like', "%{$search}%")
                  ->orWhere('procedure_done', 'like', "%{$search}%");
            });
        }

        // Delivery Type (LSCS, Normal, etc.)
        if ($deliveryType = $request->input('delivery_type')) {
            $query->where(function ($q) use ($deliveryType) {
                $q->where('procedure_name', 'like', "%{$deliveryType}%")
                  ->orWhere('procedure_done', 'like', "%{$deliveryType}%");
            });
        }

        // Doctor (Surgeon / Consultant / Pediatrician)
        if ($doctor = $request->input('doctor')) {
            $query->where(function ($q) use ($doctor) {
                $q->where('surgeon', $doctor)
                  ->orWhere('consultant', $doctor)
                  ->orWhere('pediatrician_name', $doctor);
            });
        }

        // Newborn gender filter
        if ($babySex = $request->input('baby_sex')) {
            $query->whereHas('newborns', function ($q) use ($babySex) {
                $q->whereRaw('LOWER(sex) = ?', [strtolower(trim((string) $babySex))]);
            });
        }

        // Handle CSV Export
        if ($request->input('export') === 'csv') {
            return $this->exportBirthCsv($query->get());
        }

        // Summary metrics
        $metricsQuery = clone $query;
        $allDeliveries = $metricsQuery->get();
        $totalDeliveries = $allDeliveries->count();

        $allNewborns = $allDeliveries->flatMap->newborns;
        $totalBabies = $allNewborns->count();
        $maleBabies = $allNewborns->filter(fn ($b) => strtolower(trim((string) $b->sex)) === 'male')->count();
        $femaleBabies = $allNewborns->filter(fn ($b) => strtolower(trim((string) $b->sex)) === 'female')->count();
        $avgWeightKg = $totalBabies > 0 ? round($allNewborns->whereNotNull('birth_weight_kg')->avg('birth_weight_kg'), 2) : 0;

        $lscsCount = $allDeliveries->filter(function ($a) {
            $proc = strtolower($a->procedure_done . ' ' . $a->procedure_name);
            return str_contains($proc, 'lscs') || str_contains($proc, 'caesarean') || str_contains($proc, 'cesarean');
        })->count();

        $normalCount = $allDeliveries->filter(function ($a) {
            $proc = strtolower($a->procedure_done . ' ' . $a->procedure_name);
            return str_contains($proc, 'normal') || str_contains($proc, 'vaginal') || str_contains($proc, 'ftnd');
        })->count();

        $metrics = [
            'total_deliveries' => $totalDeliveries,
            'total_babies' => $totalBabies,
            'male_babies' => $maleBabies,
            'female_babies' => $femaleBabies,
            'avg_weight_kg' => $avgWeightKg,
            'lscs_count' => $lscsCount,
            'normal_count' => $normalCount,
        ];

        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 200) $perPage = 25;

        $deliveries = $query->paginate($perPage)->withQueryString();
        $filters = $request->only(['date_from', 'date_to', 'search', 'delivery_type', 'doctor', 'baby_sex', 'per_page']);
        $filters['per_page'] = $perPage;

        $clinicalStaff = ClinicalStaff::cachedActive();

        return Inertia::render('Reports/BirthReport', compact('deliveries', 'metrics', 'filters', 'clinicalStaff'));
    }

    /**
     * 2. Surgery Report
     */
    public function surgery(Request $request)
    {
        $query = Admission::query()
            ->where(function ($q) {
                $q->whereIn('surgery_type', ['other', 'delivery'])
                  ->orWhereNotNull('procedure_done')
                  ->orWhereNotNull('procedure_name');
            })
            ->latest('procedure_at')
            ->latest('admission_at');

        // Date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('admission_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('admission_at', '<=', $dateTo);
        }

        // Search
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('uhid', 'like', "%{$search}%")
                  ->orWhere('ip_no', 'like', "%{$search}%")
                  ->orWhere('procedure_name', 'like', "%{$search}%")
                  ->orWhere('procedure_done', 'like', "%{$search}%")
                  ->orWhere('surgeon', 'like', "%{$search}%");
            });
        }

        // Surgeon
        if ($surgeon = $request->input('surgeon')) {
            $query->where('surgeon', $surgeon);
        }

        // Anaesthetist
        if ($anaesthetist = $request->input('anaesthetist')) {
            $query->where('anaesthetist', $anaesthetist);
        }

        // Anesthesia Type
        if ($anaesthesiaType = $request->input('anaesthesia_type')) {
            $query->where('anaesthesia_type', $anaesthesiaType);
        }

        // Format
        if ($format = $request->input('format')) {
            $query->where('format', $format);
        }

        // Handle CSV Export
        if ($request->input('export') === 'csv') {
            return $this->exportSurgeryCsv($query->get());
        }

        // Summary metrics
        $metricsQuery = clone $query;
        $allSurgeries = $metricsQuery->get();
        $totalSurgeries = $allSurgeries->count();

        $avgBloodLoss = $totalSurgeries > 0
            ? round($allSurgeries->whereNotNull('blood_loss_ml')->avg('blood_loss_ml'), 1)
            : 0;

        $stentPlacedCount = $allSurgeries->whereNotNull('stent_removal_date')->count();

        $topSurgeons = $allSurgeries->whereNotNull('surgeon')
            ->groupBy('surgeon')
            ->map->count()
            ->sortDesc()
            ->take(5)
            ->toArray();

        $anesthesiaBreakdown = $allSurgeries->whereNotNull('anaesthesia_type')
            ->groupBy('anaesthesia_type')
            ->map->count()
            ->sortDesc()
            ->take(5)
            ->toArray();

        $metrics = [
            'total_surgeries' => $totalSurgeries,
            'avg_blood_loss_ml' => $avgBloodLoss,
            'stent_placed_count' => $stentPlacedCount,
            'top_surgeons' => $topSurgeons,
            'anesthesia_breakdown' => $anesthesiaBreakdown,
        ];

        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 200) $perPage = 25;

        $surgeries = $query->paginate($perPage)->withQueryString();
        $filters = $request->only(['date_from', 'date_to', 'search', 'surgeon', 'anaesthetist', 'anaesthesia_type', 'format', 'per_page']);
        $filters['per_page'] = $perPage;

        $clinicalStaff = ClinicalStaff::cachedActive();
        $anesthesiaTypes = AnesthesiaType::cachedActive();

        return Inertia::render('Reports/SurgeryReport', compact('surgeries', 'metrics', 'filters', 'clinicalStaff', 'anesthesiaTypes'));
    }

    /**
     * 3. Medical Case Report
     */
    public function medical(Request $request)
    {
        $query = Admission::query()
            ->where('surgery_type', 'medical')
            ->latest('admission_at');

        // Date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('admission_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('admission_at', '<=', $dateTo);
        }

        // Search
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('uhid', 'like', "%{$search}%")
                  ->orWhere('ip_no', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhere('consultant', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Department
        if ($dept = $request->input('department')) {
            $query->where('department', $dept);
        }

        // Consultant
        if ($consultant = $request->input('consultant')) {
            $query->where('consultant', $consultant);
        }

        // Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Payment / Insurance Mode
        if ($paymentMode = $request->input('payment_mode')) {
            $query->where('payment_mode', $paymentMode);
        }

        // Handle CSV Export
        if ($request->input('export') === 'csv') {
            return $this->exportMedicalCsv($query->get());
        }

        // Summary metrics
        $metricsQuery = clone $query;
        $allMedical = $metricsQuery->get();
        $totalCases = $allMedical->count();

        $activeInpatients = $allMedical->filter(fn ($a) => empty($a->discharge_at))->count();
        $dischargedCases = $allMedical->filter(fn ($a) => !empty($a->discharge_at))->count();

        // Calculate Average Length of Stay in Days
        $stays = $allMedical->filter(fn ($a) => !empty($a->admission_at) && !empty($a->discharge_at))
            ->map(function ($a) {
                return Carbon::parse($a->admission_at)->diffInDays(Carbon::parse($a->discharge_at)) ?: 1;
            });
        $avgStayDays = $stays->count() > 0 ? round($stays->avg(), 1) : 0;

        $topDepartments = $allMedical->whereNotNull('department')
            ->groupBy('department')
            ->map->count()
            ->sortDesc()
            ->take(5)
            ->toArray();

        $metrics = [
            'total_medical_cases' => $totalCases,
            'active_inpatient_count' => $activeInpatients,
            'discharged_count' => $dischargedCases,
            'avg_stay_days' => $avgStayDays,
            'top_departments' => $topDepartments,
        ];

        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 200) $perPage = 25;

        $medicalCases = $query->paginate($perPage)->withQueryString();
        $filters = $request->only(['date_from', 'date_to', 'search', 'department', 'consultant', 'status', 'payment_mode', 'per_page']);
        $filters['per_page'] = $perPage;

        $clinicalStaff = ClinicalStaff::cachedActive();

        return Inertia::render('Reports/MedicalReport', compact('medicalCases', 'metrics', 'filters', 'clinicalStaff'));
    }

    /**
     * 4. Month Wise Report
     */
    public function monthWise(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        if ($year < 2020 || $year > 2040) {
            $year = (int) date('Y');
        }

        $month = $request->input('month', 'all');

        // Available years for dropdown
        $minYear = Admission::min('admission_at') ? Carbon::parse(Admission::min('admission_at'))->year : $year;
        $availableYears = range(max($minYear, 2024), max($year, (int) date('Y') + 1));

        // Month-by-month stats
        $yearAdmissions = Admission::query()
            ->whereYear('admission_at', $year)
            ->get();

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $monthlyStats = [];
        for ($m = 1; $m <= 12; $m++) {
            $subset = $yearAdmissions->filter(function ($a) use ($m) {
                return $a->admission_at && Carbon::parse($a->admission_at)->month === $m;
            });

            $deliveries = $subset->where('surgery_type', 'delivery')->count();
            $surgeries = $subset->where('surgery_type', 'other')->count();
            $medical = $subset->where('surgery_type', 'medical')->count();
            $discharged = $subset->filter(fn ($a) => !empty($a->discharge_at))->count();
            $insurance = $subset->filter(fn ($a) => !empty($a->payment_mode) && $a->payment_mode !== 'cash' && $a->payment_mode !== 'self')->count();
            $cash = $subset->filter(fn ($a) => empty($a->payment_mode) || $a->payment_mode === 'cash' || $a->payment_mode === 'self')->count();

            $monthlyStats[] = [
                'month_num' => $m,
                'month_name' => $monthNames[$m],
                'total_admissions' => $subset->count(),
                'deliveries_count' => $deliveries,
                'surgeries_count' => $surgeries,
                'medical_count' => $medical,
                'discharged_count' => $discharged,
                'insurance_count' => $insurance,
                'cash_count' => $cash,
            ];
        }

        // Summary totals for selected year
        $yearSummary = [
            'year' => $year,
            'total_admissions' => $yearAdmissions->count(),
            'total_deliveries' => $yearAdmissions->where('surgery_type', 'delivery')->count(),
            'total_surgeries' => $yearAdmissions->where('surgery_type', 'other')->count(),
            'total_medical' => $yearAdmissions->where('surgery_type', 'medical')->count(),
            'total_discharged' => $yearAdmissions->filter(fn ($a) => !empty($a->discharge_at))->count(),
        ];

        // Specific month query or all
        $listQuery = Admission::query()
            ->summaryColumns()
            ->whereYear('admission_at', $year)
            ->latest('admission_at');

        if ($month !== 'all' && is_numeric($month)) {
            $listQuery->whereMonth('admission_at', (int) $month);
        }

        if ($search = trim((string) $request->input('search'))) {
            $listQuery->filterSearch($search, true);
        }

        if ($workflow = $request->input('surgery_type')) {
            $listQuery->where('surgery_type', $workflow);
        }

        if ($status = $request->input('status')) {
            $listQuery->where('status', $status);
        }

        // CSV Export
        if ($request->input('export') === 'csv') {
            return $this->exportMonthWiseCsv($listQuery->get(), $year, $month !== 'all' ? $monthNames[(int) $month] : 'All_Months');
        }

        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 200) $perPage = 25;

        $admissions = $listQuery->paginate($perPage)->withQueryString();

        $filters = $request->only(['year', 'month', 'search', 'surgery_type', 'status', 'per_page']);
        $filters['year'] = $year;
        $filters['month'] = $month;
        $filters['per_page'] = $perPage;

        return Inertia::render('Reports/MonthWiseReport', compact(
            'monthlyStats',
            'yearSummary',
            'admissions',
            'filters',
            'availableYears'
        ));
    }

    /**
     * CSV Exporter for Birth Reports
     */
    protected function exportBirthCsv($admissions): StreamedResponse
    {
        $filename = 'birth_report_' . date('Y-m-d_His') . '.csv';
        return response()->streamDownload(function () use ($admissions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Admission ID',
                'Mother UHID',
                'IP Number',
                'Mother Full Name',
                'Age',
                'Admission Date',
                'Delivery Date & Time',
                'Delivery Procedure',
                'Obstetrician / Surgeon',
                'Consultant Doctor',
                'Pediatrician',
                'Total Babies',
                'Baby 1 Sex',
                'Baby 1 Birth Weight (kg)',
                'Baby 1 APGAR 1m',
                'Baby 1 APGAR 5m',
                'Baby 1 Neonatal Care',
                'Baby 2 Sex',
                'Baby 2 Birth Weight (kg)',
            ]);

            foreach ($admissions as $adm) {
                $b1 = $adm->newborns->first();
                $b2 = $adm->newborns->skip(1)->first();

                fputcsv($handle, [
                    $adm->id,
                    $adm->uhid,
                    $adm->ip_no,
                    $adm->patient_name,
                    $adm->age,
                    $adm->admission_at ? Carbon::parse($adm->admission_at)->format('d.m.Y H:i') : '',
                    $adm->procedure_at ? Carbon::parse($adm->procedure_at)->format('d.m.Y H:i') : ($b1 && $b1->birth_at ? Carbon::parse($b1->birth_at)->format('d.m.Y H:i') : ''),
                    $adm->procedure_done ?: $adm->procedure_name ?: 'Delivery',
                    $adm->surgeon,
                    $adm->consultant,
                    $adm->pediatrician_name,
                    $adm->newborns->count(),
                    $b1 ? ucfirst((string)$b1->sex) : '',
                    $b1 ? $b1->birth_weight_kg : '',
                    $b1 ? $b1->apgar_1_min : '',
                    $b1 ? $b1->apgar_5_min : '',
                    $b1 ? $b1->neonatal_care : '',
                    $b2 ? ucfirst((string)$b2->sex) : '',
                    $b2 ? $b2->birth_weight_kg : '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * CSV Exporter for Surgery Reports
     */
    protected function exportSurgeryCsv($surgeries): StreamedResponse
    {
        $filename = 'surgery_report_' . date('Y-m-d_His') . '.csv';
        return response()->streamDownload(function () use ($surgeries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Admission ID',
                'UHID',
                'IP Number',
                'Patient Name',
                'Age',
                'Sex',
                'Surgery Date & Time',
                'Procedure Done',
                'Incision',
                'Primary Surgeon',
                'Assistant Surgeon',
                'Anaesthetist',
                'Anesthesia Type',
                'Blood Loss (ml)',
                'Findings',
                'Specimen Sent',
                'Drain Placed',
                'Stent Removal Date',
                'Status',
            ]);

            foreach ($surgeries as $surg) {
                fputcsv($handle, [
                    $surg->id,
                    $surg->uhid,
                    $surg->ip_no,
                    $surg->patient_name,
                    $surg->age,
                    $surg->sex,
                    $surg->procedure_at ? Carbon::parse($surg->procedure_at)->format('d.m.Y H:i') : ($surg->admission_at ? Carbon::parse($surg->admission_at)->format('d.m.Y') : ''),
                    $surg->procedure_done ?: $surg->procedure_name ?: 'Surgical Procedure',
                    $surg->incision,
                    $surg->surgeon,
                    $surg->assisted_surgeon,
                    $surg->anaesthetist,
                    $surg->anaesthesia_type,
                    $surg->blood_loss_ml,
                    $surg->findings,
                    $surg->specimen,
                    $surg->drain,
                    $surg->stent_removal_date ? Carbon::parse($surg->stent_removal_date)->format('d.m.Y') : '',
                    $surg->status,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * CSV Exporter for Medical Case Reports
     */
    protected function exportMedicalCsv($cases): StreamedResponse
    {
        $filename = 'medical_case_report_' . date('Y-m-d_His') . '.csv';
        return response()->streamDownload(function () use ($cases) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Admission ID',
                'UHID',
                'IP Number',
                'Patient Name',
                'Age',
                'Sex',
                'Admission Date',
                'Discharge Date',
                'Length of Stay (Days)',
                'Provisional / Final Diagnosis',
                'Consultant Doctor',
                'Department',
                'Ward / Room',
                'Condition at Discharge',
                'Discharge BP',
                'Discharge Pulse',
                'Discharge SpO2',
                'Payment Mode',
                'Status',
            ]);

            foreach ($cases as $case) {
                $los = ($case->admission_at && $case->discharge_at)
                    ? (Carbon::parse($case->admission_at)->diffInDays(Carbon::parse($case->discharge_at)) ?: 1)
                    : '';

                fputcsv($handle, [
                    $case->id,
                    $case->uhid,
                    $case->ip_no,
                    $case->patient_name,
                    $case->age,
                    $case->sex,
                    $case->admission_at ? Carbon::parse($case->admission_at)->format('d.m.Y') : '',
                    $case->discharge_at ? Carbon::parse($case->discharge_at)->format('d.m.Y') : '',
                    $los,
                    $case->diagnosis,
                    $case->consultant,
                    $case->department,
                    $case->ward_room_no,
                    $case->discharge_condition,
                    $case->discharge_bp,
                    $case->discharge_pulse,
                    $case->discharge_spo2,
                    $case->payment_mode,
                    $case->status,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * CSV Exporter for Month-Wise Reports
     */
    protected function exportMonthWiseCsv($admissions, int $year, string $period): StreamedResponse
    {
        $filename = "month_wise_report_{$year}_{$period}_" . date('Y-m-d_His') . '.csv';
        return response()->streamDownload(function () use ($admissions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Admission ID',
                'UHID',
                'IP Number',
                'Patient Name',
                'Age',
                'Sex',
                'Format',
                'Clinical Workflow',
                'Admission Date',
                'Discharge Date',
                'Consultant Doctor',
                'Department',
                'Ward / Room',
                'Status',
            ]);

            foreach ($admissions as $adm) {
                fputcsv($handle, [
                    $adm->id,
                    $adm->uhid,
                    $adm->ip_no,
                    $adm->patient_name,
                    $adm->age,
                    $adm->sex,
                    $adm->format,
                    $adm->surgery_type,
                    $adm->admission_at ? Carbon::parse($adm->admission_at)->format('d.m.Y') : '',
                    $adm->discharge_at ? Carbon::parse($adm->discharge_at)->format('d.m.Y') : '',
                    $adm->consultant,
                    $adm->department,
                    $adm->ward_room_no,
                    $adm->status,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Format vital measurements concisely for reports and CSV export.
     */
    protected function formatVitalsText(?string $bp, ?string $pulse, ?string $spo2): string
    {
        return implode(' | ', array_filter([
            $bp ? "BP: {$bp}" : null,
            $pulse ? "Pulse: {$pulse}" : null,
            $spo2 ? "SpO2: {$spo2}" : null,
        ]));
    }

    /**
     * CSV Exporter for Complete Daily Report (Admissions, Discharges, and Inpatients)
     */
    protected function exportDayCsv(array $sections, string $sectionFilter, string $label): StreamedResponse
    {
        $safeLabel = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $label) ?: date('Y-m-d');
        $filename = "day_report_{$safeLabel}_" . date('Y-m-d_His') . '.csv';

        $sectionMap = [
            'admissions' => '1. Admission',
            'discharges' => '2. Discharge',
            'inpatients' => '3. Inpatient',
        ];
        $activeKeys = $sectionFilter === 'all' ? array_keys($sectionMap) : [$sectionFilter];

        return response()->streamDownload(function () use ($sections, $sectionMap, $activeKeys) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'S.No',
                'Name',
                'UHID',
                'IPNO',
                'DOA',
                'DOO',
                'DOD',
                'Procedure Done & Incision',
                'Surgical & Anesthesia Team',
                'Delivery Details',
                'Baby Details',
                'Day Section',
                'MRD File No',
                'Age / Sex',
                'Attender & Relation',
                'Contact',
                'Address',
                'Department / Ward',
                'Consultant',
                'Workflow',
                'Format & Payment / Insurance',
                'Diagnosis & ICD',
                'Admission Vitals',
                'Stay Days',
                'Discharge Condition & Vitals',
                'Discharge Advice / Follow-Up / Removal Dates',
                'Handover / Floor / MRD Incharge & Status',
            ]);

            $serial = 0;
            foreach ($activeKeys as $key) {
                $sectionLabel = $sectionMap[$key] ?? ucfirst($key);
                $items = $sections[$key] ?? collect();

                foreach ($items as $adm) {
                    $serial++;
                    $procParts = array_filter([
                        $adm->procedure_done ?: $adm->procedure_name,
                        $adm->incision ? 'Incision: ' . $adm->incision : null,
                    ]);
                    if (empty($procParts) && $adm->surgery_type === 'medical') {
                        $procParts[] = 'Medical Management' . ($adm->diagnosis ? ' (' . $adm->diagnosis . ')' : '');
                    }

                    $teamParts = array_filter([
                        $adm->surgeon ? 'Surgeon: ' . $adm->surgeon : null,
                        $adm->assisted_surgeon ? 'Asst. Surgeon: ' . $adm->assisted_surgeon : null,
                        $adm->consultant ? 'Consultant: ' . $adm->consultant : null,
                        $adm->pediatrician_name ? 'Pediatrician: ' . $adm->pediatrician_name : null,
                        $adm->anaesthetist ? 'Anaesthetist: ' . $adm->anaesthetist : null,
                        $adm->assisted_anaesthetist ? 'Asst. Anaesthetist: ' . $adm->assisted_anaesthetist : null,
                        $adm->anaesthesia_type ? 'Anaesthesia: ' . $adm->anaesthesia_type : null,
                        $adm->staff_nurse ? 'Staff Nurse: ' . $adm->staff_nurse : null,
                        $adm->ot_technician ? 'OT Tech: ' . $adm->ot_technician : null,
                    ]);

                    $isDelivery = $adm->surgery_type === 'delivery' || $adm->newborns->isNotEmpty();
                    $deliveryDetails = '';
                    $babyDetails = '';

                    if ($isDelivery) {
                        $deliveryDetails = implode(' | ', array_filter([
                            $adm->procedure_done ?: $adm->procedure_name ?: 'Delivery',
                            $adm->picme_no ? 'PICME/RCH: ' . $adm->picme_no : null,
                            $adm->vhn_name ? 'VHN: ' . $adm->vhn_name : null,
                            $adm->phc ? 'PHC: ' . $adm->phc : null,
                            $adm->pediatrician_name ? 'Pediatrician: ' . $adm->pediatrician_name : null,
                        ]));

                        $babyDetails = $adm->newborns->map(function ($baby, $bIdx) {
                            $formattedVax = Newborn::formatBirthVaccinesText(
                                $baby->birth_vaccines,
                                $baby->vaccine_dates,
                                $baby->vaccine_date
                            );
                            $vaxStr = $formattedVax !== '' ? 'Vaccines: ' . $formattedVax : null;

                            return 'Baby ' . ($bIdx + 1) . ': ' . implode(', ', array_filter([
                                $baby->sex ?: null,
                                $baby->birth_at ? 'Born ' . Carbon::parse($baby->birth_at)->format('d.m.Y h:i A') : null,
                                $baby->birth_weight_kg ? 'Birth Wt ' . number_format((float) $baby->birth_weight_kg, 2) . ' kg' : null,
                                $baby->discharge_weight_kg ? 'Disch Wt ' . number_format((float) $baby->discharge_weight_kg, 2) . ' kg' : null,
                                ($baby->apgar_1_min !== null || $baby->apgar_5_min !== null)
                                    ? 'APGAR ' . ($baby->apgar_1_min ?? '-') . '/' . ($baby->apgar_5_min ?? '-')
                                    : null,
                                $baby->neonatal_care ? 'Care: ' . $baby->neonatal_care : null,
                                $baby->notes ? 'Notes: ' . $baby->notes : null,
                                $vaxStr,
                            ]));
                        })->implode(' || ');
                    }

                    $attenderText = implode(' ', array_filter([
                        $adm->attender_name,
                        $adm->attender_relation ? '(' . $adm->attender_relation . ')' : null,
                    ]));
                    $deptWard = implode(' / ', array_filter([$adm->department, $adm->ward_room_no]));
                    $diagIcd = implode(' | ', array_filter([
                        $adm->diagnosis,
                        $adm->icd_code ? 'ICD: ' . $adm->icd_code : null,
                    ]));
                    $admitVitals = $this->formatVitalsText($adm->vital_bp, $adm->vital_pulse, $adm->vital_spo2);
                    $dischVitals = $this->formatVitalsText($adm->discharge_bp, $adm->discharge_pulse, $adm->discharge_spo2);
                    $dischConditionSummary = implode(' | ', array_filter([
                        $adm->discharge_condition,
                        $dischVitals ?: null,
                    ]));
                    $adviceFollowup = implode(' | ', array_filter([
                        $adm->discharge_advice,
                        $adm->post_surgery_diet ? 'Diet: ' . $adm->post_surgery_diet : null,
                        $adm->post_surgery_activity ? 'Activity: ' . $adm->post_surgery_activity : null,
                        $adm->follow_up ? 'Follow-up: ' . $adm->follow_up : null,
                        $adm->suture_removal_date ? 'Suture Removal: ' . Carbon::parse($adm->suture_removal_date)->format('d.m.Y') : null,
                        $adm->stent_removal_date ? 'Stent Removal: ' . Carbon::parse($adm->stent_removal_date)->format('d.m.Y') : null,
                    ]));
                    $inchargeStatus = implode(' | ', array_filter([
                        $adm->handover_name ? 'Handover: ' . $adm->handover_name : null,
                        $adm->floor_incharge_name ? 'Floor: ' . $adm->floor_incharge_name : null,
                        $adm->mrd_incharge_name ? 'MRD: ' . $adm->mrd_incharge_name : null,
                        $adm->status ? 'Status: ' . strtoupper(str_replace('_', ' ', $adm->status)) : null,
                    ]));
                    $stayDaysVal = $key === 'inpatients'
                        ? ($adm->census_stay_days ?? $adm->stay_days)
                        : $adm->stay_days;

                    fputcsv($handle, [
                        $serial,
                        $adm->patient_name,
                        $adm->uhid,
                        $adm->ip_no,
                        $adm->admission_at ? Carbon::parse($adm->admission_at)->format('d.m.Y h:i A') : '',
                        $adm->procedure_at ? Carbon::parse($adm->procedure_at)->format('d.m.Y h:i A') : '',
                        $adm->discharge_at ? Carbon::parse($adm->discharge_at)->format('d.m.Y h:i A') : '',
                        implode(' | ', $procParts),
                        implode(' | ', $teamParts),
                        $deliveryDetails,
                        $babyDetails,
                        $sectionLabel,
                        $adm->resolved_mrd_no,
                        implode(' / ', array_filter([$adm->age ? $adm->age . ' Yrs' : null, $adm->sex])),
                        $attenderText,
                        $adm->contact,
                        $adm->address,
                        $deptWard,
                        $adm->consultant,
                        strtoupper((string) $adm->surgery_type),
                        trim(strtoupper((string) $adm->format) . ($adm->payment_summary && $adm->payment_summary !== '—' ? ' — ' . $adm->payment_summary : '')),
                        $diagIcd,
                        $admitVitals,
                        $stayDaysVal ? $stayDaysVal . ' day(s)' : '',
                        $dischConditionSummary,
                        $adviceFollowup,
                        $inchargeStatus,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Full-Page High-Resolution PNG / A4 Landscape PDF Exporter for Reports
     */
    protected function streamPlaywrightReport(
        string $view,
        array $viewData,
        string $filenamePrefix,
        string $title,
        string $formatType,
        DocumentPackPdfService $pdfService
    ): Response {
        $isPdf = $formatType === 'pdf';
        $ext = $isPdf ? 'pdf' : 'png';
        $contentType = $isPdf ? 'application/pdf' : 'image/png';
        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $filenamePrefix) ?: 'report';
        $filename = "{$safePrefix}_" . date('Y-m-d_His') . ".{$ext}";

        $logoPath = public_path('images/guru-hospital-logo.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath))
            : null;

        $html = view($view, array_merge($viewData, [
            'logoDataUri' => $logoDataUri,
            'isPdf' => $isPdf,
        ]))->render();

        $bytes = $isPdf
            ? $pdfService->generateReportPdfWithPlaywright($html, $title)
            : $pdfService->generateReportImageWithPlaywright($html, $title);

        return response($bytes, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($bytes),
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * CSV Exporter for MRD Report
     */
    protected function exportMrdCsv($admissions): StreamedResponse
    {
        $filename = 'mrd_report_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($admissions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'S.No',
                'MRD File No',
                'Patient Name',
                'UHID',
                'IPNO',
                'Age',
                'Sex',
                'DOA',
                'DOD',
                'LOS (Days)',
                'Diagnosis',
                'ICD Code',
                'Workflow',
                'Procedure Done',
                'Consultant',
                'Department',
                'Format',
                'Payment / Insurance',
                'Handover Name',
                'Floor Incharge',
                'MRD Incharge',
                'Status',
            ]);

            foreach ($admissions->values() as $idx => $adm) {
                $los = ($adm->admission_at && $adm->discharge_at)
                    ? max(1, Carbon::parse($adm->admission_at)->diffInDays(Carbon::parse($adm->discharge_at)))
                    : '';

                fputcsv($handle, [
                    $idx + 1,
                    $adm->resolved_mrd_no,
                    $adm->patient_name,
                    $adm->uhid,
                    $adm->ip_no,
                    $adm->age,
                    $adm->sex,
                    $adm->admission_at ? Carbon::parse($adm->admission_at)->format('d.m.Y') : '',
                    $adm->discharge_at ? Carbon::parse($adm->discharge_at)->format('d.m.Y') : '',
                    $los,
                    $adm->diagnosis,
                    $adm->icd_code,
                    $adm->surgery_type,
                    $adm->procedure_done ?: $adm->procedure_name,
                    $adm->consultant,
                    $adm->department,
                    $adm->format,
                    $adm->payment_summary,
                    $adm->handover_name,
                    $adm->floor_incharge_name,
                    $adm->mrd_incharge_name,
                    $adm->status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
