<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdmissionRequest;
use App\Http\Requests\UpdateAdmissionRequest;
use App\Models\Admission;
use App\Models\DocumentRevision;
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

        $query = Admission::with(['creator', 'approver', 'newborns'])
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('uhid', 'like', "%{$search}%")
                  ->orWhere('ip_no', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $admissions = $query->paginate(20)->withQueryString();

        return Inertia::render('Admissions/Index', compact('admissions'));
    }

    public function create(): Response
    {
        $this->authorize('create', Admission::class);
        return Inertia::render('Admissions/Create');
    }

    public function store(StoreAdmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $newborns = $validated['newborns'] ?? [];
        unset($validated['newborns']);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'draft';

        $admission = DB::transaction(function () use ($validated, $newborns) {
            $admission = Admission::create($validated);

            if ($admission->isDelivery()) {
                foreach ($newborns as $newborn) {
                    if (collect($newborn)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty()) {
                        $admission->newborns()->create($newborn);
                    }
                }
            }

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

        return redirect()->route('documents.preview', $admission)
            ->with('status', 'Draft admission saved successfully. Review all three documents before submitting for approval.');
    }

    public function show(Admission $admission): Response
    {
        $this->authorize('view', $admission);
        $admission->load(['newborns', 'approvals.performer', 'revisions.approver', 'creator', 'approver']);

        return Inertia::render('Documents/Pack', compact('admission'));
    }

    public function edit(Admission $admission): Response
    {
        $this->authorize('update', $admission);
        $admission->load('newborns');

        return Inertia::render('Admissions/Edit', compact('admission'));
    }

    public function update(UpdateAdmissionRequest $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validated();
        $newborns = $validated['newborns'] ?? [];
        unset($validated['newborns']);

        $oldData = $admission->toArray();

        DB::transaction(function () use ($admission, $validated, $newborns, $oldData) {
            $admission->update($validated);

            // Synchronize newborns if delivery
            if ($admission->isDelivery()) {
                $admission->newborns()->delete();
                foreach ($newborns as $newborn) {
                    if (collect($newborn)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty()) {
                        $admission->newborns()->create($newborn);
                    }
                }
            } else {
                $admission->newborns()->delete();
            }

            AuditService::log(
                'admission.updated',
                'Admission',
                $admission->id,
                "Admission updated for {$admission->patient_name}",
                $oldData,
                $admission->fresh()->toArray()
            );
        });

        return redirect()->route('documents.preview', $admission)
            ->with('status', 'Admission details updated successfully.');
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
}
