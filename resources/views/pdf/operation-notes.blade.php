<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guru Hospital — Operation Theatre Notes — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
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
            font-size: 10px;
            line-height: 1.45;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .letterhead-space {
            height: 35mm;
            width: 100%;
        }
        .ot-title {
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
            font-size: 10pt;
        }
        .meta-table td {
            padding: 3px 2px;
            vertical-align: top;
        }
        .section-label {
            font-weight: bold;
            font-size: 10pt;
            margin-top: 8px;
            margin-bottom: 2px;
            color: #111;
        }
        .section-text {
            text-align: justify;
            line-height: 1.45;
            font-size: 10pt;
            margin-bottom: 6px;
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
        .signature-block {
            text-align: right;
            margin-top: 45px;
            padding-top: 15px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

@if($admission->status !== 'approved' && $admission->status !== 'generated')
    <div class="watermark">DRAFT — NOT OFFICIAL</div>
@endif

<!-- ========================================== -->
<!-- 2. OPERATION THEATRE NOTES                 -->
<!-- ========================================== -->
<div class="letterhead-space"></div>

<div class="ot-title">OPERATION THEATRE NOTES</div>

<table class="meta-table" style="margin-bottom: 4px;">
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
        <td>{{ $admission->procedure_at?->format('d/m/Y') ?? ($admission->admission_at?->format('d/m/Y') ?? now()->format('d/m/Y')) }}</td>
    </tr>
</table>

<table class="meta-table" style="margin-top: 2px; margin-bottom: 4px;">
    <tr>
        <td style="font-weight: bold; width: 16%;">ADDRESS</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 81%;">{{ $admission->address ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">DATE OF SURGERY</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->procedure_at?->format('d/m/Y h:i a') ?? '' }}</td>
    </tr>
</table>

<table class="meta-table" style="margin-top: 6px; margin-bottom: 4px;">
    <tr>
        <td style="font-weight: bold; width: 16%;">DIAGNOSIS</td>
        <td style="font-weight: bold; width: 3%; text-align: center;">:</td>
        <td style="width: 81%;">{{ $admission->diagnosis }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">PROCEDURE DONE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->procedure_done ?? ($admission->procedure_name ?? '') }}</td>
    </tr>
</table>

<table class="meta-table" style="margin-top: 6px; margin-bottom: 6px;">
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
        <td>{{ $admission->assisted_by ?: ($admission->assistant ?: '') }}</td>
        <td style="font-weight: bold;">ANESTHESIA TYPE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->anaesthesia_type ?: 'Spinal' }}</td>
    </tr>
</table>

<div class="section-label">PROCEDURE NOTE :</div>
@if($admission->procedure_note)
    <div class="section-text">{!! nl2br(e($admission->procedure_note)) !!}</div>
@endif

<div class="section-label" style="margin-top: 6px;">INCISION : @if($admission->incision)<span style="font-weight: normal;">{!! nl2br(e($admission->incision)) !!}</span>@endif</div>

<div class="section-label">FINDINGS :</div>
@if($admission->findings)
    <div class="section-text">{!! nl2br(e($admission->findings)) !!}</div>
@endif

<div class="section-label">COMPLICATIONS :</div>
@if($admission->complications)
    <div class="section-text">{!! nl2br(e($admission->complications)) !!}</div>
@endif

<table class="meta-table" style="margin-top: 6px; margin-bottom: 6px;">
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
<table class="meta-table" style="margin-top: 6px; margin-bottom: 6px;">
    <tr>
        <td style="font-weight: bold; width: 16%; vertical-align: top;">BABY</td>
        <td style="font-weight: bold; width: 3%; text-align: center; vertical-align: top;">:</td>
        <td style="width: 81%; vertical-align: top;">
            @if($admission->newborns->isNotEmpty())
                @foreach($admission->newborns as $i => $baby)
                    <div style="margin-bottom: 4px;">
                        Alive {{ $baby->sex ?? 'Female' }} baby on {{ $baby->birth_at?->format('d/m/Y @ h.iA') ?? ($admission->procedure_at?->format('d/m/Y @ h.iA') ?? '') }}<br>
                        Baby Weight {{ $baby->birth_weight_kg ? number_format($baby->birth_weight_kg, 3) . 'Kg' : '2.650Kg' }}
                        @if($baby->neonatal_care)
                            <span style="color: #555;">({{ $baby->neonatal_care }})</span>
                        @endif
                    </div>
                @endforeach
            @else
                <div>
                    Alive Female baby on {{ $admission->procedure_at?->format('d/m/Y @ h.iA') ?? now()->format('d/m/Y @ h.iA') }}<br>
                    Baby Weight 2.650Kg
                </div>
            @endif
        </td>
    </tr>
</table>
@endif

<div class="section-label">POST-OPERATIVE ORDERS :</div>
@if($admission->postoperative_orders)
    <div class="section-text">{!! nl2br(e($admission->postoperative_orders)) !!}</div>
@endif

<div class="signature-block">
    <span style="font-weight: bold; font-size: 10.5pt; letter-spacing: 0.5px;">SURGEON SEAL &amp; SIGNATURE</span>
    @if($admission->surgeon || $admission->consultant)
        <div style="font-size: 9pt; color: #444; margin-top: 4px;">{{ $admission->surgeon ?: $admission->consultant }}</div>
    @endif
</div>

</body>
</html>

