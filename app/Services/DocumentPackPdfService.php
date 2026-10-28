<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\DocumentRevision;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentPackPdfService
{
    /**
     * Generates and returns a downloadable/streamable PDF response for the approved admission pack.
     */
    public function download(Admission $admission): Response
    {
        $admission->load(['newborns', 'creator', 'approver']);

        // Ensure private directory exists
        $storageDirectory = 'documents/' . date('Y/m');
        Storage::disk('local')->makeDirectory($storageDirectory);

        $safeIp = preg_replace('/[^A-Za-z0-9_\-]/', '_', $admission->ip_no);
        $fileName = "Guru_Hospital_Discharge_Pack_{$safeIp}.pdf";
        $storagePath = "{$storageDirectory}/{$fileName}";

        // Configure Dompdf options
        $pdf = Pdf::loadView('pdf.pack', compact('admission'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
        ]);

        // Render PDF
        $domPdf = $pdf->getDomPDF();
        $domPdf->render();

        // Canvas for page numbering (Pass 2): "Page {PAGE_NUM} of {PAGE_COUNT}"
        $canvas = $domPdf->getCanvas();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $size = 9;
        $color = [0.4, 0.4, 0.4]; // Muted gray

        // Bottom right page number on every page
        $canvas->page_text(
            450,
            810,
            "Page {PAGE_NUM} of {PAGE_COUNT}",
            $font,
            $size,
            $color
        );

        // Bottom left hospital confidential notice
        $canvas->page_text(
            40,
            810,
            "Guru Hospital — Confidential Medical Record (IP: {$admission->ip_no})",
            $font,
            $size,
            $color
        );

        $output = $domPdf->output();

        // Store privately
        Storage::disk('local')->put($storagePath, $output);

        // Update admission status to generated if it was approved
        $user = Auth::user();
        if ($admission->status === 'approved') {
            $admission->update(['status' => 'generated']);

            // Record in Document Revisions
            $revisionCount = $admission->revisions()->count();
            DocumentRevision::create([
                'admission_id' => $admission->id,
                'revision_number' => $revisionCount + 1,
                'snapshot_data' => $admission->toArray(),
                'pdf_storage_path' => $storagePath,
                'approved_by' => $admission->approved_by ?? $user->id,
                'approved_at' => $admission->approved_at ?? now(),
                'created_at' => now(),
            ]);

            AuditService::log(
                'document.generated',
                'Admission',
                $admission->id,
                "Final approved document pack generated and saved to private storage ({$fileName}) by {$user->name}"
            );
        } else {
            // Document reprint
            AuditService::log(
                'document.reprinted',
                'Admission',
                $admission->id,
                "Document pack reprinted by {$user->name}"
            );
        }

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
