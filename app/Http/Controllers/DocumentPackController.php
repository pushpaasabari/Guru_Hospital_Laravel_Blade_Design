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
        $admission->loadDocumentRelations(true);

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

        $documentType = $type ?: $request->query('type', 'pack');
        $isInline = $request->boolean('inline') || $request->query('mode') === 'inline';

        return $pdfService->download($admission, $documentType, !$isInline);
    }

    public function history(Admission $admission): InertiaResponse
    {
        $this->authorize('view', $admission);
        $admission->loadDocumentRelations(false);

        return Inertia::render('Documents/History', compact('admission'));
    }
}
