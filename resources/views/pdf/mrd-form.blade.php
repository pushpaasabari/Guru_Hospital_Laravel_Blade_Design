<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guru Hospital — Medical Records Department — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 20mm 15mm 20mm;
        }
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            color: #111;
            margin: 0;
            padding: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
        }
        .header-container {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 20px;
        }
        .logo-wrapper {
            margin-bottom: 6px;
        }
        .logo-img {
            width: 76px;
            height: auto;
            display: inline-block;
        }
        .hospital-title {
            font-size: 21pt;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #111;
            margin: 0 0 4px 0;
        }
        .address-line {
            font-size: 10pt;
            color: #222;
            line-height: 1.4;
            margin: 1px 0;
        }
        .phone-line {
            font-size: 10pt;
            font-weight: 600;
            color: #111;
            margin-top: 2px;
        }
        .mrd-title-wrapper {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 25px;
        }
        .mrd-title {
            font-family: 'Times New Roman', Times, Georgia, serif;
            font-size: 16.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111;
            display: inline-block;
            border-bottom: 3px double #111;
            padding-bottom: 3px;
        }
        .mrd-form-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .mrd-form-table td {
            padding: 21px 4px;
            vertical-align: middle;
            font-size: 14pt;
            line-height: 1.35;
        }
        .field-label {
            width: 270px;
            font-weight: bold;
            color: #111;
            white-space: nowrap;
            font-size: 14pt;
        }
        .colon-col {
            width: 35px;
            font-weight: bold;
            text-align: center;
            color: #111;
            font-size: 14pt;
        }
        .field-value {
            color: #111;
            font-size: 14pt;
            font-weight: normal;
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
    </style>
</head>
<body>

@if($admission->status !== 'approved' && $admission->status !== 'generated')
    <div class="watermark">DRAFT — NOT OFFICIAL</div>
@endif

@php
    $logoPath = public_path('images/guru-hospital-logo.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
@endphp

<!-- HEADER -->
<div class="header-container">
    <div class="logo-wrapper">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Guru Hospital Logo" class="logo-img" />
        @endif
    </div>

    <div class="hospital-title">GURU HOSPITAL</div>
    <div class="address-line">32, A/2, Antony Street, Nethaji Bypass Road,</div>
    <div class="address-line">Govt. Medical College Hospital Opp. Dharmapuri - 636 701.</div>
    <div class="phone-line">Ph : 04342-262266, Cell : 9418084266</div>
</div>

<!-- MEDICAL RECORDS DEPARTMENT TITLE -->
<div class="mrd-title-wrapper">
    <span class="mrd-title">MEDICAL RECORDS DEPARTMENT</span>
</div>

<!-- EXACT 9 FORM FIELDS COVERING ENTIRE PAGE -->
<table class="mrd-form-table">
    <tbody>
        <tr>
            <td class="field-label">Patient Name</td>
            <td class="colon-col">:</td>
            <td class="field-value"><strong>{{ $admission->patient_name }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Discharge Date &amp; Time</td>
            <td class="colon-col">:</td>
            <td class="field-value">
                {{ $admission->discharge_at ? $admission->discharge_at->format('d.m.Y') . ' @ ' . $admission->discharge_at->format('h.i A') : '29.08.2026 @ 04.00 PM' }}
            </td>
        </tr>
        <tr>
            <td class="field-label">MRD No.</td>
            <td class="colon-col">:</td>
            <td class="field-value">
                {{ $admission->mrd_no ?: ($admission->ip_no ?: '2026/567') }}
            </td>
        </tr>
        <tr>
            <td class="field-label">IP No.</td>
            <td class="colon-col">:</td>
            <td class="field-value">
                <strong>{{ $admission->ip_no }}</strong>
            </td>
        </tr>
        <tr>
            <td class="field-label">No of pages</td>
            <td class="colon-col">:</td>
            <td class="field-value">
                {{ $admission->no_of_pages ?? ($admission->isDelivery() ? 3 : 2) }}
            </td>
        </tr>
        <tr>
            <td class="field-label">Payment Mode</td>
            <td class="colon-col">:</td>
            <td class="field-value">
                @if($admission->payment_mode)
                    {{ $admission->payment_mode }}
                @elseif($admission->format === 'cmchistn')
                    Chief Minister Comprehensive Health Insurance Scheme (CMCHISTN)
                @elseif($admission->format === 'private')
                    Insurance / Mediclaim
                @elseif($admission->format === 'employee')
                    Hospital Staff Healthcare Scheme
                @else
                    Cash / General
                @endif
            </td>
        </tr>
        <tr>
            <td class="field-label">Hand Over Sign</td>
            <td class="colon-col">:</td>
            <td class="field-value"></td>
        </tr>
        <tr>
            <td class="field-label">Floor Incharge Sign</td>
            <td class="colon-col">:</td>
            <td class="field-value"></td>
        </tr>
        <tr>
            <td class="field-label">MRD Incharge Sign</td>
            <td class="colon-col">:</td>
            <td class="field-value"></td>
        </tr>
    </tbody>
</table>

</body>
</html>
