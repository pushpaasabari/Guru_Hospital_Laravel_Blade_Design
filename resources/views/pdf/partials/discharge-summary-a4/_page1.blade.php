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
            <div class="a4-patient-bottom" style="display: grid; grid-template-columns: {{ $admission->attender_name ? '1fr 1.2fr 1.8fr' : '1fr 2.5fr' }};">
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

        {{-- Clinical History Grid: DM / HT / ASTHMA / TB | OTHER MEDICAL | PAST SURGICAL HISTORY | ALLERGIES / PRIOR MEDS --}}
        <div class="a4-history-grid">
            <div class="a4-grid-cell" style="border: none; padding: 2px 4px;">
                <span class="a4-lbl">DM / HT / ASTHMA / TB</span>
                <span class="a4-val">DM: {{ $pastHist['dm'] }} | HT: {{ $pastHist['ht'] }} | Asthma: {{ $pastHist['asthma'] }} | TB: {{ $pastHist['tb'] }}</span>
            </div>
            <div class="a4-grid-cell" style="border: none; padding: 2px 4px;">
                <span class="a4-lbl">OTHER MEDICAL</span>
                <span class="a4-val">{{ $pastHist['other'] }}</span>
            </div>
            <div class="a4-grid-cell" style="border: none; padding: 2px 4px;">
                <span class="a4-lbl">PAST SURGICAL HISTORY</span>
                <span class="a4-val">{{ $pastHist['past_surgical'] }}</span>
            </div>
            <div class="a4-grid-cell" style="border: none; padding: 2px 4px;">
                <span class="a4-lbl">ALLERGIES / PRIOR MEDS</span>
                <span class="a4-val">{{ $pastHist['allergies'] }} | {{ $pastHist['medications'] }}</span>
            </div>
        </div>

        @php
            $hasComplaints = !empty(trim((string)($sections['complaints'] ?? ''))) && trim((string)$sections['complaints']) !== '—';
            $hasHpi = !empty(trim((string)($sections['hpi'] ?? ''))) && trim((string)$sections['hpi']) !== '—';
            $hasExam = !empty(trim((string)($admission->examination ?? ''))) && trim((string)$admission->examination) !== '—';
        @endphp

        @if($hasComplaints || $hasHpi)
            <div class="a4-clinical-row">
                @if($hasComplaints)
                    <div>
                        <span class="a4-lbl">Presenting Complaints</span>
                        <span class="a4-val">{!! nl2br(e($sections['complaints'])) !!}</span>
                    </div>
                @endif
                @if($hasHpi)
                    <div>
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

        {{-- 6. Investigations (Card View: 5 cards per row) --}}
        @php
            $validInvs = collect($investigations)->reject(function ($inv) {
                $val = trim((string)($inv['value'] ?? ''));
                return $val === '' || $val === '—' || $val === '-' || strcasecmp($val, 'nil') === 0 || strcasecmp($val, 'none') === 0;
            })->values()->all();
        @endphp
        @if(!empty($validInvs))
            <div class="a4-block" data-overflow-movable="page1">
                <div class="a4-section-hdr">Investigations</div>
                <div class="a4-inv-grid-5">
                    @foreach($validInvs as $inv)
                        <div class="a4-inv-card">
                            <div class="a4-inv-card-hdr">
                                <span class="a4-inv-card-name">{{ $inv['label'] }}</span>@if(!empty($inv['unit']) && !str_contains((string)$inv['value'], $inv['unit'])) <span class="a4-inv-card-unit">({{ $inv['unit'] }})</span>@endif
                            </div>
                            <div class="a4-inv-card-val">
                                {{ $inv['value'] }}
                            </div>
                            @if(!empty($inv['normal_range']))
                                <div class="a4-inv-card-ref">
                                    Ref: {{ $inv['normal_range'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
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

