@extends('layouts.app')
@section('title', 'Staff User Management — Guru Hospital')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark">Hospital Staff & Role Management</h1>
        <p class="text-muted mb-0">Manage authorized users, doctor medical council registration numbers, and role-based permissions.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus-fill me-1"></i> Add Hospital Staff User
    </button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Staff Name & Email</th>
                    <th>Role & Permissions</th>
                    <th>Department</th>
                    <th>Doctor Reg No (TMC)</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $user->name }}</div>
                        <div class="text-muted small">{{ $user->email }}</div>
                    </td>
                    <td>
                        <span class="badge bg-secondary text-uppercase" style="font-size:0.7rem;">
                            {{ str_replace('_', ' ', $user->role) }}
                        </span>
                    </td>
                    <td>
                        <span class="small">{{ $user->department ?? 'General' }}</span>
                    </td>
                    <td>
                        @if($user->doctor_reg_no)
                            <span class="badge bg-light text-dark border font-monospace">{{ $user->doctor_reg_no }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Deactivated</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.toggle', $user) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @else
                            <span class="badge bg-light text-muted border">Current User</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('users.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Hospital Staff User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Ramesh Kumar MS" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Hospital Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. ramesh@guruhospital.local" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Hospital Role</label>
                    <select name="role" class="form-select" required>
                        <option value="doctor">Doctor / Clinician (Can Review & Approve)</option>
                        <option value="nursing_staff">Nursing Staff (Can Enter & Draft)</option>
                        <option value="ot_staff">OT Staff (Can Enter OT Notes)</option>
                        <option value="mrd_staff">MRD Staff (Medical Records Archive)</option>
                        <option value="administrator">Administrator (Full Access)</option>
                        <option value="auditor">Auditor (Read-Only Quality Audit)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Doctor Medical Council Reg No (if Doctor)</label>
                    <input type="text" name="doctor_reg_no" class="form-control" placeholder="e.g. TNMC-89241">
                </div>
                <div class="mb-3">
                    <label class="form-label">Department / Unit</label>
                    <input type="text" name="department" class="form-control" placeholder="e.g. General Surgery, OBGYN, OT">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User Account</button>
            </div>
        </form>
    </div>
</div>
@endsection

