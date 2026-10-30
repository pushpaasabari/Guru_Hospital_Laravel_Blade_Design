<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Services\DocumentPackPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DocumentPackController extends Controller
{
    public function preview(Request $request, Admission $admission): InertiaResponse
    {
        $this->authorize('view', $admission);
        $admission->load(['newborns', 'approvals.performer', 'revisions.approver', 'creator', 'approver']);

        $initialDoc = $request->query('doc', 'all');
        $initialMode = $request->query('mode', 'sheet');

        return Inertia::render('Documents/Pack', [
            'admission' => $admission,
            'initialDoc' => $initialDoc,
            'initialMode' => $initialMode,
        ]);
    }

    public function pdf(Request $request, Admission $admission, DocumentPackPdfService $pdfService, ?string $type = null): Response
    {
        $this->authorize('generateDocuments', $admission);

        // Require clinician approval
        abort_unless(
            in_array($admission->status, ['approved', 'generated'], true),
            403,
            'Clinician approval is required before final PDF generation.'
        );

        $documentType = $type ?: $request->query('type', 'pack');
        $isInline = $request->boolean('inline') || $request->query('mode') === 'inline';

        return $pdfService->download($admission, $documentType, !$isInline);
    }

    public function history(Admission $admission): InertiaResponse
    {
        $this->authorize('view', $admission);
        $admission->load(['approvals.performer', 'revisions.approver', 'creator', 'approver']);

        return Inertia::render('Documents/History', compact('admission'));
    }
}
