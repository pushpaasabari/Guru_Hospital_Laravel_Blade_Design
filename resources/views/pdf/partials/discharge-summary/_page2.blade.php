@php
    $treatmentRows = $admission->formatted_treatment_course;
    $newbornCards = $admission->formatted_newborns;
    $meds = $admission->formatted_medications;
    $cond = $admission->formatted_condition;
    $adviceData = $admission->formatted_advice_and_review;
    $surgAdvice = $admission->isSurgical() ? $admission->formatted_surgical_advice : null;
    $hasSurgerySection = $admission->procedure_name || $admission->procedure_done || $admission->procedure_note || $admission->surgeon || $admission->incision || $admission->findings || !empty($newbornCards) || $admission->postoperative_orders;
@endphp

<div class="ds-page ds-page-2">
    <div class="ds-page-content">
        @if($admission->isDraft())
            <div class="ds-watermark">DRAFT COPY</div>
        @endif

        {{-- Minimal Patient Identification Bar (full-page continuation sheet) --}}
        <div class="ds-p2-idbar">
            <div class="ds-p2-idbar-left">DISCHARGE SUMMARY</div>
            <div class="ds-p2-idbar-right">
                <strong>{{ $admission->patient_name }}</strong> &nbsp;|&nbsp; IP No: {{ $admission->ip_no }} &nbsp;|&nbsp; UHID: {{ $admission->uhid }}
            </div>
        </div>

        {{-- Target slot for any Page 1 overflow blocks moved automatically by Chromium layout pass --}}
        <div data-overflow-target="page2"></div>

        {{-- 1. Treatment Provided --}}
        @php
            $hasStructuredTreatment = !empty($treatmentRows) && collect($treatmentRows)->contains(fn ($r) => ($r['dose'] ?? '—') !== '—' || strlen((string) ($r['medicine'] ?? '')) <= 70);
        @endphp
        <div class="ds-section-hdr" style="margin-top: 2px;">TREATMENT PROVIDED</div>
        @if($hasStructuredTreatment)
            <table class="ds-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Medication / Fluid / Therapy</th>
                        <th style="width: 40%;">Dose &amp; Route</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($treatmentRows as $row)
                        <tr>
                            <td style="font-weight: 600;">{{ $row['medicine'] }}</td>
                            <td>{{ $row['dose'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($admission->treatment_course)
            <div class="ds-prose" style="border: 0.5pt solid #cbd5e1;" data-treatment-prose="1">
                Administered inpatient supportive and peri-operative therapy as documented in Hospital Course.
            </div>
        @else
            <div class="ds-prose" style="border: 0.5pt solid #cbd5e1;">—</div>
        @endif

        {{-- 2. Surgery / Procedures --}}
        @if($hasSurgerySection)
            <div class="ds-section-hdr">SURGERY / PROCEDURES</div>
            <table class="ds-grid">
                <colgroup>
                    <col style="width: 34%;">
                    <col style="width: 33%;">
                    <col style="width: 33%;">
                </colgroup>
                <tr>
                    <td>
                        <span class="ds-lbl">Procedure Performed</span>
                        <span class="ds-val">{{ \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($admission) }}</span>
                    </td>
                    <td>
                        <span class="ds-lbl">Surgeon</span>
                        <span class="ds-val">{{ $admission->surgeon ?: ($admission->consultant ?: '—') }}</span>
                    </td>
                    <td>
                        <span class="ds-lbl">Anaesthetist &amp; Type</span>
                        <span class="ds-val">{{ $admission->anaesthetist ? $admission->anaesthetist . ($admission->anaesthesia_type ? ' (' . $admission->anaesthesia_type . ')' : '') : ($admission->anaesthesia_type ?: '—') }}</span>
                    </td>
                </tr>
                @if($admission->assisted_surgeon || $admission->assisted_by || $admission->assistant || $admission->pediatrician_name || $admission->ot_technician)
                    <tr>
                        <td>
                            <span class="ds-lbl">Assisted Surgeon / Staff</span>
                            <span class="ds-val ds-val-normal">{{ $admission->assisted_surgeon ?: ($admission->assisted_by ?: ($admission->assistant ?: '—')) }}</span>
                        </td>
                        <td>
                            <span class="ds-lbl">Scrub Nurse / OT Tech</span>
                            <span class="ds-val ds-val-normal">
                                {{ collect([$admission->staff_nurse, $admission->ot_technician])->filter()->implode(' / ') ?: '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="ds-lbl">Pediatrician</span>
                            <span class="ds-val ds-val-normal">{{ $admission->pediatrician_name ?: '—' }}</span>
                        </td>
                    </tr>
                @endif
                @if($admission->incision || $admission->findings || $admission->drain || $admission->specimen || $admission->blood_loss_ml || $admission->complications)
                    <tr>
                        <td>
                            <span class="ds-lbl">Incision / Specimen</span>
                            <span class="ds-val ds-val-normal">{{ collect([$admission->incision, $admission->specimen ? 'Specimen: ' . $admission->specimen : null])->filter()->implode(' | ') ?: '—' }}</span>
                        </td>
                        <td>
                            <span class="ds-lbl">Intra-operative Findings</span>
                            <span class="ds-val ds-val-normal">{{ $admission->findings ?: '—' }}</span>
                        </td>
                        <td>
                            <span class="ds-lbl">Drain / Blood Loss / Complications</span>
                            <span class="ds-val ds-val-normal">{{ collect([$admission->drain, $admission->blood_loss_ml ? 'EBL: ' . $admission->blood_loss_ml . ' ml' : null, $admission->complications])->filter()->implode(' | ') ?: 'Nil' }}</span>
                        </td>
                    </tr>
                @endif
            </table>

            @if($admission->procedure_note)
                <div class="ds-prose" style="border: 0.5pt solid #cbd5e1; margin-top: -1px;">
                    <span class="ds-lbl" style="margin-bottom: 1px;">Operative / Procedure Note</span>
                    {!! \App\Services\DischargeSummaryFormatter::formatProcedureNoteHtml($admission->procedure_note) !!}
                </div>
            @endif

            @if($admission->postoperative_orders)
                <div class="ds-prose" style="border: 0.5pt solid #cbd5e1; margin-top: -1px;">
                    <span class="ds-lbl" style="margin-bottom: 1px;">Immediate Postoperative Orders</span>
                    {!! nl2br(e($admission->postoperative_orders)) !!}
                </div>
            @endif

            @if(!empty($newbornCards))
                <table class="ds-table" style="margin-top: 2px;">
                    <thead>
                        <tr>
                            @foreach($newbornCards as $card)
                                <th style="width: {{ count($newbornCards) > 1 ? (100 / count($newbornCards)) . '%' : '100%' }};">
                                    {{ $card['title'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($newbornCards as $card)
                                <td style="padding: 2px 5px; font-size: 7.9pt; background: #ffffff;">
                                    <strong>Sex:</strong> {{ $card['sex'] }} &nbsp;|&nbsp; <strong>Born:</strong> {{ $card['born'] }}<br>
                                    <strong>Mode:</strong> {{ $card['mode'] }} &nbsp;|&nbsp; <strong>Wt:</strong> {{ $card['weight'] }} &nbsp;|&nbsp; <strong>APGAR:</strong> {{ $card['apgar'] }}<br>
                                    <strong>Neonatal Care:</strong> {{ $card['neonatal_care'] }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            @endif
        @endif

        {{-- 3. Discharge Medication --}}
        <div class="ds-section-hdr">DISCHARGE MEDICATION</div>
        @if(!empty($meds))
            <table class="ds-table">
                <thead>
                    <tr>
                        <th style="width: 44%;">Medicine / Drug</th>
                        <th style="width: 18%;">Frequency</th>
                        <th style="width: 18%;">Duration / Qty</th>
                        <th style="width: 20%;">Timing</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meds as $med)
                        <tr>
                            <td style="font-weight: 700;">{{ $med['medicine'] }}</td>
                            <td>{{ $med['frequency'] }}</td>
                            <td>{{ $med['duration'] }}</td>
                            <td>{{ $med['timing'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="ds-prose" style="border: 0.5pt solid #cbd5e1;">—</div>
        @endif

        {{-- 4. Condition at Discharge --}}
        <div class="ds-section-hdr">CONDITION AT DISCHARGE</div>
        @if($admission->isDelivery())
            <table class="ds-grid">
                <tr>
                    <td style="width: 24%;"><span class="ds-lbl">General Condition</span><span class="ds-val">{{ $cond['general'] }}</span></td>
                    <td style="width: 16%;"><span class="ds-lbl">BP</span><span class="ds-val">{{ $cond['bp'] }}</span></td>
                    <td style="width: 16%;"><span class="ds-lbl">Pulse</span><span class="ds-val">{{ $cond['pulse'] }}</span></td>
                    <td style="width: 44%;"><span class="ds-lbl">P/A &amp; Local Exam</span><span class="ds-val">{{ $cond['pa'] }}</span></td>
                </tr>
            </table>
        @else
            <div class="ds-prose" style="border: 0.5pt solid #cbd5e1;">
                {!! $admission->discharge_condition ? nl2br(e($admission->discharge_condition)) : '—' !!}
            </div>
        @endif

        {{-- 5. Discharge Instructions --}}
        @php
            $displayAdviceItems = collect($adviceData['advice_items'] ?? [])
                ->reject(function ($item) use ($surgAdvice) {
                    if (!$surgAdvice) {
                        return false;
                    }
                    return (bool) preg_match('/^(Implant|Diet|Exercise|Emergency\s*Contact|When\s*to\s*Contact|Review)\s*[:\-]/i', trim((string) $item));
                })
                ->values()
                ->all();
        @endphp
        @if(!empty($displayAdviceItems) || $surgAdvice)
            <div class="ds-section-hdr">DISCHARGE INSTRUCTIONS</div>
            <table class="ds-grid bg-tint">
                @if(!empty($displayAdviceItems))
                    <tr>
                        <td colspan="3" style="padding: 2.5px 6px;">
                            @foreach($displayAdviceItems as $item)
                                <span style="display: inline-block; margin-right: 14px; font-size: 8.2pt;"><strong>&bull;</strong> {{ $item }}</span>
                            @endforeach
                        </td>
                    </tr>
                @endif
                @if($surgAdvice)
                    <tr>
                        <td style="width: 30%;"><span class="ds-lbl">Implant / Device</span><span class="ds-val">{{ $surgAdvice['implant'] }}</span></td>
                        <td style="width: 35%;"><span class="ds-lbl">Diet</span><span class="ds-val ds-val-normal">{{ $surgAdvice['diet'] }}</span></td>
                        <td style="width: 35%;"><span class="ds-lbl">Activity / Exercise</span><span class="ds-val ds-val-normal">{{ $surgAdvice['exercise'] }}</span></td>
                    </tr>
                @endif
            </table>
        @endif

        {{-- 6. Follow-up Details --}}
        @php
            $followText = $adviceData['review_text'] ?: ($surgAdvice['review'] ?? '');
            $followDate = $adviceData['review_date'] ?? '';
            $tamilNote = $adviceData['tamil_note'] ?: ($surgAdvice['tamil_note'] ?? '');
        @endphp
        @if($followText || $followDate || $tamilNote)
            <div class="ds-section-hdr">FOLLOW-UP DETAILS</div>
            @if($followText || $followDate)
                <div class="ds-followup-box">
                    <div style="display: table-cell; font-size: 8.7pt; font-weight: 700; vertical-align: middle;">
                        {{ $followText ?: 'Follow-up Review' }}
                    </div>
                    @if($followDate)
                        <div style="display: table-cell; text-align: right; font-size: 9.8pt; font-weight: 700; vertical-align: middle;">
                            {{ $followDate }}
                        </div>
                    @endif
                </div>
            @endif
            @if($tamilNote)
                <div class="ds-tamil-box">
                    <strong style="font-size: 7.6pt; display: block; margin-bottom: 1px;">ஸ்டெண்ட் குறிப்பு (STENT REMOVAL NOTICE):</strong>
                    {{ $tamilNote }}
                </div>
            @endif
        @endif

        {{-- Bottom-anchored Emergency Advice + Signature (full-page continuation footer) --}}
        <div class="ds-page2-bottom">
            {{-- 7. Warning Signs / Emergency Advice --}}
            <div class="ds-section-hdr" style="margin-top: 2px;">WARNING SIGNS / EMERGENCY ADVICE</div>
            <div class="ds-emergency-box">
                <div>
                    <strong>When to Contact:</strong>
                    {{ $surgAdvice['when_to_contact'] ?? 'Report immediately if there is bleeding, fever, severe abdominal pain, wound discharge, or any other emergencies.' }}
                </div>
                <div style="margin-top: 2px;">
                    <strong>Emergency Contact:</strong>
                    {{ $surgAdvice['emergency_contact'] ?? '04342-262266 | +91-7418084266' }}
                </div>
            </div>

            {{-- 8. Consultant / Doctor Signature --}}
            <div class="ds-signature-row">
                <div class="ds-sig-box" style="width: 45%; text-align: left; font-size: 7.4pt; color: #64748b;">
                    Discharge Summary verified clinically
                </div>
                <div class="ds-sig-box" style="width: 55%; text-align: right;">
                    <div class="ds-sig-space"></div>
                    <div class="ds-sig-line">
                        @if($admission->consultant || $admission->surgeon)
                            <div style="font-size: 8.8pt; font-weight: 700; color: #0f172a;">
                                {{ $admission->consultant ?: $admission->surgeon }}
                            </div>
                        @endif
                        @if($admission->approver?->doctor_reg_no)
                            <div style="font-size: 7.4pt; color: #475569;">Reg. No: {{ $admission->approver->doctor_reg_no }}</div>
                        @endif
                        <div style="font-size: 7.2pt; color: #475569;">Consultant / Treating Doctor Signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
