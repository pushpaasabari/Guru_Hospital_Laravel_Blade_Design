<?php

namespace App\Http\Controllers;

use App\Models\ClinicalDropdownOption;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClinicalDropdownOptionController extends Controller
{
    /**
     * Quick-add a medication or immediate postoperative order so it persists across admissions.
     */
    public function quickStore(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->canEditAdmissions()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(ClinicalDropdownOption::ALLOWED_TYPES)],
            'label' => ['required', 'string', 'max:300'],
            'group_label' => ['nullable', 'string', 'max:120'],
        ]);

        $cleanLabel = ClinicalDropdownOption::cleanLabel($validated['label']);
        if (!ClinicalDropdownOption::isValidOptionLabel($cleanLabel)) {
            return response()->json([
                'message' => 'Please enter a valid option text.',
                'errors' => [
                    'label' => ['Please enter a valid option text.'],
                ],
            ], 422);
        }

        $result = ClinicalDropdownOption::rememberOne(
            $validated['type'],
            $cleanLabel,
            $currentUser->id,
            $validated['group_label'] ?? null
        );

        if ($result && ($result['created'] ?? false) && !empty($result['id'])) {
            $kindLabel = $validated['type'] === ClinicalDropdownOption::TYPE_POSTOP_ORDER
                ? 'Postoperative order'
                : 'Discharge medication';

            AuditService::log(
                'clinical_dropdown_option.quick_created',
                'ClinicalDropdownOption',
                (int) $result['id'],
                "{$kindLabel} '{$result['label']}' saved to reusable dropdown by {$currentUser->name}"
            );
        }

        return response()->json([
            'option' => $result,
            'created' => (bool) ($result['created'] ?? false),
        ], ($result['created'] ?? false) ? 201 : 200);
    }
}
