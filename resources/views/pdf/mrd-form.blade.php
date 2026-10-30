<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guru Hospital — MRD Inpatient Form — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
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
            font-size: 10.5px;
            line-height: 1.45;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .hospital-header {
            text-align: center;
            border-bottom: 2px solid #17365d;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .hospital-name {
            font-size: 18px;
            font-weight: bold;
            color: #17365d;
            letter-spacing: 1px;
            margin: 0;
        }
        .hospital-subtitle {
            font-size: 9.5px;
            color: #444;
            margin: 2px 0;
        }
        .document-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f0f4f8;
            border: 1px solid #17365d;
            color: #17365d;
            text-align: center;
            padding: 5px;
            margin: 8px 0 12px 0;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #999;
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .meta-table th {
            background-color: #f7f9fa;
            color: #222;
            font-weight: bold;
            width: 24%;
        }
        .section-heading {
            font-size: 11px;
            font-weight: bold;
            color: #17365d;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
        }
        .signature-row {
            margin-top: 45px;
            width: 100%;
            page-break-inside: avoid;
        }
        .sig-block {
            width: 32%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            font-size: 9.5px;
        }
        .sig-line {
            border-top: 1px dashed #555;
            margin: 35px 10px 4px 10px;
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

<!-- ========================================== -->
<!-- 3. MRD INPATIENT FORM                      -->
<!-- ========================================== -->
<div class="hospital-header">
    <div class="hospital-name">GURU HOSPITAL</div>
    <div class="hospital-subtitle">MEDICAL RECORDS DEPARTMENT (MRD)</div>
    <div class="hospital-subtitle">32, A/2, Antony Street, Nethaji Bypass Road, Govt. Medical College Hospital Opp. Dharmapuri - 636 701</div>
    <div class="hospital-subtitle">Ph: 04342-262266 &bull; Cell: +91 7418084266</div>
</div>

<div class="document-title">MEDICAL RECORD INPATIENT FILE HANDOVER & ARCHIVE FORM</div>

<table class="meta-table">
    <tr>
        <th>Patient Full Name</th>
        <td><strong>{{ $admission->patient_name }}</strong></td>
        <th>MRD File Number</th>
        <td><strong>{{ $admission->mrd_no ?: ('MRD-' . str_replace('/', '-', $admission->ip_no)) }}</strong></td>
    </tr>
    <tr>
        <th>UHID / MRN</th>
        <td>{{ $admission->uhid }}</td>
        <th>Inpatient No (IP No)</th>
        <td><strong>{{ $admission->ip_no }}</strong></td>
    </tr>
    <tr>
        <th>Admission Date & Time</th>
        <td>{{ $admission->admission_at?->format('d/m/Y h:i A') }}</td>
        <th>Discharge Date & Time</th>
        <td>{{ $admission->discharge_at?->format('d/m/Y h:i A') }}</td>
    </tr>
    <tr>
        <th>Department</th>
        <td>{{ $admission->department ?? 'Obstetrics & Gynaecology' }}</td>
        <th>Payment Mode / Scheme</th>
        <td>{{ $admission->payment_mode ?: 'Cash / General' }}</td>
    </tr>
    <tr>
        <th>Primary Consultant</th>
        <td colspan="3">{{ $admission->surgeon ?: ($admission->consultant ?? 'Dr. Ambika M.S (O&G)') }}</td>
    </tr>
    <tr>
        <th>Final Diagnosis (ICD-10)</th>
        <td colspan="3">{{ $admission->diagnosis }}</td>
    </tr>
</table>

<div class="section-heading">Case-Sheet Document Audit Checklist</div>
<table class="meta-table" style="margin-top: 6px;">
    <thead>
        <tr>
            <th style="width: 8%;">S.No</th>
            <th style="width: 62%;">Document Description</th>
            <th style="width: 15%; text-align: center;">Verified</th>
            <th style="width: 15%; text-align: center;">Remarks</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Inpatient Admission Face Sheet & Consent Form</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Complete</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Clinical History & Initial Physical Examination</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Verified</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Doctor's Daily Progress Notes & Treatment Charts</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Signed</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Operation Theatre Notes & Anaesthesia Record</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Enclosed</td>
        </tr>
        <tr>
            <td>5</td>
            <td>Laboratory & Diagnostic Investigation Reports</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Attached</td>
        </tr>
        <tr>
            <td>6</td>
            <td>Nursing Vital Signs & Medication Administration Record</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Verified</td>
        </tr>
        <tr>
            <td>7</td>
            <td>Final Approved Discharge Summary</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Approved</td>
        </tr>
    </tbody>
</table>

<div style="margin-top: 15px; font-size: 9.5px; color: #555;">
    <em>Note: This medical record is formally audited and archived in the Guru Hospital Medical Records Department in compliance with NABH / National Hospital Documentation Standards.</em>
</div>

<div class="signature-row">
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Ward Handover Nurse</div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->handover_name ?: 'Staff Nurse' }}</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Floor In-Charge</div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->floor_incharge_name ?: 'Nursing Supervisor' }}</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>MRD In-Charge / Custodian</div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->mrd_incharge_name ?: 'Medical Records Officer' }}</div>
    </div>
</div>

</body>
</html>

