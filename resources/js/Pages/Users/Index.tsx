import { useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  doctor_reg_no?: string;
  department?: string;
  is_active: boolean;
}

interface Props {
  users: User[];
}

export default function Index({ users }: Props) {
  const { auth } = usePage<{ auth: { user: any } }>().props;
  const currentUser = auth.user;
  const [modalOpen, setModalOpen] = useState(false);

  const { data, setData, post, processing, reset, errors } = useForm({
    name: '',
    email: '',
    password: '',
    role: 'doctor',
    doctor_reg_no: '',
    department: '',
  });

  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    post('/users', {
      onSuccess: () => {
        reset();
        setModalOpen(false);
      },
    });
  };

  const handleToggle = (user: User) => {
    if (confirm(`Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} ${user.name}?`)) {
      router.patch(`/users/${user.id}/toggle`);
    }
  };

  return (
    <AppLayout>
      <Head title="Staff User Management — Guru Hospital" />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Hospital Staff & Role Management
          </h1>
          <p className="text-muted mb-0">
            Manage authorized users, doctor medical council registration numbers, and role-based
            permissions.
          </p>
        </div>
        <button
          type="button"
          className="btn btn-primary"
          onClick={() => setModalOpen(true)}
        >
          <i className="bi bi-person-plus-fill me-1"></i> Add Hospital Staff User
        </button>
      </div>

      <div className="card shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th>Staff Name & Email</th>
                <th>Role & Permissions</th>
                <th>Department</th>
                <th>Doctor Reg No (TMC)</th>
                <th>Status</th>
                <th className="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.map((u) => (
                <tr key={u.id}>
                  <td>
                    <div className="fw-bold text-dark">{u.name}</div>
                    <div className="text-muted small">{u.email}</div>
                  </td>
                  <td>
                    <span
                      className="badge bg-secondary text-uppercase"
                      style={{ fontSize: '0.7rem' }}
                    >
                      {u.role.replace(/_/g, ' ')}
                    </span>
                  </td>
                  <td>
                    <span className="small">{u.department || 'General'}</span>
                  </td>
                  <td>
                    {u.doctor_reg_no ? (
                      <span className="badge bg-light text-dark border font-monospace">
                        {u.doctor_reg_no}
                      </span>
                    ) : (
                      <span className="text-muted small">—</span>
                    )}
                  </td>
                  <td>
                    {u.is_active ? (
                      <span className="badge badge-approved">Active</span>
                    ) : (
                      <span className="badge badge-returned_for_correction">Deactivated</span>
                    )}
                  </td>
                  <td className="text-end">
                    {u.id !== currentUser?.id ? (
                      <button
                        type="button"
                        onClick={() => handleToggle(u)}
                        className={`btn btn-sm ${u.is_active ? 'btn-outline-danger' : 'btn-outline-success'}`}
                      >
                        {u.is_active ? 'Deactivate' : 'Activate'}
                      </button>
                    ) : (
                      <span className="badge bg-light text-muted border">Current User</span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create User Modal */}
      {modalOpen && (
        <div
          className="modal fade show d-block"
          tabIndex={-1}
          style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
        >
          <div className="modal-dialog modal-dialog-centered">
            <form onSubmit={handleCreateUser} className="modal-content border-0 shadow-lg">
              <div className="modal-header">
                <h5 className="modal-title fw-bold" style={{ letterSpacing: '-0.01em' }}>
                  Add Hospital Staff User
                </h5>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setModalOpen(false)}
                ></button>
              </div>
              <div className="modal-body p-4">
                <div className="mb-3">
                  <label className="form-label required fw-semibold">Full Name</label>
                  <input
                    type="text"
                    className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                    placeholder="e.g. Dr. Ramesh Kumar MS"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    required
                  />
                  {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                </div>

                <div className="mb-3">
                  <label className="form-label required fw-semibold">Hospital Email Address</label>
                  <input
                    type="email"
                    className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                    placeholder="e.g. ramesh@guruhospital.local"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    required
                  />
                  {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                </div>

                <div className="mb-3">
                  <label className="form-label required fw-semibold">Password</label>
                  <input
                    type="password"
                    className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                    minLength={8}
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    required
                  />
                  {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                </div>

                <div className="mb-3">
                  <label className="form-label required fw-semibold">Hospital Role</label>
                  <select
                    className={`form-select ${errors.role ? 'is-invalid' : ''}`}
                    value={data.role}
                    onChange={(e) => setData('role', e.target.value)}
                    required
                  >
                    <option value="doctor">Doctor / Clinician (Can Review & Approve)</option>
                    <option value="nursing_staff">Nursing Staff (Can Enter & Draft)</option>
                    <option value="ot_staff">OT Staff (Can Enter OT Notes)</option>
                    <option value="mrd_staff">MRD Staff (Medical Records Archive)</option>
                    <option value="administrator">Administrator (Full Access)</option>
                    <option value="auditor">Auditor (Read-Only Quality Audit)</option>
                  </select>
                  {errors.role && <div className="invalid-feedback">{errors.role}</div>}
                </div>

                <div className="mb-3">
                  <label className="form-label fw-semibold">
                    Doctor Medical Council Reg No (if Doctor)
                  </label>
                  <input
                    type="text"
                    className={`form-control ${errors.doctor_reg_no ? 'is-invalid' : ''}`}
                    placeholder="e.g. TNMC-89241"
                    value={data.doctor_reg_no}
                    onChange={(e) => setData('doctor_reg_no', e.target.value)}
                  />
                  {errors.doctor_reg_no && (
                    <div className="invalid-feedback">{errors.doctor_reg_no}</div>
                  )}
                </div>

                <div className="mb-3">
                  <label className="form-label fw-semibold">Department / Unit</label>
                  <input
                    type="text"
                    className={`form-control ${errors.department ? 'is-invalid' : ''}`}
                    placeholder="e.g. General Surgery, OBGYN, OT"
                    value={data.department}
                    onChange={(e) => setData('department', e.target.value)}
                  />
                  {errors.department && <div className="invalid-feedback">{errors.department}</div>}
                </div>
              </div>
              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-outline-secondary"
                  onClick={() => setModalOpen(false)}
                >
                  Cancel
                </button>
                <button type="submit" className="btn btn-primary" disabled={processing}>
                  {processing ? 'Creating...' : 'Create User Account'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AppLayout>
  );
}
