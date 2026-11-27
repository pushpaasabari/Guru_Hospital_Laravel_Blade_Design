<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Services\DischargeSummaryFormatter;
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

        $sections = $admission->formatted_clinical_sections;
        $investigations = $admission->formatted_investigations;
        $investigationRows = DischargeSummaryFormatter::groupInvestigationsForGrid($investigations);

        $digitalSummary = [
            'resolved_mrd_no' => $admission->resolved_mrd_no,
            'admission_at_formatted' => $admission->admission_at?->format('d.m.Y, h:i A') ?? '—',
            'procedure_at_formatted' => $admission->procedure_at?->format('d.m.Y, h:i A') ?? '—',
            'discharge_at_formatted' => $admission->discharge_at?->format('d.m.Y, h:i A') ?? '—',
            'stent_removal_date_formatted' => DischargeSummaryFormatter::resolveStentRemovalDate($admission),
            'consultant_with_degree' => $admission->consultant_with_degree,
            'doctor_stamp_details' => $admission->doctor_stamp_details,
            'insurance_details' => $admission->insurance_details_list,
            'clinical_sections' => $sections,
            'investigation_rows' => $investigationRows,
            'surgery_performed' => DischargeSummaryFormatter::formatSurgeryPerformed($admission),
            'procedure_note_html' => DischargeSummaryFormatter::formatProcedureNoteHtml($admission->procedure_note),
            'postoperative_orders' => $admission->formatted_postoperative_orders,
            'has_stent' => DischargeSummaryFormatter::hasStent($admission),
            'stent_notice_en' => DischargeSummaryFormatter::getStentRemovalNoticeEn($admission),
            'stent_notice_ta' => DischargeSummaryFormatter::getStentRemovalNoticeTa($admission),
            'newborn_cards' => $admission->formatted_newborns,
            'medications' => $admission->formatted_medications,
            'condition' => $admission->formatted_condition,
            'advice_and_review' => $admission->formatted_advice_and_review,
            'surgical_advice' => $admission->isSurgical() ? $admission->formatted_surgical_advice : null,
        ];

        return Inertia::render('Documents/Pack', [
            'admission' => $admission,
            'digitalSummary' => $digitalSummary,
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
