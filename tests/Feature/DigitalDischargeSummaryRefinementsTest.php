<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\ClinicalStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalDischargeSummaryRefinementsTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::create([
            'name' => 'Dr. Ambika',
            'email' => 'ambika@guruhospital.local',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'department' => 'Surgery',
            'doctor_reg_no' => 'TNMC-45920',
        ]);
    }

    public function test_digital_a4_typography_increases_font_size_except_patient_name(): void
    {
        $html = view('pdf.partials.discharge-summary-a4._styles')->render();

        // Base font size +2px (~1.5pt)
        $this->assertStringContainsString('font-size: 10.3pt;', $html);
        $this->assertStringContainsString('.a4-section-hdr {', $html);
        $this->assertStringContainsString('font-size: 11pt;', $html);
        $this->assertStringContainsString('.a4-lbl {', $html);
        $this->assertStringContainsString('font-size: 8pt;', $html);

        // Patient Name MUST stay strictly at 12pt
        $this->assertMatchesRegularExpression('/\.a4-patient-name\s*\{[^}]*font-size:\s*12pt;/s', $html);
        $this->assertDoesNotMatchRegularExpression('/\.a4-patient-name\s*\{[^}]*font-size:\s*1[3-9]pt;/s', $html);

        // Operative procedure note justified
        $this->assertStringContainsString('.a4-procedure-note {', $html);
        $this->assertStringContainsString('text-align: justify;', $html);

        // Findings list and postop table styles
        $this->assertStringContainsString('.a4-findings-list', $html);
        $this->assertStringContainsString('.a4-postop-table', $html);
    }

    public function test_admission_details_procedure_date_is_bold_and_consultant_includes_degree(): void
    {
        ClinicalStaff::create([
            'name' => 'Dr. N. Jayaprakash',
            'category' => 'consultant_doctor',
            'roles' => ['consultant_doctor', 'surgeon'],
            'qualification' => 'M.S (Gen Surg), M.Ch (Uro)',
            'doctor_reg_no' => 'TNMC-38912',
            'is_active' => true,
        ]);

        $admission = Admission::create([
            'patient_name' => 'SABARINATHAN',
            'uhid' => '20261234',
            'ip_no' => '2026/1234',
            'age' => '36',
            'sex' => 'Male',
            'admission_at' => '2026-11-09 22:54:00',
            'procedure_at' => '2026-11-10 12:56:00',
            'discharge_at' => '2026-11-10 16:57:00',
            'consultant' => 'N.JAYAPRAKASH',
            'department' => 'Urology',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'Urolithiasis',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        // Accessor automatically appends qualification
        $this->assertEquals('N.JAYAPRAKASH, M.S (Gen Surg), M.Ch (Uro)', $admission->consultant_with_degree);

        $html = view('pdf.partials.discharge-summary-a4._page1', ['admission' => $admission])->render();

        // Date of Procedure in bold
        $this->assertMatchesRegularExpression('/Date of Procedure \/ Surgery<\/span>\s*<span class="a4-val a4-val-bold">\s*10\/11\/2026, 12:56 PM/s', $html);

        // Consultant with degrees
        $this->assertStringContainsString('N.JAYAPRAKASH, M.S (Gen Surg), M.Ch (Uro)', $html);
    }

    public function test_digital_discharge_summary_removes_medical_history_grid_and_treatment_provided(): void
    {
        $admission = Admission::create([
            'patient_name' => 'SABARINATHAN',
            'uhid' => '20261234',
            'ip_no' => '2026/1234',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'GENERAL SURGICAL ASSESSMENT',
            'clinical_history' => 'C/O Flank pain for 3 days. Dysuria present.',
            'examination' => 'P/A: Soft, non-tender, no organomegaly',
            'treatment_course' => "Treated with IV Fluids\nInj-Pipzo 4.5g BD",
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $html = view('pdf.partials.discharge-summary-a4._page1', ['admission' => $admission])->render();

        // Final diagnosis and clinical symptoms remain
        $this->assertStringContainsString('Final Diagnosis', $html);
        $this->assertStringContainsString('Presenting Complaints', $html);
        $this->assertStringContainsString('Clinical Examination', $html);

        // Clinical history grid is rendered under Diagnosis & Clinical Summary
        $this->assertStringContainsString('DM / HT / ASTHMA / TB', $html);
        $this->assertStringContainsString('OTHER MEDICAL', $html);
        $this->assertStringContainsString('PAST SURGICAL HISTORY', $html);
        $this->assertStringContainsString('ALLERGIES / PRIOR MEDS', $html);

        // Treatment Provided / Course in Hospital must NOT be rendered
        $this->assertStringNotContainsString('Treatment Provided', $html);
        $this->assertStringNotContainsString('Course in Hospital', $html);
    }

    public function test_investigations_renders_unit_after_result_value(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Test Patient',
            'uhid' => 'TP001',
            'ip_no' => 'IP001',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'Calculus',
            'investigations' => "HB: 12.9\nUrea: 32\nCreatinine: 0.8\nBlood Group: A positive\nHIV: Negative",
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $html = view('pdf.partials.discharge-summary-a4._page1', ['admission' => $admission])->render();

        // 5-card grid structure
        $this->assertStringContainsString('a4-inv-grid-5', $html);
        $this->assertStringContainsString('a4-inv-card', $html);
        $this->assertStringContainsString('HB', $html);
        $this->assertStringContainsString('(g/dL)', $html);
        $this->assertStringContainsString('12.9', $html);

        // Normal range below
        $this->assertStringContainsString('Ref: 12.0 - 16.0', $html);
    }

    public function test_page2_pediatrician_findings_list_justified_note_postop_table_and_instructions(): void
    {
        // 1. Adult surgery without pediatrician
        $adultAdmission = Admission::create([
            'patient_name' => 'Adult Surgical Patient',
            'uhid' => 'AD001',
            'ip_no' => 'IP-AD001',
            'format' => 'general',
            'surgery_type' => 'other',
            'procedure_name' => 'URSL & Stenting',
            'procedure_done' => 'URSL & Stenting',
            'surgeon' => 'Dr. N. Jayaprakash',
            'anaesthetist' => 'Dr. Karthick',
            'findings' => "1. Right ureteric calculus 8mm\n2. Mucosal edema\n3. Clear efflux",
            'procedure_note' => "Under SA, cystoscopy done. Ureteric orifice identified. Calculus fragmented using lithotripsy. Stent placed.",
            'postoperative_orders' => "1. IV RL 1 pint @ 75ml/hr | IV Infusion\n2. Inj. Tramadol 50mg | IV Stat\n3. NPO for 6 hours",
            'discharge_advice' => "Implant: Right DJ Stent\nDiet: Normal\nExercise: Normal activity\nEmergency Contact: 04342-262266\nReview: After 7 days",
            'diagnosis' => 'Urolithiasis',
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $htmlAdult = view('pdf.partials.discharge-summary-a4._page2', ['admission' => $adultAdmission])->render();

        // Pediatrician is NOT rendered for adult non-pediatric surgery
        $this->assertStringNotContainsString('<span class="a4-lbl">Pediatrician</span>', $htmlAdult);

        // Findings as bulleted list
        $this->assertStringContainsString('class="a4-findings-list"', $htmlAdult);
        $this->assertStringContainsString('<li>Right ureteric calculus 8mm</li>', $htmlAdult);

        // Procedure note justified
        $this->assertStringContainsString('a4-procedure-note', $htmlAdult);

        // Immediate Postoperative Orders in structured table and rows
        $this->assertStringContainsString('a4-postop-table', $htmlAdult);
        $this->assertStringContainsString('IV RL 1 pint @ 75ml/hr', $htmlAdult);
        $this->assertStringContainsString('IV Infusion', $htmlAdult);

        // Discharge instructions: Implant/Diet/Activity is first
        $implantPos = strpos($htmlAdult, '<strong>Implant:</strong>');
        $advicePos = strpos($htmlAdult, '&bull;');
        $this->assertNotFalse($implantPos);
        if ($advicePos !== false) {
            $this->assertLessThan($advicePos, $implantPos);
        }

        // Follow-up review is bold and inline
        $this->assertStringContainsString('Follow-up Review</span>', $htmlAdult);
        $this->assertMatchesRegularExpression('/Follow-up Review<\/span>\s*<div class="a4-val a4-val-bold">\s*After 7 days/s', $htmlAdult);
        $this->assertStringNotContainsString('display: flex; justify-content: space-between;', $htmlAdult);

        // 2. Delivery case: Pediatrician is displayed
        $deliveryAdmission = Admission::create([
            'patient_name' => 'Mother Patient',
            'uhid' => 'DEL001',
            'ip_no' => 'IP-DEL001',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'procedure_name' => 'Emergency LSCS',
            'surgeon' => 'Dr. Ambika',
            'pediatrician_name' => 'Dr. S. Ramesh',
            'diagnosis' => 'Full Term Pregnancy with Foetal Distress',
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $htmlDelivery = view('pdf.partials.discharge-summary-a4._page2', ['admission' => $deliveryAdmission])->render();
        $this->assertStringContainsString('<span class="a4-lbl">Pediatrician</span>', $htmlDelivery);
        $this->assertStringContainsStringIgnoringCase('Dr. S. Ramesh', $htmlDelivery);
    }

    public function test_procedure_performed_in_digital_discharge_summary_matches_procedure_done(): void
    {
        // 1. Procedure Done takes priority over procedure_name when both exist
        $admission = Admission::create([
            'patient_name' => 'MR.BALAMURUGAN',
            'uhid' => '2023/4975',
            'ip_no' => '2026/862',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'Left lower ureteric calculus.',
            'procedure_name' => 'Left lower ureteric calculus',
            'procedure_done' => 'LEFT URSL & DJ STENTING',
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $this->assertEquals('LEFT URSL & DJ STENTING', \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($admission));

        $html = view('pdf.partials.discharge-summary-a4._page2', ['admission' => $admission])->render();
        $this->assertStringContainsString('Procedure Performed</span>', $html);
        $this->assertStringContainsString('LEFT URSL &amp; DJ STENTING', $html);
        $this->assertStringNotContainsString('Left lower ureteric calculus</span>', $html);

        // 2. Twin delivery renders actual procedure rather than hardcoded stub
        $twinAdmission = Admission::create([
            'patient_name' => 'Twin Mother',
            'uhid' => 'TM001',
            'ip_no' => 'IP-TM001',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'procedure_name' => 'Bilateral Twin LSCS',
            'procedure_done' => 'Emergency LSCS for Twin Gestation',
            'diagnosis' => 'Twin Pregnancy at 37 Weeks',
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);
        $twinAdmission->newborns()->create(['sex' => 'Male', 'birth_weight_kg' => 2.5]);
        $twinAdmission->newborns()->create(['sex' => 'Female', 'birth_weight_kg' => 2.4]);
        $twinAdmission->load('newborns');

        $this->assertEquals('Emergency LSCS for Twin Gestation', \App\Services\DischargeSummaryFormatter::formatSurgeryPerformed($twinAdmission));
        $htmlTwin = view('pdf.partials.discharge-summary-a4._page2', ['admission' => $twinAdmission])->render();
        $this->assertStringContainsString('Emergency LSCS for Twin Gestation', $htmlTwin);
        $this->assertStringNotContainsString('Twin A: Labour natural with episiotomy | Twin B: Emergency LSCS', $htmlTwin);
    }

    public function test_page2_signature_removes_verified_clinically_and_includes_doctor_degrees_and_stamp(): void
    {
        // 1. Dr. Ambika delivery admission
        $ambikaAdmission = Admission::create([
            'patient_name' => 'MRS. MANJULA',
            'uhid' => '2026/4388',
            'ip_no' => '2026/908',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'procedure_name' => 'Emergency LSCS',
            'procedure_done' => 'Emergency LSCS with Pfannenstiel Incision',
            'consultant' => 'DR.K.AMBIKA',
            'department' => 'OBSTETRICS & GYNAECOLOGIST',
            'diagnosis' => 'Full Term Primigravida in Labour',
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $htmlAmbika = view('pdf.partials.discharge-summary-a4._page2', ['admission' => $ambikaAdmission])->render();

        // "Verified clinically" MUST NOT be present
        $this->assertStringNotContainsString('Verified clinically', $htmlAmbika);

        // Doctor Name MUST include qualification / degrees in signature line
        $this->assertStringContainsString('DR.K.AMBIKA, M.S (O&amp;G)', $htmlAmbika);

        // Stamp Pad Impression Box MUST be present with authentic seal lines
        $this->assertStringContainsString('a4-stamp-box', $htmlAmbika);
        $this->assertStringContainsString('DR.K.AMBIKA, MS.,(O&amp;G)', $htmlAmbika);
        $this->assertStringContainsString('Reg.No:80822', $htmlAmbika);
        $this->assertStringContainsString('Consultant Gynaecologist', $htmlAmbika);
        $this->assertStringContainsString('Infertility Specialist', $htmlAmbika);
        $this->assertStringContainsString('Dharmapuri-636701', $htmlAmbika);

        // 2. Dr. Jayaprakash urology surgery admission
        $jayaprakashAdmission = Admission::create([
            'patient_name' => 'MR. BALAMURUGAN',
            'uhid' => '2023/4975',
            'ip_no' => '2026/862',
            'format' => 'general',
            'surgery_type' => 'other',
            'procedure_name' => 'Left URSL & DJ Stenting',
            'procedure_done' => 'LEFT URSL & DJ STENTING',
            'consultant' => 'DR.N.JAYAPRAKASH',
            'department' => 'UROLOGY',
            'diagnosis' => 'Left lower ureteric calculus',
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $htmlJp = view('pdf.partials.discharge-summary-a4._page2', ['admission' => $jayaprakashAdmission])->render();

        $this->assertStringNotContainsString('Verified clinically', $htmlJp);
        $this->assertStringContainsString('DR.N.JAYAPRAKASH, M.S (Gen Surg), M.Ch (Uro)', $htmlJp);
        $this->assertStringContainsString('a4-stamp-box', $htmlJp);
        $this->assertStringContainsString('DR.N.JAYAPRAKASH, MS.,MCh (URO)', $htmlJp);
        $this->assertStringContainsString('Reg.No:74950', $htmlJp);
        $this->assertStringContainsString('Consultant Urologist &amp; Surgeon', $htmlJp);
        $this->assertStringContainsString('Dharmapuri-636701', $htmlJp);
    }

    public function test_page2_follow_up_review_moves_date_adjacent_and_bolds_text(): void
    {
        $admission = Admission::create([
            'patient_name' => 'MRS. MANJULA',
            'uhid' => '2026/4388',
            'ip_no' => '2026/908',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'procedure_name' => 'Emergency LSCS',
            'procedure_done' => 'Emergency LSCS done under SA',
            'consultant' => 'DR.K.AMBIKA',
            'diagnosis' => 'Full Term Primigravida in Labour',
            'discharge_advice' => "Review after one week For Suture Removal (27/11/2026)",
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $html = view('pdf.partials.discharge-summary-a4._page2', ['admission' => $admission])->render();

        // Must be in bold
        $this->assertStringContainsString('class="a4-val a4-val-bold"', $html);
        $this->assertStringContainsString('Review after one week For Suture Removal', $html);
        $this->assertStringContainsString('27/11/2026', $html);

        // Date must NOT be pushed across the page with space-between
        $this->assertStringNotContainsString('display: flex; justify-content: space-between;', $html);

        // Value must be formatted inline together
        $this->assertMatchesRegularExpression('/<div class="a4-val a4-val-bold">\s*Review after one week For Suture Removal\s*[:(]?\s*27\/11\/2026/s', $html);
    }
}
