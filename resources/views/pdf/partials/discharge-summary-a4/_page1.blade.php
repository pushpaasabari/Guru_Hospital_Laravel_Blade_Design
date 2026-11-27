@php
    $sections = $admission->formatted_clinical_sections;
    $investigations = $admission->formatted_investigations;
    $treatmentRows = $admission->formatted_treatment_course;
    $pastHist = $sections['past_history'];
    $insItems = $admission->insurance_details_list;
    $schemeLabel = match ($admission->format) {
        'cmchistn' => 'CMCHISTN Government Health Insurance Scheme',
        'private' => 'Private Insurance / TPA Scheme',
        'employee' => 'Hospital Employee Healthcare Scheme',
        default => 'General Discharge Record',
    };
    $deptSubtitle = collect([$admission->department ?: null, $schemeLabel])->filter()->implode(' | ');
@endphp

<div class="a4-page a4-page-1">
    <div class="a4-page-content">
        @if($admission->isDraft())
            <div class="a4-watermark">DRAFT COPY</div>
        @endif

        {{-- 1. Document Header: Centered Uppercase Title & Clean Hairline Border (No Background) --}}
        <div class="a4-header-band">
            <table class="a4-header-table">
                <tr>
                    <td class="a4-header-left">
                        <div class="a4-meta-pill">
                            <span class="a4-meta-lbl">MRD FILE NO.</span>
                            <span class="a4-meta-val">{{ $admission->resolved_mrd_no ?: '—' }}</span>
                        </div>
                    </td>
                    <td class="a4-header-center">
                        <h1 class="a4-doc-title">DISCHARGE SUMMARY</h1>
                        @if($deptSubtitle)
                            <div class="a4-doc-subtitle">{{ $deptSubtitle }}</div>
                        @endif
                    </td>
                    <td class="a4-header-right">
                        <div class="a4-meta-pill">
                            <span class="a4-meta-lbl">ICD CODE</span>
                            <span class="a4-meta-val">{{ $admission->icd_code ?: '—' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- 2. Patient Banner --}}
        <div class="a4-patient-card">
            <div class="a4-patient-top">
                <div class="a4-patient-cell">
                    <span class="a4-lbl">Patient Name</span>
                    <span class="a4-patient-name">{{ $admission->patient_name ?: '—' }}</span>
                </div>
                <div class="a4-patient-cell">
                    <span class="a4-lbl">Age / Sex</span>
                    <span class="a4-val a4-val-bold">{{ $admission->age ? $admission->age . ' Yrs / ' : '' }}{{ $admission->sex ?: '—' }}</span>
                </div>
                <div class="a4-patient-cell">
                    <span class="a4-lbl">UHID</span>
                    <span class="a4-val a4-val-bold">{{ $admission->uhid ?: '—' }}</span>
                </div>
                <div class="a4-patient-cell">
                    <span class="a4-lbl">IP No.</span>
                    <span class="a4-val a4-val-bold">{{ $admission->ip_no ?: '—' }}</span>
                </div>
            </div>
            <div class="a4-patient-bottom" style="display: grid; grid-template-columns: {{ $admission->attender_name ? 'minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.8fr)' : 'minmax(0, 1fr) minmax(0, 2.5fr)' }};">
                <div class="a4-patient-cell">
                    <span class="a4-lbl">Phone / Contact</span>
                    <span class="a4-val">{{ $admission->contact ?: '—' }}</span>
                </div>
                @if($admission->attender_name)
                    <div class="a4-patient-cell">
                        <span class="a4-lbl">Attender / Relation</span>
                        <span class="a4-val a4-val-bold">
                            {{ $admission->attender_name }}@if($admission->attender_relation) <span style="font-weight: 500; color: #475569;">({{ $admission->attender_relation }})</span>@endif
                        </span>
                    </div>
                @endif
                <div class="a4-patient-cell">
                    <span class="a4-lbl">Address</span>
                    <span class="a4-val">
                        {{ str_replace(["\r\n", "\r", "\n"], ', ', $admission->address ?: '—') }}
                        @if($admission->picme_no)
                            <span style="color: #6B7B80;"> (RCH ID: {{ $admission->picme_no }})</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- 3. Admission Details --}}
        <div class="a4-section-hdr">Admission Details</div>
        <div class="a4-grid-3">
            <div class="a4-grid-cell">
                <span class="a4-lbl">Date of Admission</span>
                <span class="a4-val a4-val-bold">{{ $admission->admission_at?->format('d.m.Y, h:i A') ?? '—' }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Date of Procedure / Surgery</span>
                <span class="a4-val a4-val-bold">{{ $admission->procedure_at?->format('d.m.Y, h:i A') ?? '—' }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Date of Discharge</span>
                <span class="a4-val a4-val-bold">{{ $admission->discharge_at?->format('d.m.Y, h:i A') ?? '—' }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Consultant / Treating Doctor</span>
                <span class="a4-val a4-val-bold">{{ $admission->consultant_with_degree }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Ward / Room &amp; Case Type</span>
                <span class="a4-val">
                    {{ match ($admission->surgery_type) { 'delivery' => 'Obstetric / Delivery', 'other' => 'Surgical', 'medical' => 'Medical', default => '—' } }}@if($admission->ward_room_no) &bull; {{ $admission->ward_room_no }}@endif
                </span>
            </div>
            @if(!empty(trim((string)$admission->payment_mode)) && trim((string)$admission->payment_mode) !== '—')
                <div class="a4-grid-cell">
                    <span class="a4-lbl">Payment Mode</span>
                    <span class="a4-val">{{ $admission->payment_mode }}</span>
                </div>
            @else
                <div class="a4-grid-cell"><span class="a4-lbl">&nbsp;</span><span class="a4-val">&nbsp;</span></div>
            @endif
        </div>

        {{-- 4. Insurance / TPA (3-column pale teal grid; green bold strictly for Approved pre-auth status) --}}
        @if(!empty($insItems))
            <div class="a4-section-hdr">Insurance / TPA</div>
            <div class="a4-grid-3">
                @foreach($insItems as $item)
                    @php
                        $isApprovedPreAuth = $item['label'] === 'Pre-Auth Status'
                            && strcasecmp(trim((string) $item['value']), 'Approved') === 0;
                    @endphp
                    <div class="a4-grid-cell">
                        <span class="a4-lbl">{{ $item['label'] }}</span>
                        <span class="a4-val {{ $isApprovedPreAuth ? 'a4-status-approved' : ($item['label'] === 'Approved Amount' ? 'a4-val-bold' : '') }}">
                            {{ $item['value'] ?: '—' }}
                        </span>
                    </div>
                @endforeach
                @for($emptyCell = count($insItems) % 3; $emptyCell > 0 && $emptyCell < 3; $emptyCell++)
                    <div class="a4-grid-cell"><span class="a4-lbl">&nbsp;</span><span class="a4-val">&nbsp;</span></div>
                @endfor
            </div>
        @endif

        {{-- 5. Diagnosis & Clinical Information --}}
        <div class="a4-section-hdr">Diagnosis &amp; Clinical Summary</div>
        @if(!empty(trim((string)$admission->diagnosis)) && trim((string)$admission->diagnosis) !== '—')
            <div class="a4-diagnosis-card">
                <span class="a4-lbl">Final Diagnosis</span>
                <div class="a4-diagnosis-val">{{ $admission->diagnosis }}</div>
            </div>
        @endif

        @php
            $hasComplaints = !empty(trim((string)($sections['complaints'] ?? ''))) && trim((string)$sections['complaints']) !== '—';
            $hasHpi = !empty(trim((string)($sections['hpi'] ?? ''))) && trim((string)$sections['hpi']) !== '—';
            $hasExam = !empty(trim((string)($admission->examination ?? ''))) && trim((string)$admission->examination) !== '—';
        @endphp

        @if($hasComplaints || $hasHpi)
            <div class="a4-clinical-row" style="grid-template-columns: {{ ($hasComplaints && $hasHpi) ? 'repeat(2, minmax(0, 1fr))' : 'minmax(0, 1fr)' }};">
                @if($hasComplaints)
                    <div class="a4-clinical-col">
                        <span class="a4-lbl">Presenting Complaints</span>
                        <span class="a4-val">{!! nl2br(e($sections['complaints'])) !!}</span>
                    </div>
                @endif
                @if($hasHpi)
                    <div class="a4-clinical-col">
                        <span class="a4-lbl">History of Present Illness</span>
                        <span class="a4-val">{!! nl2br(e($sections['hpi'])) !!}</span>
                    </div>
                @endif
            </div>
        @endif

        @if($hasExam)
            <div class="a4-clinical-block" style="margin-bottom: 4px;">
                <span class="a4-lbl">Clinical Examination</span>
                <span class="a4-val">{!! nl2br(e($admission->examination)) !!}</span>
            </div>
        @endif

        {{-- 6. Investigations (Row 1: Vitals BP/Pulse/SpO2, BG, HB | Row 2: Biochem/Renal/RBS + ECG | Row 3: Viral Screening) --}}
        @php
            $invRows = \App\Services\DischargeSummaryFormatter::groupInvestigationsForGrid($investigations);
        @endphp
        @if(!empty($invRows))
            <div class="a4-block" data-overflow-movable="page1">
                <div class="a4-section-hdr">Investigations</div>
                <div class="a4-inv-grid-5">
                    @foreach($invRows as $rowIdx => $rowCards)
                        @foreach($rowCards as $cardIdx => $inv)
                            <div class="a4-inv-card" @if($rowIdx > 0 && $cardIdx === 0) style="grid-column-start: 1;" @endif>
                                <div class="a4-inv-card-hdr">
                                    <span class="a4-inv-card-name">{{ $inv['label'] }}</span>@if(!empty($inv['unit']) && !str_contains((string)$inv['value'], $inv['unit'])) <span class="a4-inv-card-unit">({{ $inv['unit'] }})</span>@endif
                                </div>
                                <div class="a4-inv-card-val">
                                    {{ $inv['value'] }}
                                </div>
                                <div class="a4-inv-card-ref">
                                    @if(!empty($inv['normal_range']))
                                        Ref: {{ $inv['normal_range'] }}
                                    @else
                                        &nbsp;
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 7. Surgery / Procedures (Part 1: Team, Procedure Performed & Findings) --}}
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

            $isNatDel = $admission->isNaturalDelivery();
            $surgeryCells = [];
            $procPerf = \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($admission);
            if ($procPerf && $procPerf !== '—') {
                $surgeryCells[] = ['lbl' => $isNatDel ? 'Mode of Delivery' : 'Procedure Performed', 'val' => $procPerf, 'bold' => true];
            }
            $surgeonVal = $admission->surgeon ?: $admission->consultant;
            if ($surgeonVal && $surgeonVal !== '—') {
                $surgeryCells[] = ['lbl' => $isNatDel ? 'Delivered By / Obstetrician' : 'Surgeon', 'val' => $surgeonVal, 'bold' => true];
            }
            $anaesthVal = $admission->anaesthetist ? $admission->anaesthetist . ($admission->anaesthesia_type ? ' (' . $admission->anaesthesia_type . ')' : '') : $admission->anaesthesia_type;
            if ($anaesthVal && $anaesthVal !== '—') {
                $surgeryCells[] = ['lbl' => $isNatDel ? 'Analgesia / Anaesthesia' : 'Anaesthetist & Type', 'val' => $anaesthVal, 'bold' => false];
            }
            $assistedVal = $admission->assisted_surgeon ?: ($admission->assisted_by ?: $admission->assistant);
            if ($assistedVal && $assistedVal !== '—') {
                $surgeryCells[] = ['lbl' => $isNatDel ? 'Assisted By / Staff' : 'Assisted Surgeon / Staff', 'val' => $assistedVal, 'bold' => false];
            }
            $nurseOtVal = collect([$admission->staff_nurse, $admission->ot_technician])->filter()->implode(' / ');
            if ($nurseOtVal && $nurseOtVal !== '—') {
                $surgeryCells[] = ['lbl' => $isNatDel ? 'Labour Nurse / Assistant' : 'Scrub Nurse / OT Tech', 'val' => $nurseOtVal, 'bold' => false];
            }
            if (!empty($admission->pediatrician_name) && $admission->pediatrician_name !== '—') {
                $surgeryCells[] = ['lbl' => 'Pediatrician', 'val' => $admission->pediatrician_name, 'bold' => false];
            }
            $incisionSpecimen = collect([$admission->incision, $admission->specimen ? 'Specimen: ' . $admission->specimen : null])->filter()->implode(' | ');
            if ($incisionSpecimen && $incisionSpecimen !== '—') {
                $surgeryCells[] = ['lbl' => $isNatDel ? 'Episiotomy / Placenta' : 'Incision / Specimen', 'val' => $incisionSpecimen, 'bold' => false];
            }
            $drainBloodComp = collect([$admission->drain, $admission->blood_loss_ml ? 'EBL: ' . $admission->blood_loss_ml . ' ml' : null, $admission->complications])->filter()->implode(' | ');
            if ($drainBloodComp && $drainBloodComp !== '—') {
                $surgeryCells[] = ['lbl' => 'Drain / Blood Loss / Complications', 'val' => $drainBloodComp, 'bold' => false];
            }
            $hasSurgeryDetails = !empty($surgeryCells) || !empty($fItems);
        @endphp

        @if($hasSurgeryDetails)
            <div class="a4-block" data-overflow-movable="page1">
                <div class="a4-section-hdr">{{ $isNatDel ? 'Delivery / Labour Details' : 'Surgery / Procedures' }}</div>
                <div class="a4-grid-3">
                    @foreach($surgeryCells as $cell)
                        <div class="a4-grid-cell">
                            <span class="a4-lbl">{{ $cell['lbl'] }}</span>
                            <span class="a4-val {{ $cell['bold'] ? 'a4-val-bold' : '' }}">{{ $cell['val'] }}</span>
                        </div>
                    @endforeach
                    @if(!empty($fItems))
                        <div class="a4-grid-cell" style="grid-column: span {{ (count($surgeryCells) % 3 === 2) ? 1 : ((count($surgeryCells) % 3 === 1) ? 2 : 3) }};">
                            <span class="a4-lbl">{{ $isNatDel ? 'Delivery / Clinical Findings' : 'Intra-operative Findings' }}</span>
                            <div class="a4-val">
                                <ul class="a4-findings-list">
                                    @foreach($fItems as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        @for($emptyCell = count($surgeryCells) % 3; $emptyCell > 0 && $emptyCell < 3; $emptyCell++)
                            <div class="a4-grid-cell"><span class="a4-lbl">&nbsp;</span><span class="a4-val">&nbsp;</span></div>
                        @endfor
                    @endif
                </div>
            </div>
        @endif

        {{-- Page 1 Footer --}}
        <div class="a4-page-bottom">
            <div class="a4-footer-hairline">
                <div class="a4-footer-left">
                    {{ $admission->patient_name ?: '—' }} &nbsp;|&nbsp; IP No: {{ $admission->ip_no ?: '—' }} &nbsp;|&nbsp; UHID: {{ $admission->uhid ?: '—' }}
                </div>
                <div class="a4-footer-right">Page 1</div>
            </div>
        </div>
    </div>
</div>

