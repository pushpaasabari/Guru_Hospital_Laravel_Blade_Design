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

    public function test_diagnosis_and_clinical_summary_orders_medical_history_first_with_default_nil(): void
    {
        $admission = Admission::create([
            'patient_name' => 'SABARINATHAN',
            'uhid' => '20261234',
            'ip_no' => '2026/1234',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'GENERAL SURGICAL ASSESSMENT',
            'clinical_history' => 'C/O Flank pain for 3 days. Dysuria present.',
            'admission_at' => now(),
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'status' => 'approved',
        ]);

        $html = view('pdf.partials.discharge-summary-a4._page1', ['admission' => $admission])->render();

        $diagPos = strpos($html, 'Final Diagnosis');
        $histPos = strpos($html, 'DM / HT / Asthma / TB');
        $complaintsPos = strpos($html, 'Presenting Complaints');
        $examPos = strpos($html, 'Clinical Examination');

        $this->assertNotFalse($diagPos);
        $this->assertNotFalse($histPos);
        $this->assertNotFalse($complaintsPos);
        $this->assertNotFalse($examPos);

        // History grid comes FIRST directly after Final Diagnosis
        $this->assertLessThan($histPos, $diagPos);
        $this->assertLessThan($complaintsPos, $histPos);
        $this->assertLessThan($examPos, $complaintsPos);

        // Default Nil values
        $this->assertStringContainsString('DM: Nil | HT: Nil | Asthma: Nil | TB: Nil', $html);
        $this->assertStringContainsString('Other Medical</span>', $html);
        $this->assertStringContainsString('Past Surgical History</span>', $html);
        $this->assertStringContainsString('Allergies / Prior Meds</span>', $html);
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

        // Label must not contain (g/dL)
        $this->assertStringNotContainsString('HB <span style="font-weight: 400; text-transform: none; color: #64748b; font-size: 5.6pt;">(g/dL)</span>', $html);
        $this->assertStringContainsString('<span class="a4-lbl">HB</span>', $html);

        // Value followed by unit
        $this->assertMatchesRegularExpression('/12\.9\s*<span class="a4-inv-unit">g\/dL<\/span>/', $html);

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
}
