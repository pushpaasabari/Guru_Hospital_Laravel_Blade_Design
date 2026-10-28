<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized access to user management.');
        }

        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['administrator', 'doctor', 'nursing_staff', 'ot_staff', 'mrd_staff', 'auditor'])],
            'doctor_reg_no' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $newUser = User::create($validated);

        AuditService::log(
            'user.created',
            'User',
            $newUser->id,
            "User {$newUser->name} ({$newUser->email}, Role: {$newUser->role}) created by {$currentUser->name}"
        );

        return redirect()->route('users.index')
            ->with('status', "User '{$newUser->name}' created successfully.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        AuditService::log(
            'user.status_toggled',
            'User',
            $user->id,
            "User {$user->name} status changed to " . ($user->is_active ? 'Active' : 'Inactive') . " by {$currentUser->name}"
        );

        return redirect()->route('users.index')
            ->with('status', "User '{$user->name}' " . ($user->is_active ? 'activated' : 'deactivated') . " successfully.");
    }
}

