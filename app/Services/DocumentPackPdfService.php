<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\DocumentRevision;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DocumentPackPdfService
{
    public const TYPE_DISCHARGE_SUMMARY = 'discharge_summary';
    public const TYPE_OPERATION_NOTES = 'operation_notes';
    public const TYPE_MRD_FORM = 'mrd_form';
    public const TYPE_PACK = 'pack';

    /**
     * Resolves normalized document type and metadata.
     */
    public function resolveDocumentMetadata(string $type, Admission $admission): array
    {
        $type = strtolower(trim($type));
        $safeIp = preg_replace('/[^A-Za-z0-9_\-]/', '_', $admission->ip_no);

        if (in_array($type, ['discharge_summary', 'discharge-summary', 'discharge', 'summary', '1'], true)) {
            return [
                'type' => self::TYPE_DISCHARGE_SUMMARY,
                'view' => 'pdf.discharge-summary',
                'title' => 'Discharge Summary',
                'fileName' => "Guru_Hospital_Discharge_Summary_{$safeIp}.pdf",
                'headerTitle' => "Guru Hospital — Discharge Summary (IP: {$admission->ip_no})",
            ];
        }

        if (in_array($type, ['operation_notes', 'operation-notes', 'operation', 'ot', 'ot_notes', '2'], true)) {
            return [
                'type' => self::TYPE_OPERATION_NOTES,
                'view' => 'pdf.operation-notes',
                'title' => 'Operation Theatre Notes',
                'fileName' => "Guru_Hospital_Operation_Theatre_Notes_{$safeIp}.pdf",
                'headerTitle' => "Guru Hospital — Operation Theatre Notes (IP: {$admission->ip_no})",
            ];
        }

        if (in_array($type, ['mrd_form', 'mrd-form', 'mrd', 'archive', '3'], true)) {
            return [
                'type' => self::TYPE_MRD_FORM,
                'view' => 'pdf.mrd-form',
                'title' => 'MRD Inpatient Form',
                'fileName' => "Guru_Hospital_MRD_Form_{$safeIp}.pdf",
                'headerTitle' => "Guru Hospital — MRD Inpatient Form (IP: {$admission->ip_no})",
            ];
        }

        return [
            'type' => self::TYPE_PACK,
            'view' => 'pdf.pack',
            'title' => 'Complete Document Dossier Pack',
            'fileName' => "Guru_Hospital_Discharge_Pack_{$safeIp}.pdf",
            'headerTitle' => "Guru Hospital — Confidential Medical Record (IP: {$admission->ip_no})",
        ];
    }

    /**
     * Generates and returns a downloadable/streamable PDF response for the admission document.
     * Uses Playwright with Google Chrome/Chromium for high-fidelity rendering, with DomPDF fallback.
     */
    public function download(Admission $admission, string $type = 'pack'): Response
    {
        $admission->load(['newborns', 'creator', 'approver']);
        $meta = $this->resolveDocumentMetadata($type, $admission);

        // Ensure private directory exists
        $storageDirectory = 'documents/' . date('Y/m');
        Storage::disk('local')->makeDirectory($storageDirectory);

        $fileName = $meta['fileName'];
        $storagePath = "{$storageDirectory}/{$fileName}";

        // 1. Primary Engine: Playwright Chrome / Chromium
        $output = $this->generateWithPlaywright($admission, $type);

        // 2. Fallback Engine: DomPDF if Playwright is unavailable
        if (!$output) {
            $output = $this->generateWithDompdf($admission, $type);
        }

        // Store privately in storage
        Storage::disk('local')->put($storagePath, $output);

        // Update admission status to generated if it was approved
        $user = Auth::user();
        $userName = $user ? $user->name : 'Clinical System';
        $userId = $user ? $user->id : ($admission->approved_by ?? $admission->created_by);

        if ($admission->status === 'approved') {
            $admission->update(['status' => 'generated']);

            // Record in Document Revisions
            $revisionCount = $admission->revisions()->count();
            DocumentRevision::create([
                'admission_id' => $admission->id,
                'revision_number' => $revisionCount + 1,
                'snapshot_data' => $admission->toArray(),
                'pdf_storage_path' => $storagePath,
                'approved_by' => $admission->approved_by ?? $userId,
                'approved_at' => $admission->approved_at ?? now(),
                'created_at' => now(),
            ]);

            AuditService::log(
                'document.generated',
                'Admission',
                $admission->id,
                "Final approved {$meta['title']} generated with Playwright Chromium and saved ({$fileName}) by {$userName}"
            );
        } else {
            // Document reprint
            AuditService::log(
                'document.reprinted',
                'Admission',
                $admission->id,
                "{$meta['title']} reprinted with Playwright Chromium by {$userName}"
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

    /**
     * Renders pixel-perfect hospital discharge pack PDF using Playwright Chromium / Google Chrome.
     */
    public function generateWithPlaywright(Admission $admission, string $type = 'pack'): ?string
    {
        try {
            $meta = $this->resolveDocumentMetadata($type, $admission);
            $html = view($meta['view'], compact('admission'))->render();

            $tempDir = storage_path('app/temp_pdf');
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            $uniqueId = uniqid('pdf_', true);
            $tempHtml = "{$tempDir}/{$uniqueId}.html";
            $tempPdf = "{$tempDir}/{$uniqueId}.pdf";

            file_put_contents($tempHtml, $html);

            $scriptPath = resource_path('scripts/generate-pdf.js');
            $headerTitle = $meta['headerTitle'];

            $process = new Process([
                'node',
                $scriptPath,
                '--input', $tempHtml,
                '--output', $tempPdf,
                '--title', $headerTitle,
            ]);

            $process->setTimeout(30);
            $process->run();

            if ($process->isSuccessful() && file_exists($tempPdf) && filesize($tempPdf) > 0) {
                $output = file_get_contents($tempPdf);
                @unlink($tempHtml);
                @unlink($tempPdf);
                return $output;
            }

            Log::warning('Playwright PDF process failed: ' . $process->getErrorOutput());
            @unlink($tempHtml);
            if (file_exists($tempPdf)) {
                @unlink($tempPdf);
            }
        } catch (\Throwable $e) {
            Log::warning('Playwright PDF generation exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Fallback rendering via Barryvdh DomPDF.
     */
    public function generateWithDompdf(Admission $admission, string $type = 'pack'): string
    {
        $meta = $this->resolveDocumentMetadata($type, $admission);
        $pdf = Pdf::loadView($meta['view'], compact('admission'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
        ]);

        $domPdf = $pdf->getDomPDF();
        $domPdf->render();

        $canvas = $domPdf->getCanvas();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $size = 9;
        $color = [0.4, 0.4, 0.4];

        // Bottom right page number
        $canvas->page_text(450, 810, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, $color);

        // Bottom left hospital confidential notice
        $canvas->page_text(40, 810, $meta['headerTitle'], $font, $size, $color);

        return $domPdf->output();
    }
}
