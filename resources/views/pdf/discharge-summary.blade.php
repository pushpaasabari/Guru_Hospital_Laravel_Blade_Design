<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guru Hospital — Discharge Summary — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 14mm 14mm 14mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000000;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        .letterhead-space {
            height: 38mm;
            width: 100%;
        }
        .page-break {
            page-break-before: always;
            break-before: page;
        }
        .doc-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            color: #111111;
            letter-spacing: 0.5px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 6px;
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
            padding: 3px 6px;
            vertical-align: top;
        }
        .cell-lbl {
            font-size: 7pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1px;
            display: block;
        }
        .cell-val {
            font-size: 9.2pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.25;
            display: block;
        }
        .section-header {
            font-size: 10pt;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            border-bottom: 1.2pt solid #222222;
            padding-bottom: 2px;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .prose-text {
            font-size: 9pt;
            line-height: 1.38;
            color: #000000;
            text-align: justify;
            margin-bottom: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 6px;
            border: 0.6pt solid #94a3b8;
        }
        .data-table th {
            background-color: #000000;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 3.5px 6px;
            text-align: left;
            border: 0.6pt solid #000000;
        }
        .data-table td {
            border: 0.6pt solid #94a3b8;
            padding: 3.5px 6px;
            font-size: 9pt;
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
        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            transform: rotate(-30deg);
            font-size: 55px;
            color: rgba(220, 53, 69, 0.08);
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

<!-- ============================================================ -->
<!-- PAGE 1: DEMOGRAPHICS, CLINICAL TEAM, NOTES, NEWBORNS         -->
<!-- ============================================================ -->
<div class="page-1">
    {{-- Letterhead blank clearance for pre-printed hospital stationary --}}
    <div class="letterhead-space"></div>

    <div class="doc-title">DISCHARGE SUMMARY</div>

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
                <span class="cell-val">{{ $admission->admission_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </td>
            <td colspan="4">
                <span class="cell-lbl">DISCHARGE</span>
                <span class="cell-val">{{ $admission->discharge_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="12">
                <span class="cell-lbl">ADDRESS</span>
                <span class="cell-val">{{ str_replace(["\r\n", "\r", "\n"], ', ', $admission->address ?? '—') }}@if($admission->picme_no) &nbsp;<span style="font-size: 8pt; color: #475569; font-weight: normal;">(PICME: {{ $admission->picme_no }})</span>@endif</span>
            </td>
        </tr>
    </table>

    {{-- Box 2: Clinical / Surgical Team (White Fill) --}}
    <table class="info-table bg-white">
        <tr>
            <td style="width: 33.3%;">
                <span class="cell-lbl">CONSULTANT DOCTOR</span>
                <span class="cell-val">{{ $admission->consultant ?: ($admission->surgeon ?: 'Dr. Ambika MS OG') }}</span>
            </td>
            <td style="width: 33.3%;">
                <span class="cell-lbl">DEPARTMENT</span>
                <span class="cell-val">{{ $admission->department ?? 'Obstetrics & Gynaecology' }}</span>
            </td>
            <td style="width: 33.4%;">
                <span class="cell-lbl">DATE OF SURGERY</span>
                <span class="cell-val">{{ $admission->procedure_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 33.3%;">
                <span class="cell-lbl">SURGEON</span>
                <span class="cell-val">{{ $admission->surgeon ?: ($admission->consultant ?: 'Dr. Ambika MS OG') }}</span>
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
                    <td style="padding: 4px 6px; font-size: 8.5pt;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 28%; font-size: 7pt; font-weight: bold; color: #475569; padding: 1.5px 2px; border: none;">SEX</td>
                                <td style="font-size: 8.5pt; font-weight: bold; padding: 1.5px 2px; border: none;">: {{ $card['sex'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 7pt; font-weight: bold; color: #475569; padding: 1.5px 2px; border: none;">BORN</td>
                                <td style="font-size: 8.5pt; padding: 1.5px 2px; border: none;">: {{ $card['born'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 7pt; font-weight: bold; color: #475569; padding: 1.5px 2px; border: none;">MODE</td>
                                <td style="font-size: 8.5pt; padding: 1.5px 2px; border: none;">: {{ $card['mode'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 7pt; font-weight: bold; color: #475569; padding: 1.5px 2px; border: none;">WEIGHT</td>
                                <td style="font-size: 8.5pt; font-weight: bold; padding: 1.5px 2px; border: none;">: {{ $card['weight'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 7pt; font-weight: bold; color: #475569; padding: 1.5px 2px; border: none;">APGAR</td>
                                <td style="font-size: 8.5pt; font-weight: bold; padding: 1.5px 2px; border: none;">: {{ $card['apgar'] }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 7pt; font-weight: bold; color: #475569; padding: 1.5px 2px; border: none; vertical-align: top;">NEONATAL CARE</td>
                                <td style="font-size: 8.5pt; padding: 1.5px 2px; border: none; vertical-align: top;">: {{ $card['neonatal_care'] }}</td>
                            </tr>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif
</div>

<!-- ============================================================ -->
<!-- PAGE 2: TREATMENT, LABS, DISCHARGE CONDITION, MEDS, ADVICE   -->
<!-- ============================================================ -->
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

    {{-- Investigations (5 columns grid) --}}
    @php
        $investigations = $admission->formatted_investigations;
    @endphp
    @if(!empty($investigations))
        <div class="section-header">INVESTIGATIONS</div>
        <table class="info-table bg-white">
            @foreach(array_chunk($investigations, 5) as $chunk)
                <tr>
                    @foreach($chunk as $inv)
                        <td style="width: 20%; padding: 3px 5px;">
                            <span class="cell-lbl" style="font-size: 6.8pt;">{{ $inv['label'] }}</span>
                            <span class="cell-val" style="font-size: 8.8pt;">{{ $inv['value'] }}</span>
                        </td>
                    @endforeach
                    @if(count($chunk) < 5)
                        @for($i = 0; $i < (5 - count($chunk)); $i++)
                            <td style="width: 20%; border: 0.6pt solid #cbd5e1;">&nbsp;</td>
                        @endfor
                    @endif
                </tr>
            @endforeach
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
    <div class="section-header">ADVICE</div>
    <div style="font-size: 8.8pt; line-height: 1.5; margin-bottom: 6px;">
        @foreach($adviceData['advice_items'] as $item)
            <span style="display: inline-block; margin-right: 18px;">
                <strong>&bull;</strong> {{ $item }}
            </span>
        @endforeach
    </div>

    <div class="section-header">REVIEW & SUTURE REMOVAL</div>
    <div class="review-box">
        <div style="display: table-cell; font-size: 9.5pt; font-weight: bold; vertical-align: middle;">
            {{ $adviceData['review_text'] }}
        </div>
        <div style="display: table-cell; text-align: right; font-size: 11.5pt; font-weight: bold; vertical-align: middle;">
            {{ $adviceData['review_date'] }}
        </div>
    </div>

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
        <div class="sig-cell" style="width: 28%;">
            <div style="font-size: 7.5pt; color: #475569; font-weight: bold;">Prepared by</div>
            <div style="font-size: 9pt; color: #000000; margin-top: 2px;">{{ $admission->handover_name ?: ($admission->creator?->name ?? 'Staff Nurse') }}</div>
        </div>
        <div class="sig-cell" style="width: 44%; text-align: left;">
            <div style="font-size: 9pt; font-weight: bold; color: #000000;">Dr. Karthik Rajan MS, MCh</div>
            <div style="font-size: 7.5pt; color: #475569; margin-top: 1px;">Medical Officer / Resident Consultant Surgeon / O&G</div>
        </div>
        <div class="sig-cell" style="width: 28%; text-align: left;">
            <div style="font-size: 9pt; font-weight: bold; color: #000000;">{{ $admission->consultant ?: ($admission->surgeon ?: 'Dr. Ambika MS OG') }}</div>
            <div style="font-size: 7.5pt; color: #475569; margin-top: 1px;">Reg: {{ $admission->approver?->doctor_reg_no ?? 'TNMC-64821' }}</div>
        </div>
    </div>
</div>

</body>
</html>
