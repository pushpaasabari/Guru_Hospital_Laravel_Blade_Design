<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $statusCounts = Admission::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $inAdmissionCount = (int) Admission::query()
            ->whereNull('discharge_at')
            ->count();

        $counts = [
            'total' => (int) $statusCounts->sum(),
            'draft' => (int) ($statusCounts['draft'] ?? 0),
            'ready_for_review' => (int) ($statusCounts['ready_for_review'] ?? 0),
            'returned_for_correction' => (int) ($statusCounts['returned_for_correction'] ?? 0),
            'approved' => (int) ($statusCounts['approved'] ?? 0),
            'generated' => (int) ($statusCounts['generated'] ?? 0),
            'in_admission' => $inAdmissionCount,
        ];

        $query = Admission::query()
            ->summaryColumns()
            ->with('newborns:id,admission_id')
            ->filterSearch($request->input('search'), true)
            ->latest('id');

        if ($status = $request->input('status')) {
            if ($status === 'in_admission') {
                $query->whereNull('discharge_at');
            } else {
                $query->where('status', $status);
            }
        }

        if ($format = $request->input('format')) {
            $query->where('format', $format);
        }

        if ($surgeryType = $request->input('surgery_type')) {
            $query->where('surgery_type', $surgeryType);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('admission_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('admission_at', '<=', $dateTo);
        }

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage < 5 || $perPage > 250) {
            $perPage = 50;
        }

        $admissions = $query->paginate($perPage)->withQueryString();
        $filters = $request->only(['search', 'status', 'format', 'surgery_type', 'date_from', 'date_to']);
        $filters['per_page'] = $perPage;

        return Inertia::render('Dashboard', compact('counts', 'admissions', 'filters'));
    }
}
