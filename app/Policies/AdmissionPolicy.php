<?php

namespace App\Policies;

use App\Models\Admission;
use App\Models\User;

class AdmissionPolicy
{
    public function view(User $user, Admission $admission): bool
    {
        // Replace with hospital role/department access rules.
        return $user->can('view-medical-records');
    }

    public function generateDocuments(User $user, Admission $admission): bool
    {
        // Replace with explicit clinician-approved role and audit workflow.
        return $user->can('generate-approved-document-pack') && $admission->status === 'approved';
    }
}
