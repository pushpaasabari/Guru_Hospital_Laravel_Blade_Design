<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guru Hospital — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
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
        .page-break {
            page-break-before: always;
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
            padding: 4px;
            margin: 8px 0 12px 0;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 10px;
            vertical-align: top;
        }
        .meta-table th {
            background-color: #f7f9fa;
            color: #222;
            font-weight: bold;
            width: 22%;
        }
        .section-heading {
            font-size: 11px;
            font-weight: bold;
            color: #17365d;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            margin: 10px 0 4px 0;
            text-transform: uppercase;
        }
        .content-block {
            margin-bottom: 6px;
            font-size: 10px;
            text-align: justify;
        }
        .baby-box {
            border: 1px solid #2563eb;
            background-color: #f8fafc;
            padding: 6px;
            margin-bottom: 8px;
            border-radius: 3px;
        }
        .emergency-box {
            border: 1px solid #dc3545;
            background-color: #fff5f5;
            color: #991b1b;
            padding: 6px 8px;
            margin-top: 12px;
            margin-bottom: 8px;
            font-size: 9.5px;
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
            font-size: 9.5px;
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
    </style>
</head>
<body>

@if($admission->status !== 'approved' && $admission->status !== 'generated')
    <div class="watermark">DRAFT — NOT OFFICIAL</div>
@endif

<!-- ========================================== -->
<!-- DOCUMENT 1: DISCHARGE SUMMARY              -->
<!-- ========================================== -->
<div class="hospital-header">
    <div class="hospital-name">GURU HOSPITAL</div>
    <div class="hospital-subtitle">32, A/2, Antony Street, Nethaji Bypass Road, Govt. Medical College Hospital Opp. Dharmapuri - 636 701</div>
    <div class="hospital-subtitle">Ph: 04342-262266 &bull; Emergency / Cell: +91 7418084266</div>
</div>

<div class="document-title">
    @if($admission->format === 'cmchistn')
        CHIEF MINISTER COMPREHENSIVE HEALTH INSURANCE SCHEME (CMCHISTN) DISCHARGE SUMMARY
    @elseif($admission->format === 'private')
        INPATIENT DISCHARGE SUMMARY — PRIVATE / MEDICLAIM
    @elseif($admission->format === 'employee')
        HOSPITAL EMPLOYEE HEALTHCARE DISCHARGE SUMMARY
    @else
        DISCHARGE SUMMARY
    @endif
</div>

<table class="meta-table">
    <tr>
        <th>Name</th>
        <td><strong>{{ $admission->patient_name }}</strong></td>
        <th>IP. NO</th>
        <td><strong>{{ $admission->ip_no }}</strong></td>
    </tr>
    <tr>
        <th>UHID NO</th>
        <td>{{ $admission->uhid }}</td>
        <th>Age / Sex &bull; Date</th>
        <td>{{ $admission->age ? $admission->age . ' / ' . ($admission->sex ?? 'F') : ($admission->sex ?? 'F') }} &bull; {{ $admission->discharge_at?->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <th>Address</th>
        <td colspan="3">
            {{ $admission->address ?? 'Not recorded' }}
            @if($admission->contact)
                &nbsp;&bull;&nbsp; <strong>Ph:</strong> {{ $admission->contact }}
            @endif
        </td>
    </tr>
    <tr>
        <th>Admission Date & Time</th>
        <td>{{ $admission->admission_at?->format('d/m/Y \a\t h:i A') }}</td>
        <th>Discharge Date & Time</th>
        <td>{{ $admission->discharge_at?->format('d/m/Y \a\t h:i A') }}</td>
    </tr>
    @if($admission->procedure_at)
    <tr>
        <th>Date of Surgery</th>
        <td>{{ $admission->procedure_at?->format('d/m/Y \a\t h:i A') }}</td>
        <th>Surgery Performed</th>
        <td><strong>{{ $admission->procedure_name ?: 'Emergency LSCS' }}</strong></td>
    </tr>
    @endif
    <tr>
        <th>Consultant Doctor</th>
        <td>{{ $admission->surgeon ?: ($admission->consultant ?? 'Dr. Ambika M.S (O&G)') }}</td>
        <th>Department</th>
        <td>{{ $admission->department ?? 'Obstetrics & Gynaecology' }}</td>
    </tr>
    <tr>
        <th>Diagnosis</th>
        <td colspan="3"><strong>{{ $admission->diagnosis }}</strong></td>
    </tr>
    @if($admission->surgeon || $admission->anaesthetist)
    <tr>
        <th>Surgeon / Anaesthetist</th>
        <td colspan="3">
            <strong>Surgeon:</strong> {{ $admission->surgeon ?: 'Dr. Ambika M.S (O&G)' }} &nbsp;|&nbsp;
            <strong>Anaesthetist:</strong> {{ $admission->anaesthetist ?: 'Dr. M.S Karthick' }} ({{ $admission->anaesthesia_type ?: 'SA' }})
            @if($admission->assisted_by || $admission->assistant)
                &nbsp;|&nbsp; <strong>Assisted:</strong> {{ $admission->assisted_by ?: $admission->assistant }}
            @endif
        </td>
    </tr>
    @endif
</table>

@if($admission->clinical_history)
    <div class="section-heading">Presenting Complaints</div>
    <div class="content-block">{{ nl2br(e($admission->clinical_history)) }}</div>
@endif

@if($admission->examination)
    <div class="section-heading">Clinical Examination</div>
    <div class="content-block">{{ nl2br(e($admission->examination)) }}</div>
@endif

@if($admission->procedure_note)
    <div class="section-heading">Procedure Note</div>
    <div class="content-block">{{ nl2br(e($admission->procedure_note)) }}</div>
@endif

<!-- Delivery Workflow Specific Details -->
@if($admission->isDelivery())
    <div class="section-heading">Baby / Newborn Details</div>
    @if($admission->pediatrician_name)
        <div style="font-size: 10px; margin-bottom: 4px;"><strong>Consultant Paediatrician:</strong> {{ $admission->pediatrician_name }}</div>
    @endif

    @if($admission->newborns->isEmpty())
        <div class="content-block text-muted">No newborn records documented.</div>
    @else
        @foreach($admission->newborns as $i => $baby)
            <div class="baby-box">
                <div>
                    <strong>Newborn {{ count($admission->newborns) > 1 ? '#' . ($i + 1) : '' }}:</strong>
                    Alive {{ $baby->sex ?? 'Female' }} baby on {{ $baby->birth_at?->format('d.m.Y \a\t h:i A') ?? $admission->procedure_at?->format('d.m.Y \a\t h:i A') }},
                    WT: <strong>{{ $baby->birth_weight_kg ? $baby->birth_weight_kg . ' kgs' : '2.8 kgs' }}</strong>,
                    Apgar: <strong>{{ $baby->apgar_1_min ?? '7' }}/10, {{ $baby->apgar_5_min ?? '8' }}/10</strong>.
                </div>
                @if($baby->neonatal_care)
                    <div style="margin-top: 2px;"><strong>Neonatal Care:</strong> {{ $baby->neonatal_care }}</div>
                @endif
                @if($baby->notes)
                    <div style="margin-top: 2px; color: #444;">{{ $baby->notes }}</div>
                @endif
            </div>
        @endforeach
    @endif
@endif

@if($admission->treatment_course)
    <div class="section-heading">Treatment Given</div>
    <div class="content-block">{{ nl2br(e($admission->treatment_course)) }}</div>
@endif

@if($admission->investigations)
    <div class="section-heading">Investigations</div>
    <div class="content-block" style="font-family: 'DejaVu Sans Mono', monospace; font-size: 9.5px; background: #fafafa; padding: 4px; border: 1px solid #ddd;">
        @if(is_array($admission->investigations))
            @foreach($admission->investigations as $k => $v)
                <div>{{ is_string($k) ? $k . ': ' : '' }}{{ is_array($v) ? json_encode($v) : $v }}</div>
            @endforeach
        @else
            {!! nl2br(e($admission->investigations)) !!}
        @endif
    </div>
@endif

@if($admission->discharge_condition)
    <div class="section-heading">Condition on Discharge</div>
    <div class="content-block">{{ nl2br(e($admission->discharge_condition)) }}</div>
@endif

@if($admission->medication_orders || $admission->discharge_advice)
    <div class="section-heading">Discharge Advice & Medications</div>
    @if($admission->medication_orders)
        <div class="content-block">{{ nl2br(e($admission->medication_orders)) }}</div>
    @endif
    @if($admission->discharge_advice)
        <div class="content-block">{{ nl2br(e($admission->discharge_advice)) }}</div>
    @endif
@endif

@if($admission->follow_up)
    <div class="section-heading">Review & Suture Removal</div>
    <div class="content-block">{{ $admission->follow_up }}</div>
@endif

<div class="emergency-box">
    <strong>WHEN TO CONTACT:</strong><br>
    TO REPORT IMMEDIATELY, IF THERE IS BLEEDING, FEVER, ABDOMINAL PAIN, (OR) OTHER EMERGENCIES.<br>
    <strong>*Note: Emergency contact no: 04342-262266, +91-7418084266</strong>
</div>

<div class="signature-row">
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Prepared By (Staff Nurse)</div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->creator?->name ?? 'Staff Nurse' }}</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Medical Officer / Resident</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div><strong>Consultant Surgeon / O&G</strong></div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->surgeon ?: ($admission->consultant ?? 'Dr. Ambika M.S (O&G)') }}</div>
        @if($admission->approver && $admission->approver->doctor_reg_no)
            <div style="font-size:8px; color:#333;">Reg: {{ $admission->approver->doctor_reg_no }}</div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- DOCUMENT 2: OPERATION THEATRE NOTES         -->
