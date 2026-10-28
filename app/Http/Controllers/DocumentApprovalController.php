<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\DocumentApproval;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentApprovalController extends Controller
{
    public function submitForReview(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorize('submitForReview', $admission);

        DB::transaction(function () use ($admission, $request) {
            $admission->update([
                'status' => 'ready_for_review',
            ]);

            DocumentApproval::create([
                'admission_id' => $admission->id,
                'action' => 'submitted_for_review',
                'performed_by' => $request->user()->id,
                'user_role' => $request->user()->role,
                'notes_or_correction_reason' => $request->input('notes'),
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            AuditService::log(
                'document.submitted_for_review',
                'Admission',
                $admission->id,
                "Document pack submitted for clinical review by {$request->user()->name}"
            );
        });

        return redirect()->route('documents.preview', $admission)
            ->with('status', 'Document pack submitted for clinical review.');
    }

    public function approve(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorize('approve', $admission);

        DB::transaction(function () use ($admission, $request) {
            $admission->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            DocumentApproval::create([
                'admission_id' => $admission->id,
                'action' => 'approved',
                'performed_by' => $request->user()->id,
                'user_role' => $request->user()->role,
                'notes_or_correction_reason' => $request->input('approval_notes', 'Clinically reviewed and approved.'),
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            AuditService::log(
                'document.approved',
                'Admission',
                $admission->id,
                "Document pack clinically approved by Dr. {$request->user()->name} (Reg No: {$request->user()->doctor_reg_no})"
            );
        });

        return redirect()->route('documents.preview', $admission)
            ->with('status', 'Document pack has been clinically approved and is now ready for final PDF generation.');
    }

    public function returnForCorrection(Request $request, Admission $admission): RedirectResponse
    {
        $this->authorize('returnForCorrection', $admission);

        $request->validate([
            'correction_reason' => ['required', 'string', 'max:2000'],
        ]);

        $reason = $request->input('correction_reason');

        DB::transaction(function () use ($admission, $reason, $request) {
            $admission->update([
                'status' => 'returned_for_correction',
                'rejection_reason' => $reason,
            ]);

            DocumentApproval::create([
                'admission_id' => $admission->id,
                'action' => 'returned_for_correction',
                'performed_by' => $request->user()->id,
                'user_role' => $request->user()->role,
                'notes_or_correction_reason' => $reason,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            AuditService::log(
                'document.returned',
                'Admission',
                $admission->id,
                "Document pack returned for correction by {$request->user()->name}. Reason: {$reason}"
            );
        });

        return redirect()->route('documents.preview', $admission)
            ->with('status', 'Document pack has been returned for correction.');
    }
}

