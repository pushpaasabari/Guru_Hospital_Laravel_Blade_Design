<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdmissionRequest;
use App\Http\Requests\UpdateAdmissionRequest;
use App\Http\Requests\UpdateSurgeryRequest;
use App\Http\Requests\UpdateTestReportsRequest;
use App\Http\Requests\UpdateDischargeRequest;
use App\Models\Admission;
use App\Models\AnesthesiaType;
use App\Models\ClinicalDropdownOption;
use App\Models\ClinicalStaff;
use App\Models\DocumentRevision;
use App\Models\Newborn;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Admission::class);

        $query = Admission::query()
            ->summaryColumns()
            ->filterSearch($request->input('search'))
            ->latest('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $admissions = $query->paginate(20)->withQueryString();
        $filters = $request->only(['search', 'status']);

        return Inertia::render('Admissions/Index', compact('admissions', 'filters'));
    }

    public function create(): Response
    {
        $this->authorize('create', Admission::class);
        $clinicalStaff = ClinicalStaff::cachedActive();
        $anesthesiaTypes = AnesthesiaType::cachedActive();

        return Inertia::render('Admissions/Create', compact('clinicalStaff', 'anesthesiaTypes'));
    }

    public function store(StoreAdmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $newborns = $validated['newborns'] ?? [];
        unset($validated['newborns']);

        $validated = $this->resolveAssistedBy($validated);
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'draft';

        $admission = DB::transaction(function () use ($validated, $newborns) {
            $admission = Admission::create($validated);
            $this->syncNewborns($admission, $newborns, false);

            AuditService::log(
                'admission.created',
                'Admission',
                $admission->id,
                "New admission created for {$admission->patient_name} (UHID: {$admission->uhid}, IP: {$admission->ip_no})",
                null,
                $admission->toArray()
            );

            return $admission;
        });

        $redirectTo = $request->input('redirect_to', $request->input('next_step', 'clinical'));
        if ($redirectTo === 'preview') {
            return redirect()->route('documents.preview', $admission)
                ->with('status', 'Draft admission saved successfully.');
        }
        if ($redirectTo === 'test-reports') {
            return redirect()->route('admissions.test-reports', $admission)
                ->with('status', 'Admission details saved successfully. Proceed to Laboratory & Diagnostic Test Reports.');
        }
        if ($redirectTo === 'surgery') {
            return redirect()->route('admissions.surgery', $admission)
                ->with('status', 'Admission details saved successfully. Proceed to Labour / LSCS / Surgery.');
        }

        return redirect()->route('admissions.clinical', $admission)
            ->with('status', 'Admission details saved successfully. Proceed to Clinical Assessment.');
    }

    public function show(Request $request, Admission $admission): Response
    {
        return app(DocumentPackController::class)->preview($request, $admission);
    }

    public function edit(Admission $admission): Response
    {
        $this->authorize('update', $admission);
        $admission->load('newborns');
        $clinicalStaff = ClinicalStaff::cachedActive();
        $anesthesiaTypes = AnesthesiaType::cachedActive();

        return Inertia::render('Admissions/Edit', compact('admission', 'clinicalStaff', 'anesthesiaTypes'));
    }

    public function update(UpdateAdmissionRequest $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validated();
        $newborns = $validated['newborns'] ?? [];
        unset($validated['newborns']);

        $validated = $this->resolveAssistedBy($validated);
        $oldData = $admission->toArray();

        DB::transaction(function () use ($admission, $validated, $newborns, $oldData) {
            $admission->update($validated);
            $this->syncNewborns($admission, $newborns);

            AuditService::log(
                'admission.updated',
                'Admission',
                $admission->id,
                "Admission updated for {$admission->patient_name}",
                $oldData,
                $admission->toArray()
            );
        });

        $nextStep = $request->input('next_step', 'clinical');
        if ($nextStep === 'stay') {
            return redirect()->route('admissions.edit', $admission)
                ->with('status', 'Admission details updated successfully.');
        }
        if ($nextStep === 'preview') {
            return redirect()->route('documents.preview', $admission)
                ->with('status', 'Admission details updated successfully.');
        }
        if ($nextStep === 'test-reports') {
            return redirect()->route('admissions.test-reports', $admission)
                ->with('status', 'Admission details updated. Proceed to Laboratory & Diagnostic Test Reports.');
        }
        if ($nextStep === 'surgery') {
            return redirect()->route('admissions.surgery', $admission)
                ->with('status', 'Admission details updated. Proceed to Labour / LSCS / Surgery.');
        }

        return redirect()->route('admissions.clinical', $admission)
            ->with('status', 'Admission details updated. Proceed to Clinical Assessment.');
    }

    public function editClinical(Admission $admission): Response
    {
        $this->authorize('update', $admission);
        return Inertia::render('Admissions/Clinical', compact('admission'));
    }

    public function updateClinical(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorize('update', $admission);
        $validated = $request->validate([
            'clinical_history' => ['nullable', 'string', 'max:10000'],
            'examination' => ['nullable', 'string', 'max:10000'],
        ]);

        $oldData = $admission->toArray();
        $admission->update($validated);

        AuditService::log(
            'admission.clinical_updated',
            'Admission',
            $admission->id,
            "Clinical assessment updated for {$admission->patient_name}",
            $oldData,
            $admission->toArray()
        );

        $nextStep = $request->input('next_step', 'test-reports');
        if ($nextStep === 'stay') {
            return redirect()->route('admissions.clinical', $admission)
                ->with('status', 'Clinical assessment saved successfully.');
        }
        if ($nextStep === 'admission') {
            return redirect()->route('admissions.edit', $admission)
                ->with('status', 'Clinical assessment saved. Proceed to Admission Details.');
        }
        if ($nextStep === 'preview') {
            return redirect()->route('documents.preview', $admission)
                ->with('status', 'Clinical assessment saved successfully.');
        }

        return redirect()->route('admissions.test-reports', $admission)
            ->with('status', 'Clinical assessment saved. Proceed to Laboratory & Diagnostic Test Reports.');
    }

    public function editSurgery(Admission $admission): Response
    {
        $this->authorize('update', $admission);
        $admission->load('newborns');
        $clinicalStaff = ClinicalStaff::cachedActive();
        $anesthesiaTypes = AnesthesiaType::cachedActive();
        $postopOrderGroups = ClinicalDropdownOption::groupedOptionsFor(
            ClinicalDropdownOption::TYPE_POSTOP_ORDER
        );

        return Inertia::render(
            'Admissions/Surgery',
            compact('admission', 'clinicalStaff', 'anesthesiaTypes', 'postopOrderGroups')
        );
    }

    public function updateSurgery(UpdateSurgeryRequest $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validated();
        $newborns = $validated['newborns'] ?? [];
        unset($validated['newborns']);

        $validated = $this->resolveAssistedBy($validated);
        $oldData = $admission->toArray();

        DB::transaction(function () use ($admission, $validated, $newborns, $oldData) {
            $admission->update($validated);
            $this->syncNewborns($admission, $newborns);

            AuditService::log(
                'admission.surgery_updated',
                'Admission',
                $admission->id,
                "Surgery & OT details updated for {$admission->patient_name}",
                $oldData,
                $admission->toArray()
            );
        });

        if (!empty($validated['postoperative_orders'])) {
            ClinicalDropdownOption::rememberMany(
                ClinicalDropdownOption::TYPE_POSTOP_ORDER,
                ClinicalDropdownOption::extractPostopOrderLines($validated['postoperative_orders']),
                $request->user()?->id
            );
        }

        $nextStep = $request->input('next_step', 'discharge');
        if ($nextStep === 'stay') {
            return redirect()->route('admissions.surgery', $admission)
                ->with('status', 'Surgery / Labour details saved successfully.');
        }
        if ($nextStep === 'preview') {
            return redirect()->route('documents.preview', $admission)
                ->with('status', 'Surgery / Labour details saved successfully.');
        }
        if ($nextStep === 'test-reports') {
            return redirect()->route('admissions.test-reports', $admission)
                ->with('status', 'Surgery details saved. Proceed to Test Reports.');
        }

        return redirect()->route('admissions.discharge', $admission)
            ->with('status', 'Surgery / Labour details saved. Proceed to Discharge Summary.');
    }

    public function editTestReports(Admission $admission): Response
    {
        $this->authorize('update', $admission);
        return Inertia::render('Admissions/TestReports', compact('admission'));
    }

    public function updateTestReports(UpdateTestReportsRequest $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validated();
        $oldData = $admission->toArray();

        $admission->update($validated);

        AuditService::log(
            'admission.test_reports_updated',
            'Admission',
            $admission->id,
            "Test reports / investigations updated for {$admission->patient_name}",
            $oldData,
            $admission->toArray()
        );

        $nextStep = $request->input('next_step', 'surgery');
        if ($nextStep === 'stay') {
            return redirect()->route('admissions.test-reports', $admission)
                ->with('status', 'Test reports saved successfully.');
        }
        if ($nextStep === 'preview') {
            return redirect()->route('documents.preview', $admission)
                ->with('status', 'Test reports saved successfully.');
        }
        if ($nextStep === 'discharge') {
            return redirect()->route('admissions.discharge', $admission)
                ->with('status', 'Test reports saved. Proceed to Discharge Summary.');
        }

        return redirect()->route('admissions.surgery', $admission)
            ->with('status', 'Test reports saved. Proceed to Labour / LSCS / Surgery Notes.');
    }

    public function editDischarge(Admission $admission): Response
    {
        $this->authorize('update', $admission);
        $clinicalStaff = ClinicalStaff::cachedActive();
        $medicationOptions = ClinicalDropdownOption::flatOptionsFor(
            ClinicalDropdownOption::TYPE_MEDICATION
        );

        return Inertia::render(
            'Admissions/Discharge',
            compact('admission', 'clinicalStaff', 'medicationOptions')
        );
    }

    public function updateDischarge(UpdateDischargeRequest $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validated();
        $oldData = $admission->toArray();

        // Stage 4 completion automatically marks the admission as approved and finalized.
        // No separate approval from anyone is required.
        if ($admission->status !== 'generated') {
            $validated['status'] = 'approved';
        }
        if (!$admission->approved_by) {
            $validated['approved_by'] = $request->user()?->id;
            $validated['approved_at'] = now();
        }

        $admission->update($validated);

        if (!empty($validated['medication_orders'])) {
            ClinicalDropdownOption::rememberMany(
                ClinicalDropdownOption::TYPE_MEDICATION,
                ClinicalDropdownOption::extractMedicationNames($validated['medication_orders']),
                $request->user()?->id
            );
        }

        AuditService::log(
            'admission.discharge_updated',
            'Admission',
            $admission->id,
            "Discharge details completed for {$admission->patient_name} (Auto-approved, ready for direct download)",
            $oldData,
            $admission->toArray()
        );

        $nextStep = $request->input('next_step', 'download_ds');

        if ($nextStep === 'stay') {
            return redirect()->route('admissions.discharge', $admission)
                ->with('status', 'Discharge details saved successfully.');
        }

        if ($nextStep === 'preview') {
            return redirect()->route('documents.preview', $admission)
                ->with('status', 'Discharge details saved. Documents are ready for direct download.');
        }

        if ($nextStep === 'download_pack') {
            return redirect()->route('documents.preview', ['admission' => $admission, 'auto_download' => 'pack'])
                ->with('status', 'Discharge completed! Complete Dossier Pack PDF download started.');
        }

        return redirect()->route('documents.preview', ['admission' => $admission, 'auto_download' => 'discharge-summary'])
            ->with('status', 'Discharge completed! Discharge Summary PDF download started.');
    }

    public function createRevision(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorize('createRevision', $admission);

        $request->validate([
            'revision_reason' => ['required', 'string', 'max:1000'],
        ]);

        $reason = $request->input('revision_reason');

        DB::transaction(function () use ($admission, $reason, $request) {
            // Save current state as an immutable revision before unlocking
            $currentRevisionCount = $admission->revisions()->count();
            $newRevisionNumber = $currentRevisionCount + 1;

            DocumentRevision::create([
                'admission_id' => $admission->id,
                'revision_number' => $newRevisionNumber,
                'snapshot_data' => $admission->load('newborns')->toArray(),
                'approved_by' => $admission->approved_by ?? $request->user()->id,
                'approved_at' => $admission->approved_at ?? now(),
                'created_at' => now(),
            ]);

            // Reset status to draft for corrections
            $admission->update([
                'status' => 'draft',
                'rejection_reason' => "Correction requested: {$reason}",
                'approved_by' => null,
                'approved_at' => null,
            ]);

            AuditService::log(
                'admission.revision_started',
                'Admission',
                $admission->id,
                "New revision #{$newRevisionNumber} started by {$request->user()->name}. Reason: {$reason}"
            );
        });

        return redirect()->route('admissions.edit', $admission)
            ->with('status', 'New revision created. You may now modify the clinical records and submit for re-approval.');
    }

    private function resolveAssistedBy(array $validated): array
    {
        if (empty($validated['assisted_by'])) {
            $validated['assisted_by'] = $validated['assisted_surgeon'] ?? ($validated['staff_nurse'] ?? null);
        }

        return $validated;
    }

    private function syncNewborns(Admission $admission, array $newborns, bool $replaceExisting = true): void
    {
        if ($replaceExisting) {
            $admission->newborns()->delete();
        }

        if (!$admission->isDelivery()) {
            return;
        }

        foreach ($newborns as $newborn) {
            $rawDates = is_array($newborn['vaccine_dates'] ?? null) ? $newborn['vaccine_dates'] : [];
            $selected = !empty($newborn['birth_vaccines']) && is_array($newborn['birth_vaccines'])
                ? array_values(array_intersect(Newborn::BIRTH_VACCINES, $newborn['birth_vaccines']))
                : [];

            // Also include any vaccine whose date was picked directly
            foreach (Newborn::BIRTH_VACCINES as $vax) {
                if (!empty($rawDates[$vax]) && !in_array($vax, $selected, true)) {
                    $selected[] = $vax;
                }
            }
            $selected = array_values(array_intersect(Newborn::BIRTH_VACCINES, $selected));

            $cleanDates = [];
            foreach ($selected as $vax) {
                $val = trim((string) ($rawDates[$vax] ?? ''));
                if ($val !== '') {
                    $cleanDates[$vax] = substr($val, 0, 10);
                } elseif (!empty($newborn['vaccine_date'])) {
                    $cleanDates[$vax] = substr((string) $newborn['vaccine_date'], 0, 10);
                }
            }

            $newborn['birth_vaccines'] = !empty($selected) ? $selected : null;
            $newborn['vaccine_dates'] = !empty($cleanDates) ? $cleanDates : null;
            if (empty($newborn['vaccine_date']) && !empty($cleanDates)) {
                $firstDate = reset($cleanDates);
                if (count(array_unique($cleanDates)) === 1) {
                    $newborn['vaccine_date'] = $firstDate;
                }
            }

            if (collect($newborn)->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])->isNotEmpty()) {
                $admission->newborns()->create($newborn);
            }
        }
    }
}
