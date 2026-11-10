<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\DocumentRevision;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
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
        $safeUhid = preg_replace('/[^A-Za-z0-9_\-]/', '_', $admission->uhid ?: 'UHID');
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($admission->patient_name ?: 'Patient'));
        $safeName = preg_replace('/_+/', '_', $safeName);
        $safeName = trim($safeName, '_');

        $opType = $admission->procedure_name ?: ($admission->procedure_done ?: ($admission->surgery_type ?: 'Operation'));
        $safeOpType = preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($opType));
        $safeOpType = preg_replace('/_+/', '_', $safeOpType);
        $safeOpType = trim($safeOpType, '_');
        if (strlen($safeOpType) > 60) {
            $safeOpType = substr($safeOpType, 0, 60);
            $safeOpType = trim($safeOpType, '_');
        }

        if (in_array($type, ['discharge_summary', 'discharge-summary', 'discharge', 'summary', '1'], true)) {
            return [
                'type' => self::TYPE_DISCHARGE_SUMMARY,
                'view' => 'pdf.discharge-summary',
                'title' => 'Discharge Summary',
                'fileName' => "{$safeUhid}_{$safeName}_Discharge_Summary.pdf",
                'headerTitle' => "GURU HOSPITAL — Discharge Summary (IP: {$admission->ip_no})",
            ];
        }

        if (in_array($type, ['operation_notes', 'operation-notes', 'operation', 'ot', 'ot_notes', '2'], true)) {
            return [
                'type' => self::TYPE_OPERATION_NOTES,
                'view' => 'pdf.operation-notes',
                'title' => 'Operation Theatre Notes',
                'fileName' => "{$safeUhid}_{$safeName}_{$safeOpType}.pdf",
                'headerTitle' => "GURU HOSPITAL — Operation Theatre Notes (IP: {$admission->ip_no})",
            ];
        }

        if (in_array($type, ['mrd_form', 'mrd-form', 'mrd', 'archive', '3'], true)) {
            return [
                'type' => self::TYPE_MRD_FORM,
                'view' => 'pdf.mrd-form',
                'title' => 'MRD Inpatient Form',
                'fileName' => "Guru_Hospital_MRD_Form_{$safeIp}.pdf",
                'headerTitle' => "GURU HOSPITAL — MRD Inpatient Form (IP: {$admission->ip_no})",
            ];
        }

        return [
            'type' => self::TYPE_PACK,
            'view' => 'pdf.pack',
            'title' => 'Complete Document Dossier Pack',
            'fileName' => "Guru_Hospital_Discharge_Pack_{$safeIp}.pdf",
            'headerTitle' => "GURU HOSPITAL — Confidential Medical Record (IP: {$admission->ip_no})",
        ];
    }

    /**
     * Generates and returns a downloadable/streamable PDF response for the admission document.
     * Strictly uses Playwright + Chromium for high-fidelity A4 rendering.
     */
    public function download(Admission $admission, string $type = 'pack', bool $asAttachment = true): Response
    {
        $admission->load(['newborns', 'creator', 'approver']);
        $meta = $this->resolveDocumentMetadata($type, $admission);

        // Ensure private directory exists
        $storageDirectory = 'documents/' . date('Y/m');
        Storage::disk('local')->makeDirectory($storageDirectory);

        $fileName = $meta['fileName'];
        $storagePath = "{$storageDirectory}/{$fileName}";

        $output = $this->generateWithPlaywright($admission, $type);

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

        $disposition = $asAttachment ? 'attachment' : 'inline';

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$fileName}\"",
            'Content-Length' => strlen($output),
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Renders hospital document PDFs strictly using Playwright + Chromium.
     *
     * @throws RuntimeException When Playwright + Chromium fails to generate a valid PDF.
     */
    public function generateWithPlaywright(Admission $admission, string $type = 'pack'): string
    {
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

        try {
            $scriptPath = resource_path('scripts/generate-pdf.js');
            $nodeBinary = $this->resolveNodeBinary();
            $env = $this->buildPlaywrightEnv();

            $process = new Process([
                $nodeBinary,
                $scriptPath,
                '--input', $tempHtml,
                '--output', $tempPdf,
                '--title', $meta['headerTitle'],
                '--type', $meta['type'],
            ], base_path(), $env);

            $process->setTimeout(60);
            $process->run();

            if ($process->isSuccessful() && file_exists($tempPdf) && filesize($tempPdf) > 0) {
                return (string) file_get_contents($tempPdf);
            }

            $errorDetail = trim($process->getErrorOutput() ?: $process->getOutput());
            Log::error('Playwright Chromium PDF process failed', [
                'type' => $meta['type'],
                'exit_code' => $process->getExitCode(),
                'error' => $errorDetail,
            ]);

            throw new RuntimeException(
                'Chromium + Playwright PDF generation failed' . ($errorDetail ? ": {$errorDetail}" : '.')
            );
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Playwright Chromium PDF exception: ' . $e->getMessage());
            throw new RuntimeException('Chromium + Playwright PDF generation error: ' . $e->getMessage(), 0, $e);
        } finally {
            @unlink($tempHtml);
            if (file_exists($tempPdf)) {
                @unlink($tempPdf);
            }
        }
    }

    private function resolveNodeBinary(): string
    {
        $configured = getenv('NODE_BINARY') ?: ($_ENV['NODE_BINARY'] ?? $_SERVER['NODE_BINARY'] ?? '');
        if ($configured !== '') {
            if (!is_file($configured)) {
                throw new RuntimeException("Configured NODE_BINARY does not exist: {$configured}");
            }
            return $configured;
        }

        $candidates = PHP_OS_FAMILY === 'Windows'
            ? [
                'C:\\Program Files\\nodejs\\node.exe',
                'C:\\Program Files (x86)\\nodejs\\node.exe',
            ]
            : [
                '/usr/local/bin/node',
                '/usr/bin/node',
            ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return 'node';
    }

    private function buildPlaywrightEnv(): array
    {
        $env = array_merge($_SERVER, $_ENV);

        if (PHP_OS_FAMILY === 'Windows') {
            $userProfile = getenv('USERPROFILE') ?: ($env['USERPROFILE'] ?? 'C:\\Users\\Admin');
            $localAppData = getenv('LOCALAPPDATA') ?: ($env['LOCALAPPDATA'] ?? ($userProfile . '\\AppData\\Local'));
            $appData = getenv('APPDATA') ?: ($env['APPDATA'] ?? ($userProfile . '\\AppData\\Roaming'));

            return array_merge($env, [
                'SystemRoot' => getenv('SystemRoot') ?: ($env['SystemRoot'] ?? 'C:\\Windows'),
                'USERPROFILE' => $userProfile,
                'LOCALAPPDATA' => $localAppData,
                'APPDATA' => $appData,
                'PATH' => getenv('PATH') ?: ($env['PATH'] ?? 'C:\\Windows\\system32;C:\\Program Files\\nodejs'),
                'TEMP' => sys_get_temp_dir(),
                'TMP' => sys_get_temp_dir(),
            ]);
        }

        $currentPath = getenv('PATH') ?: ($env['PATH'] ?? '');
        $pathParts = array_filter(explode(':', $currentPath));
        foreach (['/usr/local/bin', '/usr/bin', '/bin'] as $stdDir) {
            if (!in_array($stdDir, $pathParts, true)) {
                $pathParts[] = $stdDir;
            }
        }

        return array_merge($env, [
            'PATH' => implode(':', $pathParts),
            'HOME' => getenv('HOME') ?: ($env['HOME'] ?? '/tmp'),
            'TMPDIR' => sys_get_temp_dir(),
        ]);
    }
}
