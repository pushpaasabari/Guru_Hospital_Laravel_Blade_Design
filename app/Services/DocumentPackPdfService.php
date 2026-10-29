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
    /**
     * Generates and returns a downloadable/streamable PDF response for the approved admission pack.
     * Uses Playwright with Google Chrome/Chromium for high-fidelity rendering, with DomPDF fallback.
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

        // 1. Primary Engine: Playwright Chrome / Chromium
        $output = $this->generateWithPlaywright($admission);

        // 2. Fallback Engine: DomPDF if Playwright is unavailable
        if (!$output) {
            $output = $this->generateWithDompdf($admission);
        }

        // Store privately in storage
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
                "Final approved document pack generated with Playwright Chromium and saved ({$fileName}) by {$user->name}"
            );
        } else {
            // Document reprint
            AuditService::log(
                'document.reprinted',
                'Admission',
                $admission->id,
                "Document pack reprinted with Playwright Chromium by {$user->name}"
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
    public function generateWithPlaywright(Admission $admission): ?string
    {
        try {
            $html = view('pdf.pack', compact('admission'))->render();

            $tempDir = storage_path('app/temp_pdf');
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            $uniqueId = uniqid('pdf_', true);
            $tempHtml = "{$tempDir}/{$uniqueId}.html";
            $tempPdf = "{$tempDir}/{$uniqueId}.pdf";

            file_put_contents($tempHtml, $html);

            $scriptPath = resource_path('scripts/generate-pdf.js');
            $headerTitle = "Guru Hospital — Confidential Medical Record (IP: {$admission->ip_no})";

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
    public function generateWithDompdf(Admission $admission): string
    {
        $pdf = Pdf::loadView('pdf.pack', compact('admission'));
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
        $canvas->page_text(40, 810, "Guru Hospital — Confidential Medical Record (IP: {$admission->ip_no})", $font, $size, $color);

        return $domPdf->output();
    }
}