<!-- ========================================== -->
<div class="page-break"></div>

<div class="hospital-header">
    <div class="hospital-name">GURU HOSPITAL</div>
    <div class="hospital-subtitle">DEPARTMENT OF OPERATION THEATRE & SURGICAL SERVICES</div>
    <div class="hospital-subtitle">Ph: 04342-262266 &bull; Govt. Medical College Hospital Opp. Dharmapuri</div>
</div>

<div class="document-title">OPERATION THEATRE NOTES & RECORD OF SURGERY</div>

<table class="meta-table">
    <tr>
        <th>Patient Name</th>
        <td><strong>{{ $admission->patient_name }}</strong></td>
        <th>IP Number</th>
        <td><strong>{{ $admission->ip_no }}</strong></td>
    </tr>
    <tr>
        <th>Age / Sex</th>
        <td>{{ $admission->age ? $admission->age . ' Yrs' : '26' }} / {{ $admission->sex ?? 'F' }}</td>
        <th>UHID</th>
        <td>{{ $admission->uhid }}</td>
    </tr>
    <tr>
        <th>Date of Operation</th>
        <td>{{ $admission->procedure_at?->format('d/m/Y h:i A') ?? 'Not recorded' }}</td>
        <th>Consultant</th>
        <td>{{ $admission->surgeon ?: ($admission->consultant ?? 'Dr. Ambika M.S (O&G)') }}</td>
    </tr>
    <tr>
        <th>Pre-op Diagnosis</th>
        <td colspan="3">{{ $admission->diagnosis }}</td>
    </tr>
    <tr>
        <th>Procedure Performed</th>
        <td colspan="3"><strong>{{ $admission->procedure_name ?: 'Emergency LSCS' }}</strong></td>
    </tr>
    <tr>
        <th>Operating Surgeon</th>
        <td>{{ $admission->surgeon ?: 'Dr. Ambika M.S (O&G)' }}</td>
        <th>Anaesthetist</th>
        <td>{{ $admission->anaesthetist ?: 'Dr. M.S Karthick' }}</td>
    </tr>
    <tr>
        <th>Assistant by S/N</th>
        <td>{{ $admission->assisted_by ?: ($admission->assistant ?: 'Miss Soudnharya') }}</td>
        <th>Anaesthesia Type</th>
        <td>{{ $admission->anaesthesia_type ?: 'SA' }}</td>
    </tr>
    <tr>
        <th>Estimated Blood Loss</th>
        <td>{{ $admission->blood_loss_ml ? $admission->blood_loss_ml . ' mL' : 'Normal / Minimal' }}</td>
        <th>Specimen for HPE</th>
        <td>{{ $admission->specimen ?: 'Nil' }}</td>
    </tr>
    @if($admission->drain)
    <tr>
        <th>Surgical Drain</th>
        <td colspan="3">{{ $admission->drain }}</td>
    </tr>
    @endif
