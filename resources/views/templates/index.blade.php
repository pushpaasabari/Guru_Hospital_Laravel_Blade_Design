@extends('layouts.app')
@section('title', 'Hospital Document Templates — Guru Hospital')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">Hospital Document Templates Registry</h1>
        <p class="text-muted mb-0">Manage approved layout versions, effective dates, and clinical review status for all document types.</p>
    </div>
</div>

<div class="alert alert-warning shadow-sm border-0">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Statutory Notice:</strong> Only layouts formally signed and approved by Guru Hospital Clinical Governance and Medical Administration can be marked as <em>Hospital Approved</em>. Generated patient documents reference the active approved version tag.
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Template Code</th>
                    <th>Document Title</th>
                    <th>Version Tag</th>
                    <th>Effective Date</th>
                    <th>Approval Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $tpl)
                <tr>
                    <td>
                        <span class="badge bg-secondary font-monospace">{{ $tpl->template_code }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $tpl->title }}</div>
                        <div class="text-muted small">{{ $tpl->description }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $tpl->version_tag }}</span>
                    </td>
                    <td>
                        <span class="small">{{ $tpl->effective_date?->format('d/m/Y') ?? 'Pending' }}</span>
                    </td>
                    <td>
                        @if($tpl->is_approved_by_hospital)
                            <span class="badge badge-approved"><i class="bi bi-check-circle-fill me-1"></i> Approved</span>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">By: {{ $tpl->approved_by_name }}</div>
                        @else
                            <span class="badge badge-superseded"><i class="bi bi-hourglass-split me-1"></i> Draft Placeholder</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTplModal{{ $tpl->id }}">
                            <i class="bi bi-pencil me-1"></i> Configure
                        </button>
                    </td>
                </tr>

                <!-- Configure Modal -->
                <div class="modal fade" id="editTplModal{{ $tpl->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('templates.update', $tpl) }}" class="modal-content">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" style="letter-spacing: -0.01em;">Configure Template: {{ $tpl->title }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label required">Version Tag</label>
                                    <input type="text" name="version_tag" class="form-control" value="{{ $tpl->version_tag }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Display Title</label>
                                    <input type="text" name="title" class="form-control" value="{{ $tpl->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description / Scheme Notes</label>
                                    <textarea name="description" class="form-control" rows="2">{{ $tpl->description }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Effective Date</label>
                                    <input type="date" name="effective_date" class="form-control" value="{{ $tpl->effective_date?->format('Y-m-d') }}">
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_approved_by_hospital" value="1" id="apprCheck{{ $tpl->id }}" @checked($tpl->is_approved_by_hospital)>
                                    <label class="form-check-label fw-semibold" for="apprCheck{{ $tpl->id }}">Mark as Officially Approved by Guru Hospital</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Approved By (Clinician / Authority Name)</label>
                                    <input type="text" name="approved_by_name" class="form-control" value="{{ $tpl->approved_by_name }}" placeholder="e.g. Dr. Medical Director, MS">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
