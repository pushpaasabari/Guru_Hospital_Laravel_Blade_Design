<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>GURU HOSPITAL — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
    <style>
        @font-face {
            font-family: 'Nirmala';
            font-style: normal;
            font-weight: normal;
            src: url('{{ str_replace('\\', '/', public_path('fonts/Nirmala.ttf')) }}') format('truetype');
        }
        @font-face {
            font-family: 'Nirmala';
            font-style: normal;
            font-weight: bold;
            src: url('{{ str_replace('\\', '/', public_path('fonts/NirmalaB.ttf')) }}') format('truetype');
        }
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 18mm 15mm;
        }
        body {
            font-family: 'Nirmala', 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-before: always;
        }
        .letterhead-space {
            height: 33mm;
            width: 100%;
        }
        .document-title {
            text-align: center;
            margin-top: 4px;
            margin-bottom: 18px;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10pt;
            table-layout: fixed;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 10pt;
            vertical-align: top;
            text-align: left;
        }
        .meta-table th {
            background-color: #f7f9fa;
            color: #222;
            font-weight: bold;
            width: 20%;
            text-align: left;
        }
        .meta-table td {
            width: 30%;
            text-align: left;
        }
        .section-heading {
            font-size: 10.5pt;
            font-weight: bold;
            color: #17365d;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            margin: 10px 0 4px 0;
            text-transform: uppercase;
        }
        .content-block {
            margin-bottom: 6px;
            font-size: 10pt;
            text-align: justify;
            line-height: 1.45;
        }
        .baby-box {
            border: 1px solid #2563eb;
            background-color: #f8fafc;
            padding: 6px;
            margin-bottom: 8px;
            border-radius: 3px;
            font-size: 10pt;
            line-height: 1.45;
        }
        .emergency-box {
            border: 1px solid #dc3545;
            background-color: #fff5f5;
            color: #991b1b;
            padding: 6px 8px;
            margin-top: 12px;
            margin-bottom: 8px;
            font-size: 9.5pt;
        }
        .signature-row {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }
        .sig-block {
            width: 32%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            font-size: 10pt;
        }
        .sig-line {
            border-top: 1px dashed #555;
            margin: 30px 10px 4px 10px;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            transform: rotate(-30deg);
            font-size: 55px;
            color: rgba(220, 53, 69, 0.10);
            font-weight: bold;
            text-transform: uppercase;
            z-index: -1000;
        }

        /* Modern Exact Discharge Summary Styles */
        .doc-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: #111111;
            letter-spacing: 0.5px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 4px;
            border: 0.6pt solid #94a3b8;
        }
        .info-table.bg-gray {
            background-color: #f1f5f9;
        }
        .info-table.bg-white {
            background-color: #ffffff;
        }
        .info-table td, .info-table th {
            border: 0.6pt solid #cbd5e1;
            padding: 2px 5px;
            vertical-align: top;
        }
        .cell-lbl {
            font-size: 6.8pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0px;
            display: block;
        }
        .cell-val {
            font-size: 8.8pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.2;
            display: block;
        }
        .section-header {
            font-size: 9.5pt;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            border-bottom: 1.2pt solid #222222;
            padding-bottom: 1.5px;
            margin-top: 4.5px;
            margin-bottom: 2px;
        }
        .prose-text {
            font-size: 8.5pt;
            line-height: 1.25;
            color: #000000;
            text-align: justify;
            margin-bottom: 3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 4px;
            border: 0.6pt solid #94a3b8;
        }
        .data-table th {
            background-color: #000000;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 2.5px 5px;
            text-align: left;
            border: 0.6pt solid #000000;
        }
        .data-table td {
            border: 0.6pt solid #94a3b8;
            padding: 2.5px 5px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .running-header {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-bottom: 0.8pt solid #94a3b8;
            padding-bottom: 3px;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }
        .running-header-left {
            display: table-cell;
            font-weight: bold;
            text-transform: uppercase;
            color: #111111;
            width: 35%;
        }
        .running-header-right {
            display: table-cell;
            text-align: right;
            color: #555555;
            width: 65%;
        }
        .emergency-container {
            width: 100%;
            background-color: #ebebeb;
            border-left: 3.5pt solid #000000;
            padding: 6px 10px;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        .review-box {
            width: 100%;
            border: 1pt solid #000000;
            background-color: #f1f5f9;
            padding: 5px 10px;
            margin-bottom: 6px;
            display: table;
            table-layout: fixed;
        }
        .sig-container {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-top: 0.8pt solid #444444;
            padding-top: 4px;
            margin-top: 18px;
        }
        .sig-cell {
            display: table-cell;
            vertical-align: top;
        }

        /* Surgical Tabular Format Styles */
        .surgical-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 5px;
            border: 0.8pt solid #334155;
        }
        .surgical-table th, .surgical-table td {
            border: 0.6pt solid #64748b;
            padding: 3px 5px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .surgical-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
        }
    </style>
    @include('pdf.partials.discharge-summary-a4._styles')
    <style>
        /* Keep Complete 3-in-1 Pack printable on standard A4 margins across all 3 documents */
        html, body {
            width: 100%;
            font-family: 'Open Sans', 'Helvetica Neue', Arial, 'Nirmala UI', 'Nirmala', sans-serif;
            font-size: 10pt;
            color: #111;
        }
        .a4-page {
            width: 100%;
            height: auto;
            padding: 0;
            overflow: visible;
        }
        .a4-page-1 {
            page-break-after: always;
            break-after: page;
        }
        .a4-page-2 {
            padding: 0;
            page-break-after: auto;
            break-after: auto;
        }
        .a4-page-content,
        .a4-page-2 .a4-page-content {
            height: auto;
            max-height: none;
        }
    </style>
</head>
<body>

<!-- ========================================== -->
<!-- DOCUMENT 1: DIGITAL DISCHARGE SUMMARY      -->
<!-- ========================================== -->
@include('pdf.partials.discharge-summary-a4._page1', ['admission' => $admission])
@include('pdf.partials.discharge-summary-a4._page2', ['admission' => $admission])

<!-- ========================================== -->
<!-- DOCUMENT 2: OPERATION THEATRE NOTES         -->
<!-- ========================================== -->
<div class="page-break"></div>

{{-- Letterhead Space (First page of OT notes only) --}}
<div style="height: 40mm;"></div>

<div style="text-align: center; margin-top: 4px; margin-bottom: 22px;">
    <div style="font-size: 14pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; letter-spacing: 0.5px;">OPERATION THEATRE NOTES</div>
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 10pt;">
    <tr>
        <td style="font-weight: bold; width: 16%;">NAME</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 33%;">{{ $admission->patient_name }}</td>
        <td style="font-weight: bold; width: 15%;">IP.NO</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 30%;">{{ $admission->ip_no }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">AGE/SEX</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->age ? $admission->age . ' / ' . ($admission->sex ?? 'Female') : ($admission->sex ?? 'Female') }}</td>
        <td style="font-weight: bold;">UHID NO</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->uhid }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">CONSULTANT</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->consultant ?: ($admission->surgeon ?: '') }}</td>
        <td style="font-weight: bold;">PHONE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->contact ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">ICD CODE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->icd_code ?? '' }}</td>
        <td style="font-weight: bold;">DATE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->procedure_at?->format('d.m.Y') ?? ($admission->admission_at?->format('d.m.Y') ?? now()->format('d.m.Y')) }}</td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-top: 2px; margin-bottom: 4px; font-size: 10pt;">
    <tr>
        <td style="font-weight: bold; width: 16%;">ADDRESS</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 81%;">{{ $admission->address ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">DATE OF SURGERY</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->procedure_at?->format('d.m.Y h:i a') ?? '' }}</td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 4px; font-size: 10pt;">
    <tr>
        <td style="font-weight: bold; width: 16%;">DIAGNOSIS</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 81%;">{{ $admission->diagnosis }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">PROCEDURE DONE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->procedure_done ?? '' }}</td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 6px; font-size: 10pt;">
    <tr>
        <td style="font-weight: bold; width: 16%;">SURGERY</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td colspan="4" style="font-weight: bold;">{{ $admission->procedure_name ?: 'ELECTIVE LSCS' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; width: 16%;">SURGEON</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 33%;">{{ $admission->surgeon ?: ($admission->consultant ?: '') }}</td>
        <td style="font-weight: bold; width: 15%;">ANESTHETIST</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 30%;">{{ $admission->anaesthetist ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">ASSISTANT BY S/N</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->assisted_surgeon ?: ($admission->assisted_by ?: ($admission->staff_nurse ?: ($admission->assistant ?: ''))) }}</td>
        <td style="font-weight: bold;">ANESTHESIA TYPE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->anaesthesia_type ?: 'Spinal' }}</td>
    </tr>
    @if($admission->ot_technician || $admission->assisted_anaesthetist || ($admission->assisted_surgeon && $admission->staff_nurse))
    <tr>
        <td style="font-weight: bold;">{{ $admission->assisted_surgeon && $admission->staff_nurse ? 'STAFF NURSE' : ($admission->ot_technician ? 'OT TECHNICIAN' : 'ASST. ANAESTHETIST') }}</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->assisted_surgeon && $admission->staff_nurse ? $admission->staff_nurse : ($admission->ot_technician ?: $admission->assisted_anaesthetist) }}</td>
        @if($admission->assisted_anaesthetist && ($admission->ot_technician || ($admission->assisted_surgeon && $admission->staff_nurse)))
            <td style="font-weight: bold;">ASST. ANESTHETIST</td>
            <td style="font-weight: bold; text-align: center;">:</td>
            <td>{{ $admission->assisted_anaesthetist }}</td>
        @else
            <td></td><td></td><td></td>
        @endif
    </tr>
    @endif
