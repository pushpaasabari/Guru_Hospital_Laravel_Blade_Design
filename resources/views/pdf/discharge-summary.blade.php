<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guru Hospital — Discharge Summary — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
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
            margin: 12mm 14mm 14mm 14mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Nirmala', Arial, 'Helvetica Neue', Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000000;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        .letterhead-space {
            height: 33mm;
            width: 100%;
        }
        .page-break {
            page-break-before: always;
            break-before: page;
        }
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
        .page-2-footer {
            margin-top: 22px;
            border-top: 0.5pt solid #94a3b8;
            padding-top: 4px;
            display: table;
            width: 100%;
            table-layout: fixed;
            font-size: 8pt;
            color: #555555;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
            width: 75%;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            width: 25%;
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

        /* Surgical Tabular Format Styles (Mr. Gopalakrishnan Specification) */
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

@if($admission->isDelivery())
<!-- ============================================================ -->
<!-- DELIVERY / OBSTETRIC DISCHARGE SUMMARY (Mrs. Anitha Format)  -->
<!-- ============================================================ -->
<div class="page-1">
    <div class="letterhead-space"></div>

    <div class="doc-title">DISCHARGE SUMMARY</div>

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

    @if($admission->clinical_history)
        <div class="section-header">PRESENTING COMPLAINTS</div>
        <div class="prose-text">{!! nl2br(e($admission->clinical_history)) !!}</div>
    @endif

    @if($admission->examination)
        <div class="section-header">CLINICAL EXAMINATION</div>
        <div class="prose-text">{!! nl2br(e($admission->examination)) !!}</div>
    @endif

    @if($admission->procedure_note)
        <div class="section-header">PROCEDURE NOTE</div>
        <div class="prose-text">{!! \App\Services\DischargeSummaryFormatter::formatProcedureNoteHtml($admission->procedure_note) !!}</div>
    @endif

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
    <div class="running-header">
        <div class="running-header-left">DISCHARGE SUMMARY</div>
        <div class="running-header-right">
            {{ $admission->patient_name }} &nbsp;|&nbsp; IP No. {{ $admission->ip_no }} &nbsp;|&nbsp; UHID {{ $admission->uhid }}
        </div>
    </div>

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

    @if(!empty($adviceData['tamil_note']))
        <div style="margin-top: 4px; margin-bottom: 5px; padding: 4px 8px; background: #f8fafc; border: 1px solid #111; border-left: 3px solid #111; font-family: 'Nirmala', Arial, sans-serif; font-size: 8.2pt; line-height: 1.35; color: #111;">
            <strong style="font-size: 7.8pt; display: block; margin-bottom: 1px;">ஸ்டெண்ட் குறிப்பு (STENT REMOVAL NOTICE):</strong>
            {{ $adviceData['tamil_note'] }}
        </div>
    @endif

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

    <div class="page-2-footer">
        <div class="footer-left">Guru Hospital - Discharge Summary (IP: {{ $admission->ip_no }}) - {{ $admission->patient_name }}</div>
        <div class="footer-right">Page 2 of 2</div>
    </div>
</div>

@else
<!-- ============================================================ -->
<!-- SURGICAL / NON-OBSTETRIC DISCHARGE SUMMARY (Mr. Gopalakrishnan) -->
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
            <td style="width: 14%; font-weight: bold;">{{ strtoupper($admission->consultant ?: ($admission->surgeon ?: 'DR. N. JAYAPRAKASH')) }}</td>
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
            <td>{{ $admission->mrd_no ?: '2026-1247' }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ICD CODE</td>
            <td>{{ $admission->icd_code ?: '—' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="background: #ffffff; border: none; padding: 0;"></td>
            <td style="font-weight: bold; background: #f8fafc;">IP NO :</td>
            <td style="font-weight: bold;">{{ $admission->ip_no }}</td>
        </tr>
    </table>

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
                {{ $admission->clinical_history ? strtok($admission->clinical_history, "\n") : 'Admitted for elective surgical procedure.' }}
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
                {{ $admission->examination ?: 'CVS: NAD  RS: Clear  Abdomen: Soft, no mass, no free fluid, no organomegaly.' }}
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
            <td style="width: 55%; padding: 4px 6px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        @foreach($inv['group1'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold;">{{ $k }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group1'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $v }}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
            <td style="width: 45%; padding: 4px 6px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        @foreach($inv['group2'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold;">{{ $k }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group2'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $v }}</td>
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
            <td colspan="3" style="font-weight: bold;">{{ strtoupper($admission->procedure_name ?: ($admission->procedure_done ?: 'RIGHT URSL & DJ STENTING')) }}</td>
        </tr>
        <tr>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">DATE</td>
            <td style="width: 35%;">{{ $admission->procedure_at?->format('d.m.Y @ h.iA') ?? '07.08.2026 @ 05.00PM' }}</td>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">ANAESTHESIA</td>
            <td style="width: 35%;">{{ $admission->anaesthesia_type ?: 'SA' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">SURGEON</td>
            <td>{{ strtoupper($admission->surgeon ?: 'DR. N. JAYAPRAKASH') }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ANESTHETIST</td>
            <td>{{ strtoupper($admission->anaesthetist ?: 'DR. S.G. ARUN KUMAR') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">ASSISTED BY</td>
            <td>{{ strtoupper($admission->assisted_by ?: ($admission->assistant ?: 'MISS. SOUNDHARYA')) }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ICD CODE</td>
            <td>{{ $admission->icd_code ?: 'N20.0' }}</td>
        </tr>
        <tr>
            <td colspan="4" style="padding: 6px 8px; font-size: 8.5pt; line-height: 1.4; text-align: justify;">
                {!! nl2br(e($admission->procedure_note)) !!}
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
                {!! nl2br(e($admission->treatment_course ?: 'Admitted electively and underwent procedure. Received peri-operative antibiotics. Uneventful hospital stay.')) !!}
            </td>
        </tr>
    </table>

    {{-- CONDITION AT DISCHARGE --}}
    <table class="surgical-table mb-2">
        <tr>
            <td style="width: 25%; font-weight: bold; background: #f1f5f9; font-size: 8.5pt;">CONDITION AT DISCHARGE</td>
            <td style="width: 75%; font-weight: bold; font-size: 9pt;">{{ strtoupper($admission->discharge_condition ? strtok($admission->discharge_condition, "\n-:") : 'STABLE') }}</td>
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
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">REVIEW</td>
            <td colspan="3" style="font-weight: bold; font-size: 8.8pt;">{{ $surgAdvice['review'] }}</td>
        </tr>
        @if(!empty($surgAdvice['tamil_note']))
            <tr>
                <td style="background: #f8fafc;"></td>
                <td colspan="3" style="font-size: 8.5pt; font-family: 'Nirmala', 'Arial Unicode MS', sans-serif; line-height: 1.4; color: #1e293b;">
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
                    {{ strtoupper($admission->handover_name ?: ($admission->consultant ?: ($admission->surgeon ?: 'DR. N. JAYAPRAKASH'))) }}
                </div>
            </td>
            <td style="width: 50%; border: none; padding: 10px 8px 0 8px; text-align: right;">
                <div style="font-size: 8pt; font-weight: bold; color: #475569;">COUNTERSIGNED BY</div>
                <div style="font-size: 9.5pt; font-weight: bold; color: #000; margin-top: 2px;">
                    {{ strtoupper($admission->consultant ?: ($admission->surgeon ?: 'DR. N. JAYAPRAKASH')) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="page-2-footer">
        <div class="footer-left">Guru Hospital - Discharge Summary (IP: {{ $admission->ip_no }}) - {{ $admission->patient_name }}</div>
        <div class="footer-right">Page 2 of 2</div>
    </div>
</div>
@endif

</body>
</html>
