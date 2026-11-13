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
            <div class="a4-patient-bottom">
                <div class="a4-patient-cell">
                    <span class="a4-lbl">Phone / Contact</span>
                    <span class="a4-val">{{ $admission->contact ?: '—' }}</span>
                </div>
                <div class="a4-patient-cell">
                    <span class="a4-lbl">Address</span>
                    <span class="a4-val">
                        {{ str_replace(["\r\n", "\r", "\n"], ', ', $admission->address ?: '—') }}
                        @if($admission->picme_no)
                            <span style="color: #6B7B80;"> (PICME: {{ $admission->picme_no }})</span>
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
                <span class="a4-val a4-val-bold">{{ $admission->admission_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Date of Procedure / Surgery</span>
                <span class="a4-val">{{ $admission->procedure_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Date of Discharge</span>
                <span class="a4-val a4-val-bold">{{ $admission->discharge_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Consultant / Treating Doctor</span>
                <span class="a4-val a4-val-bold">{{ $admission->consultant ?: ($admission->surgeon ?: '—') }}</span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Ward / Room &amp; Case Type</span>
                <span class="a4-val">
                    {{ match ($admission->surgery_type) { 'delivery' => 'Obstetric / Delivery', 'other' => 'Surgical', 'medical' => 'Medical', default => '—' } }}@if($admission->ward_room_no) &bull; {{ $admission->ward_room_no }}@endif
                </span>
            </div>
            <div class="a4-grid-cell">
                <span class="a4-lbl">Payment Mode</span>
                <span class="a4-val">{{ $admission->payment_mode ?: '—' }}</span>
            </div>
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
        <div class="a4-diagnosis-card">
            <span class="a4-lbl">Final Diagnosis</span>
            <div class="a4-diagnosis-val">{{ $admission->diagnosis ?: '—' }}</div>
        </div>

        <div class="a4-clinical-row">
            <div>
                <span class="a4-lbl">Presenting Complaints</span>
                <span class="a4-val">{!! nl2br(e($sections['complaints'])) !!}</span>
            </div>
            <div>
                <span class="a4-lbl">History of Present Illness</span>
                <span class="a4-val">{!! nl2br(e($sections['hpi'])) !!}</span>
            </div>
        </div>

        @if($sections['has_past_history'] || $admission->isSurgical())
            <div class="a4-history-grid">
                <div><span class="a4-lbl">DM / HT / Asthma / TB</span><span class="a4-val">DM: {{ $pastHist['dm'] }} | HT: {{ $pastHist['ht'] }} | Asthma: {{ $pastHist['asthma'] }} | TB: {{ $pastHist['tb'] }}</span></div>
                <div><span class="a4-lbl">Other Medical</span><span class="a4-val">{{ $pastHist['other'] }}</span></div>
                <div><span class="a4-lbl">Past Surgical History</span><span class="a4-val">{{ $pastHist['past_surgical'] }}</span></div>
                <div><span class="a4-lbl">Allergies / Prior Meds</span><span class="a4-val">{{ $pastHist['allergies'] }} | {{ $pastHist['medications'] }}</span></div>
            </div>
        @else
            <div class="a4-clinical-block">
                <span class="a4-lbl">Past Medical / Surgical History &amp; Allergies</span>
                <span class="a4-val">—</span>
            </div>
        @endif

        <div class="a4-clinical-block" style="margin-bottom: 4px;">
            <span class="a4-lbl">Clinical Examination</span>
            <span class="a4-val">{!! $admission->examination ? nl2br(e($admission->examination)) : '—' !!}</span>
        </div>

        {{-- 6. Investigations --}}
        <div class="a4-block" data-overflow-movable="page1">
            <div class="a4-section-hdr">Investigations</div>
            @if(!empty($investigations))
                @php
                    $invCols = count($investigations) > 10 ? 6 : 5;
                @endphp
                <div class="a4-inv-grid" style="grid-template-columns: repeat({{ $invCols }}, 1fr);">
                    @foreach($investigations as $inv)
                        <div class="a4-inv-box" style="padding: 2.5px 4.5px;">
                            <span class="a4-lbl" style="font-size: 6.2pt;">
                                {{ $inv['label'] }}
                                @if(!empty($inv['unit']))
                                    <span style="font-weight: 400; text-transform: none; color: #64748b; font-size: 5.6pt;">({{ $inv['unit'] }})</span>
                                @endif
                            </span>
                            <span class="a4-val a4-val-bold" style="font-size: 8.2pt; line-height: 1.15;">{{ $inv['value'] ?: '—' }}</span>
                            @if(!empty($inv['normal_range']))
                                <span class="a4-ref" style="display: block; font-size: 5.6pt; color: #64748b; font-weight: 500; line-height: 1; margin-top: 1px;">
                                    Ref: {{ $inv['normal_range'] }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                    @for($empty = count($investigations) % $invCols; $empty > 0 && $empty < $invCols; $empty++)
                        <div class="a4-inv-box"><span class="a4-lbl">&nbsp;</span><span class="a4-val">&nbsp;</span></div>
                    @endfor
                </div>
            @else
                <div class="a4-outlined-card"><span class="a4-val">—</span></div>
            @endif
        </div>

        {{-- 7. Treatment Provided --}}
        <div class="a4-block" data-overflow-movable="page1">
            <div class="a4-section-hdr">Treatment Provided</div>
            @php
                $hasStructuredTreatment = !empty($treatmentRows) && collect($treatmentRows)->contains(fn ($r) => ($r['dose'] ?? '—') !== '—' || strlen((string) ($r['medicine'] ?? '')) <= 70);
            @endphp
            @if($hasStructuredTreatment)
                <table class="a4-table">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Medication / Fluid / Therapy</th>
                            <th style="width: 40%;">Dose &amp; Route</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($treatmentRows as $row)
                            <tr>
                                <td class="a4-val-bold">{{ $row['medicine'] ?: '—' }}</td>
                                <td>{{ $row['dose'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif($admission->treatment_course)
                <div class="a4-outlined-card">
                    <span class="a4-val">{!! nl2br(e($sections['hospital_course'])) !!}</span>
                </div>
            @else
                <div class="a4-outlined-card"><span class="a4-val">—</span></div>
            @endif
        </div>

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