</table>

<div style="margin-top: 10px; font-size: 10pt;">
    <div style="font-weight: bold; margin-bottom: 2px;">PROCEDURE NOTE :</div>
    @if($admission->procedure_note)
        <div style="text-align: justify; line-height: 1.45; margin-bottom: 6px;">
            {!! nl2br(e($admission->procedure_note)) !!}
        </div>
    @endif
    <div style="font-weight: bold; margin-top: 6px; margin-bottom: 2px;">
        INCISION : @if($admission->incision)<span style="font-weight: normal;">{!! nl2br(e($admission->incision)) !!}</span>@endif
    </div>
</div>

<div style="margin-top: 6px; font-size: 10pt;">
    <div style="font-weight: bold; margin-bottom: 2px;">FINDINGS :</div>
    @if($admission->findings)
        <div style="text-align: justify; margin-bottom: 6px;">{!! nl2br(e($admission->findings)) !!}</div>
    @endif
</div>

<div style="margin-top: 6px; font-size: 10pt;">
    <div style="font-weight: bold; margin-bottom: 2px;">COMPLICATIONS :</div>
    @if($admission->complications)
        <div style="text-align: justify; margin-bottom: 6px;">{!! nl2br(e($admission->complications)) !!}</div>
    @endif
</div>

<table style="width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 6px; font-size: 10pt;">
    <tr>
        <td style="width: 35%;">
            <strong style="color: #000;">BLOOD LOSS (ML) :</strong>
            <span>{{ $admission->blood_loss_ml ? $admission->blood_loss_ml . ' ml' : '' }}</span>
        </td>
        <td style="width: 35%;">
            <strong style="color: #000;">SPECIMEN :</strong>
            <span>{{ $admission->specimen ?? '' }}</span>
        </td>
        <td style="width: 30%;">
            <strong style="color: #000;">DRAIN :</strong>
            <span>{{ $admission->drain ?? '' }}</span>
        </td>
    </tr>
