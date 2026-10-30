import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

interface StaffMember {
  id: number;
  name: string;
  category: string;
  roles: string[];
  role_labels: string[];
  qualification?: string;
  doctor_reg_no?: string;
  department?: string;
  phone?: string;
  is_active: boolean;
  created_at: string;
}

interface Props {
  staff: StaffMember[];
  availableRoles: Record<string, string>;
  currentFilter: string;
}

const roleBadgeColor: Record<string, { bg: string; text: string; border: string }> = {
  consultant_doctor: { bg: '#eff6ff', text: '#1d4ed8', border: '#bfdbfe' },
  surgeon: { bg: '#eef2ff', text: '#4338ca', border: '#c7d2fe' },
  assisted_surgeon: { bg: '#faf5ff', text: '#7e22ce', border: '#e9d5ff' },
  anaesthetist: { bg: '#f0fdf4', text: '#15803d', border: '#bbf7d0' },
  assisted_anaesthetist: { bg: '#ecfeff', text: '#0e7490', border: '#a5f3fc' },
  staff_nurse: { bg: '#fdf2f8', text: '#be185d', border: '#fbcfe8' },
  ot_technician: { bg: '#fffbeb', text: '#b45309', border: '#fde68a' },
};

export default function Index({ staff, availableRoles, currentFilter }: Props) {
  const [modalOpen, setModalOpen] = useState(false);
  const [editingStaff, setEditingStaff] = useState<StaffMember | null>(null);

  const { data, setData, post, put, processing, reset, errors } = useForm({
    name: '',
    category: 'consultant_doctor',
    roles: ['consultant_doctor'] as string[],
    qualification: '',
    doctor_reg_no: '',
    department: '',
    phone: '',
    is_active: true,
  });

  const openCreateModal = () => {
    setEditingStaff(null);
    reset();
    setData({
      name: '',
      category: 'consultant_doctor',
      roles: ['consultant_doctor'],
      qualification: '',
      doctor_reg_no: '',
      department: '',
      phone: '',
      is_active: true,
    });
    setModalOpen(true);
  };

  const openEditModal = (member: StaffMember) => {
    setEditingStaff(member);
    setData({
      name: member.name,
      category: member.category,
      roles: member.roles || [member.category],
      qualification: member.qualification || '',
      doctor_reg_no: member.doctor_reg_no || '',
      department: member.department || '',
      phone: member.phone || '',
      is_active: member.is_active,
    });
    setModalOpen(true);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingStaff) {
      put(`/clinical-staff/${editingStaff.id}`, {
        onSuccess: () => {
          setModalOpen(false);
          reset();
        },
      });
    } else {
      post('/clinical-staff', {
        onSuccess: () => {
          setModalOpen(false);
          reset();
        },
      });
    }
  };

  const handleToggleRole = (roleKey: string) => {
    const current = [...data.roles];
    const index = current.indexOf(roleKey);
    if (index >= 0) {
      if (current.length > 1) {
        current.splice(index, 1);
        setData({
          ...data,
          roles: current,
          category: data.category === roleKey ? current[0] : data.category,
        });
      }
    } else {
      current.push(roleKey);
      setData({ ...data, roles: current });
    }
  };

  const handleToggleActive = (member: StaffMember) => {
    if (confirm(`Are you sure you want to ${member.is_active ? 'deactivate' : 'activate'} ${member.name}?`)) {
      router.patch(`/clinical-staff/${member.id}/toggle`);
    }
  };

  const handleDelete = (member: StaffMember) => {
    if (confirm(`Are you sure you want to delete ${member.name} from clinical staff?`)) {
      router.delete(`/clinical-staff/${member.id}`);
    }
  };

  const handleFilterChange = (roleKey: string) => {
    router.get('/clinical-staff', roleKey === 'all' ? {} : { role: roleKey }, { preserveState: true });
  };

  return (
    <AppLayout>
      <Head title="Clinical Staff & Doctors Management — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Clinical Staff & Doctors Directory
          </h1>
          <p className="text-muted mb-0">
            Manage Consultant Doctors, Surgeons, Assisted Surgeons, Anaesthetists, Assisted Anaesthetists, Staff Nurses, and OT Technicians for admission form dropdowns.
          </p>
        </div>
        <button
          type="button"
          className="btn btn-primary shadow-sm"
          onClick={openCreateModal}
        >
          <i className="bi bi-person-plus-fill me-1"></i> Add Clinical Staff
        </button>
      </div>

      {/* Filter Tabs */}
      <div className="card shadow-sm border-0 mb-3" style={{ borderRadius: '14px' }}>
        <div className="card-body p-2 d-flex flex-wrap gap-1 align-items-center">
          <button
            type="button"
            className={`btn btn-sm ${currentFilter === 'all' ? 'btn-primary' : 'btn-light border-0 text-muted'}`}
            style={{ borderRadius: '8px', fontWeight: 500 }}
            onClick={() => handleFilterChange('all')}
          >
            All Roles ({staff.length})
          </button>
          {Object.entries(availableRoles).map(([key, label]) => (
            <button
              key={key}
              type="button"
              className={`btn btn-sm ${currentFilter === key ? 'btn-primary' : 'btn-light border-0 text-muted'}`}
              style={{ borderRadius: '8px', fontWeight: 500 }}
              onClick={() => handleFilterChange(key)}
            >
              {label}
            </button>
          ))}
        </div>
      </div>

      {/* Staff Table */}
      <div className="card shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th style={{ width: '28%' }}>Staff / Doctor Name</th>
                <th style={{ width: '26%' }}>Clinical Role(s)</th>
                <th style={{ width: '18%' }}>Department & Qualification</th>
                <th style={{ width: '12%' }}>Doctor / Reg No</th>
                <th style={{ width: '8%' }}>Status</th>
                <th style={{ width: '8%' }} className="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              {staff.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                    No clinical staff records found matching the filter. Click "Add Clinical Staff" to register one.
                  </td>
                </tr>
              ) : (
                staff.map((member) => (
                  <tr key={member.id}>
                    <td>
                      <div className="d-flex align-items-center gap-2">
                        <div
                          className="d-flex align-items-center justify-content-center text-primary fw-bold"
                          style={{
                            width: '38px',
                            height: '38px',
                            borderRadius: '10px',
                            background: '#eff6ff',
                            fontSize: '1rem',
                            flexShrink: 0,
                          }}
                        >
                          {member.name.replace(/^Dr\.\s*/i, '').charAt(0).toUpperCase()}
                        </div>
                        <div>
                          <div className="fw-bold text-dark">{member.name}</div>
                          {member.phone && (
                            <div className="text-muted small">
                              <i className="bi bi-telephone me-1"></i>
                              {member.phone}
                            </div>
                          )}
                        </div>
                      </div>
                    </td>
                    <td>
                      <div className="d-flex flex-wrap gap-1">
                        {(member.roles || [member.category]).map((r) => {
                          const colors = roleBadgeColor[r] || { bg: '#f1f5f9', text: '#334155', border: '#cbd5e1' };
                          const label = availableRoles[r] || r.replace(/_/g, ' ');
                          return (
                            <span
                              key={r}
                              className="badge"
                              style={{
                                backgroundColor: colors.bg,
                                color: colors.text,
                                border: `1px solid ${colors.border}`,
                                fontSize: '0.72rem',
                                fontWeight: 600,
                                padding: '4px 8px',
                                borderRadius: '6px',
                              }}
                            >
                              {label}
                            </span>
                          );
                        })}
                      </div>
                    </td>
                    <td>
                      <div className="fw-semibold text-dark small">{member.department || '—'}</div>
                      {member.qualification && (
                        <div className="text-muted small">{member.qualification}</div>
                      )}
                    </td>
                    <td>
                      {member.doctor_reg_no ? (
                        <span className="font-monospace small fw-bold text-dark">
                          {member.doctor_reg_no}
                        </span>
                      ) : (
                        <span className="text-muted small">—</span>
                      )}
                    </td>
                    <td>
                      <span
                        className={`badge ${member.is_active ? 'bg-success' : 'bg-secondary'}`}
                        style={{ fontSize: '0.7rem', textTransform: 'uppercase' }}
                      >
                        {member.is_active ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td className="text-end">
                      <div className="btn-group btn-group-sm">
                        <button
                          type="button"
                          className="btn btn-outline-secondary"
                          onClick={() => openEditModal(member)}
                          title="Edit Staff Member"
                        >
                          <i className="bi bi-pencil-fill"></i>
                        </button>
                        <button
                          type="button"
                          className={`btn ${member.is_active ? 'btn-outline-warning' : 'btn-outline-success'}`}
                          onClick={() => handleToggleActive(member)}
                          title={member.is_active ? 'Deactivate' : 'Activate'}
                        >
                          <i className={`bi ${member.is_active ? 'bi-pause-circle' : 'bi-play-circle'}`}></i>
                        </button>
                        <button
                          type="button"
                          className="btn btn-outline-danger"
                          onClick={() => handleDelete(member)}
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

      {/* Create / Edit Modal */}
      {modalOpen && (
        <div
          className="modal fade show d-block"
          tabIndex={-1}
          style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}
        >
          <div className="modal-dialog modal-dialog-centered modal-lg">
            <form onSubmit={handleSubmit} className="modal-content border-0 shadow-lg" style={{ borderRadius: '16px' }}>
              <div className="modal-header border-bottom-0 pb-0">
                <div>
                  <h5 className="modal-title fw-bold">
                    {editingStaff ? 'Edit Clinical Staff Member' : 'Add New Clinical Staff / Doctor'}
                  </h5>
                  <p className="text-muted small mb-0">
                    Staff members added here appear in admission and operation notes dropdowns.
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
                  <div className="col-md-7">
                    <label className="form-label fw-bold small text-uppercase required">
                      Staff / Doctor Full Name
                    </label>
                    <input
                      type="text"
                      className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                      placeholder="e.g. Dr. Ambika M.S (O&G)"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      required
                    />
                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                  </div>

                  <div className="col-md-5">
                    <label className="form-label fw-bold small text-uppercase required">
                      Primary Category
                    </label>
                    <select
                      className={`form-select ${errors.category ? 'is-invalid' : ''}`}
                      value={data.category}
                      onChange={(e) => {
                        const newCat = e.target.value;
                        const newRoles = data.roles.includes(newCat) ? data.roles : [...data.roles, newCat];
                        setData({ ...data, category: newCat, roles: newRoles });
                      }}
                      required
                    >
                      {Object.entries(availableRoles).map(([k, lbl]) => (
                        <option key={k} value={k}>
                          {lbl}
                        </option>
                      ))}
                    </select>
                  </div>

                  {/* Multi-role selection */}
                  <div className="col-12">
                    <label className="form-label fw-bold small text-uppercase d-block mb-1">
                      Roles & Designations (Appears in selected dropdowns)
                    </label>
                    <div className="d-flex flex-wrap gap-2 p-2 bg-light rounded border">
                      {Object.entries(availableRoles).map(([k, lbl]) => {
                        const isChecked = data.roles.includes(k);
                        return (
                          <div
                            key={k}
                            className={`badge p-2 cursor-pointer border ${isChecked ? 'bg-primary text-white border-primary' : 'bg-white text-dark'}`}
                            style={{ cursor: 'pointer', userSelect: 'none', fontSize: '0.8rem', fontWeight: 500 }}
                            onClick={() => handleToggleRole(k)}
                          >
                            <input
                              type="checkbox"
                              className="form-check-input me-1"
                              checked={isChecked}
                              onChange={() => {}}
                            />
                            {lbl}
                          </div>
                        );
                      })}
                    </div>
                    <small className="text-muted d-block mt-1">
                      Check all roles this person can perform so their name appears in each corresponding dropdown.
                    </small>
                  </div>

                  <div className="col-md-6">
                    <label className="form-label fw-bold small text-uppercase">
                      Qualification / Degrees
                    </label>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="e.g. M.S (O&G), MBBS, DA, B.Sc Nursing"
                      value={data.qualification}
                      onChange={(e) => setData('qualification', e.target.value)}
                    />
                  </div>

                  <div className="col-md-6">
                    <label className="form-label fw-bold small text-uppercase">
                      Doctor / Council Reg. No.
                    </label>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="e.g. TNMC-64821"
                      value={data.doctor_reg_no}
                      onChange={(e) => setData('doctor_reg_no', e.target.value)}
                    />
                  </div>

                  <div className="col-md-6">
                    <label className="form-label fw-bold small text-uppercase">
                      Department / Specialty
                    </label>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="e.g. Obstetrics & Gynaecology, Surgery, OT"
                      value={data.department}
                      onChange={(e) => setData('department', e.target.value)}
                    />
                  </div>

                  <div className="col-md-6">
                    <label className="form-label fw-bold small text-uppercase">
                      Contact / Mobile Number
                    </label>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="e.g. 9443212345"
                      value={data.phone}
                      onChange={(e) => setData('phone', e.target.value)}
                    />
                  </div>

                  <div className="col-12">
                    <div className="form-check form-switch mt-2">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="staffActiveSwitch"
                        checked={data.is_active}
                        onChange={(e) => setData('is_active', e.target.checked)}
                      />
                      <label className="form-check-label fw-semibold" htmlFor="staffActiveSwitch">
                        Active for Form Dropdowns
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div className="modal-footer border-top-0 pt-0">
                <button
                  type="button"
                  className="btn btn-light"
                  onClick={() => setModalOpen(false)}
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="btn btn-primary px-4"
                  disabled={processing}
                >
                  {processing ? 'Saving...' : editingStaff ? 'Save Changes' : 'Add Clinical Staff'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AppLayout>
  );
}

