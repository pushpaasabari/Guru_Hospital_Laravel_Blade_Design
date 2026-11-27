import React, { useEffect, useState } from 'react';
import { toUpperCaseValue } from '../utils/inputNormalizers';

export type QuickAddMode = 'staff' | 'anesthesia';

export interface QuickAddModalConfig {
  fieldKey: string;
  fieldLabel: string;
  mode: QuickAddMode;
  defaultRole?: string;
  uppercase?: boolean;
}

const STAFF_ROLE_OPTIONS = [
  { value: 'surgeon', label: 'Surgeon' },
  { value: 'assisted_surgeon', label: 'Assisted Surgeon' },
  { value: 'anaesthetist', label: 'Anaesthetist' },
  { value: 'assisted_anaesthetist', label: 'Assisted Anaesthetist' },
  { value: 'staff_nurse', label: 'Scrub / Staff Nurse' },
  { value: 'ot_technician', label: 'OT Technician' },
  { value: 'consultant_pediatrician', label: 'Pediatrician / Neonatologist' },
  { value: 'consultant_doctor', label: 'Consultant Doctor / Clinical Help' },
] as const;

interface QuickAddTeamModalProps {
  config: QuickAddModalConfig | null;
  onClose: () => void;
  onStaffAdded: (fieldKey: string, staff: any, uppercase: boolean) => void;
  onAnesthesiaAdded: (fieldKey: string, item: any) => void;
}

