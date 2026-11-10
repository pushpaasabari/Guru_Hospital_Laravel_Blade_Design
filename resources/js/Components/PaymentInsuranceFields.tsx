import React from 'react';

export const INSURANCE_TYPES = [
  'CMCHISTN Govt Scheme',
  'Private Insurance / TPA',
  'Corporate / Employee',
] as const;

export const PRE_AUTH_STATUSES = [
  'Pending',
  'Approved',
  'Enhancement Requested',
  'Rejected',
] as const;

export const HOLDER_RELATIONS = [
  'Self',
  'Spouse',
  'Child',
  'Parent',
  'Other',
] as const;

export function normalizePaymentMode(mode?: string | null): 'Cash' | 'Insurance' {
  const val = (mode || '').trim().toLowerCase();
  if (!val) return 'Cash';
  if (
    val === 'insurance' ||
    val.includes('insurance') ||
    val.includes('cmchis') ||
    val.includes('tpa') ||
    val.includes('mediclaim') ||
    val.includes('reimbursement')
  ) {
    return 'Insurance';
  }
  return 'Cash';
}

export function mapInsuranceTypeToFormat(insuranceType?: string | null): 'cmchistn' | 'private' | 'employee' {
  if (insuranceType === 'CMCHISTN Govt Scheme') return 'cmchistn';
  if (insuranceType === 'Corporate / Employee') return 'employee';
  return 'private';
}

export interface PaymentInsuranceData {
  payment_mode?: string | null;
  format?: string | null;
  insurance_type?: string | null;
  insurance_company?: string | null;
  policy_number?: string | null;
  pre_auth_no?: string | null;
  pre_auth_status?: string | null;
  approved_amount?: string | number | null;
  policy_holder_name?: string | null;
  policy_holder_relation?: string | null;
  policy_validity?: string | null;
}

interface Props {
  data: PaymentInsuranceData;
  errors: Record<string, string | undefined>;
  onPatch: (patch: Partial<PaymentInsuranceData>) => void;
  syncFormat?: boolean;
  label?: string;
}

