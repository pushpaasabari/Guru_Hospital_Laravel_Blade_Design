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
</head>
<body>

<!-- ========================================== -->
<!-- DOCUMENT 1: DISCHARGE SUMMARY              -->
<!-- ========================================== -->
@if($admission->isDelivery())
<div class="page-1">
    {{-- Letterhead blank clearance for pre-printed hospital stationary --}}
    <div class="letterhead-space"></div>

    <div class="doc-title">
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

    {{-- Box 1: Patient Demographics (Light Gray Fill) with 12-column grid --}}
    <table class="info-table bg-gray">
        <colgroup>
            <col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;">
            <col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;">
            <col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;">
        </colgroup>
        <tr>
            <td colspan="3">
                <span class="cell-lbl">PATIENT NAME</span>
                <span class="cell-val">{{ $admission->patient_name }}</span>
            </td>
            <td colspan="3">
                <span class="cell-lbl">AGE / SEX</span>
                <span class="cell-val">{{ $admission->age ? $admission->age . ' / ' : '' }}{{ $admission->sex ?? 'Female' }}</span>
            </td>
            <td colspan="3">
                <span class="cell-lbl">IP NO.</span>
                <span class="cell-val">{{ $admission->ip_no }}</span>
            </td>
            <td colspan="3">
                <span class="cell-lbl">UHID NO.</span>
                <span class="cell-val">{{ $admission->uhid }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="cell-lbl">PHONE</span>
                <span class="cell-val">{{ $admission->contact ?? '—' }}</span>
            </td>
            <td colspan="4">
                <span class="cell-lbl">ADMISSION</span>
                <span class="cell-val">{{ $admission->admission_at?->format('d.m.Y, h:i A') ?? '—' }}</span>
            </td>
            <td colspan="4">
                <span class="cell-lbl">DISCHARGE</span>
                <span class="cell-val">{{ $admission->discharge_at?->format('d.m.Y, h:i A') ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="12">
                <span class="cell-lbl">ADDRESS</span>
                <span class="cell-val">{{ str_replace(["\r\n", "\r", "\n"], ', ', $admission->address ?? '—') }}@if($admission->picme_no) &nbsp;<span style="font-size: 8pt; color: #475569; font-weight: normal;">(PICME: {{ $admission->picme_no }})</span>@endif</span>
            </td>
        </tr>
    </table>

    @include('pdf.partials.insurance-strip', ['admission' => $admission, 'tableClass' => 'info-table'])

    {{-- Box 2: Clinical / Surgical Team (White Fill) --}}
    <table class="info-table bg-white">
        <tr>
            <td style="width: 33.3%;">
                <span class="cell-lbl">CONSULTANT DOCTOR</span>
                <span class="cell-val">{{ $admission->consultant ?: ($admission->surgeon ?: '—') }}</span>
            </td>
            <td style="width: 33.3%;">
                <span class="cell-lbl">DEPARTMENT</span>
                <span class="cell-val">{{ $admission->department ?: '—' }}</span>
            </td>
            <td style="width: 33.4%;">
                <span class="cell-lbl">DATE OF SURGERY</span>
                <span class="cell-val">{{ $admission->procedure_at?->format('d.m.Y, h:i A') ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 33.3%;">
                <span class="cell-lbl">SURGEON</span>
                <span class="cell-val">{{ $admission->surgeon ?: ($admission->consultant ?: '—') }}</span>
            </td>
            <td style="width: 33.3%;">
                <span class="cell-lbl">ANAESTHETIST</span>
                <span class="cell-val">{{ $admission->anaesthetist ? $admission->anaesthetist . ($admission->anaesthesia_type ? ' (' . $admission->anaesthesia_type . ')' : '') : '—' }}</span>
            </td>
            <td style="width: 33.4%;">
                <span class="cell-lbl">ASSISTED BY</span>
                <span class="cell-val">{{ $admission->assisted_by ?: ($admission->assistant ?: '—') }}</span>
            </td>
        </tr>
    </table>

    {{-- Box 3: Diagnosis & Surgery Performed (Light Gray Fill) --}}
    <table class="info-table bg-gray">
        <tr>
            <td style="width: 22%; background-color: #f1f5f9;">
                <span class="cell-lbl" style="font-size: 7.5pt;">DIAGNOSIS</span>
            </td>
            <td style="width: 78%;">
                <span class="cell-val" style="font-size: 9.5pt;">{{ $admission->diagnosis }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 22%; background-color: #f1f5f9;">
                <span class="cell-lbl" style="font-size: 7.5pt;">SURGERY PERFORMED</span>
            </td>
            <td style="width: 78%;">
                <span class="cell-val" style="font-size: 9.2pt;">{{ \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($admission) }}</span>
            </td>
        </tr>
    </table>

    {{-- Section: Presenting Complaints --}}
    @if($admission->clinical_history)
        <div class="section-header">PRESENTING COMPLAINTS</div>
        <div class="prose-text">{!! nl2br(e($admission->clinical_history)) !!}</div>
    @endif

    {{-- Section: Clinical Examination --}}
    @if($admission->examination)
        <div class="section-header">CLINICAL EXAMINATION</div>
        <div class="prose-text">{!! nl2br(e($admission->examination)) !!}</div>
    @endif

    {{-- Section: Investigations (Grid) --}}
    @php
        $investigations = $admission->formatted_investigations;
        $invCols = count($investigations) > 10 ? 6 : 5;
        $colWidth = count($investigations) > 10 ? '16.66%' : '20%';
    @endphp
    @if(!empty($investigations))
        <div class="section-header">INVESTIGATIONS</div>
        <table class="info-table bg-white">
            @foreach(array_chunk($investigations, $invCols) as $chunk)
                <tr>
                    @foreach($chunk as $inv)
                        <td style="width: {{ $colWidth }}; padding: 2px 4px;">
                            <span class="cell-lbl" style="font-size: 6.4pt;">
                                {{ $inv['label'] }}
                                @if(!empty($inv['unit']))
                                    <span style="font-weight: normal; text-transform: none; color: #64748b; font-size: 5.6pt;">({{ $inv['unit'] }})</span>
                                @endif
                            </span>
                            <span class="cell-val" style="font-size: 8.2pt;">{{ $inv['value'] }}</span>
                            @if(!empty($inv['normal_range']))
                                <span style="display: block; font-size: 5.6pt; color: #64748b; font-weight: normal; line-height: 1; margin-top: 0.5px;">
                                    Ref: {{ $inv['normal_range'] }}
                                </span>
                            @endif
                        </td>
                    @endforeach
                    @if(count($chunk) < $invCols)
                        @for($i = 0; $i < ($invCols - count($chunk)); $i++)
                            <td style="width: {{ $colWidth }}; border: 0.6pt solid #cbd5e1;">&nbsp;</td>
                        @endfor
                    @endif
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Section: Procedure Note --}}
    @if($admission->procedure_note)
        <div class="section-header">PROCEDURE NOTE</div>
        <div class="prose-text">{!! \App\Services\DischargeSummaryFormatter::formatProcedureNoteHtml($admission->procedure_note) !!}</div>
    @endif

    {{-- Section: Baby / Newborn Details --}}
    @php
        $newbornCards = $admission->formatted_newborns;
    @endphp
    @if(!empty($newbornCards))
        <div class="section-header">BABY / NEWBORN DETAILS</div>
        <table class="data-table" style="margin-bottom: 2px;">
            <tr>
                @foreach($newbornCards as $card)
                    <th style="width: {{ count($newbornCards) > 1 ? (100 / count($newbornCards)) . '%' : '100%' }};">
                        {{ $card['title'] }}
                    </th>
                @endforeach
            </tr>
            <tr>
                @foreach($newbornCards as $card)
                    <td style="padding: 2.5px 5px; font-size: 8pt;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 28%; font-size: 6.8pt; font-weight: bold; color: #475569; padding: 1px 2px; border: none;">SEX</td>
                                <td style="font-size: 8pt; font-weight: bold; padding: 1px 2px; border: none;">: {{ $card['sex'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 6.8pt; font-weight: bold; color: #475569; padding: 1px 2px; border: none;">BORN</td>
                                <td style="font-size: 8pt; padding: 1px 2px; border: none;">: {{ $card['born'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 6.8pt; font-weight: bold; color: #475569; padding: 1px 2px; border: none;">MODE</td>
                                <td style="font-size: 8pt; padding: 1px 2px; border: none;">: {{ $card['mode'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 6.8pt; font-weight: bold; color: #475569; padding: 1px 2px; border: none;">WEIGHT</td>
                                <td style="font-size: 8pt; font-weight: bold; padding: 1px 2px; border: none;">: {{ $card['weight'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 6.8pt; font-weight: bold; color: #475569; padding: 1px 2px; border: none;">APGAR</td>
                                <td style="font-size: 8pt; font-weight: bold; padding: 1px 2px; border: none;">: {{ $card['apgar'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 6.8pt; font-weight: bold; color: #475569; padding: 1px 2px; border: none; vertical-align: top;">NEONATAL CARE</td>
                                <td style="font-size: 8pt; padding: 1px 2px; border: none; vertical-align: top;">: {{ $card['neonatal_care'] }}</td>
                            </tr>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif
</div>

<div class="page-break"></div>
<div class="page-2">
    {{-- Running Header on Page 2 --}}
    <div class="running-header">
        <div class="running-header-left">DISCHARGE SUMMARY</div>
        <div class="running-header-right">
            {{ $admission->patient_name }} &nbsp;|&nbsp; IP No. {{ $admission->ip_no }} &nbsp;|&nbsp; UHID {{ $admission->uhid }}
        </div>
    </div>

    {{-- Treatment Given --}}
    @php
        $treatmentRows = $admission->formatted_treatment_course;
    @endphp
    @if(!empty($treatmentRows))
        <div class="section-header" style="margin-top: 4px;">TREATMENT GIVEN</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Medication / fluid</th>
                    <th style="width: 40%;">Dose & route</th>
                </tr>
            </thead>
            <tbody>
                @foreach($treatmentRows as $row)
                    <tr>
                        <td style="font-weight: 500;">{{ $row['medicine'] }}</td>
                        <td>{{ $row['dose'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Condition on Discharge --}}
    @php
        $cond = $admission->formatted_condition;
    @endphp
    <div class="section-header">CONDITION ON DISCHARGE</div>
    <table class="info-table bg-white">
        <tr>
            <td style="width: 20%;">
                <span class="cell-lbl">General condition</span>
                <span class="cell-val">{{ $cond['general'] }}</span>
            </td>
            <td style="width: 18%;">
                <span class="cell-lbl">BP</span>
                <span class="cell-val">{{ $cond['bp'] }}</span>
            </td>
            <td style="width: 17%;">
                <span class="cell-lbl">Pulse</span>
                <span class="cell-val">{{ $cond['pulse'] }}</span>
            </td>
            <td style="width: 45%;">
                <span class="cell-lbl">P/A</span>
                <span class="cell-val" style="font-size: 8.8pt;">{{ $cond['pa'] }}</span>
            </td>
        </tr>
    </table>

    {{-- Discharge Medications --}}
    @php
        $meds = $admission->formatted_medications;
    @endphp
    @if(!empty($meds))
        <div class="section-header">DISCHARGE MEDICATIONS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 44%;">Medicine</th>
                    <th style="width: 18%;">Frequency</th>
                    <th style="width: 18%;">Duration</th>
                    <th style="width: 20%;">Timing</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meds as $med)
                    <tr>
                        <td style="font-weight: bold;">{{ $med['medicine'] }}</td>
                        <td>{{ $med['frequency'] }}</td>
                        <td>{{ $med['duration'] }}</td>
                        <td>{{ $med['timing'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Advice & Review --}}
    @php
        $adviceData = $admission->formatted_advice_and_review;
    @endphp
    @if(!empty($adviceData['advice_items']))
        <div class="section-header">ADVICE</div>
        <div style="font-size: 8.8pt; line-height: 1.5; margin-bottom: 6px;">
            @foreach($adviceData['advice_items'] as $item)
                <span style="display: inline-block; margin-right: 18px;">
                    <strong>&bull;</strong> {{ $item }}
                </span>
            @endforeach
        </div>
    @endif

    @if(!empty($adviceData['review_text']) || !empty($adviceData['review_date']))
        <div class="section-header">REVIEW & SUTURE REMOVAL</div>
        <div class="review-box">
            <div style="display: table-cell; font-size: 9.5pt; font-weight: bold; vertical-align: middle;">
                {{ $adviceData['review_text'] ?: 'Review' }}
            </div>
            <div style="display: table-cell; text-align: right; font-size: 11.5pt; font-weight: bold; vertical-align: middle;">
                {{ $adviceData['review_date'] }}
            </div>
        </div>
    @endif

    @if(!empty($adviceData['tamil_note']))
        <div style="margin-top: 4px; margin-bottom: 5px; padding: 4px 8px; background: #f8fafc; border: 1px solid #111; border-left: 3px solid #111; font-family: 'Nirmala UI', 'Nirmala', 'Latha', Arial, sans-serif; font-size: 8.2pt; line-height: 1.35; color: #111;">
            <strong style="font-size: 7.8pt; display: block; margin-bottom: 1px;">ஸ்டெண்ட் குறிப்பு (STENT REMOVAL NOTICE):</strong>
            {{ $adviceData['tamil_note'] }}
        </div>
    @endif

    {{-- When to Contact / Emergency Box --}}
    <div class="emergency-container">
        <div style="margin-bottom: 3px;">
            <span style="font-size: 7.5pt; font-weight: bold; color: #111; width: 130px; display: inline-block; vertical-align: top;">WHEN TO CONTACT</span>
            <span style="font-size: 8.8pt; font-weight: bold; color: #000; display: inline-block;">Report immediately if there is bleeding, fever, abdominal pain, or other emergencies.</span>
        </div>
        <div>
            <span style="font-size: 7.5pt; font-weight: bold; color: #111; width: 130px; display: inline-block; vertical-align: middle;">EMERGENCY</span>
            <span style="font-size: 10.5pt; font-weight: bold; color: #000; display: inline-block; vertical-align: middle;">04342-262266 &nbsp;&nbsp;|&nbsp;&nbsp; +91-7418084266</span>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="sig-container">
        <div class="sig-cell" style="width: 100%; text-align: right;">
            <div style="font-size: 9pt; font-weight: bold; color: #000000;">{{ $admission->consultant ?: ($admission->surgeon ?: '—') }}</div>
            @if($admission->approver?->doctor_reg_no)
                <div style="font-size: 7.5pt; color: #475569; margin-top: 1px;">Reg: {{ $admission->approver->doctor_reg_no }}</div>
            @endif
        </div>
    </div>
</div>

@else
<!-- ============================================================ -->
<!-- SURGICAL / NON-OBSTETRIC DISCHARGE SUMMARY FORMAT -->
<!-- ============================================================ -->
<div class="page-1 surgical-doc">
    <div class="letterhead-space"></div>

    <div class="doc-title">DISCHARGE SUMMARY</div>

    {{-- Top Demographics Grid --}}
    <table class="surgical-table mb-2">
        <tr>
            <td style="width: 12%; font-weight: bold; background: #f8fafc;">NAME</td>
            <td style="width: 44%; font-weight: bold; font-size: 9.5pt;">{{ strtoupper($admission->patient_name) }}</td>
            <td style="width: 10%; font-weight: bold; text-align: center;">{{ $admission->age }}</td>
            <td style="width: 6%; font-weight: bold; text-align: center;">{{ strtoupper(substr($admission->sex ?? 'M', 0, 1)) }}</td>
            <td style="width: 14%; font-weight: bold; background: #f8fafc;">CONSULTANT</td>
            <td style="width: 14%; font-weight: bold;">{{ strtoupper($admission->consultant ?: ($admission->surgeon ?: '—')) }}</td>
        </tr>
    </table>

    <table class="surgical-table mb-2">
        <tr>
            <td style="width: 14%; font-weight: bold; background: #f8fafc; vertical-align: top;">ADDRESS</td>
            <td style="width: 36%; vertical-align: top;">
                {{ str_replace(["\r\n", "\r", "\n"], ' ', $admission->address ?? '—') }}
            </td>
            <td style="width: 18%; font-weight: bold; background: #f8fafc; vertical-align: top;">FINAL DIAGNOSIS</td>
            <td style="width: 32%; font-weight: bold; vertical-align: top;">
                {{ strtoupper($admission->diagnosis) }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">PHONE</td>
            <td>{{ $admission->contact ?? '—' }}</td>
            <td style="font-weight: bold; background: #f8fafc;">PROCEDURE DONE</td>
            <td style="font-weight: bold;">{{ strtoupper($admission->procedure_done ?: ($admission->procedure_name ?: '—')) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">MRD No</td>
            <td>{{ $admission->resolved_mrd_no ?: '—' }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ICD CODE</td>
            <td>{{ $admission->icd_code ?: '—' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="background: #ffffff; border: none; padding: 0;"></td>
            <td style="font-weight: bold; background: #f8fafc;">IP NO :</td>
            <td style="font-weight: bold;">{{ $admission->ip_no }}</td>
        </tr>
    </table>

    @include('pdf.partials.insurance-strip', ['admission' => $admission, 'tableClass' => 'surgical-table mb-2'])

    {{-- Dates Row --}}
    <table class="surgical-table mb-2">
        <tr>
            <td style="width: 10%; font-weight: bold; background: #f8fafc;">DOA</td>
            <td style="width: 23%;">{{ $admission->admission_at?->format('d.m.Y \a\t h.i A') ?? '—' }}</td>
            <td style="width: 10%; font-weight: bold; background: #f8fafc;">DOO</td>
            <td style="width: 23%;">{{ $admission->procedure_at?->format('d.m.Y \a\t h.i A') ?? '—' }}</td>
            <td style="width: 10%; font-weight: bold; background: #f8fafc;">DOD</td>
            <td style="width: 24%;">{{ $admission->discharge_at?->format('d.m.Y \a\t h.i A') ?? '—' }}</td>
        </tr>
    </table>

    {{-- PRESENTING COMPLAINTS --}}
    <table class="surgical-table mb-2">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;">PRESENTING COMPLAINTS</th>
        </tr>
        <tr>
            <td style="padding: 5px 8px; font-size: 8.8pt;">
                {{ $admission->clinical_history ? strtok($admission->clinical_history, "\n") : '—' }}
            </td>
        </tr>
    </table>

    {{-- PAST MEDICAL & SURGICAL HISTORY --}}
    @php
        $hist = $admission->formatted_surgical_past_history;
    @endphp
    <table class="surgical-table mb-2">
        <tr>
            <th style="width: 60%; background: #f1f5f9; text-align: left; font-size: 8.5pt;">PAST MEDICAL HISTORY</th>
            <th style="width: 40%; background: #f1f5f9; text-align: left; font-size: 8.5pt;">PAST SURGICAL HISTORY</th>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 20%;">DM</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 20%;">HT</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 20%;">ASTHMA</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 25%;">TUBERCULOSIS</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 15%;">Other</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['dm'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['ht'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['asthma'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['tb'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['other'] }}</td>
                    </tr>
                </table>
            </td>
            <td style="padding: 4px 6px; font-size: 8.5pt; vertical-align: top;">
                {{ $hist['past_surgical'] }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">ALLERGIES:</span> {{ $hist['allergies'] }}
            </td>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">MEDICATIONS:</span> {{ $hist['medications'] }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">PULSE:</span> {{ $hist['pulse'] }}
            </td>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">BP:</span> {{ $hist['bp'] }}
            </td>
        </tr>
    </table>

    {{-- CLINICAL EXAMINATION --}}
    <table class="surgical-table mb-2">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;">CLINICAL EXAMINATION</th>
        </tr>
        <tr>
            <td style="padding: 5px 8px; font-size: 8.8pt;">
                {{ $admission->examination ?: '—' }}
            </td>
        </tr>
    </table>

    {{-- INVESTIGATIONS --}}
    @php
        $inv = $admission->formatted_surgical_investigations;
    @endphp
    <table class="surgical-table mb-2">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;" colspan="2">INVESTIGATIONS</th>
        </tr>
        <tr>
            <td style="width: 50%; padding: 4px 6px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        @foreach($inv['group1'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 7.2pt; font-weight: bold;">
                                {{ $k }}
                                @if(!empty($inv['units'][$k]))
                                    <span style="font-size: 5.8pt; font-weight: normal; color: #64748b;">({{ $inv['units'][$k] }})</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group1'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $v }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group1'] as $k => $v)
                            <td style="border: none; padding: 0 2px; font-size: 5.8pt; color: #64748b;">
                                @if(!empty($inv['ranges'][$k]))
                                    Ref: {{ $inv['ranges'][$k] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding: 4px 6px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        @foreach($inv['group2'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 7.2pt; font-weight: bold;">
                                {{ $k }}
                                @if(!empty($inv['units'][$k]))
                                    <span style="font-size: 5.8pt; font-weight: normal; color: #64748b;">({{ $inv['units'][$k] }})</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group2'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $v }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group2'] as $k => $v)
                            <td style="border: none; padding: 0 2px; font-size: 5.8pt; color: #64748b;">
                                @if(!empty($inv['ranges'][$k]))
                                    Ref: {{ $inv['ranges'][$k] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
        @if(!empty($inv['imaging']))
            @foreach($inv['imaging'] as $imgKey => $imgVal)
                <tr>
                    <td style="font-weight: bold; background: #f8fafc; font-size: 8pt; width: 25%;">{{ $imgKey }}</td>
                    <td style="font-weight: bold; font-size: 8.5pt;">{{ $imgVal }}</td>
                </tr>
            @endforeach
        @endif
    </table>

    {{-- THEATER NOTES --}}
    <table class="surgical-table mb-2">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;" colspan="4">THEATER NOTES</th>
        </tr>
        <tr>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">SURGERY</td>
            <td colspan="3" style="font-weight: bold;">{{ strtoupper($admission->procedure_name ?: ($admission->procedure_done ?: '—')) }}</td>
        </tr>
        <tr>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">DATE</td>
            <td style="width: 35%;">{{ $admission->procedure_at?->format('d.m.Y @ h.iA') ?? '—' }}</td>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">ANAESTHESIA</td>
            <td style="width: 35%;">{{ $admission->anaesthesia_type ?: '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">SURGEON</td>
            <td>{{ strtoupper($admission->surgeon ?: '—') }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ANESTHETIST</td>
            <td>{{ strtoupper($admission->anaesthetist ?: '—') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">ASSISTED BY</td>
            <td>{{ strtoupper($admission->assisted_by ?: ($admission->assistant ?: '—')) }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ICD CODE</td>
            <td>{{ $admission->icd_code ?: '—' }}</td>
        </tr>
        <tr>
            <td colspan="4" style="padding: 6px 8px; font-size: 8.5pt; line-height: 1.4; text-align: justify;">
                {!! nl2br(e($admission->procedure_note ?: '—')) !!}
            </td>
        </tr>
    </table>

    {{-- COURSE IN THE HOSPITAL --}}
    <table class="surgical-table mb-2">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;">COURSE IN THE HOSPITAL</th>
        </tr>
        <tr>
            <td style="padding: 5px 8px; font-size: 8.8pt; line-height: 1.4; text-align: justify;">
                {!! nl2br(e($admission->treatment_course ?: '—')) !!}
            </td>
        </tr>
    </table>

    {{-- CONDITION AT DISCHARGE --}}
    <table class="surgical-table mb-2">
        <tr>
            <td style="width: 25%; font-weight: bold; background: #f1f5f9; font-size: 8.5pt;">CONDITION AT DISCHARGE</td>
            <td style="width: 75%; font-weight: bold; font-size: 9pt;">{{ strtoupper($admission->discharge_condition ? strtok($admission->discharge_condition, "\n-:") : '—') }}</td>
        </tr>
    </table>

    {{-- DISCHARGE ADVICE --}}
    @php
        $surgDrugs = $admission->formatted_surgical_drugs;
        $surgAdvice = $admission->formatted_surgical_advice;
    @endphp
    <table class="surgical-table mb-2">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;" colspan="4">DISCHARGE ADVICE</th>
        </tr>
        @if(!empty($surgDrugs))
            <tr>
                <td rowspan="{{ count($surgDrugs) + 1 }}" style="width: 12%; font-weight: bold; background: #f8fafc; vertical-align: top; text-align: center;">DRUGS</td>
                <td style="width: 48%; font-weight: bold; font-size: 8pt; background: #f8fafc;">DRUG</td>
                <td style="width: 25%; font-weight: bold; font-size: 8pt; background: #f8fafc;">DOSAGE</td>
                <td style="width: 15%; font-weight: bold; font-size: 8pt; background: #f8fafc; text-align: center;">NO</td>
            </tr>
            @foreach($surgDrugs as $drug)
                <tr>
                    <td style="font-size: 8.8pt; font-weight: 500;">{{ $drug['drug'] }}</td>
                    <td style="font-size: 8.8pt;">{{ $drug['dosage'] }}</td>
                    <td style="font-size: 8.8pt; text-align: center;">{{ $drug['no'] }}</td>
                </tr>
            @endforeach
        @endif
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">IMPLANT</td>
            <td colspan="3" style="font-weight: bold; font-size: 8.8pt;">{{ $surgAdvice['implant'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">DIET</td>
            <td colspan="3" style="font-size: 8.8pt;">{{ $surgAdvice['diet'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">EXERCISE</td>
            <td colspan="3" style="font-size: 8.8pt;">{{ $surgAdvice['exercise'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">EMERGENCY CONTACT</td>
            <td colspan="3" style="font-weight: bold; font-size: 8.8pt;">{{ $surgAdvice['emergency_contact'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">WHEN TO CONTACT</td>
            <td colspan="3" style="font-size: 8.8pt; color: #b91c1c; font-weight: 600;">{{ $surgAdvice['when_to_contact'] }}</td>
        </tr>
        @if(!empty($surgAdvice['review']))
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">REVIEW</td>
                <td colspan="3" style="font-weight: bold; font-size: 8.8pt;">{{ $surgAdvice['review'] }}</td>
            </tr>
        @endif
        @if(!empty($surgAdvice['tamil_note']))
            <tr>
                <td style="background: #f8fafc;"></td>
                <td colspan="3" style="font-size: 8.5pt; font-family: 'Nirmala UI', 'Nirmala', 'Arial Unicode MS', sans-serif; line-height: 1.4; color: #1e293b;">
                    {{ $surgAdvice['tamil_note'] }}
                </td>
            </tr>
        @endif
    </table>

    {{-- Signatures --}}
    <table class="surgical-table" style="margin-top: 15px; border: none;">
        <tr>
            <td style="width: 50%; border: none; padding: 10px 8px 0 8px;">
                <div style="font-size: 8pt; font-weight: bold; color: #475569;">PREPARED BY</div>
                <div style="font-size: 9.5pt; font-weight: bold; color: #000; margin-top: 2px;">
                    {{ strtoupper($admission->handover_name ?: ($admission->consultant ?: ($admission->surgeon ?: '—'))) }}
                </div>
            </td>
            <td style="width: 50%; border: none; padding: 10px 8px 0 8px; text-align: right;">
                <div style="font-size: 8pt; font-weight: bold; color: #475569;">COUNTERSIGNED BY</div>
                <div style="font-size: 9.5pt; font-weight: bold; color: #000; margin-top: 2px;">
                    {{ strtoupper($admission->consultant ?: ($admission->surgeon ?: '—')) }}
                </div>
            </td>
        </tr>
    </table>
</div>
@endif

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
