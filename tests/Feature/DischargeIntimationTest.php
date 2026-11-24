<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DischargeIntimationTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected User $nurse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::create([
            'name' => 'Dr. Karthik MS',
            'email' => 'doctor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'doctor_reg_no' => 'TNMC-64821',
            'is_active' => true,
        ]);

        $this->nurse = User::create([
            'name' => 'Staff Nurse Anitha',
            'email' => 'nurse@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
            'is_active' => true,
        ]);
    }

    public function test_nurse_can_store_vhn_and_phc_details_on_admission(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Kavitha Murugan',
            'attender_name' => 'Murugan S',
            'attender_relation' => 'Husband',
            'uhid' => 'GH-2026-9901',
            'ip_no' => 'IP-9901',
            'picme_no' => '331024567890',
            'vhn_name' => 'S. Lakshmi',
            'phc' => 'Pennagaram PHC',
            'age' => '26',
            'sex' => 'Female',
            'address_line1' => '12 Gandhi Road',
            'address_line2' => 'Pennagaram Village',
            'contact' => '9876543210',
            'admission_at' => now()->format('Y-m-d H:i:s'),
            'diagnosis' => 'P1L1A1 | GDM | Emergency LSCS',
            'format' => 'general',
            'surgery_type' => 'delivery',
        ]);

        $response->assertSessionHasNoErrors();

        $admission = Admission::where('ip_no', 'IP-9901')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('S. LAKSHMI', $admission->vhn_name);
        $this->assertEquals('PENNAGARAM PHC', $admission->phc);
        $this->assertEquals('331024567890', $admission->picme_no);
    }

    public function test_doctor_can_update_vhn_and_phc_details_on_admission(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Meena Kumar',
            'attender_name' => 'Kumar R',
            'attender_relation' => 'Husband',
            'uhid' => 'GH-2026-9902',
            'ip_no' => 'IP-9902',
            'picme_no' => '331024567891',
            'age' => '28',
            'sex' => 'Female',
            'address_line1' => '45 Main Street',
            'contact' => '9876543211',
            'admission_at' => now(),
            'diagnosis' => 'Term Pregnancy with Oligohydramnios',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'created_by' => $this->doctor->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->doctor)->put(route('admissions.update', $admission), [
            'patient_name' => 'Meena Kumar',
            'attender_name' => 'Kumar R',
            'attender_relation' => 'Husband',
            'uhid' => 'GH-2026-9902',
            'ip_no' => 'IP-9902',
            'picme_no' => '331024567891',
            'vhn_name' => 'R. Revathi',
            'phc' => 'Indur PHC',
            'age' => '28',
            'sex' => 'Female',
            'address_line1' => '45 Main Street',
            'contact' => '9876543211',
            'admission_at' => now()->format('Y-m-d H:i:s'),
            'diagnosis' => 'Term Pregnancy with Oligohydramnios',
            'format' => 'general',
            'surgery_type' => 'delivery',
        ]);

        $response->assertSessionHasNoErrors();
        $admission->refresh();
        $this->assertEquals('R. REVATHI', $admission->vhn_name);
        $this->assertEquals('INDUR PHC', $admission->phc);
    }
}
