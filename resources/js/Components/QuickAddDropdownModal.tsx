import React, { useEffect, useState } from 'react';

export interface QuickAddDropdownConfig {
  type: 'medication' | 'postop_order';
  title: string;
  fieldLabel: string;
  placeholder: string;
  helperText: string;
  submitLabel?: string;
  initialValue?: string;
}

export interface SavedDropdownOption {
  id: number | null;
  label: string;
  group_label: string;
  created: boolean;
  is_default: boolean;
}

interface QuickAddDropdownModalProps {
  config: QuickAddDropdownConfig | null;
  onClose: () => void;
  onSaved: (option: SavedDropdownOption) => void;
}

function resolveCsrfToken(): string {
  if (typeof document === 'undefined') return '';
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export default function QuickAddDropdownModal({
  config,
  onClose,
  onSaved,
}: QuickAddDropdownModalProps) {
  const [label, setLabel] = useState('');
  const [saving, setSaving] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    if (!config) return;
    setLabel(config.initialValue || '');
    setSaving(false);
    setErrorMsg('');
  }, [config]);

  if (!config) return null;

  const isMedication = config.type === 'medication';

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const cleaned = label.trim();
    if (!cleaned) {
      setErrorMsg(`Please enter a ${config.fieldLabel.toLowerCase()}.`);
      return;
    }

    setSaving(true);
    setErrorMsg('');

    try {
      const res = await fetch('/admissions/quick-dropdown-option', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': resolveCsrfToken(),
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          type: config.type,
          label: cleaned,
        }),
      });

      const body = await res.json().catch(() => ({}));
      if (!res.ok) {
        const firstErr =
          body?.message ||
          (body?.errors && Object.values(body.errors).flat()[0]) ||
          'Unable to save entry.';
        throw new Error(String(firstErr));
      }

      if (body?.option?.label) {
        onSaved(body.option as SavedDropdownOption);
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
      aria-labelledby="quickAddDropdownModalTitle"
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
                  <i className={`bi ${isMedication ? 'bi-capsule-pill' : 'bi-list-check'}`}></i>
                </div>
                <div>
                  <h5 id="quickAddDropdownModalTitle" className="modal-title fs-6 fw-bold mb-0">
                    {config.title}
                  </h5>
                  <small className="text-muted" style={{ fontSize: '11px' }}>
                    {config.helperText}
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

              <div>
                <label className="form-label required small fw-semibold">
                  {config.fieldLabel}
                </label>
                <input
                  type="text"
                  autoFocus
                  className="form-control"
                  value={label}
                  onChange={(e) => setLabel(e.target.value)}
                  placeholder={config.placeholder}
                  maxLength={300}
                  required
                />
                <div className="form-text small text-muted">
                  Also saved automatically whenever you save a patient&apos;s form with a new entry.
                </div>
              </div>
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
                    {config.submitLabel || 'Save to Dropdown'}
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