function resolveCsrfToken(): string {
  if (typeof document === 'undefined') return '';
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function mapDefaultRoleCategory(role?: string): string {
  if (!role || role === 'all' || role === 'other') return 'consultant_doctor';
  if (role === 'delivery_conductor') return 'consultant_doctor';
  if (role === 'delivery_assistant') return 'staff_nurse';
  if (STAFF_ROLE_OPTIONS.some((r) => r.value === role)) return role;
  if (role === 'nurse') return 'staff_nurse';
  if (role === 'ot_tech') return 'ot_technician';
  if (role === 'pediatrician') return 'consultant_pediatrician';
  return 'consultant_doctor';
}

export default function QuickAddTeamModal({
  config,
  onClose,
  onStaffAdded,
  onAnesthesiaAdded,
}: QuickAddTeamModalProps) {
  const [name, setName] = useState('');
  const [category, setCategory] = useState('surgeon');
  const [qualification, setQualification] = useState('');
  const [department, setDepartment] = useState('');
  const [code, setCode] = useState('');
  const [description, setDescription] = useState('');
  const [saving, setSaving] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    if (!config) return;
    setName('');
    setCategory(mapDefaultRoleCategory(config.defaultRole));
    setQualification('');
    setDepartment('');
    setCode('');
    setDescription('');
    setErrorMsg('');
    setSaving(false);
  }, [config]);

  if (!config) return null;

  const isStaff = config.mode === 'staff';
  const useUpper = Boolean(config.uppercase);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const cleanedName = (useUpper && isStaff ? toUpperCaseValue(name) : name).trim();
    if (!cleanedName) {
      setErrorMsg(`Please enter a ${isStaff ? 'name' : 'anaesthesia type name'}.`);
      return;
    }

    setSaving(true);
    setErrorMsg('');

    try {
      const endpoint = isStaff ? '/admissions/quick-staff' : '/admissions/quick-anesthesia';
      const payload = isStaff
        ? {
            name: cleanedName,
            category,
            qualification: qualification.trim() || null,
            department: department.trim() || null,
          }
        : {
            name: cleanedName,
            code: code.trim() || null,
            description: description.trim() || null,
          };

      const res = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': resolveCsrfToken(),
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });

      const body = await res.json().catch(() => ({}));
      if (!res.ok) {
        const firstErr =
          body?.message ||
          (body?.errors && Object.values(body.errors).flat()[0]) ||
          'Unable to save entry.';
        throw new Error(String(firstErr));
      }

      if (isStaff && body?.staff) {
        onStaffAdded(config.fieldKey, body.staff, useUpper);
      } else if (!isStaff && body?.anesthesiaType) {
        onAnesthesiaAdded(config.fieldKey, body.anesthesiaType);
      }
      onClose();
    } catch (err: any) {
      setErrorMsg(err?.message || 'Unable to save entry. Please try again.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div
      className="modal fade show d-block"
      tabIndex={-1}
      role="dialog"
      aria-modal="true"
      aria-labelledby="quickAddTeamModalTitle"
      style={{ backgroundColor: 'rgba(15, 23, 42, 0.6)', zIndex: 1065 }}
      onClick={(e) => {
        if (e.target === e.currentTarget && !saving) onClose();
      }}
    >
      <div className="modal-dialog modal-dialog-centered" role="document">
        <div className="modal-content shadow-lg border-0 rounded-3">
          <form onSubmit={handleSubmit}>
            <div className="modal-header bg-light py-3 border-bottom">
              <div className="d-flex align-items-center gap-2">
                <div
                  className="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                  style={{ width: '32px', height: '32px', fontSize: '14px' }}
                >
                  <i className={`bi ${isStaff ? 'bi-person-plus-fill' : 'bi-capsule'}`}></i>
                </div>
                <div>
                  <h5 id="quickAddTeamModalTitle" className="modal-title fs-6 fw-bold mb-0">
                    Add New {config.fieldLabel}
                  </h5>
                  <small className="text-muted" style={{ fontSize: '11px' }}>
                    Saves to the hospital directory &amp; automatically selects for this surgery
                  </small>
                </div>
              </div>
              <button
                type="button"
                className="btn-close"
                aria-label="Close"
                disabled={saving}
                onClick={onClose}
              />
            </div>

            <div className="modal-body p-3">
              {errorMsg && (
                <div className="alert alert-danger py-2 px-3 small mb-3">
                  <i className="bi bi-exclamation-triangle me-1"></i>
                  {errorMsg}
                </div>
              )}

              <div className="mb-3">
                <label className="form-label required small fw-semibold">
                  {isStaff ? `${config.fieldLabel} Full Name` : 'Anaesthesia Type Name'}
                </label>
                <input
                  type="text"
                  autoFocus
                  className="form-control"
                  style={isStaff && useUpper ? { textTransform: 'uppercase' } : undefined}
                  value={name}
                  onChange={(e) =>
                    setName(isStaff && useUpper ? toUpperCaseValue(e.target.value) : e.target.value)
                  }
                  placeholder={
                    isStaff
                      ? `e.g. DR. SANTHOSH KUMAR M.S`
                      : 'e.g. Regional Nerve Block'
                  }
                  required
                />
              </div>

              {isStaff ? (
                <>
                  <div className="mb-3">
                    <label className="form-label required small fw-semibold">Team Role Category</label>
                    <select
                      className="form-select"
                      value={category}
                      onChange={(e) => setCategory(e.target.value)}
                    >
                      {STAFF_ROLE_OPTIONS.map((opt) => (
                        <option key={opt.value} value={opt.value}>
                          {opt.label}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="row g-2">
                    <div className="col-md-6">
                      <label className="form-label small fw-semibold">Qualification (Optional)</label>
                      <input
                        type="text"
                        className="form-control"
                        value={qualification}
                        onChange={(e) => setQualification(e.target.value)}
                        placeholder="e.g. M.S (Gen Surg)"
                      />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label small fw-semibold">Department (Optional)</label>
                      <input
                        type="text"
                        className="form-control"
                        value={department}
                        onChange={(e) => setDepartment(e.target.value)}
                        placeholder="e.g. Anaesthesiology"
                      />
                    </div>
                  </div>
                </>
              ) : (
                <>
                  <div className="mb-3">
                    <label className="form-label small fw-semibold">Short Code (Optional)</label>
                    <input
                      type="text"
                      className="form-control"
                      value={code}
                      onChange={(e) => setCode(e.target.value)}
                      placeholder="e.g. RNB, TAP, PNB"
                    />
                  </div>
                  <div>
                    <label className="form-label small fw-semibold">Description (Optional)</label>
                    <input
                      type="text"
                      className="form-control"
                      value={description}
                      onChange={(e) => setDescription(e.target.value)}
                      placeholder="Brief clinical note for anaesthesia directory"
                    />
                  </div>
                </>
              )}
            </div>

            <div className="modal-footer bg-light py-2">
              <button
                type="button"
                className="btn btn-sm btn-outline-secondary"
                disabled={saving}
                onClick={onClose}
              >
                Cancel
              </button>
              <button
                type="submit"
                className="btn btn-sm btn-primary px-3 fw-semibold"
                disabled={saving}
              >
                {saving ? (
                  <>
                    <span className="spinner-border spinner-border-sm me-1" role="status"></span>
                    Saving...
                  </>
                ) : (
                  <>
                    <i className="bi bi-check2-circle me-1"></i>
                    Save &amp; Select
                  </>
                )}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
