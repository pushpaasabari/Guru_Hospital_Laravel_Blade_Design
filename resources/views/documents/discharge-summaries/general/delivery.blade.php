<div class="discharge-summary-document" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 11pt; line-height: 1.45; color: #000;">
    {{-- Space for Pre-printed Letterhead (First page only) --}}
    <div style="height: 40mm;"></div>

    <div class="text-center mb-4 pt-2">
        <h3 class="fw-bold text-decoration-underline text-uppercase mb-0" style="letter-spacing: 0.5px; font-size: 14pt;">DISCHARGE SUMMARY</h3>
    </div>

    <table class="table table-sm table-bordered mb-3">
        <tr>
            <td style="width: 50%;"><strong>Name :</strong> {{ $admission->patient_name }}</td>
            <td style="width: 50%;"><strong>IP. NO :</strong> {{ $admission->ip_no }}</td>
        </tr>
        <tr>
            <td><strong>UHID NO :</strong> {{ $admission->uhid }}</td>
            <td><strong>Age / Sex :</strong> {{ $admission->age ? $admission->age . ' Yrs' : 'N/A' }} / {{ $admission->sex ?? 'Female' }}</td>
        </tr>
        <tr>
            <td><strong>Address :</strong> {{ $admission->address ?? 'Not recorded' }}</td>
            <td><strong>Phone :</strong> {{ $admission->contact ?? 'Not recorded' }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="row g-1">
                    <div class="col-md-4"><strong>Date of admission :</strong> {{ $admission->admission_at?->format('d/m/Y \a\t h:i A') }}</div>
                    <div class="col-md-4"><strong>Date of Surgery :</strong> {{ $admission->procedure_at?->format('d/m/Y \a\t h:i A') ?? '—' }}</div>
                    <div class="col-md-4"><strong>Date of Discharge :</strong> {{ $admission->discharge_at?->format('d/m/Y \a\t h:i A') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="mb-2">
        <strong>Presenting Complaints :</strong>
        <span>{{ $admission->clinical_history ?: 'Admitted for Safe Confinement' }}</span>
    </div>

    <div class="mb-2">
        <strong>Clinical Examination :</strong>
        <span>{{ $admission->examination ?: 'O/E – No Pallor, No Pedal edema, CVS & RS – NAD' }}</span>
    </div>

    <div class="mb-2">
        <strong>Diagnosis :</strong>
        <span class="fw-bold">{{ $admission->diagnosis }}</span>
    </div>

    <div class="mb-2">
        <strong>Surgery :</strong>
        <span class="fw-bold">{{ $admission->procedure_name ?: 'Emergency LSCS' }}</span>
    </div>

    <table class="table table-sm table-borderless mb-2">
        <tr>
            <td style="width: 50%;"><strong>Surgeon :</strong> {{ $admission->surgeon ?: 'Dr. Ambika M.S (O&G)' }}</td>
            <td style="width: 50%;"><strong>Anaesthetist :</strong> {{ $admission->anaesthetist ?: 'Dr. M.S Karthick' }}</td>
        </tr>
        <tr>
            <td><strong>Assisted By S/N :</strong> {{ $admission->assisted_by ?: ($admission->assistant ?: 'Miss Soudnharya') }}</td>
            <td><strong>Anaesthesia :</strong> {{ $admission->anaesthesia_type ?: 'SA' }}</td>
        </tr>
    </table>

    @if($admission->procedure_note)
    <div class="mb-3">
        <strong>Procedure note :</strong>
        <p class="mb-0 text-justify">{{ $admission->procedure_note }}</p>
    </div>
    @endif

    <div class="mb-3 p-2 border rounded bg-light">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <strong class="text-primary text-uppercase">Baby / Newborn Details</strong>
            @if($admission->pediatrician_name || $admission->consultant)
                <span class="small"><strong>Consultants :</strong> {{ $admission->pediatrician_name ?: ($admission->consultant . ' (Paediatrician)') }}</span>
            @endif
        </div>
        @if($admission->newborns->isEmpty())
            <p class="mb-0 text-muted small">No newborn records documented.</p>
        @else
            @if($admission->newborns->count() > 1)
                <div class="row g-2">
                    @foreach($admission->newborns as $i => $baby)
                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-white h-100 small">
                                <div class="fw-bold text-dark border-bottom pb-1 mb-1">
                                    {{ $i === 0 ? 'BABY TWIN A (Newborn #1)' : ($i === 1 ? 'BABY TWIN B (Newborn #2)' : 'Newborn #' . ($i + 1)) }}
                                </div>
                                <div>: Alive {{ $baby->sex ?? 'Male' }} baby on {{ $baby->birth_at?->format('d/m/Y') ?? $admission->procedure_at?->format('d/m/Y') }}</div>
                                <div>: Wt : <strong>{{ $baby->birth_weight_kg ? number_format($baby->birth_weight_kg, 3) . ' kg' : '2.250 kg' }}</strong> at {{ $baby->birth_at?->format('h.i A') }}</div>
                                <div>- Apgar : <strong>{{ $baby->apgar_1_min ?? '7' }}/10, {{ $baby->apgar_5_min ?? '8' }}/10</strong></div>
                                @if($baby->neonatal_care)
                                    <div class="text-danger fw-semibold mt-1">{{ $baby->neonatal_care }}</div>
                                @endif
                                @if($baby->notes)
                                    <div class="text-muted mt-1 small">{{ $baby->notes }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @foreach($admission->newborns as $i => $baby)
                    <div class="small mb-1">
                        <strong>Newborn {{ count($admission->newborns) > 1 ? '#' . ($i + 1) : '' }} :</strong>
                        Alive {{ $baby->sex ?? 'Female' }} baby on {{ $baby->birth_at?->format('d.m.Y \a\t h:i A') ?? $admission->procedure_at?->format('d.m.Y \a\t h:i A') }},
                        WT: <strong>{{ $baby->birth_weight_kg ? $baby->birth_weight_kg . ' kgs' : '2.8 kgs' }}</strong>,
                        Apgar: <strong>{{ $baby->apgar_1_min ?? '7' }}/10, {{ $baby->apgar_5_min ?? '8' }}/10</strong>.
                        @if($baby->neonatal_care)
                            <span>{{ $baby->neonatal_care }}</span>
                        @endif
                        @if($baby->notes)
                            <span>({{ $baby->notes }})</span>
                        @endif
                    </div>
                @endforeach
            @endif
        @endif
    </div>

    @if($admission->treatment_course)
    <div class="mb-3">
        <strong>Inpatient Treatment Given :</strong>
        <pre class="mb-0 bg-transparent border-0 p-0" style="font-family: inherit; font-size: inherit; white-space: pre-wrap;">{{ $admission->treatment_course }}</pre>
    </div>
    @endif

    @if($admission->investigations)
    <div class="mb-3">
        <strong>INVESTIGATIONS :</strong>
        <div class="p-2 border rounded bg-light font-monospace small">
            @if(is_array($admission->investigations))
                @foreach($admission->investigations as $test => $val)
                    <div><strong>{{ is_string($test) ? $test . ' :' : '' }}</strong> {{ is_array($val) ? json_encode($val) : $val }}</div>
                @endforeach
            @else
                {!! nl2br(e($admission->investigations)) !!}
            @endif
        </div>
    </div>
    @endif

    @if($admission->discharge_condition)
    <div class="mb-3">
        <strong>Condition on discharge :</strong>
        <div class="text-justify">{!! nl2br(e($admission->discharge_condition)) !!}</div>
    </div>
    @endif

    @if($admission->medication_orders || $admission->discharge_advice)
    <div class="mb-3">
        <strong>Discharge Advice & Medications :</strong>
        @if($admission->medication_orders)
            <div class="mb-2 ps-2">{!! nl2br(e($admission->medication_orders)) !!}</div>
        @endif
        @if($admission->discharge_advice)
            <div class="ps-2">{!! nl2br(e($admission->discharge_advice)) !!}</div>
        @endif
    </div>
    @endif

    @if($admission->follow_up)
    <div class="mb-3">
        <strong>Follow-up & Review :</strong>
        <span>{{ $admission->follow_up }}</span>
    </div>
    @endif

    <div class="mt-4 p-2 border border-danger rounded bg-light text-danger small">
        <strong>WHEN TO CONTACT :</strong><br>
        TO REPORT IMMEDIATELY, IF THERE IS BLEEDING, FEVER, ABDOMINAL PAIN, (OR) OTHER EMERGENCIES.<br>
        <span class="text-dark"><strong>*Note: Emergency contact no:</strong> 04342-262266, +91-7418084266</span>
    </div>

    <div class="d-flex justify-content-between align-items-end mt-5 pt-3">
        <div class="text-center" style="width: 30%;">
            <div style="border-top: 1px dashed #666; padding-top: 4px;">Prepared by</div>
            <div class="small text-muted">{{ $admission->creator?->name ?? 'Staff Nurse' }}</div>
        </div>
        <div class="text-center" style="width: 35%;">
            <div style="border-top: 1px dashed #666; padding-top: 4px;"><strong>Consultant Obstetrician / Surgeon</strong></div>
            <div class="small text-muted">{{ $admission->surgeon ?: ($admission->consultant ?: 'Dr. Ambika M.S (O&G)') }}</div>
            @if($admission->approver && $admission->approver->doctor_reg_no)
                <div class="small font-monospace" style="font-size: 0.75rem;">Reg No: {{ $admission->approver->doctor_reg_no }}</div>
            @endif
        </div>
    </div>
</div>
