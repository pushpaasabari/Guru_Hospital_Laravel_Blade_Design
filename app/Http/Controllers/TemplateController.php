<?php

namespace App\Http\Controllers;

use App\Models\TemplateVersion;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized access to hospital template management.');
        }

        $templates = TemplateVersion::with('updater')->orderBy('template_code')->get();

        return Inertia::render('Templates/Index', compact('templates'));
    }

    public function update(Request $request, TemplateVersion $template): RedirectResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'version_tag' => ['required', 'string', 'max:30'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_approved_by_hospital' => ['boolean'],
            'approved_by_name' => ['nullable', 'string', 'max:150'],
            'effective_date' => ['nullable', 'date'],
        ]);

        $validated['updated_by'] = $user->id;
        $validated['is_approved_by_hospital'] = $request->boolean('is_approved_by_hospital');

        $oldData = $template->toArray();
        $template->update($validated);

        AuditService::log(
            'template.updated',
            'TemplateVersion',
            $template->id,
            "Template {$template->template_code} updated by {$user->name}",
            $oldData,
            $template->toArray()
        );

        return redirect()->route('templates.index')
            ->with('status', "Template '{$template->title}' updated successfully.");
    }
}