</table>

@if($admission->isDelivery())
<table style="width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 6px; font-size: 10pt;">
    <tr>
        <td style="font-weight: bold; width: 16%; vertical-align: top;">BABY</td>
        <td style="font-weight: bold; width: 3%; text-align: center; vertical-align: top;">:</td>
        <td style="width: 81%; vertical-align: top;">
            @if($admission->newborns->isNotEmpty())
                @foreach($admission->newborns as $i => $baby)
                    <div style="margin-bottom: 4px;">
                        Alive {{ $baby->sex ?: 'newborn' }} baby on {{ $baby->birth_at?->format('d.m.Y @ h.iA') ?? ($admission->procedure_at?->format('d.m.Y @ h.iA') ?? '') }}<br>
                        Baby Weight {{ $baby->birth_weight_kg ? number_format($baby->birth_weight_kg, 3) . 'Kg' : '—' }}
                        @if($baby->neonatal_care)
                            <span style="color: #555;">({{ $baby->neonatal_care }})</span>
                        @endif
                        @if($baby->notes)
                            <div style="color: #333; font-size: 9.5pt;">Presentation / Notes: {{ $baby->notes }}</div>
                        @endif
                    </div>
                @endforeach
            @else
                <div>Newborn details not recorded.</div>
            @endif
        </td>
    </tr>
