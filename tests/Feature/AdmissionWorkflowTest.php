<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $nurse;
    protected User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nurse = User::create([
            'name' => 'Staff Nurse Anitha',
            'email' => 'nurse@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
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
    }

    public function test_nurse_can_create_draft_admission_with_shared_demographics(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Meena Kumar',
            'uhid' => 'GH-2026-0012',
            'ip_no' => 'IP-8891',
            'age' => '29',
            'sex' => 'Female',
            'address' => '12 Gandhi Road, Dharmapuri',
            'contact' => '9876543210',
            'admission_at' => '2026-10-25 09:00:00',
            'discharge_at' => '2026-10-28 11:30:00',
            'consultant' => 'Dr. Karthik MS',
            'department' => 'General Surgery',
            'diagnosis' => 'Acute Appendicitis with Localised Peritonitis',
            'format' => 'general',
            'surgery_type' => 'other',
            'procedure_name' => 'Emergency Appendectomy',
            'surgeon' => 'Dr. Karthik MS',
            'anaesthetist' => 'Dr. Ramesh DA',
            'findings' => 'Inflamed, swollen retrocecal appendix with fibrinous exudate',
            'discharge_condition' => 'Hemodynamically stable, wound healthy',
        ]);

        $this->assertDatabaseHas('admissions', [
            'patient_name' => 'Meena Kumar',
            'ip_no' => 'IP-8891',
            'status' => 'draft',
            'format' => 'general',
            'surgery_type' => 'other',
        ]);

        $admission = Admission::where('ip_no', 'IP-8891')->first();
        $response->assertRedirect(route('documents.preview', $admission));
    }

    public function test_duplicate_ip_number_is_rejected(): void
    {
        Admission::create([
            'patient_name' => 'Existing Patient',
            'uhid' => 'GH-1001',
            'ip_no' => 'IP-DUPLICATE-TEST',
            'admission_at' => now(),
            'discharge_at' => now()->addDays(2),
            'diagnosis' => 'Observation',
            'format' => 'general',
            'surgery_type' => 'other',
            'created_by' => $this->nurse->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Second Patient',
            'uhid' => 'GH-1002',
            'ip_no' => 'IP-DUPLICATE-TEST',
            'admission_at' => now()->toDateTimeString(),
            'discharge_at' => now()->addDays(1)->toDateTimeString(),
            'diagnosis' => 'Viral Fever',
            'format' => 'general',
            'surgery_type' => 'other',
        ]);

        $response->assertSessionHasErrors('ip_no');
    }

    public function test_delivery_workflow_persists_multiple_newborns(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Sowmya Venkat',
            'uhid' => 'GH-2026-0044',
            'ip_no' => 'IP-OBGYN-9912',
            'age' => '26',
            'sex' => 'Female',
            'admission_at' => '2026-10-20 08:00:00',
            'procedure_at' => '2026-10-20 10:15:00',
            'discharge_at' => '2026-10-24 16:00:00',
            'consultant' => 'Dr. Priya Sundaram MD',
            'department' => 'Obstetrics & Gynaecology',
            'diagnosis' => 'Twin Pregnancy at 38 weeks with In-labor status',
            'format' => 'cmchistn',
            'surgery_type' => 'delivery',
            'procedure_name' => 'Emergency Lower Segment Caesarean Section (LSCS)',
            'surgeon' => 'Dr. Priya Sundaram MD',
            'newborns' => [
                [
                    'sex' => 'Female',
                    'birth_at' => '2026-10-20 10:20:00',
                    'birth_weight_kg' => '2.65',
                    'apgar_1_min' => 8,
                    'apgar_5_min' => 9,
                    'neonatal_care' => 'Routine newborn care, cried immediately',
                    'notes' => 'Twin A, cephalic presentation',
                ],
                [
                    'sex' => 'Male',
                    'birth_at' => '2026-10-20 10:23:00',
                    'birth_weight_kg' => '2.80',
                    'apgar_1_min' => 8,
                    'apgar_5_min' => 9,
                    'neonatal_care' => 'Routine newborn care, cried immediately',
                    'notes' => 'Twin B, breech extraction',
                ],
            ],
        ]);

        $admission = Admission::where('ip_no', 'IP-OBGYN-9912')->first();
        $this->assertNotNull($admission);
        $this->assertCount(2, $admission->newborns);

        $twinA = $admission->newborns()->where('notes', 'like', '%Twin A%')->first();
        $this->assertEquals('Female', $twinA->sex);
        $this->assertEquals('2.65', $twinA->birth_weight_kg);

        $twinB = $admission->newborns()->where('notes', 'like', '%Twin B%')->first();
        $this->assertEquals('Male', $twinB->sex);
        $this->assertEquals('2.80', $twinB->birth_weight_kg);
    }

    public function test_other_surgery_workflow_does_not_persist_newborn_records(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Kannan R',
            'uhid' => 'GH-2026-0089',
            'ip_no' => 'IP-SURG-7711',
            'age' => '54',
            'sex' => 'Male',
            'admission_at' => '2026-10-21 07:00:00',
            'discharge_at' => '2026-10-23 12:00:00',
            'diagnosis' => 'Right Inguinal Hernia',
            'format' => 'private',
            'surgery_type' => 'other',
            'procedure_name' => 'Lichtenstein Tension-Free Mesh Hernioplasty',
            'newborns' => [
                ['sex' => 'Male', 'birth_weight_kg' => '3.0'] // Should not be created for 'other'
            ]
        ]);

        $admission = Admission::where('ip_no', 'IP-SURG-7711')->first();
        $this->assertNotNull($admission);
        $this->assertCount(0, $admission->newborns);
    }
}

