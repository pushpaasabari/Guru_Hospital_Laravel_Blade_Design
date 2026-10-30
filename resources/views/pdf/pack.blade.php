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
            height: 35mm;
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
        }
        .meta-table th, .meta-table td {
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 10pt;
            vertical-align: top;
        }
        .meta-table th {
            background-color: #f7f9fa;
            color: #222;
            font-weight: bold;
            width: 22%;
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
    </style>
</head>
<body>

@if($admission->status !== 'approved' && $admission->status !== 'generated')
    <div class="watermark">DRAFT — NOT OFFICIAL</div>
@endif

<!-- ========================================== -->
<!-- DOCUMENT 1: DISCHARGE SUMMARY              -->
<!-- ========================================== -->
<div class="letterhead-space"></div>

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
    <div class="content-block">{!! nl2br(e($admission->clinical_history)) !!}</div>
@endif

@if($admission->examination)
    <div class="section-heading">Clinical Examination</div>
    <div class="content-block">{!! nl2br(e($admission->examination)) !!}</div>
@endif

@if($admission->procedure_note)
    <div class="section-heading">Procedure Note</div>
    <div class="content-block">{!! nl2br(e($admission->procedure_note)) !!}</div>
@endif

<!-- Delivery Workflow Specific Details -->
@if($admission->isDelivery())
    <div class="section-heading">Baby / Newborn Details</div>
    @if($admission->pediatrician_name)
        <div style="font-size: 10pt; margin-bottom: 4px;"><strong>Consultant Paediatrician:</strong> {{ $admission->pediatrician_name }}</div>
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
    <div class="content-block">{!! nl2br(e($admission->treatment_course)) !!}</div>
@endif

@if($admission->investigations)
    <div class="section-heading">Investigations</div>
    <div class="content-block" style="font-family: 'DejaVu Sans Mono', monospace; font-size: 9.5pt; background: #fafafa; padding: 4px; border: 1px solid #ddd;">
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
    <div class="content-block">{!! nl2br(e($admission->discharge_condition)) !!}</div>
@endif

@if($admission->medication_orders || $admission->discharge_advice)
    <div class="section-heading">Discharge Advice & Medications</div>
    @if($admission->medication_orders)
        <div class="content-block">{!! nl2br(e($admission->medication_orders)) !!}</div>
    @endif
    @if($admission->discharge_advice)
        <div class="content-block">{!! nl2br(e($admission->discharge_advice)) !!}</div>
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
        <div style="font-size:9pt; color:#555;">{{ $admission->creator?->name ?? 'Staff Nurse' }}</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Medical Officer / Resident</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div><strong>Consultant Surgeon / O&G</strong></div>
        <div style="font-size:9pt; color:#555;">{{ $admission->surgeon ?: ($admission->consultant ?? 'Dr. Ambika M.S (O&G)') }}</div>
        @if($admission->approver && $admission->approver->doctor_reg_no)
            <div style="font-size:8.5pt; color:#333;">Reg: {{ $admission->approver->doctor_reg_no }}</div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- DOCUMENT 2: OPERATION THEATRE NOTES         -->
<!-- ========================================== -->
<div class="page-break"></div>

{{-- Letterhead Space (First page of OT notes only) --}}
<div style="height: 35mm;"></div>

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
        <td>{{ $admission->procedure_at?->format('d/m/Y') ?? ($admission->admission_at?->format('d/m/Y') ?? now()->format('d/m/Y')) }}</td>
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
        <td>{{ $admission->procedure_at?->format('d/m/Y h:i a') ?? '' }}</td>
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
        <td>{{ $admission->assisted_by ?: ($admission->assistant ?: '') }}</td>
        <td style="font-weight: bold;">ANESTHESIA TYPE</td>
        <td style="font-weight: bold; text-align: center;">:</td>
        <td>{{ $admission->anaesthesia_type ?: 'Spinal' }}</td>
    </tr>
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
    <div style="font-size: 10pt; font-weight: 600; color: #111; margin-top: 2px;">Ph : 04342-262266, Cell : 9418084266</div>
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
                {{ $admission->mrd_no ?: ($admission->ip_no ?: '2026/567') }}
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
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;">
                {{ $admission->no_of_pages ?? ($admission->isDelivery() ? 3 : 2) }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #111; padding: 21px 4px; font-size: 14pt;">Payment Mode</td>
            <td style="font-weight: bold; text-align: center; color: #111; padding: 21px 4px; font-size: 14pt;">:</td>
            <td style="color: #111; padding: 21px 4px; font-size: 14pt;">
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
