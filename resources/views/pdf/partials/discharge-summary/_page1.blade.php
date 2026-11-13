@php
    $sections = $admission->formatted_clinical_sections;
    $investigations = $admission->formatted_investigations;
    $pastHist = $sections['past_history'];
    $formatTag = match ($admission->format) {
        'cmchistn' => 'CMCHISTN Government Health Insurance Scheme',
        'private' => 'Private Insurance / TPA Scheme',
        'employee' => 'Hospital Employee Healthcare Scheme',
        default => null,
    };
@endphp

<div class="ds-page ds-page-1">
    <div class="ds-page-content">
        @if($admission->isDraft())
            <div class="ds-watermark">DRAFT COPY</div>
        @endif

        {{-- 1. DISCHARGE SUMMARY --}}
        <div class="ds-title-wrap">
            <h1 class="ds-title">DISCHARGE SUMMARY</h1>
            @if($formatTag)
                <div class="ds-format-tag">{{ $formatTag }}</div>
            @endif
        </div>

        {{-- 2. Patient Information --}}
        <div class="ds-section-hdr">PATIENT INFORMATION</div>
        <table class="ds-grid bg-tint">
            <colgroup>
                <col style="width: 34%;">
                <col style="width: 18%;">
                <col style="width: 24%;">
                <col style="width: 24%;">
            </colgroup>
            <tr>
                <td>
                    <span class="ds-lbl">Patient Name</span>
                    <span class="ds-val">{{ $admission->patient_name ?: '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">Age / Sex</span>
                    <span class="ds-val">{{ $admission->age ? $admission->age . ' Yrs / ' : '' }}{{ $admission->sex ?: '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">UHID No.</span>
                    <span class="ds-val">{{ $admission->uhid ?: '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">IP No.</span>
                    <span class="ds-val">{{ $admission->ip_no ?: '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="ds-lbl">Phone / Contact</span>
                    <span class="ds-val">{{ $admission->contact ?: '—' }}</span>
                </td>
                <td colspan="3">
                    <span class="ds-lbl">Address</span>
                    <span class="ds-val ds-val-normal" style="font-weight: 600;">
                        {{ str_replace(["\r\n", "\r", "\n"], ', ', $admission->address ?: '—') }}
                        @if($admission->picme_no)
                            &nbsp;<span style="color: #475569; font-weight: 600;">(PICME: {{ $admission->picme_no }})</span>
                        @endif
                    </span>
                </td>
            </tr>
        </table>

        {{-- 3. Admission Details --}}
        <div class="ds-section-hdr">ADMISSION DETAILS</div>
        <table class="ds-grid">
            <colgroup>
                <col style="width: 33.3%;">
                <col style="width: 33.3%;">
                <col style="width: 33.4%;">
            </colgroup>
            <tr>
                <td>
                    <span class="ds-lbl">Date of Admission</span>
                    <span class="ds-val">{{ $admission->admission_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">Date of Procedure / Surgery</span>
                    <span class="ds-val">{{ $admission->procedure_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">Date of Discharge</span>
                    <span class="ds-val">{{ $admission->discharge_at?->format('d/m/Y, h:i A') ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="ds-lbl">Consultant / Treating Doctor</span>
                    <span class="ds-val">{{ $admission->consultant ?: ($admission->surgeon ?: '—') }}</span>
                </td>
                <td>
                    <span class="ds-lbl">Department / Specialty</span>
                    <span class="ds-val">{{ $admission->department ?: '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">MRD File No. / Ward</span>
                    <span class="ds-val">
                        {{ $admission->resolved_mrd_no ?: '—' }}@if($admission->ward_room_no) &bull; {{ $admission->ward_room_no }}@endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="ds-lbl">Payment / Insurance Mode</span>
                    <span class="ds-val">{{ $admission->payment_mode ?: '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">ICD Code</span>
                    <span class="ds-val">{{ $admission->icd_code ?: '—' }}</span>
                </td>
                <td>
                    <span class="ds-lbl">Case Type</span>
                    <span class="ds-val">{{ match ($admission->surgery_type) { 'delivery' => 'Obstetric / Delivery', 'other' => 'Surgical', 'medical' => 'Medical', default => '—' } }}</span>
                </td>
            </tr>
        </table>

        @include('pdf.partials.insurance-strip', ['admission' => $admission, 'tableClass' => 'ds-grid bg-tint'])

        {{-- 4. Final Diagnosis --}}
        <div class="ds-section-hdr">FINAL DIAGNOSIS</div>
        <table class="ds-grid bg-tint">
            <tr>
                <td style="padding: 3px 6px;">
                    <span class="ds-val" style="font-size: 9.2pt;">{{ $admission->diagnosis ?: '—' }}</span>
                </td>
            </tr>
        </table>

        {{-- 5. Presenting Complaints & 6. History of Present Illness --}}
        <table class="ds-grid" style="margin-bottom: 3.5px;">
            <tr>
                <td style="width: {{ $sections['hpi'] !== '—' ? '45%' : '100%' }}; padding: 0;">
                    <div class="ds-section-hdr" style="margin: 0; border-bottom: 0.5pt solid #cbd5e1;">PRESENTING COMPLAINTS</div>
                    <div class="ds-prose">{!! nl2br(e($sections['complaints'])) !!}</div>
                </td>
                @if($sections['hpi'] !== '—')
                    <td style="width: 55%; padding: 0;">
                        <div class="ds-section-hdr" style="margin: 0; border-bottom: 0.5pt solid #cbd5e1;">HISTORY OF PRESENT ILLNESS</div>
                        <div class="ds-prose">{!! nl2br(e($sections['hpi'])) !!}</div>
                    </td>
                @endif
            </tr>
        </table>

        {{-- 7. Relevant Medical / Surgical History --}}
        <div class="ds-section-hdr">RELEVANT MEDICAL / SURGICAL HISTORY</div>
        @if($sections['has_past_history'] || $admission->isSurgical())
            <table class="ds-grid">
                <tr>
                    <td style="width: 12%;"><span class="ds-lbl">DM</span><span class="ds-val ds-val-normal">{{ $pastHist['dm'] }}</span></td>
                    <td style="width: 12%;"><span class="ds-lbl">HT</span><span class="ds-val ds-val-normal">{{ $pastHist['ht'] }}</span></td>
                    <td style="width: 12%;"><span class="ds-lbl">Asthma</span><span class="ds-val ds-val-normal">{{ $pastHist['asthma'] }}</span></td>
                    <td style="width: 12%;"><span class="ds-lbl">TB</span><span class="ds-val ds-val-normal">{{ $pastHist['tb'] }}</span></td>
                    <td style="width: 14%;"><span class="ds-lbl">Other</span><span class="ds-val ds-val-normal">{{ $pastHist['other'] }}</span></td>
                    <td style="width: 38%;"><span class="ds-lbl">Past Surgical History</span><span class="ds-val ds-val-normal">{{ $pastHist['past_surgical'] }}</span></td>
                </tr>
                <tr>
                    <td colspan="3"><span class="ds-lbl">Allergies</span><span class="ds-val ds-val-normal">{{ $pastHist['allergies'] }}</span></td>
                    <td colspan="3"><span class="ds-lbl">Prior Medications</span><span class="ds-val ds-val-normal">{{ $pastHist['medications'] }}</span></td>
                </tr>
            </table>
        @else
            <table class="ds-grid">
                <tr>
                    <td style="width: 50%;"><span class="ds-lbl">Past Medical History</span><span class="ds-val ds-val-normal">—</span></td>
                    <td style="width: 50%;"><span class="ds-lbl">Past Surgical History &amp; Allergies</span><span class="ds-val ds-val-normal">—</span></td>
                </tr>
            </table>
        @endif

        {{-- 8. Clinical Examination --}}
        <div class="ds-section-hdr">CLINICAL EXAMINATION</div>
        <div class="ds-prose" style="border: 0.5pt solid #cbd5e1; margin-bottom: 3.5px;">
            {!! $admission->examination ? nl2br(e($admission->examination)) : '—' !!}
        </div>

        {{-- 9. Investigations --}}
        <div data-overflow-movable="page1">
            <div class="ds-section-hdr">INVESTIGATIONS</div>
            @if(!empty($investigations))
                @php
                    $invCols = count($investigations) > 10 ? 6 : 5;
                    $colWidth = count($investigations) > 10 ? '16.66%' : '20%';
                @endphp
                <table class="ds-grid">
                    @foreach(array_chunk($investigations, $invCols) as $chunk)
                        <tr>
                            @foreach($chunk as $inv)
                                <td style="width: {{ $colWidth }}; padding: 1.5px 3.5px;">
                                    <span class="ds-lbl" style="font-size: 6.2pt;">
                                        {{ $inv['label'] }}
                                        @if(!empty($inv['unit']))
                                            <span style="font-weight: normal; text-transform: none; color: #64748b; font-size: 5.6pt;">({{ $inv['unit'] }})</span>
                                        @endif
                                    </span>
                                    <span class="ds-val" style="font-size: 7.8pt; line-height: 1.15;">{{ $inv['value'] }}</span>
                                    @if(!empty($inv['normal_range']))
                                        <span class="ds-ref" style="display: block; font-size: 5.4pt; color: #64748b; font-weight: 500; line-height: 1; margin-top: 0.5px;">
                                            Ref: {{ $inv['normal_range'] }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                            @for($empty = count($chunk); $empty < $invCols; $empty++)
                                <td style="width: {{ $colWidth }};">&nbsp;</td>
                            @endfor
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="ds-prose" style="border: 0.5pt solid #cbd5e1; margin-bottom: 3.5px;">—</div>
            @endif
        </div>

        {{-- 10. Hospital Course --}}
        <div data-overflow-movable="page1">
            <div class="ds-section-hdr">HOSPITAL COURSE</div>
            <div class="ds-prose" style="border: 0.5pt solid #cbd5e1;">
                {!! $sections['hospital_course'] !== '—' ? nl2br(e($sections['hospital_course'])) : '—' !!}
            </div>
        </div>
    </div>
</div>
