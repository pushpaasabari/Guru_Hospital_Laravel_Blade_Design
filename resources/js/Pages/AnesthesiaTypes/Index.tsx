import { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

interface AnesthesiaTypeItem {
  id: number;
  name: string;
  code?: string;
  description?: string;
  is_active: boolean;
  sort_order: number;
  created_at?: string;
}

interface Props {
  anesthesiaTypes: AnesthesiaTypeItem[];
}

export default function Index({ anesthesiaTypes }: Props) {
  const [modalOpen, setModalOpen] = useState(false);
  const [editingType, setEditingType] = useState<AnesthesiaTypeItem | null>(null);

  const { data, setData, post, put, reset, errors } = useForm({
    name: '',
    code: '',
    description: '',
    sort_order: 0,
    is_active: true,
  });

  const openCreateModal = () => {
    setEditingType(null);
    reset();
    setData({
      name: '',
      code: '',
      description: '',
      sort_order: (anesthesiaTypes.length + 1),
      is_active: true,
    });
    setModalOpen(true);
  };

  const openEditModal = (item: AnesthesiaTypeItem) => {
    setEditingType(item);
    setData({
      name: item.name,
      code: item.code || '',
      description: item.description || '',
      sort_order: item.sort_order,
      is_active: item.is_active,
    });
    setModalOpen(true);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingType) {
      put(`/anesthesia-types/${editingType.id}`, {
        onSuccess: () => {
          setModalOpen(false);
          reset();
        },
      });
    } else {
      post('/anesthesia-types', {
        onSuccess: () => {
          setModalOpen(false);
          reset();
        },
      });
    }
  };

  const handleToggleActive = (item: AnesthesiaTypeItem) => {
    if (confirm(`Are you sure you want to ${item.is_active ? 'deactivate' : 'activate'} "${item.name}"?`)) {
      router.patch(`/anesthesia-types/${item.id}/toggle`);
    }
  };

  const handleDelete = (item: AnesthesiaTypeItem) => {
    if (confirm(`Are you sure you want to permanently delete "${item.name}"?`)) {
      router.delete(`/anesthesia-types/${item.id}`);
    }
  };

  return (
    <AppLayout>
      <Head title="Anesthesia Types Management — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
              Admin Configuration
            </span>
          </div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Anesthesia Types Directory
          </h1>
          <p className="text-muted mb-0">
            Manage anesthesia types displayed in admission forms and Operation Theatre notes dropdowns.
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href="/clinical-staff" className="btn btn-outline-secondary">
            <i className="bi bi-people-fill me-1"></i> Clinical Staff Directory
          </Link>
          <button
            type="button"
            className="btn btn-primary shadow-sm"
            onClick={openCreateModal}
          >
            <i className="bi bi-plus-circle-fill me-1"></i> Add Anesthesia Type
          </button>
        </div>
      </div>

      {/* Navigation Sub-Tabs */}
      <div className="card shadow-sm border-0 mb-3" style={{ borderRadius: '14px' }}>
        <div className="card-body p-2 d-flex flex-wrap gap-2 align-items-center">
          <Link
            href="/clinical-staff"
            className="btn btn-sm btn-light border-0 text-muted"
            style={{ borderRadius: '8px', fontWeight: 500 }}
          >
            <i className="bi bi-heart-pulse me-1"></i> Clinical Staff & Doctors
          </Link>
          <button
            type="button"
            className="btn btn-sm btn-primary"
            style={{ borderRadius: '8px', fontWeight: 500 }}
          >
            <i className="bi bi-capsule me-1"></i> Anesthesia Types ({anesthesiaTypes.length})
          </button>
        </div>
      </div>

      {/* Anesthesia Types Table */}
      <div className="card shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th style={{ width: '8%' }}>Order</th>
                <th style={{ width: '28%' }}>Anesthesia Type Name</th>
                <th style={{ width: '12%' }}>Code / Abbr</th>
                <th style={{ width: '32%' }}>Clinical Description / Indications</th>
                <th style={{ width: '10%' }}>Status</th>
                <th style={{ width: '10%' }} className="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              {anesthesiaTypes.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-capsule fs-1 d-block mb-2 text-secondary"></i>
                    No anesthesia types found. Click "Add Anesthesia Type" to register one.
                  </td>
                </tr>
              ) : (
                anesthesiaTypes.map((item) => (
                  <tr key={item.id}>
                    <td>
                      <span className="badge bg-light text-dark border">
                        #{item.sort_order}
                      </span>
                    </td>
                    <td>
                      <div className="fw-bold text-dark">{item.name}</div>
                    </td>
                    <td>
                      {item.code ? (
                        <span
                          className="badge"
                          style={{
                            backgroundColor: '#eff6ff',
                            color: '#1d4ed8',
                            border: '1px solid #bfdbfe',
                            fontSize: '0.78rem',
                            fontWeight: 600,
                            padding: '4px 8px',
                          }}
                        >
                          {item.code}
                        </span>
                      ) : (
                        <span className="text-muted small">—</span>
                      )}
                    </td>
                    <td>
                      <div className="text-muted small">
                        {item.description || '—'}
                      </div>
                    </td>
                    <td>
                      <span
                        className={`badge ${item.is_active ? 'bg-success' : 'bg-secondary'}`}
                        style={{ fontSize: '0.7rem', textTransform: 'uppercase' }}
                      >
                        {item.is_active ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td className="text-end">
                      <div className="btn-group btn-group-sm">
                        <button
                          type="button"
                          className="btn btn-outline-secondary"
                          onClick={() => openEditModal(item)}
                          title="Edit Type"
                        >
                          <i className="bi bi-pencil-fill"></i>
                        </button>
                        <button
                          type="button"
                          className={`btn ${item.is_active ? 'btn-outline-warning' : 'btn-outline-success'}`}
                          onClick={() => handleToggleActive(item)}
                          title={item.is_active ? 'Deactivate' : 'Activate'}
                        >
                          <i className={`bi ${item.is_active ? 'bi-pause-circle' : 'bi-play-circle'}`}></i>
                        </button>
                        <button
                          type="button"
                          className="btn btn-outline-danger"
                          onClick={() => handleDelete(item)}
                          title="Delete"
                        >
                          <i className="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal */}
      {modalOpen && (
        <div
          className="modal fade show d-block"
          tabIndex={-1}
          style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}
        >
          <div className="modal-dialog modal-dialog-centered">
            <form onSubmit={handleSubmit} className="modal-content border-0 shadow-lg" style={{ borderRadius: '16px' }}>
              <div className="modal-header border-bottom-0 pb-0">
                <div>
                  <h5 className="modal-title fw-bold">
                    {editingType ? 'Edit Anesthesia Type' : 'Add New Anesthesia Type'}
                  </h5>
                  <p className="text-muted small mb-0">
                    This option will appear in admission forms and Operation Notes.
                  </p>
                </div>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setModalOpen(false)}
                ></button>
              </div>

              <div className="modal-body p-4">
                <div className="row g-3">
                  <div className="col-12">
                    <label className="form-label fw-bold small text-uppercase required">
                      Anesthesia Type Name
                    </label>
                    <input
                      type="text"
                      className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                      placeholder="e.g. Spinal Anesthesia (SA)"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      required
                    />
                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                  </div>

                  <div className="col-md-6">
                    <label className="form-label fw-bold small text-uppercase">
                      Code / Abbreviation
                    </label>
                    <input
                      type="text"
                      className={`form-control ${errors.code ? 'is-invalid' : ''}`}
                      placeholder="e.g. SA, GA, LA, EA"
                      value={data.code}
                      onChange={(e) => setData('code', e.target.value)}
                    />
                    {errors.code && <div className="invalid-feedback">{errors.code}</div>}
                  </div>

                  <div className="col-md-6">
                    <label className="form-label fw-bold small text-uppercase">
                      Display Sort Order
                    </label>
                    <input
                      type="number"
                      className="form-control"
                      value={data.sort_order}
                      onChange={(e) => setData('sort_order', parseInt(e.target.value, 10) || 0)}
                    />
                  </div>

                  <div className="col-12">
                    <label className="form-label fw-bold small text-uppercase">
                      Description & Indications
                    </label>
                    <textarea
                      rows={2}
                      className="form-control"
                      placeholder="e.g. Subarachnoid block for Caesarean / pelvic procedures"
                      value={data.description}
                      onChange={(e) => setData('description', e.target.value)}
                    />
                  </div>

                  <div className="col-12">
                    <div className="form-check form-switch mt-2">
                      <input
                        type="checkbox"
                        className="form-check-input"
                        id="is_active_check"
                        checked={data.is_active}
                        onChange={(e) => setData('is_active', e.target.checked)}
                      />
                      <label className="form-check-label fw-semibold" htmlFor="is_active_check">
                        Active (Visible in admission & OT notes dropdowns)
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div className="modal-footer border-top-0 pt-0">
                <button
                  type="button"
                  className="btn btn-outline-secondary"
                  onClick={() => setModalOpen(false)}
                >
                  Cancel
                </button>
                <button type="submit" className="btn btn-primary">
                  <i className="bi bi-check-circle-fill me-1"></i>
                  {editingType ? 'Save Changes' : 'Create Anesthesia Type'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AppLayout>
  );
}

