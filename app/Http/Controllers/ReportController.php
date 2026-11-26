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
     * Day Wise / Daily Clinical Register Report
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

        if ($dateFrom || $dateTo) {
            $query->where(function ($q) use ($dateColumns, $dateFrom, $dateTo) {
                foreach ($dateColumns as $col) {
                    $q->orWhere(function ($sub) use ($col, $dateFrom, $dateTo) {
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
            $query->where(function ($q) use ($dateColumns, $selectedDate) {
                foreach ($dateColumns as $col) {
                    $q->orWhereDate($col, $selectedDate);
                }
            });
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->filterSearch($search, true);
        }

        if ($workflow = $request->input('surgery_type')) {
            $query->where('surgery_type', $workflow);
        }

        if ($format = $request->input('format')) {
            $query->where('format', $format);
        }

        if ($surgeon = $request->input('surgeon')) {
            $query->where(function ($q) use ($surgeon) {
                $q->where('surgeon', $surgeon)
                    ->orWhere('consultant', $surgeon);
            });
        }

        if ($request->input('export') === 'csv') {
            $label = $allDates
                ? 'all_dates'
                : (($dateFrom || $dateTo) ? trim(($dateFrom ?: 'start') . '_to_' . ($dateTo ?: 'end')) : $selectedDate);
            return $this->exportDayCsv($query->get(), $label);
        }

        $allMatches = (clone $query)->get();
        $deliveries = $allMatches->filter(fn ($a) => $a->surgery_type === 'delivery' || $a->newborns->isNotEmpty());
        $metrics = [
            'total_cases' => $allMatches->count(),
            'surgeries_count' => $allMatches->where('surgery_type', 'other')->count(),
            'deliveries_count' => $deliveries->count(),
            'babies_count' => $deliveries->flatMap->newborns->count(),
            'medical_count' => $allMatches->where('surgery_type', 'medical')->count(),
            'discharges_count' => $allMatches->filter(fn ($a) => !empty($a->discharge_at))->count(),
        ];

        if (in_array($request->input('export'), ['image', 'pdf'], true)) {
            $exportFormat = (string) $request->input('export');
            $label = $allDates
                ? 'all_dates'
                : (($dateFrom || $dateTo) ? trim(($dateFrom ?: 'start') . '_to_' . ($dateTo ?: 'end')) : $selectedDate);
            $periodLabel = $allDates
                ? 'All Dates'
                : (($dateFrom || $dateTo)
                    ? trim(($dateFrom ? Carbon::parse($dateFrom)->format('d.m.Y') : 'Start') . ' to ' . ($dateTo ? Carbon::parse($dateTo)->format('d.m.Y') : 'End'))
                    : 'Date: ' . Carbon::parse($selectedDate)->format('d.m.Y'));
            $basisLabel = match ($dateBasis) {
                'admission' => 'DOA Only',
                'procedure' => 'DOO Only',
                'discharge' => 'DOD Only',
                default => 'Any (DOA / DOO / DOD)',
            };

            return $this->exportDayRendered($allMatches, $metrics, $label, $periodLabel, $basisLabel, $exportFormat, $pdfService);
        }

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage < 5 || $perPage > 200) {
            $perPage = 50;
        }

        $records = $query->paginate($perPage)->withQueryString();

        $filters = [
            'date' => $selectedDate,
            'date_from' => $dateFrom ?: '',
            'date_to' => $dateTo ?: '',
            'date_basis' => $dateBasis,
            'all_dates' => $allDates,
            'search' => $request->input('search', ''),
            'surgery_type' => $request->input('surgery_type', ''),
            'format' => $request->input('format', ''),
            'surgeon' => $request->input('surgeon', ''),
            'per_page' => $perPage,
        ];

        $clinicalStaff = ClinicalStaff::cachedActive();

        return Inertia::render('Reports/DayReport', compact('records', 'metrics', 'filters', 'clinicalStaff'));
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
     * CSV Exporter for Day Report
     */
    protected function exportDayCsv($admissions, string $label): StreamedResponse
    {
        $safeLabel = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $label) ?: date('Y-m-d');
        $filename = "day_report_{$safeLabel}_" . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($admissions) {
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
            ]);

            foreach ($admissions->values() as $idx => $adm) {
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

                fputcsv($handle, [
                    $idx + 1,
                    $adm->patient_name,
                    $adm->uhid,
                    $adm->ip_no,
                    $adm->admission_at ? Carbon::parse($adm->admission_at)->format('d.m.Y') : '',
                    $adm->procedure_at ? Carbon::parse($adm->procedure_at)->format('d.m.Y h:i A') : '',
                    $adm->discharge_at ? Carbon::parse($adm->discharge_at)->format('d.m.Y') : '',
                    implode(' | ', $procParts),
                    implode(' | ', $teamParts),
                    $deliveryDetails,
                    $babyDetails,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Full-Page High-Resolution PNG / A4 Landscape PDF Exporter for Day Report
     */
    protected function exportDayRendered(
        $records,
        array $metrics,
        string $label,
        string $periodLabel,
        string $basisLabel,
        string $formatType,
        DocumentPackPdfService $pdfService
    ): Response {
        $isPdf = $formatType === 'pdf';
        $ext = $isPdf ? 'pdf' : 'png';
        $contentType = $isPdf ? 'application/pdf' : 'image/png';
        $safeLabel = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $label) ?: date('Y-m-d');
        $filename = "day_report_{$safeLabel}_" . date('Y-m-d_His') . ".{$ext}";

        $logoPath = public_path('images/guru-hospital-logo.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath))
            : null;

        $html = view('pdf.day-report-image', [
            'records' => $records,
            'metrics' => $metrics,
            'periodLabel' => $periodLabel,
            'basisLabel' => $basisLabel,
            'logoDataUri' => $logoDataUri,
            'isPdf' => $isPdf,
        ])->render();

        $title = "GURU HOSPITAL — Day Report ({$periodLabel})";
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
}