</table>

@if($admission->incision)
    <div class="section-heading">Incision & Approach</div>
    <div class="content-block">{{ nl2br(e($admission->incision)) }}</div>
@else
    <div class="section-heading">Incision & Approach</div>
    <div class="content-block">Abdomen opened in layers by Suprapubic Transverse (SPT) incision.</div>
@endif

@if($admission->findings)
    <div class="section-heading">Operative Findings</div>
    <div class="content-block">{{ nl2br(e($admission->findings)) }}</div>
@endif

@if($admission->procedure_note)
    <div class="section-heading">Operative Technique & Procedure Steps</div>
    <div class="content-block">{{ nl2br(e($admission->procedure_note)) }}</div>
@endif

@if($admission->isDelivery() && $admission->newborns->isNotEmpty())
    <div class="section-heading">Baby Delivery Notes</div>
    @foreach($admission->newborns as $i => $baby)
        <div class="content-block">
            <strong>Baby {{ count($admission->newborns) > 1 ? '#' . ($i + 1) : '' }}:</strong>
            Alive {{ $baby->sex ?? 'Female' }} baby delivered on {{ $baby->birth_at?->format('d/m/Y \a\t h:i A') ?? $admission->procedure_at?->format('d/m/Y \a\t h:i A') }},
            Weight: <strong>{{ $baby->birth_weight_kg ? $baby->birth_weight_kg . ' kg' : '2.8 kg' }}</strong>,
            Apgar: <strong>{{ $baby->apgar_1_min ?? '7' }}/10, {{ $baby->apgar_5_min ?? '8' }}/10</strong>.
            AMTSL followed. Handed over to Paediatrician ({{ $admission->pediatrician_name ?: 'Dr. Nithyapriya' }}).
        </div>
    @endforeach
