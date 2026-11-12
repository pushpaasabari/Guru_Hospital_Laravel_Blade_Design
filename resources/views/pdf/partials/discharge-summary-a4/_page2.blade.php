@php
    $newbornCards = $admission->formatted_newborns;
    $meds = $admission->formatted_medications;
    $cond = $admission->formatted_condition;
    $adviceData = $admission->formatted_advice_and_review;
    $surgAdvice = $admission->isSurgical() ? $admission->formatted_surgical_advice : null;
    $hasSurgerySection = $admission->procedure_name || $admission->procedure_done || $admission->procedure_note || $admission->surgeon || $admission->incision || $admission->findings || $admission->postoperative_orders || !empty($newbornCards);

    $displayAdviceItems = collect($adviceData['advice_items'] ?? [])
        ->reject(function ($item) use ($surgAdvice) {
            if (!$surgAdvice) {
                return false;
            }
            return (bool) preg_match('/^(Implant|Diet|Exercise|Emergency\s*Contact|When\s*to\s*Contact|Review)\s*[:\-]/i', trim((string) $item));
        })
        ->values()
        ->all();

    $followText = $adviceData['review_text'] ?: ($surgAdvice['review'] ?? '');
    $followDate = $adviceData['review_date'] ?? '';
    $tamilNote = $adviceData['tamil_note'] ?: ($surgAdvice['tamil_note'] ?? '');
    $doctorDesignation = collect([
        $admission->department ?: null,
        $admission->approver?->doctor_reg_no ? 'Reg. No: ' . $admission->approver->doctor_reg_no : null,
    ])->filter()->implode(' | ');
@endphp

