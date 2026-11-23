<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalWorkflowRefinementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'Dr. Ambika',
            'email' => 'ambika@guruhospital.local',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'department' => 'Obstetrics & Gynaecology',
            'doctor_reg_no' => 'TNMC-45920',
            'is_active' => true,
        ]);
    }

    private function createAdmission(array $attributes = []): Admission
    {
        return Admission::create(array_merge([
            'created_by' => $this->user->id,
            'patient_name' => 'KAVITHA',
            'uhid' => 'GH2026/099',
            'ip_no' => 'IP/2026/099',
            'age' => '29',
            'sex' => 'Female',
            'contact' => '9876543210',
            'address' => '45 Main Street, Dharmapuri',
            'admission_at' => now(),
            'department' => 'Obstetrics & Gynaecology',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'diagnosis' => 'Primigravida at term',
            'status' => 'draft',
        ], $attributes));
    }

    public function test_attender_name_and_relation_can_be_stored_and_rendered_with_rch_id(): void
    {
        $response = $this->actingAs($this->user)->post(route('admissions.store'), [
            'patient_name' => 'DEVI',
            'uhid' => 'GH2026/001',
            'ip_no' => 'IP/2026/001',
            'age' => '28',
            'sex' => 'Female',
            'contact' => '9876543210',
            'attender_name' => 'muthu kumar',
            'attender_relation' => 'Husband',
            'address' => '12 Gandhi Road, Dharmapuri',
            'picme_no' => '331024567890',
            'admission_at' => now()->format('Y-m-d\TH:i'),
            'department' => 'Obstetrics & Gynaecology',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'diagnosis' => 'Primigravida at term with safe confinement',
            'next_step' => 'stay',
        ]);

        $response->assertSessionHasNoErrors();
        $admission = Admission::where('uhid', 'GH2026/001')->firstOrFail();

        // Normalizer should uppercase attender_name
        $this->assertEquals('MUTHU KUMAR', $admission->attender_name);
        $this->assertEquals('Husband', $admission->attender_relation);
        $this->assertEquals('331024567890', $admission->picme_no);

        // Check PDF rendering of Attender and RCH ID
        $pdfHtml = view('pdf.discharge-summary-a4', ['admission' => $admission])->render();
        $this->assertStringContainsString('MUTHU KUMAR', $pdfHtml);
        $this->assertStringContainsString('Husband', $pdfHtml);
        $this->assertStringContainsString('RCH ID: 331024567890', $pdfHtml);
        $this->assertStringNotContainsString('PICME:', $pdfHtml);
    }

    public function test_stage_2_clinical_assessment_updates_and_redirects(): void
    {
        $admission = $this->createAdmission([
            'patient_name' => 'SARANYA',
        ]);

        $getRes = $this->actingAs($this->user)->get(route('admissions.clinical', $admission));
        $getRes->assertOk();

        $postRes = $this->actingAs($this->user)->put(route('admissions.clinical.update', $admission), [
            'clinical_history' => "G2P1L1 / EDD 15-11-2026 / Safe confinement\nC/O pain abdomen for 3 hours",
            'examination' => "PR: 82/min | BP: 110/70 mmHg\nP/A: Term size, cephalic, FHS: 142 bpm",
            'next_step' => 'test-reports',
        ]);

        $postRes->assertRedirect(route('admissions.test-reports', $admission));
        $admission->refresh();
        $this->assertStringContainsString('Safe confinement', $admission->clinical_history);
        $this->assertStringContainsString('110/70 mmHg', $admission->examination);
    }

    public function test_stage_3_vitals_persistence(): void
    {
        $admission = $this->createAdmission();

        $res = $this->actingAs($this->user)->put(route('admissions.test-reports.update', $admission), [
            'vital_bp' => '120/80 mmHg',
            'vital_pulse' => '84 bpm',
            'vital_spo2' => '99%',
            'investigations' => "Hb: 13.0 g/dL\nBlood Group: O positive\nBlood Urea: 29.8 mg/dL\nCreatinine: 0.53 mg/dL",
            'next_step' => 'stay',
        ]);

        $res->assertSessionHasNoErrors();
        $admission->refresh();

        $this->assertEquals('120/80 mmHg', $admission->vital_bp);
        $this->assertEquals('84 bpm', $admission->vital_pulse);
        $this->assertEquals('99%', $admission->vital_spo2);
    }

    public function test_investigations_5_card_grid_and_uppercase_blood_group(): void
    {
        $admission = $this->createAdmission([
            'investigations' => "HB: 13.0 g/dL\nBlood Group: o positive\nBlood Urea: 29.8\nCreatinine: 0.53\nUric Acid: 6.63\nRBS: 117.4\nHIV: Negative\nHCV: Negative\nVDRL: Negative\nTSH: 2.3 mIU/L",
        ]);

        $invs = $admission->formatted_investigations;
        $bgItem = collect($invs)->firstWhere('label', 'BLOOD GROUP');
        $this->assertNotNull($bgItem);
        $this->assertEquals('O POSITIVE', $bgItem['value']);

        $pdfHtml = view('pdf.discharge-summary-a4', ['admission' => $admission])->render();

        // Must use 5-card grid container
        $this->assertStringContainsString('a4-inv-grid-5', $pdfHtml);
        $this->assertStringContainsString('a4-inv-card', $pdfHtml);
        $this->assertStringContainsString('O POSITIVE', $pdfHtml);
        $this->assertStringContainsString('13.0', $pdfHtml);
        $this->assertStringContainsString('Ref: 12.0 - 16.0', $pdfHtml);
        $this->assertStringContainsString('Ref: Compatible', $pdfHtml);
    }

    public function test_stent_removal_date_and_bilingual_notice_rendered(): void
    {
        $stentDate = Carbon::create(2026, 12, 15);

        $admission = $this->createAdmission([
            'stent_removal_date' => $stentDate,
            'procedure_name' => 'Cystoscopy + Right URSL + DJ Stenting',
        ]);

        $this->assertTrue(\App\Services\DischargeSummaryFormatter::hasStent($admission));

        $pdfHtml = view('pdf.discharge-summary-a4', ['admission' => $admission])->render();

        $this->assertStringContainsString('a4-stent-card', $pdfHtml);
        $this->assertStringContainsString('STENT REMOVAL NOTICE', $pdfHtml);
        $this->assertStringContainsString('<strong>15/12/2026</strong>', $pdfHtml);
        $this->assertStringContainsString('ஸ்டெண்ட்', $pdfHtml);
    }

    public function test_condition_at_discharge_vitals_diet_activity_and_suture_removal_date(): void
    {
        $sutureDate = Carbon::create(2026, 11, 27);

        $admission = $this->createAdmission([
            'surgery_type' => 'delivery',
            'discharge_bp' => '120/80',
            'discharge_pulse' => '84',
            'discharge_spo2' => '99',
            'discharge_condition' => 'General condition: Good | Local Exam / P/A: Soft, non-tender',
            'post_surgery_diet' => 'Normal Soft Diet',
            'post_surgery_activity' => 'Avoid heavy weight lifting for 3 weeks',
            'suture_removal_date' => $sutureDate,
            'follow_up' => 'Review after one week For Suture Removal',
        ]);

        $pdfHtml = view('pdf.discharge-summary-a4', ['admission' => $admission])->render();

        // Vitals
        $this->assertStringContainsString('120/80 mmHg', $pdfHtml);
        $this->assertStringContainsString('84 b/min', $pdfHtml);
        $this->assertStringContainsString('99%', $pdfHtml);

        // Diet & Activity
        $this->assertStringContainsString('Diet:', $pdfHtml);
        $this->assertStringContainsString('Normal Soft Diet', $pdfHtml);
        $this->assertStringContainsString('Activity:', $pdfHtml);
        $this->assertStringContainsString('Avoid heavy weight lifting for 3 weeks', $pdfHtml);

        // Suture removal review date bold
        $this->assertStringContainsString('Review after one week For Suture Removal', $pdfHtml);
        $this->assertStringContainsString('27/11/2026', $pdfHtml);
        $this->assertStringContainsString('a4-val-bold', $pdfHtml);

        // Doctor qualification and stamp impression present
        $this->assertStringContainsString('a4-sig-right', $pdfHtml);
        // "Verified clinically" must NOT be present
        $this->assertStringNotContainsString('Verified clinically', $pdfHtml);
    }

    public function test_null_or_empty_values_are_strictly_omitted(): void
    {
        $admission = $this->createAdmission([
            'payment_mode' => null, // empty payment mode
            'investigations' => null, // empty investigations
            'procedure_name' => null,
            'findings' => null,
            'procedure_note' => null,
            'postoperative_orders' => null,
            'discharge_condition' => null,
            'discharge_bp' => null,
            'discharge_pulse' => null,
            'discharge_spo2' => null,
            'follow_up' => null,
            'post_surgery_diet' => null,
            'post_surgery_activity' => null,
            'suture_removal_date' => null,
            'stent_removal_date' => null,
        ]);

        $pdfHtml = view('pdf.discharge-summary-a4', ['admission' => $admission])->render();

        // Payment mode should not be rendered
        $this->assertStringNotContainsString('Payment Mode</span>', $pdfHtml);

        // Investigations block should not be rendered
        $this->assertStringNotContainsString('<div class="a4-inv-grid-5">', $pdfHtml);

        // Stent card should not be rendered
        $this->assertStringNotContainsString('<div class="a4-stent-card">', $pdfHtml);
    }
}
