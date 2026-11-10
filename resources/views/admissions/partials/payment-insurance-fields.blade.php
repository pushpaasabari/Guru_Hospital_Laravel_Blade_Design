@php
    $rawPaymentMode = strtolower(trim((string) old('payment_mode', $admission?->payment_mode ?? 'Cash')));
    $selectedPaymentMode = (str_contains($rawPaymentMode, 'insurance') || str_contains($rawPaymentMode, 'cmchis') || str_contains($rawPaymentMode, 'tpa') || str_contains($rawPaymentMode, 'mediclaim'))
        ? 'Insurance'
        : 'Cash';
    $fieldLabel = $label ?? 'Payment / Insurance Mode';
    $shouldSyncFormat = $syncFormat ?? true;
    $uid = $uid ?? 'pi_' . substr(md5($fieldLabel), 0, 6);
@endphp

<div class="col-md-4">
    <label class="form-label">{{ $fieldLabel }}</label>
    <select name="payment_mode" id="{{ $uid }}_mode" class="form-select @error('payment_mode') is-invalid @enderror">
        <option value="Cash" @selected($selectedPaymentMode === 'Cash')>Cash</option>
        <option value="Insurance" @selected($selectedPaymentMode === 'Insurance')>Insurance</option>
    </select>
    @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-12" id="{{ $uid }}_panel" style="{{ $selectedPaymentMode === 'Insurance' ? '' : 'display: none;' }}">
    <div class="border rounded-3 p-3 bg-light mt-1" style="border-color: #cbd5e1;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="fw-bold text-dark" style="font-size: 0.92rem;">
                <i class="bi bi-shield-check text-primary me-1"></i> Insurance &amp; Pre-Authorization Details
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                Required for Insurance Admissions
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required">Insurance Category / Scheme</label>
                <select name="insurance_type" id="{{ $uid }}_type" class="form-select @error('insurance_type') is-invalid @enderror" @disabled($selectedPaymentMode !== 'Insurance')>
                    <option value="">-- Select Scheme / Category --</option>
                    @foreach(['CMCHISTN Govt Scheme', 'Private Insurance / TPA', 'Corporate / Employee'] as $insType)
                        <option value="{{ $insType }}" @selected(old('insurance_type', $admission?->insurance_type) === $insType)>{{ $insType }}</option>
                    @endforeach
                </select>
                @error('insurance_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label required">Insurance Company / TPA Name</label>
                <input type="text" name="insurance_company" id="{{ $uid }}_company" class="form-control @error('insurance_company') is-invalid @enderror"
                    value="{{ old('insurance_company', $admission?->insurance_company) }}"
                    placeholder="e.g. Star Health / CMCHISTN / Medi Assist" @disabled($selectedPaymentMode !== 'Insurance')>
                @error('insurance_company')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label required">Policy / Health Card / URN No.</label>
                <input type="text" name="policy_number" id="{{ $uid }}_policy" class="form-control @error('policy_number') is-invalid @enderror"
                    value="{{ old('policy_number', $admission?->policy_number) }}"
                    placeholder="e.g. URN / Policy / Member Card ID" @disabled($selectedPaymentMode !== 'Insurance')>
                @error('policy_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Pre-Auth / Claim Reference No.</label>
                <input type="text" name="pre_auth_no" class="form-control @error('pre_auth_no') is-invalid @enderror"
                    value="{{ old('pre_auth_no', $admission?->pre_auth_no) }}"
                    placeholder="e.g. PA-2026-88412" @disabled($selectedPaymentMode !== 'Insurance')>
                @error('pre_auth_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Pre-Auth Status</label>
                <select name="pre_auth_status" class="form-select @error('pre_auth_status') is-invalid @enderror" @disabled($selectedPaymentMode !== 'Insurance')>
                    <option value="">-- Select Status --</option>
                    @foreach(['Pending', 'Approved', 'Enhancement Requested', 'Rejected'] as $statusOpt)
                        <option value="{{ $statusOpt }}" @selected(old('pre_auth_status', $admission?->pre_auth_status) === $statusOpt)>{{ $statusOpt }}</option>
                    @endforeach
                </select>
                @error('pre_auth_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Approved Amount (&#8377;)</label>
                <input type="number" step="0.01" min="0" name="approved_amount" class="form-control @error('approved_amount') is-invalid @enderror"
                    value="{{ old('approved_amount', $admission?->approved_amount) }}"
                    placeholder="e.g. 45000" @disabled($selectedPaymentMode !== 'Insurance')>
                @error('approved_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Policy Holder Name</label>
                <input type="text" name="policy_holder_name" class="form-control @error('policy_holder_name') is-invalid @enderror"
                    value="{{ old('policy_holder_name', $admission?->policy_holder_name) }}"
                    placeholder="Primary insured member name" @disabled($selectedPaymentMode !== 'Insurance')>
                @error('policy_holder_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Relationship to Patient</label>
                <select name="policy_holder_relation" class="form-select @error('policy_holder_relation') is-invalid @enderror" @disabled($selectedPaymentMode !== 'Insurance')>
                    <option value="">-- Select Relation --</option>
                    @foreach(['Self', 'Spouse', 'Child', 'Parent', 'Other'] as $relOpt)
                        <option value="{{ $relOpt }}" @selected(old('policy_holder_relation', $admission?->policy_holder_relation) === $relOpt)>{{ $relOpt }}</option>
                    @endforeach
                </select>
                @error('policy_holder_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Policy Valid Till</label>
                <input type="date" name="policy_validity" class="form-control @error('policy_validity') is-invalid @enderror"
                    value="{{ old('policy_validity', optional($admission?->policy_validity)->format('Y-m-d')) }}" @disabled($selectedPaymentMode !== 'Insurance')>
                @error('policy_validity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeSelect = document.getElementById('{{ $uid }}_mode');
    const panel = document.getElementById('{{ $uid }}_panel');
    const typeSelect = document.getElementById('{{ $uid }}_type');
    const companyInput = document.getElementById('{{ $uid }}_company');
    const policyInput = document.getElementById('{{ $uid }}_policy');
    const formatSelect = document.querySelector('select[name="format"]');
    const syncFormat = {{ $shouldSyncFormat ? 'true' : 'false' }};

    function syncInsurancePanel() {
        const isIns = modeSelect.value === 'Insurance';
        panel.style.display = isIns ? '' : 'none';
        panel.querySelectorAll('input, select').forEach(function (el) {
            el.disabled = !isIns;
        });
        [typeSelect, companyInput, policyInput].forEach(function (el) {
            if (el) el.required = isIns;
        });
        if (syncFormat && formatSelect) {
            if (!isIns) {
                formatSelect.value = 'general';
            } else {
                const t = typeSelect.value;
                if (t === 'CMCHISTN Govt Scheme') formatSelect.value = 'cmchistn';
                else if (t === 'Corporate / Employee') formatSelect.value = 'employee';
                else formatSelect.value = 'private';
            }
        }
    }

    modeSelect.addEventListener('change', syncInsurancePanel);
    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            if (syncFormat && formatSelect && modeSelect.value === 'Insurance') {
                const t = typeSelect.value;
                if (t === 'CMCHISTN Govt Scheme') formatSelect.value = 'cmchistn';
                else if (t === 'Corporate / Employee') formatSelect.value = 'employee';
                else if (t === 'Private Insurance / TPA') formatSelect.value = 'private';
            }
        });
    }
    syncInsurancePanel();
});
</script>

