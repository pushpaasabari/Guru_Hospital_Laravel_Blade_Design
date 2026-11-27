@php $isNatDel = $item->isNaturalDelivery(); @endphp
@if($item->surgeon || $item->assisted_surgeon || $item->pediatrician_name || $item->anaesthetist || $item->anaesthesia_type || $item->staff_nurse || $item->ot_technician)
    <div style="margin-top: 3px;">
        @if($item->surgeon)
            <div class="team-row">
                <span class="team-lbl">{{ $isNatDel ? 'Delivered By:' : 'Surgeon:' }}</span> {{ $item->surgeon }}
                @if($item->assisted_surgeon) (Asst: {{ $item->assisted_surgeon }}) @endif
            </div>
        @endif
        @if($item->pediatrician_name)
            <div class="team-row"><span class="team-lbl">Pediatrician:</span> {{ $item->pediatrician_name }}</div>
        @endif
        @if($item->anaesthetist)
            <div class="team-row">
                <span class="team-lbl">Anaesthetist:</span> {{ $item->anaesthetist }}
                @if($item->assisted_anaesthetist) (Asst: {{ $item->assisted_anaesthetist }}) @endif
            </div>
        @endif
        @if($item->anaesthesia_type)
            <div class="team-row"><span class="badge badge-neutral">{{ $item->anaesthesia_type }}</span></div>
        @endif
        @if($item->staff_nurse || $item->ot_technician)
            <div class="meta-line">
                {{ collect([
                    $item->staff_nurse ? 'Nurse: ' . $item->staff_nurse : null,
                    $item->ot_technician ? 'OT Tech: ' . $item->ot_technician : null,
                ])->filter()->implode(' | ') }}
            </div>
        @endif
    </div>
@endif

@if($isDelivery)
    <div class="delivery-box" style="margin-top: 4px;">
        <strong style="color: #15803d;">Delivery: {{ $item->procedure_done ?: ($item->procedure_name ?: 'Obstetric Delivery') }}</strong>
        @if($item->picme_no || $item->vhn_name || $item->phc)
            <div class="meta-line">
                {{ collect([
                    $item->picme_no ? 'PICME: ' . $item->picme_no : null,
                    $item->vhn_name ? 'VHN: ' . $item->vhn_name : null,
                    $item->phc ? 'PHC: ' . $item->phc : null,
                ])->filter()->implode(' • ') }}
            </div>
        @endif
    </div>
    @if($item->newborns->isNotEmpty())
        @foreach($item->newborns as $baby)
            @php
                $sexLower = strtolower(trim((string) $baby->sex));
                $isBoy = in_array($sexLower, ['male', 'boy'], true);
                $isGirl = in_array($sexLower, ['female', 'girl'], true);
                $badgeCls = $isBoy ? 'badge-boy' : ($isGirl ? 'badge-girl' : 'badge-neutral');
                $sexLbl = $isBoy ? 'Boy' : ($isGirl ? 'Girl' : ($baby->sex ?: 'Not Recorded'));
                $formattedVaccines = \App\Models\Newborn::formatBirthVaccinesText(
                    $baby->birth_vaccines,
                    $baby->vaccine_dates,
                    $baby->vaccine_date
                );
            @endphp
            <div class="baby-box">
                <span class="badge {{ $badgeCls }}">{{ $sexLbl }}</span>
                @if($baby->birth_at)
                    <span class="meta-line" style="margin-left: 4px;">{{ $baby->birth_at->format('d.m.Y h:i A') }}</span>
                @endif
                <div style="margin-top: 2px;">
                    <strong>Birth Wt:</strong> {{ $baby->birth_weight_kg ? number_format((float) $baby->birth_weight_kg, 2) . ' kg' : '—' }}
                    @if($baby->discharge_weight_kg)
                        &nbsp;|&nbsp; <strong>Disch Wt:</strong> {{ number_format((float) $baby->discharge_weight_kg, 2) }} kg
                    @endif
                    @if($baby->apgar_1_min !== null || $baby->apgar_5_min !== null)
                        &nbsp;|&nbsp; <strong>APGAR:</strong> {{ $baby->apgar_1_min ?? '—' }}/{{ $baby->apgar_5_min ?? '—' }}
                    @endif
                </div>
                @if($baby->neonatal_care)
                    <div class="meta-line"><strong>Care:</strong> {{ $baby->neonatal_care }}</div>
                @endif
                @if($baby->notes)
                    <div style="color: #0f172a;"><strong>Notes / Presentation:</strong> {{ $baby->notes }}</div>
                @endif
                @if($formattedVaccines !== '')
                    <div style="color: #15803d;"><strong>Vaccines:</strong> {{ $formattedVaccines }}</div>
                @endif
            </div>
        @endforeach
    @else
        <span class="meta-line">Newborn details not recorded</span>
    @endif
@endif
