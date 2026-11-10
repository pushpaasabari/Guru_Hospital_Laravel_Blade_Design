<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\ClinicalStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $doctor;
    protected User $nurse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Hospital Admin',
            'email' => 'admin@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'administrator',
            'is_active' => true,
        ]);

        $this->doctor = User::create([
            'name' => 'Dr. Karthik MS',
            'email' => 'doctor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'doctor_reg_no' => 'TNMC-64821',
            'is_active' => true,
        ]);

        $this->nurse = User::create([
            'name' => 'Nurse Anitha',
            'email' => 'nurse@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_clinical_staff_directory(): void
    {
        $response = $this->actingAs($this->nurse)->get('/clinical-staff');
        $response->assertStatus(403);

        $docResponse = $this->actingAs($this->doctor)->get('/clinical-staff');
        $docResponse->assertStatus(403);
    }

    public function test_admin_can_view_clinical_staff_directory(): void
    {
        ClinicalStaff::create([
            'name' => 'Dr. Ambika M.S (O&G)',
            'category' => 'consultant_doctor',
            'roles' => ['consultant_doctor', 'surgeon'],
            'qualification' => 'M.S (O&G)',
            'doctor_reg_no' => 'TNMC-45920',
            'department' => 'Obstetrics & Gynaecology',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/clinical-staff');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ClinicalStaff/Index')
            ->has('staff', 1)
            ->where('staff.0.name', 'Dr. Ambika M.S (O&G)')
            ->has('availableRoles')
        );
    }

    public function test_admin_can_add_clinical_staff_member(): void
    {
        $payload = [
            'name' => 'Dr. Nithyapriya (Paediatrician)',
            'category' => 'consultant_pediatrician',
            'roles' => ['consultant_pediatrician', 'consultant_doctor'],
            'qualification' => 'MD (Paediatrics)',
            'doctor_reg_no' => 'TNMC-58210',
            'department' => 'Paediatrics & Neonatology',
            'phone' => '9944112233',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/clinical-staff', $payload);
        $response->assertRedirect('/clinical-staff');

        $this->assertDatabaseHas('clinical_staff', [
            'name' => 'Dr. Nithyapriya (Paediatrician)',
            'category' => 'consultant_pediatrician',
            'doctor_reg_no' => 'TNMC-58210',
        ]);
    }

    public function test_admin_can_update_clinical_staff_member(): void
    {
        $staff = ClinicalStaff::create([
            'name' => 'OT Tech Selvam',
            'category' => 'ot_technician',
            'roles' => ['ot_technician'],
            'department' => 'Operation Theatre',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/clinical-staff/{$staff->id}", [
            'name' => 'OT Tech Selvam Senior',
            'category' => 'ot_technician',
            'roles' => ['ot_technician'],
            'department' => 'Operation Theatre & Laparoscopy',
            'is_active' => true,
        ]);

        $response->assertRedirect('/clinical-staff');
        $this->assertDatabaseHas('clinical_staff', [
            'id' => $staff->id,
            'name' => 'OT Tech Selvam Senior',
            'department' => 'Operation Theatre & Laparoscopy',
        ]);
    }

    public function test_admin_can_toggle_active_status_and_delete_staff(): void
    {
        $staff = ClinicalStaff::create([
            'name' => 'Staff Nurse Deepa',
            'category' => 'staff_nurse',
            'roles' => ['staff_nurse'],
            'is_active' => true,
        ]);

        // Toggle inactive
        $this->actingAs($this->admin)->patch("/clinical-staff/{$staff->id}/toggle");
        $this->assertFalse($staff->fresh()->is_active);

        // Delete
        $this->actingAs($this->admin)->delete("/clinical-staff/{$staff->id}");
        $this->assertDatabaseMissing('clinical_staff', ['id' => $staff->id]);
    }

    public function test_admissions_create_page_provides_clinical_staff_list(): void
    {
        ClinicalStaff::create([
            'name' => 'Dr. Ambika M.S (O&G)',
            'category' => 'consultant_doctor',
            'roles' => ['consultant_doctor', 'surgeon'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->nurse)->get('/admissions/create');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admissions/Create')
            ->has('clinicalStaff', 1)
        );
    }

    public function test_admission_saves_clinical_team_dropdown_selections(): void
    {
        $payload = [
            'patient_name' => 'Test Patient',
            'uhid' => '2026/999',
            'ip_no' => '2026/8888',
            'admission_at' => '2026-10-30T10:00',
            'discharge_at' => '2026-11-02T10:00',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'Appendicitis',
            'consultant' => 'Dr. Karthik Rajan MS, MCh',
            'surgeon' => 'Dr. Karthik Rajan MS, MCh',
            'assisted_surgeon' => 'Dr. K. Senthil MBBS',
            'anaesthetist' => 'Dr. M.S Karthick',
            'assisted_anaesthetist' => 'Dr. R. Vignesh MBBS, DA',
            'staff_nurse' => 'Staff Nurse Anitha',
            'ot_technician' => 'OT Tech Selvam',
            'pediatrician_name' => 'Dr. Nithyapriya (Paediatrician)',
            'anaesthesia_type' => 'Spinal Anesthesia (SA)',
        ];

        $response = $this->actingAs($this->nurse)->post('/admissions', $payload);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('admissions', [
            'ip_no' => '2026/8888',
            'patient_name' => 'TEST PATIENT',
            'consultant' => 'DR. KARTHIK RAJAN MS, MCH',
            'surgeon' => 'DR. KARTHIK RAJAN MS, MCH',
            'assisted_surgeon' => 'DR. K. SENTHIL MBBS',
            'anaesthetist' => 'DR. M.S KARTHICK',
            'assisted_anaesthetist' => 'DR. R. VIGNESH MBBS, DA',
            'staff_nurse' => 'STAFF NURSE ANITHA',
            'ot_technician' => 'OT TECH SELVAM',
            'pediatrician_name' => 'DR. NITHYAPRIYA (PAEDIATRICIAN)',
            'anaesthesia_type' => 'Spinal Anesthesia (SA)',
        ]);
    }
}

