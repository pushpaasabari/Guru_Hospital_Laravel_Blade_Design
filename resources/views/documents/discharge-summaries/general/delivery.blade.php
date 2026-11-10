<div class="discharge-summary-document" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 9.5pt; line-height: 1.4; color: #000;">
    <style>
        .ds-doc-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            color: #111111;
            letter-spacing: 0.5px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        .ds-info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
            border: 1px solid #94a3b8;
        }
        .ds-info-table.bg-gray {
            background-color: #f1f5f9;
        }
        .ds-info-table.bg-white {
            background-color: #ffffff;
        }
        .ds-info-table td, .ds-info-table th {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            vertical-align: top;
        }
        .ds-cell-lbl {
            font-size: 7pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1px;
            display: block;
        }
        .ds-cell-val {
            font-size: 9.2pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.25;
            display: block;
        }
        .ds-section-header {
            font-size: 10pt;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            border-bottom: 1.5px solid #222222;
            padding-bottom: 2px;
            margin-top: 12px;
            margin-bottom: 6px;
        }
        .ds-prose-text {
            font-size: 9pt;
            line-height: 1.4;
            color: #000000;
            text-align: justify;
            margin-bottom: 6px;
        }
        .ds-data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
            border: 1px solid #94a3b8;
        }
        .ds-data-table th {
            background-color: #000000;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 4px 6px;
            text-align: left;
            border: 1px solid #000000;
        }
        .ds-data-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            font-size: 9pt;
            vertical-align: top;
        }
        .ds-running-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #94a3b8;
            padding-bottom: 4px;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }
        .ds-emergency-box {
            background-color: #ebebeb;
            border-left: 4px solid #000000;
            padding: 8px 12px;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .ds-review-box {
            border: 1.5px solid #000000;
            background-color: #f1f5f9;
            padding: 6px 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ds-sig-row {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #444444;
            padding-top: 8px;
            margin-top: 24px;
        }
    </style>

    {{-- Space for Pre-printed Letterhead (First page only) --}}
    <div style="height: 38mm;"></div>

    <div class="ds-doc-title">DISCHARGE SUMMARY</div>

    {{-- Box 1: Patient Demographics --}}
    <table class="ds-info-table bg-gray">
        <colgroup>
            <col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;">
            <col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;">
            <col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;"><col style="width: 8.3333%;">
        </colgroup>
        <tr>
            <td colspan="3">
                <span class="ds-cell-lbl">PATIENT NAME</span>
                <span class="ds-cell-val">{{ $admission->patient_name }}</span>
            </td>
            <td colspan="3">
                <span class="ds-cell-lbl">AGE / SEX</span>
                <span class="ds-cell-val">{{ $admission->age ? $admission->age . ' / ' : '' }}{{ $admission->sex ?? 'Female' }}</span>
            </td>
            <td colspan="3">
                <span class="ds-cell-lbl">IP NO.</span>
                <span class="ds-cell-val">{{ $admission->ip_no }}</span>
            </td>
            <td colspan="3">
                <span class="ds-cell-lbl">UHID NO.</span>
                <span class="ds-cell-val">{{ $admission->uhid }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="ds-cell-lbl">PHONE</span>
                <span class="ds-cell-val">{{ $admission->contact ?? '—' }}</span>
            </td>
            <td colspan="4">
                <span class="ds-cell-lbl">ADMISSION</span>
                <span class="ds-cell-val">{{ $admission->admission_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </td>
            <td colspan="4">
                <span class="ds-cell-lbl">DISCHARGE</span>
                <span class="ds-cell-val">{{ $admission->discharge_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="12">
                <span class="ds-cell-lbl">ADDRESS</span>
                <span class="ds-cell-val">{{ str_replace(["\r\n", "\r", "\n"], ', ', $admission->address ?? '—') }}@if($admission->picme_no) &nbsp;<span style="font-size: 8pt; color: #475569; font-weight: normal;">(PICME: {{ $admission->picme_no }})</span>@endif</span>
            </td>
        </tr>
    </table>

    {{-- Box 2: Clinical / Surgical Team --}}
    <table class="ds-info-table bg-white">
        <tr>
            <td style="width: 33.3%;">
                <span class="ds-cell-lbl">CONSULTANT DOCTOR</span>
                <span class="ds-cell-val">{{ $admission->consultant ?: ($admission->surgeon ?: '—') }}</span>
            </td>
            <td style="width: 33.3%;">
                <span class="ds-cell-lbl">DEPARTMENT</span>
                <span class="ds-cell-val">{{ $admission->department ?: '—' }}</span>
            </td>
            <td style="width: 33.4%;">
                <span class="ds-cell-lbl">DATE OF SURGERY</span>
                <span class="ds-cell-val">{{ $admission->procedure_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 33.3%;">
                <span class="ds-cell-lbl">SURGEON</span>
                <span class="ds-cell-val">{{ $admission->surgeon ?: ($admission->consultant ?: '—') }}</span>
            </td>
            <td style="width: 33.3%;">
                <span class="ds-cell-lbl">ANAESTHETIST</span>
                <span class="ds-cell-val">{{ $admission->anaesthetist ? $admission->anaesthetist . ($admission->anaesthesia_type ? ' (' . $admission->anaesthesia_type . ')' : '') : '—' }}</span>
            </td>
            <td style="width: 33.4%;">
                <span class="ds-cell-lbl">ASSISTED BY</span>
                <span class="ds-cell-val">{{ $admission->assisted_by ?: ($admission->assistant ?: '—') }}</span>
            </td>
        </tr>
    </table>

    {{-- Box 3: Diagnosis & Surgery Performed --}}
    <table class="ds-info-table bg-gray">
        <tr>
            <td style="width: 22%; background-color: #f1f5f9;">
                <span class="ds-cell-lbl" style="font-size: 7.5pt;">DIAGNOSIS</span>
            </td>
            <td style="width: 78%;">
                <span class="ds-cell-val" style="font-size: 9.5pt;">{{ $admission->diagnosis }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 22%; background-color: #f1f5f9;">
                <span class="ds-cell-lbl" style="font-size: 7.5pt;">SURGERY PERFORMED</span>
            </td>
            <td style="width: 78%;">
                <span class="ds-cell-val" style="font-size: 9.2pt;">{{ \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($admission) }}</span>
            </td>
        </tr>
    </table>

    {{-- Presenting Complaints --}}
    @if($admission->clinical_history)
        <div class="ds-section-header">PRESENTING COMPLAINTS</div>
        <div class="ds-prose-text">{!! nl2br(e($admission->clinical_history)) !!}</div>
    @endif

    {{-- Clinical Examination --}}
    @if($admission->examination)
        <div class="ds-section-header">CLINICAL EXAMINATION</div>
        <div class="ds-prose-text">{!! nl2br(e($admission->examination)) !!}</div>
    @endif

    {{-- Procedure Note --}}
    @if($admission->procedure_note)
        <div class="ds-section-header">PROCEDURE NOTE</div>
        <div class="ds-prose-text">{!! \App\Services\DischargeSummaryFormatter::formatProcedureNoteHtml($admission->procedure_note) !!}</div>
    @endif

    {{-- Baby / Newborn Details --}}
    @php
        $newbornCards = $admission->formatted_newborns;
    @endphp
    @if(!empty($newbornCards))
        <div class="ds-section-header">BABY / NEWBORN DETAILS</div>
        <table class="ds-data-table" style="margin-bottom: 2px;">
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

    {{-- Page 2 Break / Web Separator --}}
    <div class="my-4 py-2 border-top border-bottom text-center text-muted small bg-light">
        <i class="bi bi-file-earmark-break me-1"></i> Page 2 (Running Header: {{ $admission->patient_name }} &bull; IP: {{ $admission->ip_no }})
    </div>

    {{-- Treatment Given --}}
    @php
        $treatmentRows = $admission->formatted_treatment_course;
    @endphp
    @if(!empty($treatmentRows))
        <div class="ds-section-header">TREATMENT GIVEN</div>
        <table class="ds-data-table">
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
        <div class="ds-section-header">INVESTIGATIONS</div>
        <table class="ds-info-table bg-white">
            @foreach(array_chunk($investigations, 5) as $chunk)
                <tr>
                    @foreach($chunk as $inv)
                        <td style="width: 20%; padding: 4px 6px;">
                            <span class="ds-cell-lbl" style="font-size: 6.8pt;">{{ $inv['label'] }}</span>
                            <span class="ds-cell-val" style="font-size: 8.8pt;">{{ $inv['value'] }}</span>
                        </td>
                    @endforeach
                    @if(count($chunk) < 5)
                        @for($i = 0; $i < (5 - count($chunk)); $i++)
                            <td style="width: 20%; border: 1px solid #cbd5e1;">&nbsp;</td>
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
    <div class="ds-section-header">CONDITION ON DISCHARGE</div>
    <table class="ds-info-table bg-white">
        <tr>
            <td style="width: 20%;">
                <span class="ds-cell-lbl">General condition</span>
                <span class="ds-cell-val">{{ $cond['general'] }}</span>
            </td>
            <td style="width: 18%;">
                <span class="ds-cell-lbl">BP</span>
                <span class="ds-cell-val">{{ $cond['bp'] }}</span>
            </td>
            <td style="width: 17%;">
                <span class="ds-cell-lbl">Pulse</span>
                <span class="ds-cell-val">{{ $cond['pulse'] }}</span>
            </td>
            <td style="width: 45%;">
                <span class="ds-cell-lbl">P/A</span>
                <span class="ds-cell-val" style="font-size: 8.8pt;">{{ $cond['pa'] }}</span>
            </td>
        </tr>
    </table>

    {{-- Discharge Medications --}}
    @php
        $meds = $admission->formatted_medications;
    @endphp
    @if(!empty($meds))
        <div class="ds-section-header">DISCHARGE MEDICATIONS</div>
        <table class="ds-data-table">
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
    <div class="ds-section-header">ADVICE</div>
    <div style="font-size: 8.8pt; line-height: 1.5; margin-bottom: 6px;">
        @foreach($adviceData['advice_items'] as $item)
            <span style="display: inline-block; margin-right: 18px;">
                <strong>&bull;</strong> {{ $item }}
            </span>
        @endforeach
    </div>
    @endif

    @if(!empty($adviceData['review_text']) || !empty($adviceData['review_date']))
    <div class="ds-section-header">REVIEW & SUTURE REMOVAL</div>
    <div class="ds-review-box">
        @if(!empty($adviceData['review_text']))
        <div style="font-size: 9.5pt; font-weight: bold;">
            {{ $adviceData['review_text'] }}
        </div>
        @endif
        @if(!empty($adviceData['review_date']))
        <div style="font-size: 11.5pt; font-weight: bold;">
            {{ $adviceData['review_date'] }}
        </div>
        @endif
    </div>
    @endif

    {{-- When to Contact / Emergency Box --}}
    <div class="ds-emergency-box">
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
    <div class="ds-sig-row" style="justify-content: flex-end;">
        <div style="text-align: right;">
            @if($admission->consultant || $admission->surgeon)
                <div style="font-size: 9pt; font-weight: bold; color: #000000;">{{ $admission->consultant ?: $admission->surgeon }}</div>
            @endif
            @if($admission->approver?->doctor_reg_no)
                <div style="font-size: 7.5pt; color: #475569; margin-top: 1px;">Reg: {{ $admission->approver->doctor_reg_no }}</div>
            @endif
        </div>
    </div>
</div>
