<?php

namespace App\Http\Controllers;

use App\Models\ClinicalStaff;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClinicalStaffController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized access to clinical staff management.');
        }

        $query = ClinicalStaff::query()->orderBy('name');

        if ($roleFilter = $request->input('role')) {
            if ($roleFilter !== 'all' && isset(ClinicalStaff::ROLES[$roleFilter])) {
                $query->forRole($roleFilter);
            }
        }

        $staff = $query->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'category' => $member->category,
                'roles' => $member->roles ?: [$member->category],
                'role_labels' => $member->role_labels,
                'qualification' => $member->qualification,
                'doctor_reg_no' => $member->doctor_reg_no,
                'department' => $member->department,
                'phone' => $member->phone,
                'is_active' => $member->is_active,
                'created_at' => $member->created_at?->format('d.m.Y'),
            ];
        });

        return Inertia::render('ClinicalStaff/Index', [
            'staff' => $staff,
            'availableRoles' => ClinicalStaff::ROLES,
            'currentFilter' => $request->input('role', 'all'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', Rule::in(array_keys(ClinicalStaff::ROLES))],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in(array_keys(ClinicalStaff::ROLES))],
            'qualification' => ['nullable', 'string', 'max:120'],
            'doctor_reg_no' => ['nullable', 'string', 'max:60'],
            'department' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $roles = $validated['roles'] ?? [];
        if (!in_array($validated['category'], $roles, true)) {
            $roles[] = $validated['category'];
        }
        $validated['roles'] = array_values(array_unique($roles));
        $validated['is_active'] = $validated['is_active'] ?? true;

        $newStaff = ClinicalStaff::create($validated);

        AuditService::log(
            'clinical_staff.created',
            'ClinicalStaff',
            $newStaff->id,
            "Clinical staff '{$newStaff->name}' (Category: {$newStaff->category}) created by {$currentUser->name}"
        );

        return redirect()->route('clinical-staff.index')
            ->with('status', "Clinical staff '{$newStaff->name}' added successfully.");
    }

    public function update(Request $request, ClinicalStaff $clinicalStaff): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', Rule::in(array_keys(ClinicalStaff::ROLES))],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in(array_keys(ClinicalStaff::ROLES))],
            'qualification' => ['nullable', 'string', 'max:120'],
            'doctor_reg_no' => ['nullable', 'string', 'max:60'],
            'department' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $roles = $validated['roles'] ?? [];
        if (!in_array($validated['category'], $roles, true)) {
            $roles[] = $validated['category'];
        }
        $validated['roles'] = array_values(array_unique($roles));

        $clinicalStaff->update($validated);

        AuditService::log(
            'clinical_staff.updated',
            'ClinicalStaff',
            $clinicalStaff->id,
            "Clinical staff '{$clinicalStaff->name}' updated by {$currentUser->name}"
        );

        return redirect()->route('clinical-staff.index')
            ->with('status', "Clinical staff '{$clinicalStaff->name}' updated successfully.");
    }

    public function destroy(Request $request, ClinicalStaff $clinicalStaff): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $name = $clinicalStaff->name;
        $clinicalStaff->delete();

        AuditService::log(
            'clinical_staff.deleted',
            'ClinicalStaff',
            $clinicalStaff->id,
            "Clinical staff '{$name}' deleted by {$currentUser->name}"
        );

        return redirect()->route('clinical-staff.index')
            ->with('status', "Clinical staff '{$name}' removed successfully.");
    }

    public function toggleActive(Request $request, ClinicalStaff $clinicalStaff): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $clinicalStaff->is_active = !$clinicalStaff->is_active;
        $clinicalStaff->save();

        AuditService::log(
            'clinical_staff.status_toggled',
            'ClinicalStaff',
            $clinicalStaff->id,
            "Clinical staff '{$clinicalStaff->name}' status changed to " . ($clinicalStaff->is_active ? 'Active' : 'Inactive') . " by {$currentUser->name}"
        );

        return redirect()->route('clinical-staff.index')
            ->with('status', "Clinical staff '{$clinicalStaff->name}' " . ($clinicalStaff->is_active ? 'activated' : 'deactivated') . " successfully.");
    }
}

