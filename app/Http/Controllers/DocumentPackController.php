<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Services\DocumentPackPdfService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocumentPackController extends Controller
{
    public function preview(Admission $admission): View
    {
        $this->authorize('view', $admission);
        $admission->load('newborns');
        return view('documents.pack', compact('admission'));
    }

    public function pdf(Request $request, Admission $admission, DocumentPackPdfService $pdfService): Response
    {
        $this->authorize('generateDocuments', $admission);

        // Final issuance must require clinician approval. Implement this policy/role check.
        abort_unless($admission->status === 'approved', 403, 'Clinician approval is required before final PDF generation.');

        $admission->load('newborns');
        return $pdfService->download($admission);
    }
}