<div class="a4-page a4-page-2">
    <div class="a4-page-content">
        @if($admission->isDraft())
            <div class="a4-watermark">DRAFT COPY</div>
        @endif

        <div data-overflow-target="page2"></div>

        {{-- 8. Surgery / Procedures (always starts cleanly at top of Page 2) --}}
        <div class="a4-section-hdr" style="margin-top: 0;">Surgery / Procedures</div>
        @if($hasSurgerySection)
            <div class="a4-grid-3">
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Procedure Performed</span>
                    <span class="a4-val a4-val-bold">{{ \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($admission) }}</span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Surgeon</span>
                    <span class="a4-val a4-val-bold">{{ $admission->surgeon ?: ($admission->consultant ?: '—') }}</span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Anaesthetist &amp; Type</span>
                    <span class="a4-val">
                        {{ $admission->anaesthetist ? $admission->anaesthetist . ($admission->anaesthesia_type ? ' (' . $admission->anaesthesia_type . ')' : '') : ($admission->anaesthesia_type ?: '—') }}
                    </span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Assisted Surgeon / Staff</span>
                    <span class="a4-val">{{ $admission->assisted_surgeon ?: ($admission->assisted_by ?: ($admission->assistant ?: '—')) }}</span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Scrub Nurse / OT Tech</span>
                    <span class="a4-val">{{ collect([$admission->staff_nurse, $admission->ot_technician])->filter()->implode(' / ') ?: '—' }}</span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Pediatrician</span>
                    <span class="a4-val">{{ $admission->pediatrician_name ?: '—' }}</span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Incision / Specimen</span>
                    <span class="a4-val">{{ collect([$admission->incision, $admission->specimen ? 'Specimen: ' . $admission->specimen : null])->filter()->implode(' | ') ?: '—' }}</span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Intra-operative Findings</span>
                    <span class="a4-val">{{ $admission->findings ?: '—' }}</span>
                </div>
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Drain / Blood Loss / Complications</span>
                    <span class="a4-val">{{ collect([$admission->drain, $admission->blood_loss_ml ? 'EBL: ' . $admission->blood_loss_ml . ' ml' : null, $admission->complications])->filter()->implode(' | ') ?: '—' }}</span>
                </div>
            </div>

            <div class="a4-outlined-card">
                <span class="a4-lbl">Operative / Procedure Note</span>
                <div class="a4-val">
                    {!! $admission->procedure_note ? \App\Services\DischargeSummaryFormatter::formatProcedureNoteHtml($admission->procedure_note) : '—' !!}
                </div>
            </div>

            @if($admission->postoperative_orders)
                <div class="a4-outlined-card">
                    <span class="a4-lbl">Immediate Postoperative Orders</span>
                    <div class="a4-val">{!! nl2br(e($admission->postoperative_orders)) !!}</div>
                </div>
            @endif
        @else
            <div class="a4-outlined-card"><span class="a4-val">—</span></div>
        @endif

        {{-- 9. Newborns --}}
        @if(!empty($newbornCards))
            <div class="a4-section-hdr">Newborn Details</div>
            <div class="a4-newborn-grid">
                @foreach($newbornCards as $card)
                    <div class="a4-newborn-card">
                        <div class="a4-newborn-title">{{ $card['title'] }}</div>
                        <div class="a4-newborn-body">
                            <div class="a4-nb-line"><span class="a4-nb-lbl">Sex:</span>{{ $card['sex'] }}</div>
                            <div class="a4-nb-line"><span class="a4-nb-lbl">Birth Time:</span>{{ $card['born'] }}</div>
                            <div class="a4-nb-line"><span class="a4-nb-lbl">Mode:</span>{{ $card['mode'] }}</div>
                            <div class="a4-nb-line"><span class="a4-nb-lbl">Weight:</span><strong>{{ $card['weight'] }}</strong> &nbsp;|&nbsp; <span class="a4-nb-lbl">APGAR:</span>{{ $card['apgar'] }}</div>
                            <div class="a4-nb-line"><span class="a4-nb-lbl">Neonatal Care:</span>{{ $card['neonatal_care'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- 10. Discharge Medication --}}
        <div class="a4-section-hdr">Discharge Medication</div>
        @if(!empty($meds))
            <table class="a4-table">
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
                            <td class="a4-val-bold">{{ $med['medicine'] ?: '—' }}</td>
                            <td>{{ $med['frequency'] ?: '—' }}</td>
                            <td>{{ $med['duration'] ?: '—' }}</td>
                            <td>{{ $med['timing'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="a4-outlined-card"><span class="a4-val">—</span></div>
        @endif

        {{-- 11. Condition at Discharge, Instructions & Follow-up --}}
        <div class="a4-section-hdr">Condition at Discharge</div>
        @if($admission->isDelivery())
            <div class="a4-grid-4">
                <div class="a4-grid-cell"><span class="a4-lbl">General Condition</span><span class="a4-val a4-val-bold">{{ $cond['general'] }}</span></div>
                <div class="a4-grid-cell"><span class="a4-lbl">BP</span><span class="a4-val a4-val-bold">{{ $cond['bp'] }}</span></div>
                <div class="a4-grid-cell"><span class="a4-lbl">Pulse</span><span class="a4-val a4-val-bold">{{ $cond['pulse'] }}</span></div>
                <div class="a4-grid-cell"><span class="a4-lbl">Local Exam / P/A</span><span class="a4-val">{{ $cond['pa'] }}</span></div>
            </div>
        @else
            <div class="a4-outlined-card">
                <span class="a4-val">{!! $admission->discharge_condition ? nl2br(e($admission->discharge_condition)) : '—' !!}</span>
            </div>
        @endif

        <div class="a4-outlined-card">
            <span class="a4-lbl">Discharge Instructions</span>
            @if(!empty($displayAdviceItems) || $surgAdvice)
                @if(!empty($displayAdviceItems))
                    <div class="a4-val" style="margin-bottom: {{ $surgAdvice ? '3px' : '0' }};">
                        @foreach($displayAdviceItems as $item)
                            <span style="display: inline-block; margin-right: 12px;">&bull; {{ $item }}</span>
                        @endforeach
                    </div>
                @endif
                @if($surgAdvice)
                    <div class="a4-val">
                        <strong>Implant:</strong> {{ $surgAdvice['implant'] }} &nbsp;|&nbsp;
                        <strong>Diet:</strong> {{ $surgAdvice['diet'] }} &nbsp;|&nbsp;
                        <strong>Activity:</strong> {{ $surgAdvice['exercise'] }}
                    </div>
                @endif
            @else
                <span class="a4-val">—</span>
            @endif
        </div>

        <div class="a4-outlined-card">
            <span class="a4-lbl">Follow-up Review</span>
            @if($followText || $followDate)
                @php
                    $formattedFollowText = $followText
                        ? preg_replace('/(\b\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}\b)/', '<strong>$1</strong>', e($followText))
                        : 'Follow-up Review';
                @endphp
                <div class="a4-val" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{!! $formattedFollowText !!}</span>
                    @if($followDate)
                        <strong style="font-size: 9.5pt;">{{ $followDate }}</strong>
                    @endif
                </div>
            @else
                <span class="a4-val">—</span>
            @endif
        </div>

        @if($tamilNote)
            <div class="a4-tamil-card">
                <span class="a4-lbl" style="color: #0F5C63;">ஸ்டெண்ட் குறிப்பு (Stent Removal Notice)</span>
                {{ $tamilNote }}
            </div>
        @endif

        {{-- Bottom Anchor: 12. Warning Box + 13. Signature + Page 2 Footer --}}
        <div class="a4-page-bottom">
            <div class="a4-warning-box">
                <div class="a4-warning-title">Warning Signs / Emergency Advice</div>
                <div class="a4-warning-text">
                    <div>
                        <strong>When to Contact:</strong>
                        {{ $surgAdvice['when_to_contact'] ?? 'Report immediately if there is bleeding, fever, severe abdominal pain, wound discharge, or any other emergencies.' }}
                    </div>
                    <div style="margin-top: 2px;">
                        <strong>Emergency Contact:</strong>
                        {{ $surgAdvice['emergency_contact'] ?? '04342-262266 | +91-7418084266' }}
                    </div>
                </div>
            </div>

            <div class="a4-signature-row">
                <div class="a4-sig-left">Verified clinically</div>
                <div class="a4-sig-right">
                    <div class="a4-sig-space"></div>
                    <div class="a4-sig-line">
                        <div class="a4-sig-name">{{ $admission->consultant ?: ($admission->surgeon ?: '—') }}</div>
                        <div class="a4-sig-desig">
                            {{ $doctorDesignation ?: 'Consultant / Treating Doctor' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="a4-footer-hairline">
                <div class="a4-footer-left">
                    {{ $admission->patient_name ?: '—' }} &nbsp;|&nbsp; IP No: {{ $admission->ip_no ?: '—' }} &nbsp;|&nbsp; UHID: {{ $admission->uhid ?: '—' }}
                </div>
                <div class="a4-footer-right">Page 2</div>
            </div>
        </div>
    </div>
</div>
