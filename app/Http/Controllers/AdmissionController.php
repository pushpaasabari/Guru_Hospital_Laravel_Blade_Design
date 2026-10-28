<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function create(): View
    {
        return view('admissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_name' => ['required', 'string', 'max:160'],
            'uhid' => ['required', 'string', 'max:60'],
            'ip_no' => ['required', 'string', 'max:60'],
            'age' => ['nullable', 'string', 'max:30'],
            'sex' => ['nullable', Rule::in(['Female', 'Male', 'Other', 'Not recorded'])],
            'address' => ['nullable', 'string', 'max:1000'],
            'contact' => ['nullable', 'string', 'max:50'],
            'admission_at' => ['required', 'date'],
            'procedure_at' => ['nullable', 'date'],
            'discharge_at' => ['required', 'date', 'after_or_equal:admission_at'],
            'consultant' => ['nullable', 'string', 'max:160'],
            'department' => ['nullable', 'string', 'max:160'],
            'diagnosis' => ['required', 'string', 'max:3000'],
            'format' => ['required', Rule::in(['general', 'cmchistn', 'private', 'employee'])],
            'surgery_type' => ['required', Rule::in(['delivery', 'other'])],
            'clinical_history' => ['nullable', 'string', 'max:10000'],
            'examination' => ['nullable', 'string', 'max:10000'],
            'investigations' => ['nullable', 'array'],
            'treatment_course' => ['nullable', 'string', 'max:10000'],
            'discharge_condition' => ['nullable', 'string', 'max:5000'],
            'medication_orders' => ['nullable', 'string', 'max:10000'],
            'discharge_advice' => ['nullable', 'string', 'max:10000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
            'procedure_name' => ['nullable', 'string', 'max:300'],
            'surgeon' => ['nullable', 'string', 'max:160'],
            'anaesthetist' => ['nullable', 'string', 'max:160'],
            'assistant' => ['nullable', 'string', 'max:160'],
            'anaesthesia_type' => ['nullable', 'string', 'max:160'],
            'procedure_note' => ['nullable', 'string', 'max:15000'],
            'incision' => ['nullable', 'string', 'max:5000'],
            'findings' => ['nullable', 'string', 'max:10000'],
            'complications' => ['nullable', 'string', 'max:5000'],
            'blood_loss_ml' => ['nullable', 'numeric', 'min:0'],
            'specimen' => ['nullable', 'string', 'max:2000'],
            'drain' => ['nullable', 'string', 'max:2000'],
            'postoperative_orders' => ['nullable', 'string', 'max:10000'],
            'mrd_no' => ['nullable', 'string', 'max:60'],
            'payment_mode' => ['nullable', 'string', 'max:100'],
            'newborns' => ['required_if:surgery_type,delivery', 'array'],
            'newborns.*.sex' => ['nullable', Rule::in(['Female', 'Male', 'Other', 'Not recorded'])],
            'newborns.*.birth_at' => ['nullable', 'date'],
            'newborns.*.birth_weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:8'],
            'newborns.*.apgar_1_min' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.apgar_5_min' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.neonatal_care' => ['nullable', 'string', 'max:3000'],
            'newborns.*.notes' => ['nullable', 'string', 'max:3000'],
        ]);

        // Do not trust the browser's user ID; use the authenticated session.
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'draft';

        $admission = DB::transaction(function () use ($validated) {
            $newborns = $validated['newborns'] ?? [];
            unset($validated['newborns']);
            $admission = Admission::create($validated);
            if ($admission->isDelivery()) {
                foreach ($newborns as $newborn) {
                    if (collect($newborn)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty()) {
                        $admission->newborns()->create($newborn);
                    }
                }
            }
            return $admission;
        });

        return redirect()->route('documents.preview', $admission)
            ->with('status', 'Draft saved. Review all three documents before approval.');
    }
}
