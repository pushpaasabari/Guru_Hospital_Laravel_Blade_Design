<?php

namespace App\Services;

use App\Models\Admission;
use Symfony\Component\HttpFoundation\Response;

class DocumentPackPdfService
{
    /**
     * Integrate the PDF engine selected for your Laravel version here.
     * This intentionally fails closed rather than pretending a PDF has been generated.
     */
    public function download(Admission $admission): Response
    {
        abort(501, 'PDF engine not configured. Install and configure an approved Laravel-compatible PDF package.');
    }
}
