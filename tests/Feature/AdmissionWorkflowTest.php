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
        $response->assertRedirect(route('admissions.surgery', $admission));
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

    public function test_male_patient_cannot_use_delivery_surgery_workflow(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Ramesh Kumar',
            'uhid' => 'GH-2026-9901',
            'ip_no' => 'IP-MALE-DEL-01',
            'age' => '40',
            'sex' => 'Male',
            'admission_at' => '2026-10-21 07:00:00',
            'discharge_at' => '2026-10-23 12:00:00',
            'diagnosis' => 'Inguinal Hernia',
            'format' => 'general',
            'surgery_type' => 'delivery',
        ]);

        $response->assertSessionHasErrors(['surgery_type']);
        $this->assertDatabaseMissing('admissions', ['ip_no' => 'IP-MALE-DEL-01']);
    }

    public function test_female_patient_can_use_delivery_surgery_workflow(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Kavitha S',
            'uhid' => 'GH-2026-9902',
            'ip_no' => 'IP-FEM-DEL-01',
            'age' => '27',
            'sex' => 'Female',
            'admission_at' => '2026-10-21 07:00:00',
            'discharge_at' => '2026-10-23 12:00:00',
            'diagnosis' => 'Full Term Normal Delivery',
            'format' => 'general',
            'surgery_type' => 'delivery',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('admissions', [
            'ip_no' => 'IP-FEM-DEL-01',
            'surgery_type' => 'delivery',
            'sex' => 'Female',
        ]);
    }

    public function test_nurse_can_access_and_update_surgery_stage(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Kavitha S',
            'uhid' => 'GH-STAGE-01',
            'ip_no' => 'IP-STAGE-01',
            'age' => '27',
            'sex' => 'Female',
            'admission_at' => now(),
            'diagnosis' => 'Full Term Normal Delivery',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $getResponse = $this->actingAs($this->nurse)->get(route('admissions.surgery', $admission));
        $getResponse->assertOk();

        $updateResponse = $this->actingAs($this->nurse)->put(route('admissions.surgery.update', $admission), [
            'procedure_name' => 'Normal Delivery with Episiotomy',
            'surgeon' => 'Dr. Ambika MS OG',
            'findings' => 'Live female infant delivered spontaneously',
            'newborns' => [
                [
                    'sex' => 'Female',
                    'birth_weight_kg' => '2.95',
                    'apgar_1_min' => 9,
                    'apgar_5_min' => 10,
                ],
            ],
            'next_step' => 'test-reports',
        ]);

        $updateResponse->assertRedirect(route('admissions.test-reports', $admission));
        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'procedure_name' => 'Normal Delivery with Episiotomy',
        ]);
        $this->assertCount(1, $admission->fresh()->newborns);
    }

    public function test_nurse_can_access_and_update_test_reports_stage(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Prakash R',
            'uhid' => 'GH-STAGE-02',
            'ip_no' => 'IP-STAGE-02',
            'age' => '35',
            'sex' => 'Male',
            'admission_at' => now(),
            'diagnosis' => 'Renal Calculus',
            'format' => 'general',
            'surgery_type' => 'other',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $getResponse = $this->actingAs($this->nurse)->get(route('admissions.test-reports', $admission));
        $getResponse->assertOk();

        $updateResponse = $this->actingAs($this->nurse)->put(route('admissions.test-reports.update', $admission), [
            'investigations' => "Hb: 14.2 g/dL\nUrea: 24 mg/dL\nCreatinine: 0.9 mg/dL\nUSG: 5mm right lower ureteric calculus",
            'next_step' => 'discharge',
        ]);

        $updateResponse->assertRedirect(route('admissions.discharge', $admission));
        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
        ]);
        $this->assertStringContainsString('5mm right lower ureteric calculus', $admission->fresh()->investigations);
    }

    public function test_nurse_can_access_and_update_discharge_stage(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Prakash R',
            'uhid' => 'GH-STAGE-03',
            'ip_no' => 'IP-STAGE-03',
            'age' => '35',
            'sex' => 'Male',
            'admission_at' => now()->subDays(2),
            'diagnosis' => 'Renal Calculus',
            'format' => 'general',
            'surgery_type' => 'other',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $getResponse = $this->actingAs($this->nurse)->get(route('admissions.discharge', $admission));
        $getResponse->assertOk();

        $updateResponse = $this->actingAs($this->nurse)->put(route('admissions.discharge.update', $admission), [
            'discharge_at' => now()->toDateTimeString(),
            'discharge_condition' => 'Stable, asymptomatic, vitals normal',
            'discharge_advice' => 'Plenty of oral fluids, avoid oxalate-rich food',
            'medication_orders' => 'Tab. Silodosin 8mg HS x 5 days',
            'next_step' => 'preview',
        ]);

        $updateResponse->assertRedirect(route('documents.preview', $admission));
        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'discharge_condition' => 'Stable, asymptomatic, vitals normal',
        ]);
    }

    public function test_delivery_admission_saves_and_persists_picme_number(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Revathi Murugan',
            'uhid' => 'GH-2026-PICME-01',
            'ip_no' => 'IP-PICME-01',
            'picme_no' => '331024889912',
            'age' => '24',
            'sex' => 'Female',
            'admission_at' => '2026-10-22 06:00:00',
            'diagnosis' => 'Primigravida at 39 weeks in Labour',
            'format' => 'general',
            'surgery_type' => 'delivery',
        ]);

        $this->assertDatabaseHas('admissions', [
            'ip_no' => 'IP-PICME-01',
            'picme_no' => '331024889912',
            'surgery_type' => 'delivery',
        ]);

        $admission = Admission::where('ip_no', 'IP-PICME-01')->first();
        $this->assertEquals('331024889912', $admission->picme_no);

        // Update in surgery stage
        $this->actingAs($this->nurse)->put(route('admissions.surgery.update', $admission), [
            'picme_no' => '331024889999',
            'procedure_name' => 'Normal Vaginal Delivery with Episiotomy',
        ]);

        $this->assertEquals('331024889999', $admission->fresh()->picme_no);
    }
}

