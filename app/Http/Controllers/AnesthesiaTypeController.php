<?php

namespace App\Http\Controllers;

use App\Models\AnesthesiaType;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AnesthesiaTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized access to anesthesia types management.');
        }

        $types = AnesthesiaType::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'code' => $type->code,
                    'description' => $type->description,
                    'is_active' => $type->is_active,
                    'sort_order' => $type->sort_order,
                    'created_at' => $type->created_at?->format('d.m.Y'),
                ];
            });

        return Inertia::render('AnesthesiaTypes/Index', [
            'anesthesiaTypes' => $types,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:anesthesia_types,name'],
            'code' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $newType = AnesthesiaType::create($validated);

        AuditService::log(
            'anesthesia_type.created',
            'AnesthesiaType',
            $newType->id,
            "Anesthesia type '{$newType->name}' (Code: {$newType->code}) created by {$currentUser->name}"
        );

        return redirect()->route('anesthesia-types.index')
            ->with('status', "Anesthesia type '{$newType->name}' added successfully.");
    }

    public function update(Request $request, AnesthesiaType $anesthesiaType): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('anesthesia_types', 'name')->ignore($anesthesiaType->id),
            ],
            'code' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $anesthesiaType->update($validated);

        AuditService::log(
            'anesthesia_type.updated',
            'AnesthesiaType',
            $anesthesiaType->id,
            "Anesthesia type '{$anesthesiaType->name}' updated by {$currentUser->name}"
        );

        return redirect()->route('anesthesia-types.index')
            ->with('status', "Anesthesia type '{$anesthesiaType->name}' updated successfully.");
    }

    public function destroy(Request $request, AnesthesiaType $anesthesiaType): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $name = $anesthesiaType->name;
        $anesthesiaType->delete();

        AuditService::log(
            'anesthesia_type.deleted',
            'AnesthesiaType',
            $anesthesiaType->id,
            "Anesthesia type '{$name}' deleted by {$currentUser->name}"
        );

        return redirect()->route('anesthesia-types.index')
            ->with('status', "Anesthesia type '{$name}' removed successfully.");
    }

    public function toggleActive(Request $request, AnesthesiaType $anesthesiaType): RedirectResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $anesthesiaType->is_active = !$anesthesiaType->is_active;
        $anesthesiaType->save();

        AuditService::log(
            'anesthesia_type.status_toggled',
            'AnesthesiaType',
            $anesthesiaType->id,
            "Anesthesia type '{$anesthesiaType->name}' status changed to " . ($anesthesiaType->is_active ? 'Active' : 'Inactive') . " by {$currentUser->name}"
        );

        return redirect()->route('anesthesia-types.index')
            ->with('status', "Anesthesia type '{$anesthesiaType->name}' " . ($anesthesiaType->is_active ? 'activated' : 'deactivated') . " successfully.");
    }
}

