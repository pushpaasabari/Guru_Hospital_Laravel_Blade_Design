<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdmissionsArchiveDatatableTest extends TestCase
{
    use RefreshDatabase;

    public function test_admissions_archive_provides_advanced_datatable_counts_metadata_and_filters(): void
    {
        $doctor = User::create([
            'name' => 'Dr. Archive Lead',
            'email' => 'archive@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        Admission::create([
            'patient_name' => 'MRS. ARCHIVE ONE',
            'uhid' => 'GH-8001',
            'ip_no' => '2026/801',
            'mrd_no' => 'MRD-2026/801',
            'age' => '28',
            'sex' => 'Female',
            'admission_at' => '2026-11-20 09:00:00',
            'discharge_at' => '2026-11-23 11:00:00',
            'consultant' => 'DR. AMBIKA',
            'department' => 'OBG',
            'diagnosis' => 'Primi at term gestation',
            'procedure_done' => 'Emergency LSCS',
            'format' => 'cmchistn',
            'surgery_type' => 'delivery',
            'status' => 'generated',
            'created_by' => $doctor->id,
        ]);

        Admission::create([
            'patient_name' => 'MR. ARCHIVE TWO',
            'uhid' => 'GH-8002',
            'ip_no' => '2026/802',
            'age' => '45',
            'sex' => 'Male',
            'admission_at' => '2026-11-25 14:30:00',
            'consultant' => 'DR. JAYAPRAKASH',
            'department' => 'Urology',
            'diagnosis' => 'Right Ureteric Calculus',
            'procedure_done' => 'URSL',
            'format' => 'general',
            'surgery_type' => 'other',
            'status' => 'draft',
            'created_by' => $doctor->id,
        ]);

        $response = $this->actingAs($doctor)->get(route('admissions.index'));
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admissions/Index')
            ->where('counts.total', 2)
            ->where('counts.generated', 1)
            ->where('counts.draft', 1)
            ->where('counts.in_admission', 1)
            ->has('admissions.data', 2)
            ->where('admissions.data.0.diagnosis', 'Right Ureteric Calculus')
            ->where('admissions.data.1.mrd_no', 'MRD-2026/801')
        );

        // Multi-criteria filtering (diagnosis search + consultant + format + date range)
        $filtered = $this->actingAs($doctor)->get(route('admissions.index', [
            'search' => 'Ureteric',
            'consultant' => 'DR. JAYAPRAKASH',
            'format' => 'general',
            'surgery_type' => 'other',
            'date_from' => '2026-11-24',
            'date_to' => '2026-11-27',
        ]));

        $filtered->assertOk();
        $filtered->assertInertia(fn (Assert $page) => $page
            ->component('Admissions/Index')
            ->has('admissions.data', 1)
            ->where('admissions.data.0.patient_name', 'MR. ARCHIVE TWO')
        );
    }
}