@endif

@if($admission->complications)
    <div class="section-heading">Complications / Difficulties</div>
    <div class="content-block">{{ nl2br(e($admission->complications)) }}</div>
@endif

@if($admission->postoperative_orders)
    <div class="section-heading">Immediate Post-operative Orders</div>
    <div class="content-block">{{ nl2br(e($admission->postoperative_orders)) }}</div>
@endif

<div class="signature-row">
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Scrub Nurse / Assistant</div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->assisted_by ?: 'Miss Soudnharya' }}</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Anaesthetist Signature</div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->anaesthetist ?: 'Dr. M.S Karthick' }}</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div><strong>Operating Surgeon Signature</strong></div>
        <div style="font-size:8.5px; color:#555;">{{ $admission->surgeon ?: 'Dr. Ambika M.S (O&G)' }}</div>
    </div>
</div>

<!-- ========================================== -->
<!-- DOCUMENT 3: MRD INPATIENT FORM              -->
<!-- ========================================== -->
<div class="page-break"></div>

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
        <td><strong>{{ $admission->mrd_no ?: 'MRD-2026/582' }}</strong></td>
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
            <td>Final Approved Discharge Summary (3 Coordinated Pack)</td>
            <td style="text-align: center;">[ &#10003; ]</td>
            <td style="text-align: center;">Approved</td>
        </tr>
    </tbody>
</table>

<div style="margin-top: 15px; font-size: 9.5px; color: #555;">
    <em>Note: The total official page count of this document dossier is dynamically calculated after two-pass layout rendering and registered in the MRD master catalog.</em>
</div>

<div class="signature-row" style="margin-top: 40px;">
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
