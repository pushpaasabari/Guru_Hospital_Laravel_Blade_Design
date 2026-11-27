import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

interface User {
  id: number;
  name: string;
}

interface TemplateVersion {
  id: number;
  template_code: string;
  title: string;
  version_tag: string;
  description?: string;
  effective_date?: string;
  is_approved_by_hospital: boolean;
  approved_by_name?: string;
  updater?: User;
}

interface Props {
  templates: TemplateVersion[];
}

export default function Index({ templates }: Props) {
  const [editingTemplate, setEditingTemplate] = useState<TemplateVersion | null>(null);
  const [versionTag, setVersionTag] = useState('');
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [effectiveDate, setEffectiveDate] = useState('');
  const [isApproved, setIsApproved] = useState(false);
  const [approvedByName, setApprovedByName] = useState('');
  const [saving, setSaving] = useState(false);

  const openEditModal = (tpl: TemplateVersion) => {
    setEditingTemplate(tpl);
    setVersionTag(tpl.version_tag || '');
    setTitle(tpl.title || '');
    setDescription(tpl.description || '');
    setEffectiveDate(
      tpl.effective_date
        ? new Date(tpl.effective_date).toISOString().split('T')[0]
        : ''
    );
    setIsApproved(Boolean(tpl.is_approved_by_hospital));
    setApprovedByName(tpl.approved_by_name || '');
  };

  const handleUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingTemplate) return;

    setSaving(true);
    router.put(
      `/templates/${editingTemplate.id}`,
      {
        version_tag: versionTag,
        title,
        description,
        effective_date: effectiveDate || null,
        is_approved_by_hospital: isApproved,
        approved_by_name: approvedByName || null,
      },
      {
        onFinish: () => {
          setSaving(false);
          setEditingTemplate(null);
        },
      }
    );
  };

  const formatDate = (dateStr?: string) => {
    if (!dateStr) return 'Pending';
    try {
      const d = new Date(dateStr);
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${day}.${month}.${year}`;
    } catch {
      return dateStr;
    }
  };

  return (
    <AppLayout>
      <Head title="Hospital Document Templates — Guru Hospital" />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Hospital Document Templates Registry
          </h1>
          <p className="text-muted mb-0">
            Manage approved layout versions, effective dates, and clinical review status for all
            document types.
          </p>
        </div>
      </div>

      <div className="alert alert-warning shadow-sm border-0 mb-4" style={{ borderRadius: '16px' }}>
        <i className="bi bi-shield-exclamation me-2"></i>
        <strong>Statutory Notice:</strong> Only layouts formally signed and approved by Guru
        Hospital Clinical Governance and Medical Administration can be marked as{' '}
        <em>Hospital Approved</em>. Generated patient documents reference the active approved
        version tag.
      </div>

      <div className="card shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0 fg-fit-table fg-mobile-stack-table">
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th style={{ width: '15%' }}>Template Code</th>
                <th style={{ width: '30%' }}>Document Title</th>
                <th style={{ width: '12%' }}>Version Tag</th>
                <th style={{ width: '13%' }}>Effective Date</th>
                <th style={{ width: '16%' }}>Approval Status</th>
                <th style={{ width: '14%' }} className="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              {templates.map((tpl) => (
                <tr key={tpl.id}>
                  <td data-label="Template Code">
                    <span className="badge bg-secondary font-monospace">{tpl.template_code}</span>
                  </td>
                  <td data-label="Document Title">
                    <div className="fw-bold text-dark">{tpl.title}</div>
                    <div className="text-muted small">{tpl.description}</div>
                  </td>
                  <td data-label="Version Tag">
                    <span className="badge bg-light text-dark border">{tpl.version_tag}</span>
                  </td>
                  <td data-label="Effective Date">
                    <span className="small">{formatDate(tpl.effective_date)}</span>
                  </td>
                  <td data-label="Approval Status">
                    {tpl.is_approved_by_hospital ? (
                      <>
                        <span className="badge badge-approved">
                          <i className="bi bi-check-circle-fill me-1"></i> Approved
                        </span>
                        {tpl.approved_by_name && (
                          <div
                            className="text-muted small mt-1"
                            style={{ fontSize: '0.75rem' }}
                          >
                            By: {tpl.approved_by_name}
                          </div>
                        )}
                      </>
                    ) : (
                      <span className="badge badge-superseded">
                        <i className="bi bi-hourglass-split me-1"></i> Draft Placeholder
                      </span>
                    )}
                  </td>
                  <td className="text-end" data-label="Actions">
                    <button
                      type="button"
                      className="btn btn-sm btn-outline-secondary"
                      onClick={() => openEditModal(tpl)}
                    >
                      <i className="bi bi-pencil me-1"></i> Configure
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Configure Modal */}
      {editingTemplate && (
        <div
          className="modal fade show d-block"
          tabIndex={-1}
          style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
        >
          <div className="modal-dialog modal-dialog-centered">
            <form onSubmit={handleUpdate} className="modal-content border-0 shadow-lg">
              <div className="modal-header">
                <h5 className="modal-title fw-bold" style={{ letterSpacing: '-0.01em' }}>
                  Configure Template: {editingTemplate.title}
                </h5>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setEditingTemplate(null)}
                ></button>
              </div>
              <div className="modal-body p-4">
                <div className="mb-3">
                  <label className="form-label required fw-semibold">Version Tag</label>
                  <input
                    type="text"
                    className="form-control"
                    value={versionTag}
                    onChange={(e) => setVersionTag(e.target.value)}
                    required
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label required fw-semibold">Display Title</label>
                  <input
                    type="text"
                    className="form-control"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    required
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label fw-semibold">Description / Scheme Notes</label>
                  <textarea
                    className="form-control"
                    rows={2}
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                  ></textarea>
                </div>
                <div className="mb-3">
                  <label className="form-label fw-semibold">Effective Date</label>
                  <input
                    type="date"
                    className="form-control"
                    value={effectiveDate}
                    onChange={(e) => setEffectiveDate(e.target.value)}
                  />
                </div>
                <div className="form-check form-switch mb-3">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    id="apprCheck"
                    checked={isApproved}
                    onChange={(e) => setIsApproved(e.target.checked)}
                  />
                  <label className="form-check-label fw-semibold" htmlFor="apprCheck">
                    Mark as Officially Approved by Guru Hospital
                  </label>
                </div>
                <div className="mb-3">
                  <label className="form-label fw-semibold">
                    Approved By (Clinician / Authority Name)
                  </label>
                  <input
                    type="text"
                    className="form-control"
                    value={approvedByName}
                    onChange={(e) => setApprovedByName(e.target.value)}
                    placeholder="e.g. Dr. Medical Director, MS"
                  />
                </div>
              </div>
              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-outline-secondary"
                  onClick={() => setEditingTemplate(null)}
                >
                  Cancel
                </button>
                <button type="submit" className="btn btn-primary" disabled={saving}>
                  {saving ? 'Saving...' : 'Save Changes'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AppLayout>
  );
}

