<div class="discharge-summary-document" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 11pt; line-height: 1.45; color: #000;">
    {{-- Space for Pre-printed Letterhead (First page only) --}}
    <div style="height: 40mm;"></div>

    <div class="text-center mb-4 pt-2">
        <h3 class="fw-bold text-decoration-underline text-uppercase mb-0" style="letter-spacing: 0.5px; font-size: 14pt;">DISCHARGE SUMMARY</h3>
    </div>

    <table class="table table-sm table-bordered mb-3">
        <tr>
            <td style="width: 50%;"><strong>Name :</strong> {{ $admission->patient_name }}</td>
            <td style="width: 50%;"><strong>IP. No :</strong> {{ $admission->ip_no }}</td>
        </tr>
        <tr>
            <td><strong>UHID No :</strong> {{ $admission->uhid }}</td>
            <td>
                <strong>Age / Sex :</strong> {{ $admission->age ? $admission->age . ' / ' . ($admission->sex ?? 'M') : ($admission->sex ?? 'M') }} &nbsp;&nbsp;&nbsp;
                <strong>Date :</strong> {{ $admission->discharge_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Address :</strong> {{ $admission->address ?? 'Not recorded' }}
                @if($admission->contact)
                    &nbsp;&bull;&nbsp; <strong>PH :</strong> {{ $admission->contact }}
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="row g-1">
                    <div class="col-md-4"><strong>Date of admission :</strong> {{ $admission->admission_at?->format('d.m.Y \a\t h:i A') }}</div>
                    <div class="col-md-4"><strong>Date of Surgery :</strong> {{ $admission->procedure_at?->format('d.m.Y \a\t h:i A') ?? '—' }}</div>
                    <div class="col-md-4"><strong>Date of Discharge :</strong> {{ $admission->discharge_at?->format('d.m.Y \a\t h:i A') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="mb-2">
        <strong>Presenting Complaints :</strong>
        <span>{{ $admission->clinical_history ?: 'Admitted with complaints of abdominal pain' }}</span>
    </div>

    @if($admission->examination)
    <div class="mb-2">
        <strong>Vital signs / Clinical Examination :</strong>
        <span>{{ $admission->examination }}</span>
    </div>
    @endif

    <div class="mb-2">
        <strong>Diagnosis :</strong>
        <span class="fw-bold">{{ $admission->diagnosis }}</span>
    </div>

    <div class="mb-2">
        <strong>Surgery :</strong>
        <span class="fw-bold">{{ $admission->procedure_name ?: 'Lap Appendectomy' }}</span>
    </div>

    <table class="table table-sm table-borderless mb-2">
        <tr>
            <td style="width: 50%;"><strong>Surgeon :</strong> {{ $admission->surgeon ?: 'Dr. Jayaprakash M.S' }}</td>
            <td style="width: 50%;"><strong>Anesthetist :</strong> {{ $admission->anaesthetist ?: 'Dr. S.G. ArunKumar' }}</td>
        </tr>
        <tr>
            <td><strong>Assistant By S/N :</strong> {{ $admission->assisted_by ?: ($admission->assistant ?: 'Mr. Vijaykumar') }}</td>
            <td><strong>Anesthesia :</strong> {{ $admission->anaesthesia_type ?: 'GA' }}</td>
        </tr>
    </table>

    @if($admission->procedure_note)
    <div class="mb-3">
        <strong>Procedure note :</strong>
        <p class="mb-0 text-justify">{{ $admission->procedure_note }}</p>
    </div>
    @endif

    @if($admission->treatment_course)
    <div class="mb-3">
        <strong>Treatment Given :</strong>
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
        <strong>Discharge Advice :</strong>
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
        <strong>Review & Follow-up :</strong>
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
            <div style="border-top: 1px dashed #666; padding-top: 4px;"><strong>Consultant Operating Surgeon</strong></div>
            <div class="small text-muted">{{ $admission->surgeon ?: ($admission->consultant ?: 'Dr. Jayaprakash M.S') }}</div>
            @if($admission->approver && $admission->approver->doctor_reg_no)
                <div class="small font-monospace" style="font-size: 0.75rem;">Reg No: {{ $admission->approver->doctor_reg_no }}</div>
            @endif
        </div>
    </div>
</div>
