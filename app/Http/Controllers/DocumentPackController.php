<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Services\DocumentPackPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DocumentPackController extends Controller
{
    public function preview(Admission $admission): View
    {
        $this->authorize('view', $admission);
        $admission->load(['newborns', 'approvals.performer', 'revisions.approver', 'creator', 'approver']);

        return view('documents.pack', compact('admission'));
    }

    public function pdf(Request $request, Admission $admission, DocumentPackPdfService $pdfService): Response
    {
        $this->authorize('generateDocuments', $admission);

        // Require clinician approval
        abort_unless(
            in_array($admission->status, ['approved', 'generated'], true),
            403,
            'Clinician approval is required before final PDF generation.'
        );

        return $pdfService->download($admission);
    }

    public function history(Admission $admission): View
    {
        $this->authorize('view', $admission);
        $admission->load(['approvals.performer', 'revisions.approver', 'creator', 'approver']);

        return view('documents.history', compact('admission'));
    }
}