export default function PaymentInsuranceFields({
  data,
  errors,
  onPatch,
  syncFormat = true,
  label = 'Payment / Insurance Mode',
}: Props) {
  const mode = normalizePaymentMode(data.payment_mode);
  const isInsurance = mode === 'Insurance';

  const handleModeChange = (nextMode: 'Cash' | 'Insurance') => {
    if (nextMode === 'Cash') {
      const patch: Partial<PaymentInsuranceData> = {
        payment_mode: 'Cash',
        insurance_type: '',
        insurance_company: '',
        policy_number: '',
        pre_auth_no: '',
        pre_auth_status: '',
        approved_amount: '',
        policy_holder_name: '',
        policy_holder_relation: '',
        policy_validity: '',
      };
      if (syncFormat) {
        patch.format = 'general';
      }
      onPatch(patch);
      return;
    }

    const inferredType =
      data.insurance_type ||
      (data.format === 'cmchistn'
        ? 'CMCHISTN Govt Scheme'
        : data.format === 'employee'
          ? 'Corporate / Employee'
          : 'Private Insurance / TPA');

    const patch: Partial<PaymentInsuranceData> = {
      payment_mode: 'Insurance',
      insurance_type: inferredType,
      pre_auth_status: data.pre_auth_status || 'Pending',
    };
    if (syncFormat) {
      patch.format = mapInsuranceTypeToFormat(inferredType);
    }
    onPatch(patch);
  };

  const handleInsuranceTypeChange = (nextType: string) => {
    const patch: Partial<PaymentInsuranceData> = {
      insurance_type: nextType,
    };
    if (syncFormat && nextType) {
      patch.format = mapInsuranceTypeToFormat(nextType);
    }
    onPatch(patch);
  };

  return (
    <>
      <div className="col-md-4">
        <label className="form-label">{label}</label>
        <select
          className={`form-select ${errors.payment_mode ? 'is-invalid' : ''}`}
          value={mode}
          onChange={(e) => handleModeChange(e.target.value as 'Cash' | 'Insurance')}
        >
          <option value="Cash">Cash</option>
          <option value="Insurance">Insurance</option>
        </select>
        {errors.payment_mode && <div className="invalid-feedback">{errors.payment_mode}</div>}
      </div>

      {isInsurance && (
        <div className="col-12">
          <div className="border rounded-3 p-3 bg-light mt-1" style={{ borderColor: '#cbd5e1' }}>
            <div className="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div className="fw-bold text-dark d-flex align-items-center gap-2" style={{ fontSize: '0.92rem' }}>
                <i className="bi bi-shield-check text-primary"></i>
                <span>Insurance &amp; Pre-Authorization Details</span>
              </div>
              <span className="badge bg-primary-subtle text-primary border border-primary-subtle">
                Required for Insurance Admissions
              </span>
            </div>

            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label required">Insurance Category / Scheme</label>
                <select
                  className={`form-select ${errors.insurance_type ? 'is-invalid' : ''}`}
                  value={data.insurance_type || ''}
                  onChange={(e) => handleInsuranceTypeChange(e.target.value)}
                  required={isInsurance}
                >
                  <option value="">-- Select Scheme / Category --</option>
                  {INSURANCE_TYPES.map((t) => (
                    <option key={t} value={t}>
                      {t}
                    </option>
                  ))}
                </select>
                {errors.insurance_type && <div className="invalid-feedback">{errors.insurance_type}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label required">Insurance Company / TPA Name</label>
                <input
                  type="text"
                  className={`form-control ${errors.insurance_company ? 'is-invalid' : ''}`}
                  value={data.insurance_company || ''}
                  onChange={(e) => onPatch({ insurance_company: e.target.value })}
                  placeholder="e.g. Star Health / CMCHISTN / Medi Assist"
                  required={isInsurance}
                />
                {errors.insurance_company && <div className="invalid-feedback">{errors.insurance_company}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label required">Policy / Health Card / URN No.</label>
                <input
                  type="text"
                  className={`form-control ${errors.policy_number ? 'is-invalid' : ''}`}
                  value={data.policy_number || ''}
                  onChange={(e) => onPatch({ policy_number: e.target.value })}
                  placeholder="e.g. URN / Policy / Member Card ID"
                  required={isInsurance}
                />
                {errors.policy_number && <div className="invalid-feedback">{errors.policy_number}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label">Pre-Auth / Claim Reference No.</label>
                <input
                  type="text"
                  className={`form-control ${errors.pre_auth_no ? 'is-invalid' : ''}`}
                  value={data.pre_auth_no || ''}
                  onChange={(e) => onPatch({ pre_auth_no: e.target.value })}
                  placeholder="e.g. PA-2026-88412"
                />
                {errors.pre_auth_no && <div className="invalid-feedback">{errors.pre_auth_no}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label">Pre-Auth Status</label>
                <select
                  className={`form-select ${errors.pre_auth_status ? 'is-invalid' : ''}`}
                  value={data.pre_auth_status || ''}
                  onChange={(e) => onPatch({ pre_auth_status: e.target.value })}
                >
                  <option value="">-- Select Status --</option>
                  {PRE_AUTH_STATUSES.map((st) => (
                    <option key={st} value={st}>
                      {st}
                    </option>
                  ))}
                </select>
                {errors.pre_auth_status && <div className="invalid-feedback">{errors.pre_auth_status}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label">Approved Amount (₹)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  className={`form-control ${errors.approved_amount ? 'is-invalid' : ''}`}
                  value={data.approved_amount ?? ''}
                  onChange={(e) => onPatch({ approved_amount: e.target.value })}
                  placeholder="e.g. 45000"
                />
                {errors.approved_amount && <div className="invalid-feedback">{errors.approved_amount}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label">Policy Holder Name</label>
                <input
                  type="text"
                  className={`form-control ${errors.policy_holder_name ? 'is-invalid' : ''}`}
                  value={data.policy_holder_name || ''}
                  onChange={(e) => onPatch({ policy_holder_name: e.target.value })}
                  placeholder="Primary insured member name"
                />
                {errors.policy_holder_name && <div className="invalid-feedback">{errors.policy_holder_name}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label">Relationship to Patient</label>
                <select
                  className={`form-select ${errors.policy_holder_relation ? 'is-invalid' : ''}`}
                  value={data.policy_holder_relation || ''}
                  onChange={(e) => onPatch({ policy_holder_relation: e.target.value })}
                >
                  <option value="">-- Select Relation --</option>
                  {HOLDER_RELATIONS.map((rel) => (
                    <option key={rel} value={rel}>
                      {rel}
                    </option>
                  ))}
                </select>
                {errors.policy_holder_relation && <div className="invalid-feedback">{errors.policy_holder_relation}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label">Policy Valid Till</label>
                <input
                  type="date"
                  className={`form-control ${errors.policy_validity ? 'is-invalid' : ''}`}
                  value={data.policy_validity || ''}
                  onChange={(e) => onPatch({ policy_validity: e.target.value })}
                />
                {errors.policy_validity && <div className="invalid-feedback">{errors.policy_validity}</div>}
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

