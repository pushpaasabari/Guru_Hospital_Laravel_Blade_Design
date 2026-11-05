<?php

namespace App\Policies;

use App\Models\Admission;
use App\Models\User;

class AdmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function view(User $user, Admission $admission): bool
    {
        return (bool) $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canEditAdmissions();
    }

    public function update(User $user, Admission $admission): bool
    {
        return $user->is_active && $user->canEditAdmissions();
    }

    public function submitForReview(User $user, Admission $admission): bool
    {
        return $user->is_active && in_array($admission->status, ['draft', 'returned_for_correction'], true);
    }

    public function approve(User $user, Admission $admission): bool
    {
        return $user->is_active && $user->canApproveDocuments() && in_array($admission->status, ['ready_for_review', 'draft'], true);
    }

    public function returnForCorrection(User $user, Admission $admission): bool
    {
        return $user->is_active && $user->canApproveDocuments() && $admission->status === 'ready_for_review';
    }

    public function generateDocuments(User $user, Admission $admission): bool
    {
        return $user->is_active && $user->canGeneratePdfs();
    }

    public function createRevision(User $user, Admission $admission): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isDoctor()) && in_array($admission->status, ['approved', 'generated'], true);
    }
}
