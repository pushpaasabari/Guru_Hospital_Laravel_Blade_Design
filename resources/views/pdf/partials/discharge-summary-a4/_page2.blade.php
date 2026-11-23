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
        @if($hasSurgerySection)
            @php
                $findingsText = trim((string) $admission->findings);
                $fItems = [];
                if ($findingsText !== '' && $findingsText !== '—' && strcasecmp($findingsText, 'nil') !== 0) {
                    $rawLines = preg_split('/(?:\r\n|\r|\n|;)+/', $findingsText);
                    foreach ($rawLines as $l) {
                        $l = trim(preg_replace('/^[\s\-\*\•\d+\.\)]+/', '', $l));
                        if ($l !== '' && $l !== '—' && strcasecmp($l, 'nil') !== 0) {
                            $fItems[] = $l;
                        }
                    }
                    if (count($fItems) <= 1 && preg_match('/[.!?]\s+[A-Z0-9]/', $findingsText)) {
                        $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z0-9])/', $findingsText);
                        $fItems = array_values(array_filter(array_map('trim', $sentences)));
                    }
                }

                $surgeryCells = [];
                $procPerf = \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($admission);
                if ($procPerf && $procPerf !== '—') {
                    $surgeryCells[] = ['lbl' => 'Procedure Performed', 'val' => $procPerf, 'bold' => true];
                }
                $surgeonVal = $admission->surgeon ?: $admission->consultant;
                if ($surgeonVal && $surgeonVal !== '—') {
                    $surgeryCells[] = ['lbl' => 'Surgeon', 'val' => $surgeonVal, 'bold' => true];
                }
                $anaesthVal = $admission->anaesthetist ? $admission->anaesthetist . ($admission->anaesthesia_type ? ' (' . $admission->anaesthesia_type . ')' : '') : $admission->anaesthesia_type;
                if ($anaesthVal && $anaesthVal !== '—') {
                    $surgeryCells[] = ['lbl' => 'Anaesthetist & Type', 'val' => $anaesthVal, 'bold' => false];
                }
                $assistedVal = $admission->assisted_surgeon ?: ($admission->assisted_by ?: $admission->assistant);
                if ($assistedVal && $assistedVal !== '—') {
                    $surgeryCells[] = ['lbl' => 'Assisted Surgeon / Staff', 'val' => $assistedVal, 'bold' => false];
                }
                $nurseOtVal = collect([$admission->staff_nurse, $admission->ot_technician])->filter()->implode(' / ');
                if ($nurseOtVal && $nurseOtVal !== '—') {
                    $surgeryCells[] = ['lbl' => 'Scrub Nurse / OT Tech', 'val' => $nurseOtVal, 'bold' => false];
                }
                if (!empty($admission->pediatrician_name) && $admission->pediatrician_name !== '—') {
                    $surgeryCells[] = ['lbl' => 'Pediatrician', 'val' => $admission->pediatrician_name, 'bold' => false];
                }
                $incisionSpecimen = collect([$admission->incision, $admission->specimen ? 'Specimen: ' . $admission->specimen : null])->filter()->implode(' | ');
                if ($incisionSpecimen && $incisionSpecimen !== '—') {
                    $surgeryCells[] = ['lbl' => 'Incision / Specimen', 'val' => $incisionSpecimen, 'bold' => false];
                }
                $drainBloodComp = collect([$admission->drain, $admission->blood_loss_ml ? 'EBL: ' . $admission->blood_loss_ml . ' ml' : null, $admission->complications])->filter()->implode(' | ');
                if ($drainBloodComp && $drainBloodComp !== '—') {
                    $surgeryCells[] = ['lbl' => 'Drain / Blood Loss / Complications', 'val' => $drainBloodComp, 'bold' => false];
                }
            @endphp

            <div class="a4-section-hdr" style="margin-top: 0;">Surgery / Procedures</div>

            @if(!empty($surgeryCells) || !empty($fItems))
                <div class="a4-grid-3">
                    @foreach($surgeryCells as $cell)
                        <div class="a4-grid-cell">
                            <span class="a4-lbl">{{ $cell['lbl'] }}</span>
                            <span class="a4-val {{ $cell['bold'] ? 'a4-val-bold' : '' }}">{{ $cell['val'] }}</span>
                        </div>
                    @endforeach
                    @if(!empty($fItems))
                        <div class="a4-grid-cell" style="grid-column: span {{ (count($surgeryCells) % 3 === 2) ? 1 : ((count($surgeryCells) % 3 === 1) ? 2 : 3) }};">
                            <span class="a4-lbl">Intra-operative Findings</span>
                            <div class="a4-val">
                                <ul class="a4-findings-list">
                                    @foreach($fItems as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($admission->procedure_note && trim($admission->procedure_note) !== '—')
                <div class="a4-outlined-card">
                    <span class="a4-lbl">Operative / Procedure Note</span>
                    <div class="a4-val a4-procedure-note">
                        {!! \App\Services\DischargeSummaryFormatter::formatProcedureNoteHtml($admission->procedure_note) !!}
                    </div>
                </div>
            @endif

            @php
                $postOpRows = $admission->formatted_postoperative_orders;
                $hasInstructionsCol = collect($postOpRows)->contains(fn ($r) => !empty($r['instruction']));
            @endphp
            @if(!empty($postOpRows))
                <div class="a4-outlined-card" style="padding: 3.5px 6px;">
                    <span class="a4-lbl" style="margin-bottom: 2px;">Immediate Postoperative Orders</span>
                    <table class="a4-table a4-postop-table">
                        <thead>
                            <tr>
                                <th style="width: 7%; text-align: center;">#</th>
                                <th style="width: {{ $hasInstructionsCol ? '63%' : '93%' }};">Clinical Order / Medication / Fluid</th>
                                @if($hasInstructionsCol)
                                    <th style="width: 30%;">Route / Frequency / Timing</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($postOpRows as $row)
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">{{ $row['sno'] }}</td>
                                    <td class="a4-val-bold">{{ $row['order'] }}</td>
                                    @if($hasInstructionsCol)
                                        <td>{{ $row['instruction'] ?: '—' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

        {{-- Bilingual Stent Removal Notice (Both English & Tamil with Date in BOLD) --}}
        @if(\App\Services\DischargeSummaryFormatter::hasStent($admission))
            <div class="a4-stent-card">
                <div class="a4-stent-hdr">STENT REMOVAL NOTICE / ஸ்டெண்ட் அகற்றும் குறிப்பு</div>
                <div class="a4-stent-en">
                    {!! \App\Services\DischargeSummaryFormatter::getStentRemovalNoticeEn($admission) !!}
                </div>
                <div class="a4-stent-ta">
                    {!! \App\Services\DischargeSummaryFormatter::getStentRemovalNoticeTa($admission) !!}
                </div>
            </div>
        @elseif($tamilNote)
            <div class="a4-tamil-card">
                <span class="a4-lbl" style="color: #0F5C63;">ஸ்டெண்ட் குறிப்பு (Stent Removal Notice)</span>
                {{ $tamilNote }}
            </div>
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
        @if(!empty($meds))
            <div class="a4-section-hdr">Discharge Medication</div>
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
        @endif

        {{-- 11. Condition at Discharge, Instructions & Follow-up --}}
        @php
            $condItems = [];
            if (!empty($cond['general']) && $cond['general'] !== '—') $condItems['General Condition'] = $cond['general'];
            if (!empty($cond['bp']) && $cond['bp'] !== '—') $condItems['BP'] = $cond['bp'];
            if (!empty($cond['pulse']) && $cond['pulse'] !== '—') $condItems['Pulse'] = $cond['pulse'];
            if (!empty($cond['spo2']) && $cond['spo2'] !== '—') $condItems['SpO2'] = $cond['spo2'];
            if (!empty($cond['pa']) && $cond['pa'] !== '—') $condItems['Local Exam / P/A'] = $cond['pa'];
            $condCols = count($condItems);
        @endphp

        @if(!empty($condItems))
            <div class="a4-section-hdr">Condition at Discharge</div>
            <div class="a4-grid-{{ min(max($condCols, 1), 5) }}" style="display: grid; grid-template-columns: repeat({{ $condCols }}, 1fr); border: 0.75pt solid #D5E0E2; margin-bottom: 4px;">
                @foreach($condItems as $lbl => $val)
                    <div class="a4-grid-cell" style="padding: 3px 6px; border-right: 0.75pt solid #D5E0E2; border-bottom: none;">
                        <span class="a4-lbl">{{ $lbl }}</span>
                        <span class="a4-val a4-val-bold">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        @elseif($admission->discharge_condition && trim($admission->discharge_condition) !== '—')
            <div class="a4-section-hdr">Condition at Discharge</div>
            <div class="a4-outlined-card">
                <span class="a4-val">{!! nl2br(e($admission->discharge_condition)) !!}</span>
            </div>
        @endif

        {{-- Discharge Instructions --}}
        @php
            $implantVal = $surgAdvice['implant'] ?? null;
            $dietVal = $admission->post_surgery_diet ?: ($surgAdvice['diet'] ?? null);
            $activityVal = $admission->post_surgery_activity ?: ($surgAdvice['exercise'] ?? null);

            $instructionParts = [];
            if (!empty($implantVal) && $implantVal !== '—') {
                $instructionParts[] = '<strong>Implant:</strong> ' . e($implantVal);
            }
            if (!empty($dietVal) && $dietVal !== '—' && strcasecmp($dietVal, 'nil') !== 0) {
                $instructionParts[] = '<strong>Diet:</strong> ' . e($dietVal);
            }
            if (!empty($activityVal) && $activityVal !== '—' && strcasecmp($activityVal, 'nil') !== 0) {
                $instructionParts[] = '<strong>Activity:</strong> ' . e($activityVal);
            }
        @endphp

        @if(!empty($instructionParts) || !empty($displayAdviceItems))
            <div class="a4-outlined-card">
                <span class="a4-lbl">Discharge Instructions</span>
                @if(!empty($instructionParts))
                    <div class="a4-val" style="margin-bottom: {{ !empty($displayAdviceItems) ? '3px' : '0' }};">
                        {!! implode(' &nbsp;|&nbsp; ', $instructionParts) !!}
                    </div>
                @endif
                @if(!empty($displayAdviceItems))
                    <div class="a4-val">
                        @foreach($displayAdviceItems as $item)
                            <span style="display: inline-block; margin-right: 12px;">&bull; {{ $item }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Follow-up Review / Suture Removal --}}
        @php
            $text = trim((string) $followText);
            $date = trim((string) $followDate);
            $sutureDate = $admission->suture_removal_date?->format('d/m/Y');
            if ($sutureDate && !str_contains($text, $sutureDate)) {
                $date = $sutureDate;
            }
            if ($text !== '' && $date !== '' && !str_contains($text, $date)) {
                if (preg_match('/[:\-]\s*$/', $text)) {
                    $fullReviewText = rtrim($text, " :\-") . ' : ' . $date;
                } elseif (preg_match('/\b(?:on|at)\s*$/i', $text)) {
                    $fullReviewText = $text . ' ' . $date;
                } else {
                    $fullReviewText = $text . ' (' . $date . ')';
                }
            } else {
                $fullReviewText = $text !== '' ? $text : $date;
            }
        @endphp
        @if($fullReviewText !== '')
            <div class="a4-outlined-card">
                <span class="a4-lbl">Follow-up Review</span>
                <div class="a4-val a4-val-bold">{!! nl2br(e($fullReviewText)) !!}</div>
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
                <div class="a4-sig-left"></div>
                <div class="a4-sig-right">
                    <div class="a4-sig-space"></div>
                    <div class="a4-sig-line">
                        <div class="a4-sig-name">{{ $admission->consultant_with_degree }}</div>
                        <div class="a4-sig-desig">
                            {{ $doctorDesignation ?: 'Consultant / Treating Doctor' }}
                        </div>
                        @if($stamp = $admission->doctor_stamp_details)
                            <div class="a4-stamp-box">
                                <div class="a4-stamp-line a4-stamp-name">{{ $stamp['name'] }}</div>
                                <div class="a4-stamp-line a4-stamp-reg">{{ $stamp['reg_no'] }}</div>
                                <div class="a4-stamp-line a4-stamp-desig">{{ $stamp['line1'] }}</div>
                                @if(!empty($stamp['line2']))
                                    <div class="a4-stamp-line a4-stamp-sub">{{ $stamp['line2'] }}</div>
                                @endif
                                <div class="a4-stamp-line a4-stamp-loc">{{ $stamp['location'] }}</div>
                            </div>
                        @endif
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
