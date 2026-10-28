<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $counts = [
            'total' => Admission::count(),
            'draft' => Admission::where('status', 'draft')->count(),
            'ready_for_review' => Admission::where('status', 'ready_for_review')->count(),
            'returned_for_correction' => Admission::where('status', 'returned_for_correction')->count(),
            'approved' => Admission::where('status', 'approved')->count(),
            'generated' => Admission::where('status', 'generated')->count(),
        ];

        $query = Admission::with(['creator', 'approver', 'newborns'])
            ->latest('id');

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('uhid', 'like', "%{$search}%")
                  ->orWhere('ip_no', 'like', "%{$search}%")
                  ->orWhere('consultant', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Format Filter
        if ($format = $request->input('format')) {
            $query->where('format', $format);
        }

        // Workflow Type Filter
        if ($surgeryType = $request->input('surgery_type')) {
            $query->where('surgery_type', $surgeryType);
        }

        // Date Filter
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('admission_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('admission_at', '<=', $dateTo);
        }

        $admissions = $query->paginate(15)->withQueryString();

        return view('dashboard', compact('counts', 'admissions'));
    }
}

