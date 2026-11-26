<div class="section-block">
    <div class="section-banner section-{{ $sectionKey }}">
        <div class="section-banner-title">
            {{ $sectionMeta['title'] }}
            <span class="section-count-pill">{{ $items->count() }}</span>
        </div>
        <div class="section-banner-sub">{{ $sectionMeta['subtitle'] }}</div>
    </div>

    <table class="day-table">
        <thead>
            <tr>
                <th class="col-sno" style="text-align: center;">S.No</th>
                <th class="col-patient">Patient, Attender &amp; Contact</th>
                @if($sectionKey === 'discharges')
                    <th class="col-timeline">DOA &rarr; DOD &amp; Consultant</th>
                    <th class="col-clinical">Diagnosis, Procedure &amp; Baby</th>
                    <th class="col-advice">Condition, Vitals &amp; Advice</th>
                    <th class="col-admin">Payment &amp; MRD Sign-Off</th>
                @elseif($sectionKey === 'admissions')
                    <th class="col-timeline">DOA, Ward &amp; Payment</th>
                    <th class="col-clinical">Diagnosis, Vitals &amp; Intake</th>
                    <th class="col-procedure">OT Procedure, Team &amp; Delivery</th>
                    <th class="col-status">Status</th>
                @else
                    <th class="col-timeline">Ward Stay, DOA &amp; Payment</th>
                    <th class="col-clinical">Diagnosis, Vitals &amp; Course</th>
                    <th class="col-procedure">OT Procedure, Team &amp; Delivery</th>
                    <th class="col-status">Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($items->values() as $idx => $item)
                @php
                    $isDelivery = $item->surgery_type === 'delivery' || $item->newborns->isNotEmpty();
                    $procLabel = $item->procedure_done
                        ?: ($item->procedure_name ?: ($item->surgery_type === 'medical'
                            ? 'Medical Management' . ($item->diagnosis ? ': ' . $item->diagnosis : '')
                            : '—'));
                    $mrdNo = $item->resolved_mrd_no ?: \App\Models\Admission::defaultMrdNo($item->ip_no);
                    $paySummary = $item->payment_summary ?: 'Cash / General';
                    $stayLabel = $sectionKey === 'inpatients'
                        ? ('Day ' . ($item->census_stay_days ?? $item->stay_days ?? 1))
                        : (($item->stay_days ?? 1) . ' day(s) LOS');
                @endphp
                <tr>
                    <td class="col-sno" style="font-weight: 700;">{{ $idx + 1 }}</td>

                    <td class="col-patient">
                        <div class="pt-name">{{ $item->patient_name }}</div>
                        @if($item->age || $item->sex)
                            <div class="meta-line">{{ collect([$item->age ? $item->age . ' Yrs' : null, $item->sex])->filter()->implode(' • ') }}</div>
                        @endif
                        <div class="meta-line">UHID: <span class="mono">{{ $item->uhid }}</span></div>
                        <div class="meta-line">IPNO: <span class="mono">{{ $item->ip_no }}</span></div>
                        @if($mrdNo)
                            <div class="meta-line">MRD: <span class="mono">{{ $mrdNo }}</span></div>
                        @endif
                        @if($item->attender_name)
                            <div class="meta-line">
                                <strong>Attender:</strong> {{ $item->attender_name }}{{ $item->attender_relation ? ' (' . $item->attender_relation . ')' : '' }}
                            </div>
                        @endif
                        @if($item->contact)
                            <div class="meta-line"><strong>Ph:</strong> {{ $item->contact }}</div>
                        @endif
                        @if($item->address)
                            <div class="meta-line">{{ $item->address }}</div>
                        @endif
                    </td>

                    <td class="col-timeline">
                        <div><span class="team-lbl">DOA:</span> {{ $item->admission_at ? $item->admission_at->format('d.m.Y h:i A') : '—' }}</div>
                        @if($item->procedure_at)
                            <div><span class="team-lbl">DOO:</span> {{ $item->procedure_at->format('d.m.Y h:i A') }}</div>
                        @endif
                        @if($sectionKey === 'discharges')
                            <div>
                                <span class="team-lbl">DOD:</span>
                                <strong style="color: #15803d;">{{ $item->discharge_at ? $item->discharge_at->format('d.m.Y h:i A') : '—' }}</strong>
                            </div>
                        @endif
                        <div style="margin-top: 3px;">
                            <span class="badge badge-neutral">{{ $stayLabel }}</span>
                            <span class="badge badge-neutral" style="text-transform: uppercase;">{{ $item->format ?: 'general' }}</span>
                        </div>
                        @if($item->department || $item->ward_room_no)
                            <div class="meta-line" style="margin-top: 2px;">
                                <strong>Ward/Dept:</strong> {{ collect([$item->department, $item->ward_room_no])->filter()->implode(' • ') }}
                            </div>
                        @endif
                        @if($item->consultant)
                            <div class="meta-line"><strong>Consultant:</strong> {{ $item->consultant }}</div>
                        @endif
                        @if($sectionKey !== 'discharges' && $paySummary !== '—')
                            <div class="meta-line"><strong>Payment:</strong> {{ $paySummary }}</div>
                        @endif
                    </td>

                    @if($sectionKey === 'discharges')
                        <td class="col-clinical">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                                {{ $item->diagnosis ?: '—' }}
                                @if($item->icd_code)
                                    <span class="badge badge-neutral mono">{{ $item->icd_code }}</span>
                                @endif
                            </div>
                            <div style="font-weight: 700;">{{ $procLabel }}</div>
                            @if($item->incision)
                                <div class="meta-line"><strong>Incision:</strong> {{ $item->incision }}</div>
                            @endif
                            @include('pdf.partials.day-report-team-delivery', ['item' => $item, 'isDelivery' => $isDelivery])
                        </td>
                        <td class="col-advice">
                            <div><strong>Condition:</strong> {{ $item->discharge_condition ?: 'Stable' }}</div>
                            @php
                                $dBp = $item->discharge_bp ?: $item->vital_bp;
                                $dPr = $item->discharge_pulse ?: $item->vital_pulse;
                                $dSpo2 = $item->discharge_spo2 ?: $item->vital_spo2;
                            @endphp
                            @if($dBp || $dPr || $dSpo2)
                                <div class="meta-line" style="margin-top: 2px;">
                                    <strong>Disch Vitals:</strong>
                                    {{ collect([$dBp ? 'BP ' . $dBp : null, $dPr ? 'PR ' . $dPr : null, $dSpo2 ? 'SpO2 ' . $dSpo2 : null])->filter()->implode(' | ') }}
                                </div>
                            @endif
                            @if($item->discharge_advice)
                                <div class="meta-line" style="margin-top: 2px;"><strong>Advice:</strong> {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($item->discharge_advice)), 140) }}</div>
                            @endif
                            @if($item->post_surgery_diet || $item->post_surgery_activity)
                                <div class="meta-line">
                                    {{ collect([$item->post_surgery_diet ? 'Diet: ' . $item->post_surgery_diet : null, $item->post_surgery_activity ? 'Activity: ' . $item->post_surgery_activity : null])->filter()->implode(' | ') }}
                                </div>
                            @endif
                            @if($item->follow_up || $item->suture_removal_date || $item->stent_removal_date)
                                <div class="meta-line" style="color: #0369a1; margin-top: 2px;">
                                    {{ collect([
                                        $item->follow_up ? 'Follow-up: ' . \Illuminate\Support\Str::limit(trim($item->follow_up), 70) : null,
                                        $item->suture_removal_date ? 'Suture Removal: ' . $item->suture_removal_date->format('d.m.Y') : null,
                                        $item->stent_removal_date ? 'Stent Removal: ' . $item->stent_removal_date->format('d.m.Y') : null,
                                    ])->filter()->implode(' • ') }}
                                </div>
                            @endif
                        </td>
                        <td class="col-admin">
                            <div class="meta-line">{{ $paySummary !== '—' ? $paySummary : 'Cash / General' }}</div>
                            @if($item->approved_amount)
                                <div class="meta-line" style="color: #15803d; font-weight: 700;">Approved: Rs. {{ number_format((float) $item->approved_amount, 2) }}</div>
                            @endif
                            @if($item->handover_name)
                                <div class="meta-line"><strong>Handover:</strong> {{ $item->handover_name }}</div>
                            @endif
                            @if($item->floor_incharge_name)
                                <div class="meta-line"><strong>Floor:</strong> {{ $item->floor_incharge_name }}</div>
                            @endif
                            @if($item->mrd_incharge_name)
                                <div class="meta-line"><strong>MRD:</strong> {{ $item->mrd_incharge_name }}</div>
                            @endif
                            <div style="margin-top: 3px;">
                                <span class="badge badge-neutral" style="text-transform: uppercase;">{{ str_replace('_', ' ', $item->status ?: 'draft') }}</span>
                            </div>
                        </td>
                    @else
                        <td class="col-clinical">
                            <div style="margin-bottom: 2px;">
                                <span class="badge badge-neutral" style="text-transform: uppercase;">{{ $item->surgery_type === 'other' ? 'Surgical OT' : ucfirst($item->surgery_type) }}</span>
                                @if($item->icd_code)
                                    <span class="badge badge-neutral mono">ICD: {{ $item->icd_code }}</span>
                                @endif
                            </div>
                            <div style="font-weight: 700; color: #0f172a;">{{ $item->diagnosis ?: '—' }}</div>
                            @if($item->vital_bp || $item->vital_pulse || $item->vital_spo2)
                                <div class="meta-line" style="margin-top: 2px;">
                                    <strong>Vitals:</strong>
                                    {{ collect([$item->vital_bp ? 'BP ' . $item->vital_bp : null, $item->vital_pulse ? 'PR ' . $item->vital_pulse : null, $item->vital_spo2 ? 'SpO2 ' . $item->vital_spo2 : null])->filter()->implode(' | ') }}
                                </div>
                            @endif
                            @if($sectionKey === 'admissions' && $item->clinical_history)
                                <div class="meta-line" style="margin-top: 2px;"><strong>History:</strong> {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($item->clinical_history)), 120) }}</div>
                            @elseif($sectionKey === 'inpatients' && ($item->treatment_course || $item->examination))
                                <div class="meta-line" style="margin-top: 2px;"><strong>Clinical Note:</strong> {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($item->treatment_course ?: $item->examination)), 120) }}</div>
                            @endif
                        </td>
                        <td class="col-procedure">
                            <div style="font-weight: 700; color: #0f172a;">{{ $procLabel }}</div>
                            @if($item->incision)
                                <div class="meta-line"><strong>Incision:</strong> {{ $item->incision }}</div>
                            @endif
                            @include('pdf.partials.day-report-team-delivery', ['item' => $item, 'isDelivery' => $isDelivery])
                        </td>
                        <td class="col-status">
                            @if($item->discharge_at)
                                <span class="badge badge-girl">Discharged {{ $item->discharge_at->format('d.m.Y') }}</span>
                            @else
                                <span class="badge badge-info">In Admission</span>
                            @endif
                            <div style="margin-top: 3px;">
                                <span class="badge badge-neutral" style="text-transform: uppercase;">{{ str_replace('_', ' ', $item->status ?: 'draft') }}</span>
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 16px; color: #64748b;">
                        {{ $sectionMeta['empty'] }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
