<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
            'patient_name' => 'MEENA KUMAR',
            'consultant' => 'DR. KARTHIK MS',
            'surgeon' => 'DR. KARTHIK MS',
            'anaesthetist' => 'DR. RAMESH DA',
            'ip_no' => 'IP-8891',
            'status' => 'draft',
            'format' => 'general',
            'surgery_type' => 'other',
        ]);

        $admission = Admission::where('ip_no', 'IP-8891')->first();
        $response->assertRedirect(route('admissions.clinical', $admission));
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
            'next_step' => 'discharge',
        ]);

        $updateResponse->assertRedirect(route('admissions.discharge', $admission));
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
            'next_step' => 'surgery',
        ]);

        $updateResponse->assertRedirect(route('admissions.surgery', $admission));
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
            'status' => 'approved',
        ]);
    }

    public function test_completing_discharge_step_directly_initiates_pdf_download_without_approval(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Kavitha S',
            'uhid' => 'GH-STAGE-04-AUTO',
            'ip_no' => 'IP-STAGE-04-AUTO',
            'admission_at' => now()->subDays(2),
            'diagnosis' => 'Normal Delivery',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $updateResponse = $this->actingAs($this->nurse)->put(route('admissions.discharge.update', $admission), [
            'discharge_at' => now()->toDateTimeString(),
            'discharge_condition' => 'Good, vitals normal',
            'next_step' => 'download_ds',
        ]);

        $updateResponse->assertRedirect(route('documents.preview', ['admission' => $admission, 'auto_download' => 'discharge-summary']));
        $fresh = $admission->fresh();
        $this->assertEquals('approved', $fresh->status);
        $this->assertEquals($this->nurse->id, $fresh->approved_by);

        // Can immediately download PDF directly without anyone's approval
        $pdfResponse = $this->actingAs($this->nurse)
            ->get(route('documents.pdf', [$admission, 'type' => 'discharge-summary']));
        $pdfResponse->assertOk();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
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

    public function test_admission_saves_three_address_fields_and_auto_syncs_full_address(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Kavitha Selvam',
            'uhid' => 'GH-2026-ADDR-01',
            'ip_no' => 'IP-ADDR-01',
            'address_line1' => 'No. 45, Anna Salai',
            'address_line2' => 'Pennagaram Road',
            'address_line3' => 'Dharmapuri - 636701',
            'picme_no' => '331024998877',
            'age' => '27',
            'sex' => 'Female',
            'admission_at' => '2026-10-23 10:00:00',
            'diagnosis' => 'Full Term Normal Delivery',
            'format' => 'general',
            'surgery_type' => 'delivery',
        ]);

        $this->assertDatabaseHas('admissions', [
            'ip_no' => 'IP-ADDR-01',
            'patient_name' => 'KAVITHA SELVAM',
            'address_line1' => 'NO. 45, ANNA SALAI',
            'address_line2' => 'PENNAGARAM ROAD',
            'address_line3' => 'DHARMAPURI - 636701',
            'address' => 'NO. 45, ANNA SALAI, PENNAGARAM ROAD, DHARMAPURI - 636701',
            'picme_no' => '331024998877',
        ]);

        $admission = Admission::where('ip_no', 'IP-ADDR-01')->first();
        $this->assertEquals('NO. 45, ANNA SALAI', $admission->address_line1);
        $this->assertEquals('PENNAGARAM ROAD', $admission->address_line2);
        $this->assertEquals('DHARMAPURI - 636701', $admission->address_line3);
        $this->assertEquals('NO. 45, ANNA SALAI, PENNAGARAM ROAD, DHARMAPURI - 636701', $admission->address);
    }

    public function test_nurse_can_create_medical_case_workflow_admission(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Ramesh Kannan',
            'uhid' => 'GH-2026-MED-01',
            'ip_no' => 'IP-MED-01',
            'address_line1' => '22 Bharathi Street',
            'age' => '45',
            'sex' => 'Male',
            'admission_at' => '2026-10-24 10:00:00',
            'diagnosis' => 'Acute Febrile Illness / Dengue Fever with Thrombocytopenia',
            'format' => 'general',
            'surgery_type' => 'medical',
        ]);

        $this->assertDatabaseHas('admissions', [
            'ip_no' => 'IP-MED-01',
            'surgery_type' => 'medical',
        ]);

        $admission = Admission::where('ip_no', 'IP-MED-01')->first();
        $this->assertTrue($admission->isMedical());
        $this->assertFalse($admission->isDelivery());
        $this->assertFalse($admission->isSurgical());
    }

    public function test_mrd_file_number_defaults_to_mrd_dash_ip_no(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Kaviarasan S',
            'uhid' => '2026/8899',
            'ip_no' => '2026/7755',
            'address_line1' => '5 Main Street, Dharmapuri',
            'age' => '32',
            'sex' => 'Male',
            'admission_at' => '2026-10-25 10:00:00',
            'diagnosis' => 'Acute Viral Infection',
            'format' => 'general',
            'surgery_type' => 'medical',
        ]);

        $this->assertDatabaseHas('admissions', [
            'uhid' => '2026/8899',
            'ip_no' => '2026/7755',
            'mrd_no' => 'MRD-2026/7755',
        ]);

        $admission = Admission::where('ip_no', '2026/7755')->first();
        $this->assertEquals('MRD-2026/7755', $admission->mrd_no);
        $this->assertEquals('MRD-2026/7755', $admission->resolved_mrd_no);

        $mrdHtml = view('pdf.mrd-form', compact('admission'))->render();
        $this->assertStringContainsString('MRD-2026/7755', $mrdHtml);
        $this->assertStringNotContainsString('MRD-2026/8899', $mrdHtml);
    }

    public function test_immediate_postoperative_orders_persists_row_by_row(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Deepa R',
            'uhid' => 'GH-POSTOP-01',
            'ip_no' => 'IP-POSTOP-01',
            'admission_at' => now()->subDay(),
            'diagnosis' => 'Emergency LSCS',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $orders = "1. NPO for 6 hours\n2. IV Fluids RL @ 100ml/hr\n3. Inj. Tramadol 50mg IV SOS\n4. Monitor vitals q1h x 4h";

        $response = $this->actingAs($this->nurse)->put(route('admissions.surgery.update', $admission), [
            'procedure_at' => now()->toDateTimeString(),
            'procedure_name' => 'Emergency LSCS under SA',
            'postoperative_orders' => $orders,
            'next_step' => 'stay',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'postoperative_orders' => $orders,
        ]);
    }

    public function test_lab_results_and_medications_structured_formatting(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Anitha V',
            'uhid' => 'GH-LAB-MED-01',
            'ip_no' => 'IP-LAB-MED-01',
            'admission_at' => now()->subDays(3),
            'discharge_at' => now(),
            'diagnosis' => 'Twins / LSCS',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
            'investigations' => "Hb : 12.9mgs %\nBG : O positive\nHCV : Negative\nHIV : Negative\nVDRL : Negative\nHBsAG : Negative\nUREA : 9.7g/dl\nCREATINE : 0.86 mg/dl\nURIC ACID : 4.3 mg/dl\nRBS : 100.8 mg/dl",
            'medication_orders' => "* Tab. Cefixime + sulbactum (1-0-1) x 7 Days - After food\n* Tab. Acendol-P (1-0-1) x 7 Days - After food\n* Cap. Cyra-D (1-0-1) x 7 Days - Before food",
        ]);

        // Formatted investigations (Row 1: Blood Group, HB; Row 2: RBS, Urea, Creatinine, Uric Acid; Row 3: Viral)
        $inv = $admission->formatted_investigations;
        $this->assertCount(10, $inv);
        $this->assertEquals('BLOOD GROUP', $inv[0]['label']);
        $this->assertEquals('O POSITIVE', $inv[0]['value']);
        $this->assertEquals('HB', $inv[1]['label']);
        $this->assertEquals('12.9mgs %', $inv[1]['value']);
        $this->assertEquals('g/dL', $inv[1]['unit']);
        $this->assertEquals('12.0 - 16.0', $inv[1]['normal_range']);
        $this->assertEquals('RBS', $inv[2]['label']);
        $this->assertEquals('CREATININE', $inv[4]['label']);
        $this->assertEquals('0.86 mg/dl', $inv[4]['value']);
        $this->assertEquals('mg/dL', $inv[4]['unit']);
        $this->assertEquals('0.6 - 1.2', $inv[4]['normal_range']);

        // Formatted medications 4-column
        $meds = $admission->formatted_medications;
        $this->assertCount(3, $meds);
        $this->assertEquals('Tab. Cefixime + Sulbactam', $meds[0]['medicine']);
        $this->assertEquals('1-0-1', $meds[0]['frequency']);
        $this->assertEquals('7 days', $meds[0]['duration']);
        $this->assertEquals('After food', $meds[0]['timing']);

        // Formatted condition on discharge including P/A with slash
        $cond = \App\Services\DischargeSummaryFormatter::formatConditionOnDischarge(
            "Pt's general condition good. BP: 110/70 mmHg, Pulse: 86 b/min, P/A: Uterus contracted well; no undue bleeding pv"
        );
        $this->assertEquals('Good', $cond['general']);
        $this->assertEquals('110/70 mmHg', $cond['bp']);
        $this->assertEquals('86 b/min', $cond['pulse']);
        $this->assertEquals('Uterus contracted well; no undue bleeding pv', $cond['pa']);
    }

    public function test_patient_with_stent_in_procedure_includes_official_tamil_stent_notice(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Gopalakrishnan K',
            'uhid' => 'GH-STENT-01',
            'ip_no' => 'IP-STENT-01',
            'admission_at' => now()->subDays(2),
            'discharge_at' => now(),
            'diagnosis' => 'Right Lower Ureteric Calculus',
            'format' => 'general',
            'surgery_type' => 'other',
            'procedure_name' => 'Right URSL & DJ Stenting',
            'procedure_note' => 'Stone fragmented. Right DJ stent 5F/26cm placed.',
            'findings' => 'Calculus fragmented completely. DJ stent in situ.',
            'follow_up' => 'Review on 21.08.2026 for stent removal',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $this->assertTrue($admission->has_stent);
        $notice = $admission->stent_removal_notice;
        $this->assertNotNull($notice);
        $this->assertStringContainsString('வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு', $notice);
        $this->assertStringContainsString('21.08.2026', $notice);
        $this->assertStringContainsString('ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்', $notice);
    }

    public function test_stage_2_persists_ecg_and_formats_with_units_and_normal_ranges(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Meenakshi Sundaram',
            'uhid' => 'GH-ECG-01',
            'ip_no' => 'IP-ECG-01',
            'admission_at' => now(),
            'diagnosis' => 'Cholelithiasis',
            'format' => 'general',
            'surgery_type' => 'other',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $response = $this->actingAs($this->nurse)->put(route('admissions.test-reports.update', $admission), [
            'investigations' => "Hb : 13.5 g/dL\nBG : B positive\nHIV : Negative\nHCV : Negative\nVDRL : Negative\nHBsAG : Negative\nUREA : 22 mg/dL\nCREATININE : 0.8 mg/dL\nURIC ACID : 4.1 mg/dL\nRBS : 110 mg/dL\nECG : Normal Sinus Rhythm",
            'next_step' => 'surgery',
        ]);

        $response->assertRedirect(route('admissions.surgery', $admission));

        $admission->refresh();
        $this->assertStringContainsString('ECG : Normal Sinus Rhythm', $admission->investigations);

        $inv = $admission->formatted_investigations;
        $this->assertCount(11, $inv);

        $ecgItem = collect($inv)->firstWhere('label', 'ECG');
        $this->assertNotNull($ecgItem);
        $this->assertEquals('Normal Sinus Rhythm', $ecgItem['value']);
        $this->assertEquals('Normal Sinus', $ecgItem['normal_range']);

        $surgInv = $admission->formatted_surgical_investigations;
        $this->assertEquals('Normal Sinus Rhythm', $surgInv['group2']['ECG']);
        $this->assertEquals('Normal Sinus', $surgInv['ranges']['ECG']);
    }

    public function test_full_hospital_lab_requisition_catalog_formats_with_units_and_ranges(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Senthil Kumar',
            'uhid' => 'GH-LAB-01',
            'ip_no' => 'IP-LAB-01',
            'admission_at' => now(),
            'diagnosis' => 'Acute Appendicitis',
            'format' => 'general',
            'surgery_type' => 'other',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $labText = implode("\n", [
            'Hb : 14.1 g/dL',
            'BG : B positive',
            'HIV : Negative',
            'HCV : Negative',
            'VDRL : Negative',
            'HBsAG : Negative',
            'UREA : 24 mg/dL',
            'CREATININE : 0.85 mg/dL',
            'URIC ACID : 4.4 mg/dL',
            'RBS : 118 mg/dL',
            'ECG : Normal Sinus Rhythm',
            'CBC : TC 8400, Platelets 2.8L',
            'ESR : 14 mm/hr',
            'Urine Routine : Albumin Nil, Sugar Nil, Pus 1-2',
            'TSH : 2.4 mIU/L',
            'X-Ray Chest : Clear lung fields, CTR normal',
            'Serum Bilirubin : Total 0.8 mg/dL',
            'Serum Electrolytes : Na 138, K 4.2',
            'Total Cholesterol : 175 mg/dL',
            'CRP : Negative (< 6.0 mg/L)',
        ]);

        $response = $this->actingAs($this->nurse)->put(route('admissions.test-reports.update', $admission), [
            'investigations' => $labText,
            'next_step' => 'stay',
        ]);

        $response->assertRedirect(route('admissions.test-reports', $admission));

        $admission->refresh();
        $formatted = $admission->formatted_investigations;

        // Check ESR
        $esr = collect($formatted)->firstWhere('label', 'ESR');
        $this->assertNotNull($esr);
        $this->assertEquals('mm/hr', $esr['unit']);
        $this->assertEquals('0 - 20', $esr['normal_range']);

        // Check Urine Routine
        $urine = collect($formatted)->firstWhere('label', 'URINE ROUTINE');
        $this->assertNotNull($urine);
        $this->assertStringContainsString('Alb: Nil', $urine['normal_range']);

        // Check TSH
        $tsh = collect($formatted)->firstWhere('label', 'TSH');
        $this->assertNotNull($tsh);
        $this->assertEquals('mIU/L', $tsh['unit']);
        $this->assertEquals('0.4 - 4.2', $tsh['normal_range']);

        // Check Chest X-Ray
        $xray = collect($formatted)->firstWhere('label', 'CHEST X-RAY (PA)');
        $this->assertNotNull($xray);
        $this->assertEquals('Normal Chest', $xray['normal_range']);

        // Check Cholesterol
        $chol = collect($formatted)->firstWhere('label', 'CHOLESTEROL');
        $this->assertNotNull($chol);
        $this->assertEquals('mg/dL', $chol['unit']);
        $this->assertEquals('< 200', $chol['normal_range']);
    }

    public function test_dashboard_in_admission_filter_returns_undischarged_patients_and_preview_includes_digital_summary(): void
    {
        $inAdmission = Admission::create([
            'patient_name' => 'In Admission Patient',
            'uhid' => 'GH-INADM-01',
            'ip_no' => 'IP-INADM-01',
            'admission_at' => now()->subDay(),
            'discharge_at' => null,
            'diagnosis' => 'Observation',
            'format' => 'general',
            'surgery_type' => 'other',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $discharged = Admission::create([
            'patient_name' => 'Discharged Patient',
            'uhid' => 'GH-DISC-01',
            'ip_no' => 'IP-DISC-01',
            'admission_at' => now()->subDays(3),
            'discharge_at' => now()->subDay(),
            'diagnosis' => 'Post Op Recovery',
            'format' => 'general',
            'surgery_type' => 'other',
            'vital_bp' => '120/80 mmHg',
            'investigations' => "UREA : 22\nRBS : 110\nHB : 13.0",
            'status' => 'approved',
            'created_by' => $this->nurse->id,
            'approved_by' => $this->doctor->id,
        ]);

        $this->actingAs($this->nurse)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('counts.in_admission', 1)
            );

        $this->actingAs($this->nurse)
            ->get(route('dashboard', ['status' => 'in_admission']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('filters.status', 'in_admission')
                ->where('admissions.data', fn ($rows) => collect($rows)->pluck('id')->all() === [$inAdmission->id])
            );

        $this->actingAs($this->nurse)
            ->get(route('documents.preview', $discharged))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Pack')
                ->where('digitalSummary.investigation_rows.0.0.label', 'BP')
                ->where('digitalSummary.investigation_rows.1.0.label', 'RBS')
            );
    }
}