</table>
@endif

<div style="margin-top: 6px; font-size: 10pt;">
    <div style="font-weight: bold; margin-bottom: 2px;">POST-OPERATIVE ORDERS :</div>
    @if($admission->postoperative_orders)
        <div style="text-align: justify; margin-bottom: 6px;">{!! nl2br(e($admission->postoperative_orders)) !!}</div>
    @endif
</div>

<div style="text-align: right; margin-top: 40px; padding-top: 15px; page-break-inside: avoid;">
    <span style="font-weight: bold; font-size: 10.5pt; letter-spacing: 0.5px;">SURGEON SEAL &amp; SIGNATURE</span>
</div>

<!-- ========================================== -->
<!-- DOCUMENT 3: MRD INPATIENT FORM              -->
<!-- ========================================== -->
<div class="page-break"></div>

@php
    $logoPath = public_path('images/guru-hospital-logo.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
@endphp

<div style="text-align: center; margin-top: 10px; margin-bottom: 20px;">
    @if($logoBase64)
        <div style="margin-bottom: 6px;">
            <img src="{{ $logoBase64 }}" alt="Guru Hospital Logo" style="width: 76px; height: auto; display: inline-block;" />
        </div>
    @endif
    <div style="font-size: 21pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #111; margin: 0 0 4px 0;">GURU HOSPITAL</div>
    <div style="font-size: 10pt; color: #222; line-height: 1.4; margin: 1px 0;">32, A/2, Antony Street, Nethaji Bypass Road,</div>
    <div style="font-size: 10pt; color: #222; line-height: 1.4; margin: 1px 0;">Govt. Medical College Hospital Opp. Dharmapuri - 636 701.</div>
    <div style="font-size: 10pt; font-weight: 600; color: #111; margin-top: 2px;">Ph : 04342-262266, Cell : 7418084266</div>
</div>

<div style="text-align: center; margin-top: 20px; margin-bottom: 25px;">
    <span style="font-family: 'Times New Roman', Times, Georgia, serif; font-size: 16.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #111; display: inline-block; border-bottom: 3px double #111; padding-bottom: 3px;">
        MEDICAL RECORDS DEPARTMENT
    </span>
</div>

<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <tbody>
        <tr>
            <td style="width: 270px; font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">Patient Name</td>
            <td style="width: 35px; font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;"><strong>{{ $admission->patient_name }}</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">Discharge Date &amp; Time</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;">
                {{ $admission->discharge_at ? $admission->discharge_at->format('d.m.Y') . ' @ ' . $admission->discharge_at->format('h.i A') : '29.08.2026 @ 04.00 PM' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">MRD No.</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;">
                {{ $admission->resolved_mrd_no }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">IP No.</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;">
                <strong>{{ $admission->ip_no }}</strong>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">No of pages</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;"></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">Payment Mode</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;">{{ $admission->payment_summary }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">Hand Over Sign</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;"></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">Floor Incharge Sign</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;"></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">MRD Incharge Sign</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;"></td>
        </tr>
    </tbody>
</table>

</body>
</html>
