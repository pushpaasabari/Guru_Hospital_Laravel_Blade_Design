<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\DocumentApproval;
use App\Models\Newborn;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DischargeSummaryFormatsTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;

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
    }

    public function test_all_four_formats_render_successfully_in_preview(): void
    {
        $formats = ['general', 'cmchistn', 'private', 'employee'];

        foreach ($formats as $format) {
            $admission = Admission::create([
                'patient_name' => "Patient {$format}",
                'uhid' => "GH-{$format}",
                'ip_no' => "IP-{$format}",
                'admission_at' => now()->subDays(2),
                'discharge_at' => now(),
                'diagnosis' => 'Clinical Test Condition',
                'format' => $format,
                'surgery_type' => 'other',
                'created_by' => $this->doctor->id,
                'status' => 'draft',
            ]);

            $response = $this->actingAs($this->doctor)
                ->get(route('documents.preview', $admission));

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Pack')
                ->where('admission.patient_name', mb_strtoupper("Patient {$format}", 'UTF-8'))
                ->where('admission.ip_no', "IP-{$format}")
                ->where('admission.format', $format)
            );
        }
    }

    public function test_delivery_workflow_displays_newborn_table_in_preview(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Mother Revathi',
            'uhid' => 'GH-DEL-01',
            'ip_no' => 'IP-DEL-01',
            'admission_at' => now()->subDays(3),
            'discharge_at' => now(),
            'diagnosis' => 'Full Term Normal Delivery',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'created_by' => $this->doctor->id,
            'status' => 'draft',
        ]);

        $admission->newborns()->create([
            'sex' => 'Female',
            'birth_at' => now()->subDays(2),
            'birth_weight_kg' => '3.15',
            'apgar_1_min' => 9,
            'apgar_5_min' => 10,
            'neonatal_care' => 'Breastfed within 1 hour',
            'notes' => 'Healthy female baby',
        ]);

        $response = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.surgery_type', 'delivery')
            ->has('admission.newborns', 1)
            ->where('admission.newborns.0.birth_weight_kg', 3.15)
            ->where('admission.newborns.0.notes', 'Healthy female baby')
        );
    }

    public function test_mrs_ashwini_real_hospital_document_renders_and_generates_pdf_successfully(): void
    {
        $this->seedAshwiniCase();

        $admission = Admission::where('ip_no', '2026/582')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('MRS. ASHWINI', $admission->patient_name);
        $this->assertEquals('2022/151', $admission->uhid);
        $this->assertEquals('Emergency LSCS', $admission->procedure_name);
        $this->assertCount(1, $admission->newborns);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'MRS. ASHWINI')
            ->where('admission.ip_no', '2026/582')
            ->where('admission.uhid', '2022/151')
            ->where('admission.procedure_name', 'Emergency LSCS')
            ->where('admission.surgeon', 'DR. AMBIKA M.S (O&G)')
            ->where('admission.anaesthetist', 'DR. M.S KARTHICK')
            ->where('admission.assisted_by', 'MISS SOUDNHARYA')
            ->where('admission.pediatrician_name', 'DR. NITHYAPRIYA (PAEDIATRICIAN)')
            ->where('admission.investigations', fn ($inv) => str_contains($inv, '10.6'))
        );

        // PDF Generation Check
        $pdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $admission));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Guru_Hospital_Discharge_Pack_2026_582.pdf', $pdfResponse->headers->get('Content-Disposition'));
    }

    public function test_mr_ajith_kumar_other_surgery_document_renders_and_generates_pdf_successfully(): void
    {
        $this->seedAjithKumarCase();

        $admission = Admission::where('ip_no', '2026/488')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('MR. AJITH KUMAR', $admission->patient_name);
        $this->assertEquals('2026/2892', $admission->uhid);
        $this->assertEquals('Lap Appendectomy', $admission->procedure_name);
        $this->assertCount(0, $admission->newborns);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'MR. AJITH KUMAR')
            ->where('admission.ip_no', '2026/488')
            ->where('admission.uhid', '2026/2892')
            ->where('admission.procedure_name', 'Lap Appendectomy')
            ->where('admission.surgeon', 'DR. JAYAPRAKASH M.S')
            ->where('admission.anaesthetist', 'DR. S.G. ARUNKUMAR')
            ->where('admission.assisted_by', 'MR. VIJAYKUMAR')
            ->where('admission.anaesthesia_type', 'GA')
            ->where('admission.surgery_type', 'other')
            ->has('admission.newborns', 0)
        );

        // PDF Generation Check
        $pdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $admission));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Guru_Hospital_Discharge_Pack_2026_488.pdf', $pdfResponse->headers->get('Content-Disposition'));
    }

    public function test_mrs_manjupriya_exact_operation_theatre_notes_renders_and_generates_pdf_faithfully(): void
    {
        $this->seedManjupriyaCase();

        $admission = Admission::where('ip_no', '2026/778')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('MRS. MANJUPRIYA', $admission->patient_name);
        $this->assertEquals('2026/2952', $admission->uhid);
        $this->assertEquals('ELECTIVE LSCS', $admission->procedure_name);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'MRS. MANJUPRIYA')
            ->where('admission.ip_no', '2026/778')
            ->where('admission.uhid', '2026/2952')
            ->where('admission.procedure_name', 'ELECTIVE LSCS')
            ->where('admission.surgeon', 'DR.AMBIKA MS(OG)')
            ->where('admission.contact', '9677371431')
            ->where('admission.anaesthetist', 'DR.S.G.ARUN KUMAR')
            ->where('admission.assisted_by', 'MR. VIJAYAKUMAR')
            ->where('admission.anaesthesia_type', 'Spinal')
        );

        // PDF Generation Check
        $pdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $admission));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Guru_Hospital_Discharge_Pack_2026_778.pdf', $pdfResponse->headers->get('Content-Disposition'));
    }

    public function test_mrs_anitha_twin_delivery_renders_and_generates_pdf_successfully(): void
    {
        $this->seedAnithaCase();

        $admission = Admission::where('ip_no', '2026/567')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('MRS. ANITHA', $admission->patient_name);
        $this->assertEquals('2023/3931', $admission->uhid);
        $this->assertEquals('G3P1L1A1 / TWINS / Preterm / PROM', $admission->diagnosis);
        $this->assertCount(2, $admission->newborns);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'MRS. ANITHA')
            ->where('admission.ip_no', '2026/567')
            ->where('admission.uhid', '2023/3931')
            ->where('admission.diagnosis', 'G3P1L1A1 / TWINS / Preterm / PROM')
            ->has('admission.newborns', 2)
            ->where('admission.newborns.0.neonatal_care', fn ($care) => str_contains($care, 'NICU'))
            ->where('admission.newborns.1.birth_weight_kg', 2.25)
        );

        // PDF Generation Check
        $pdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $admission));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Guru_Hospital_Discharge_Pack_2026_567.pdf', $pdfResponse->headers->get('Content-Disposition'));

        // Formatted helper tests
        $this->assertCount(4, $admission->formatted_treatment_course);
        $this->assertEquals('Inj. Pipzo', $admission->formatted_treatment_course[1]['medicine']);
        $this->assertEquals('4.5 g BD IV', $admission->formatted_treatment_course[1]['dose']);
        $this->assertCount(10, $admission->formatted_investigations);
        $this->assertEquals('BLOOD GROUP', $admission->formatted_investigations[0]['label']);
        $this->assertEquals('HB', $admission->formatted_investigations[1]['label']);
        $this->assertEquals('110/70 mmHg', $admission->formatted_condition['bp']);
        $this->assertCount(3, $admission->formatted_medications);
        $this->assertStringContainsString('Cefixime', $admission->formatted_medications[0]['medicine']);
        $this->assertEquals('03/09/2026', $admission->formatted_advice_and_review['review_date']);
        $this->assertCount(2, $admission->formatted_newborns);
        $this->assertEquals('Newborn #1 - Twin A', $admission->formatted_newborns[0]['title']);
        $this->assertEquals('Newborn #2 - Twin B', $admission->formatted_newborns[1]['title']);
    }

    public function test_mr_gopalakrishnan_surgical_discharge_summary_renders_and_generates_pdf_successfully(): void
    {
        $this->seedGopalakrishnanCase();

        $admission = Admission::where('ip_no', '2026/507')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('MR. GOPALAKRISHNAN', $admission->patient_name);
        $this->assertEquals('2026/1247', $admission->uhid);
        $this->assertEquals('Right Lower Ureteric Calculus', $admission->diagnosis);
        $this->assertEquals('other', $admission->surgery_type);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'MR. GOPALAKRISHNAN')
            ->where('admission.ip_no', '2026/507')
            ->where('admission.uhid', '2026/1247')
            ->where('admission.diagnosis', 'Right Lower Ureteric Calculus')
            ->where('admission.surgery_type', 'other')
        );

        // PDF Generation Check (Individual Discharge Summary)
        $pdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', ['admission' => $admission, 'type' => 'discharge_summary']));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));

        // PDF Generation Check (Full Pack)
        $packPdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', ['admission' => $admission]));

        $packPdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $packPdfResponse->headers->get('Content-Type'));

        // Formatted surgical helpers check
        $pastHist = $admission->formatted_surgical_past_history;
        $this->assertEquals('82 b/min', $pastHist['pulse']);
        $this->assertEquals('110/70 mmHg', $pastHist['bp']);

        $inv = $admission->formatted_surgical_investigations;
        $this->assertEquals('15.5 g/dl', $inv['group1']['Hb']);
        $this->assertEquals('Neg', $inv['group2']['HIV']);

        $advice = $admission->formatted_surgical_advice;
        $this->assertEquals('Right DJ Stent', $advice['implant']);
        $this->assertStringContainsString('வயிற்றுக்குள்', $advice['tamil_note']);

        $drugs = $admission->formatted_surgical_drugs;
        $this->assertGreaterThanOrEqual(4, count($drugs));
        $this->assertEquals('T. Niftas', $drugs[0]['drug']);
        $this->assertEquals('1-0-1', $drugs[0]['dosage']);
    }

    private function seedAshwiniCase(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Mrs. Ashwini',
            'uhid' => '2022/151',
            'ip_no' => '2026/582',
            'age' => '26',
            'sex' => 'Female',
            'address' => "W/O Thirukumaran\nOdappatti, Dharmapuri",
            'contact' => '9952517589',
            'admission_at' => '2026-08-29 20:51:00',
            'procedure_at' => '2026-08-29 21:25:00',
            'discharge_at' => '2026-09-01 16:00:00',
            'consultant' => 'Dr. Ambika M.S (O&G)',
            'department' => 'Obstetrics & Gynaecology',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'diagnosis' => 'G3P1L1A1 / Previous LSCS at term',
            'clinical_history' => "G3P1L1A1 / Previous LSCS / EDD 18.09.2026 / FH Good / Admitted For Safe Confinement",
            'examination' => "O/E – No Pallor, No Pedal edema, CVS & RS – NAD\nP/A: Uterus term, Not acting, Head Mobile, FH good, SPT scar (+)",
            'procedure_name' => 'Emergency LSCS',
            'surgeon' => 'Dr. Ambika M.S (O&G)',
            'pediatrician_name' => 'Dr. Nithyapriya (Paediatrician)',
            'anaesthetist' => 'Dr. M.S Karthick',
            'assistant' => 'Miss Soudnharya',
            'assisted_by' => 'Miss Soudnharya',
            'anaesthesia_type' => 'SA',
            'procedure_note' => "Under SA, Abdomen opened in layers by SPT incision. LSCS done to deliver alive Female baby, cried soon after birth. Baby Handed over to pediatrician AMTSL followed. Uterine wound closed in 2 layers. After securing perfect Hemostasis, Abdomen closed in layers. No undue bleeding P/V.",
            'incision' => 'Suprapubic Transverse (SPT) incision',
            'findings' => 'Lower uterine segment thinned out, previous scar intact, alive female baby delivered.',
            'treatment_course' => "Treated with IV Fluids\nInj. Cefbact 1.5g BD IV\nInj. Diclo IM (SOS)\nInj. Metrogyl 100mg TDS IV\nInj. Pan 40mg IV BD",
            'investigations' => "Hb : 10.6 mgs%\nHIV : Negative\nVDRL : Negative\nHBsAG : Negative\nBG : B positive\nECG : Normal\nRBS : 101.6 mg/dl\nUrea : 24.3 mg/dl\nCreatinine : 0.56 mg/dl\nUric Acid : 2.69 mg/dl",
            'discharge_condition' => "Pt’s general condition good\n- BP: 120/80 mmHg / Pulse 88 b/min / SpO2 98%\n- LSCS wound healthy\n- Uterus contracted well. No undue bleeding per Vagina",
            'medication_orders' => "* Tab. Clavam 625 mg (1-1-1) x 7 Days - After food\n* Tab. Alonac-Sp (1-0-1) x 7 Days - After food\n* Cap. Cyra D (1-1-1) x 7 Days - Before food",
            'discharge_advice' => "• Immunisation\n• Exclusive breast feeding for 6 months\n• Review after one week for suture removal (06/09/2026)\n• Post natal exercise",
            'follow_up' => 'Review After one week For Suture Removal (06/09/2026)',
            'mrd_no' => 'MRD-2022/151',
            'payment_mode' => 'Cash / General',
            'handover_name' => 'Staff Nurse Anitha',
            'floor_incharge_name' => 'Nursing Supervisor Mary',
            'mrd_incharge_name' => 'MRD Officer Kumar',
            'status' => 'approved',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'approved_at' => '2026-09-01 15:45:00',
        ]);

        $admission->newborns()->create([
            'sex' => 'Female',
            'birth_at' => '2026-08-29 21:25:00',
            'birth_weight_kg' => '2.80',
            'apgar_1_min' => 7,
            'apgar_5_min' => 8,
            'neonatal_care' => 'Baby kept in O2 warmer for 13 hrs; cried soon after birth.',
            'notes' => 'Alive Female baby on 29.08.2026 at 09.25 PM, handed over to Paediatrician Dr. Nithyapriya.',
        ]);

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $this->doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. Ambika M.S (O&G).',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-09-01 15:45:00',
        ]);
    }

    private function seedAjithKumarCase(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Mr. Ajith kumar',
            'uhid' => '2026/2892',
            'ip_no' => '2026/488',
            'age' => '30',
            'sex' => 'Male',
            'address' => "S/O Madhu\nChinakkampatti,\nDharmapuri(Dt)",
            'contact' => '7604977115',
            'admission_at' => '2026-08-02 23:06:00',
            'procedure_at' => '2026-08-02 12:40:00',
            'discharge_at' => '2026-08-05 15:00:00',
            'consultant' => 'Dr. Jayaprakash M.S',
            'department' => 'General & Laparoscopic Surgery',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'Acute Appendicitis',
            'clinical_history' => '30 Years male admitted with C/O Abdomen pain Since one Weeks.',
            'examination' => 'Vital signs on 02/08/2026 : BP-100/60 mmhg, PR-90 bpm, Spo2-98%',
            'procedure_name' => 'Lap Appendectomy',
            'surgeon' => 'Dr. Jayaprakash M.S',
            'anaesthetist' => 'Dr. S.G. ArunKumar',
            'assistant' => 'Mr. Vijaykumar',
            'assisted_by' => 'Mr. Vijaykumar',
            'anaesthesia_type' => 'GA',
            'procedure_note' => 'Under GA, Supine Position. 10 mm Supra Umbilical / Two 5mm Working Port Placed. Appendix Found Perforated with fecolith noted Appendectomy done, DT-Placed, Port sites closed. No Intraoperative Complications.',
            'incision' => '10 mm Supra Umbilical / Two 5mm Working Ports Placed',
            'findings' => 'Appendix Found Perforated with fecolith noted.',
            'drain' => 'DT-Placed',
            'treatment_course' => "Inj-Tetcef 1.5g bd Iv\nInj-Para infusion (SOS) Iv\nInj-Pan 40 mg IV bd\nInj.Diclo Im sos",
            'investigations' => "Hb : 13.8mgs %   BG- A Positive\nRBS : 116.6 mg/dl\nUrea : 28.4 mg/dl\nCreatinine : 0.51 mg/dl\nUric Acid : 4.65 mg/dl\nHIV : Negative\nVDRL : Negative\nHBsAG : Negative",
            'discharge_condition' => "Pt’s general condition good\n- BP: 110/80mmHg\n- Pulse 134 b/mt/\n- Abdomen Soft",
            'medication_orders' => "* C. Cyra-D (1-0-1) x 5 Days- Before food\n* Tab. Clavam 625 mg (1-0-1) x 5 Days- After food\n* T. Zerodol.P (1-0-1) x 5 Days- After food",
            'discharge_advice' => 'Review after 7days on OPD (09/08/2026)',
            'follow_up' => 'Review after 7days on OPD (09/08/2026)',
            'mrd_no' => 'MRD-2026/2892',
            'payment_mode' => 'Cash / General',
            'handover_name' => 'Staff Nurse Anitha',
            'floor_incharge_name' => 'Nursing Supervisor Mary',
            'mrd_incharge_name' => 'MRD Officer Kumar',
            'status' => 'approved',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'approved_at' => '2026-08-05 14:30:00',
        ]);

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $this->doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. Jayaprakash M.S.',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-08-05 14:30:00',
        ]);
    }

    private function seedManjupriyaCase(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Mrs. MANJUPRIYA',
            'uhid' => '2026/2952',
            'ip_no' => '2026/778',
            'age' => '34',
            'sex' => 'Female',
            'address' => 'W/O KAMALGANESH',
            'contact' => '9677371431',
            'admission_at' => Carbon::parse('2026-10-16 08:30:00'),
            'procedure_at' => Carbon::parse('2026-10-16 13:26:00'),
            'discharge_at' => Carbon::parse('2026-10-19 16:00:00'),
            'consultant' => 'Dr.Ambika MS(OG)',
            'department' => 'Obstetrics & Gynaecology',
            'diagnosis' => 'G2A1/GDM/',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'clinical_history' => '34 Yrs Female, G2A1 with Gestational Diabetes Mellitus admitted for safe confinement.',
            'examination' => 'O/E: Conscious, Oriented, BP: 120/80 mmHg, PR: 84 bpm, Spo2: 99%',
            'investigations' => "Hb : 11.2 mgs%\nRBS : 124 mg/dl\nBlood Group : O Positive\nHIV : Negative\nHBsAg : Negative\nVDRL : Negative",
            'treatment_course' => 'Treated with IV Fluids, Antibiotics, Analgesics and PPI post LSCS.',
            'discharge_condition' => 'Pt general condition good. Afebrile. LSCS wound healthy.',
            'medication_orders' => "*Tab. Clavam 625mg (1-0-1) x 5 days\n*Tab. Zerodol-SP (1-0-1) x 5 days\n*Cap. Pan-D (1-0-0) x 5 days",
            'discharge_advice' => "*Exclusive breast feeding for 6 months\n*Review for suture removal after 7 days",
            'follow_up' => 'Review in OPD on 23/10/2026',
            'procedure_name' => 'ELECTIVE LSCS',
            'procedure_done' => '',
            'icd_code' => '',
            'surgeon' => 'Dr.Ambika MS(OG)',
            'anaesthetist' => 'Dr.S.G.Arun kumar',
            'assisted_by' => 'Mr. Vijayakumar',
            'assistant' => 'Mr. Vijayakumar',
            'anaesthesia_type' => 'Spinal',
            'procedure_note' => "Under SA, abdomen opened in layers by SPT incision. LSCS done in the usual way to delivery alive male baby,\nCried after birth. AMTSL followed. PPIUCD kept after getting consent from patient & husband. Uterus wound\nclosed in 2 layers. Uterus contracted well After securing perfect Hemostasis, Abdomen closed in layers. No\nundue bleeding P/V. No intra operative complications.",
            'incision' => 'SPT incision',
            'findings' => '',
            'complications' => '',
            'blood_loss_ml' => null,
            'specimen' => '',
            'drain' => '',
            'postoperative_orders' => '',
            'mrd_no' => 'MRD-2026/2952',
            'payment_mode' => 'Cash',
            'status' => 'approved',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'approved_at' => Carbon::parse('2026-10-16 15:00:00'),
        ]);

        Newborn::create([
            'admission_id' => $admission->id,
            'sex' => 'Female',
            'birth_at' => Carbon::parse('2026-10-16 13:26:00'),
            'birth_weight_kg' => 2.650,
            'apgar_1_min' => 8,
            'apgar_5_min' => 9,
            'neonatal_care' => '',
            'notes' => 'Alive Female baby',
        ]);
    }

    private function seedAnithaCase(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Mrs. Anitha',
            'uhid' => '2023/3931',
            'ip_no' => '2026/567',
            'age' => '23',
            'sex' => 'Female',
            'address' => "W/O Sarath\nVeerasanur",
            'contact' => '7373667465',
            'admission_at' => '2026-08-26 02:45:00',
            'procedure_at' => '2026-08-26 03:18:00',
            'discharge_at' => '2026-08-29 16:00:00',
            'consultant' => 'Dr. Ambika MS OG',
            'department' => 'Obstetrics & Gynaecology',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'diagnosis' => 'G3P1L1A1 / TWINS / Preterm / PROM',
            'clinical_history' => "G3P1L1A1 / TWINS / EDD 22-9-2026 / FH Good / admitted for safe confinement\nC/O abdominal Pain",
            'examination' => "O/E – A febrile, No Pallor, No Pedal edema / CVS & RS – NAD\nP/A: Uterus Overdistended, Not Acting, Both FH good",
            'procedure_name' => 'Labour natural with episiotomy (Twin A) & Emergency LSCS (Twin B)',
            'procedure_done' => 'Natural breech delivery with episiotomy (Twin A) + Emergency LSCS (Twin B)',
            'surgeon' => 'Dr. Ambika MS OG',
            'anaesthetist' => 'Dr. M.S Karthick',
            'assisted_by' => 'Staff Nurse Anitha',
            'anaesthesia_type' => 'SA',
            'procedure_note' => "Labour natural with episiotomy with good uterine contraction, full cervix dilated. After putting episiotomy, Patient Delivered a alive Twin A male baby By Breech, Baby Handed over to pediatrician. Twin B baby not progressed Delivered Hence Plaanned To proceed C-Section\nUnder SA, Abdomen opened in layers by SPT incision. LSCS done to deliver a alive Twin B male baby, cried soon after birth. Baby Handed over to pediatrician AMTSL followed. Uterine wound closed in 2 layers. After securing perfect Hemostasis, Abdomen closed in layer. AMTSL Followed. Uterus firm & contracted well. Episiotomy wound closed in layers. No Undue Bleeding P/V.",
            'incision' => 'Episiotomy (Twin A) & Suprapubic Transverse (SPT) incision (Twin B)',
            'findings' => 'Labour natural with episiotomy. Twin A delivered by Breech. Twin B obstructed labor requiring emergency LSCS. Alive twin male babies delivered.',
            'treatment_course' => "Treated with IV Fluids\nInj-Pipzo 4.5 g BD Iv\nInj-Metrogyl 100mg BD\nInj-Pan 40 Iv BD",
            'investigations' => "Hb : 12.9 mgs %\nBG : O positive\nHCV : Negative\nHIV : Negative\nVDRL : Negative\nHBsAG : Negative\nUREA : 9.7 g/dl\nCREATININE : 0.86 mg/dl\nURIC ACID : 4.3 mg/dl\nRBS : 100.8 mg/dl",
            'discharge_condition' => "Pt’s general condition good\n- BP: 110/70 mmHg\n- Pulse 86 b/mt\nClinical Examination : PA - Uterus contracted well\n- No undue Bleeding pv",
            'medication_orders' => "* Tab. Cefixime + sulbactum (1-0-1) x 7 Days - After food\n* Tab. Acendol-P (1-0-1) x 7 Days - After food\n* Cap. Cyra-D (1-0-1) x 7 Days - Before food",
            'discharge_advice' => "• Immunisation\n• Exclusive breast feeding for 6 months\n• Post Natal Exercise\n• Review after one week (03/09/2026)",
            'follow_up' => 'Review after one week (03/09/2026)',
            'mrd_no' => 'MRD-2023/3931',
            'payment_mode' => 'Cash / General',
            'handover_name' => 'Staff Nurse Anitha',
            'floor_incharge_name' => 'Nursing Supervisor Mary',
            'mrd_incharge_name' => 'MRD Officer Kumar',
            'status' => 'approved',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'approved_at' => '2026-08-29 15:30:00',
        ]);

        $admission->newborns()->create([
            'sex' => 'Male',
            'birth_at' => '2026-08-26 03:18:00',
            'birth_weight_kg' => 2.25,
            'apgar_1_min' => 7,
            'apgar_5_min' => 8,
            'neonatal_care' => 'RDS(+), Twin A Baby shifted to NICU',
            'notes' => 'Baby Twin A: Alive Male baby on 26/08/2026 at 03.18 AM delivered by Breech with episiotomy. Handed over to pediatrician.',
        ]);

        $admission->newborns()->create([
            'sex' => 'Male',
            'birth_at' => '2026-08-26 03:44:00',
            'birth_weight_kg' => 2.25,
            'apgar_1_min' => 8,
            'apgar_5_min' => 9,
            'neonatal_care' => 'Cried soon after birth. Baby Handed over to pediatrician AMTSL followed.',
            'notes' => 'Baby Twin B: Alive Male baby on 26/08/2026 at 03.44 AM delivered by Emergency LSCS under SA.',
        ]);

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $this->doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. Ambika MS OG.',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-08-29 15:30:00',
        ]);
    }

    private function seedGopalakrishnanCase(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Mr. Gopalakrishnan',
            'uhid' => '2026/1247',
            'ip_no' => '2026/507',
            'age' => '37',
            'sex' => 'Male',
            'address' => "S/O Narasimman\nMittahanahalli",
            'address_line1' => 'S/O Narasimman',
            'address_line2' => 'Mittahanahalli',
            'contact' => '9578559426',
            'admission_at' => '2026-08-07 15:14:00',
            'procedure_at' => '2026-08-07 17:00:00',
            'discharge_at' => '2026-08-08 17:00:00',
            'consultant' => 'Dr. N. Jayaprakash',
            'department' => 'Urology & General Surgery',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'Right Lower Ureteric Calculus',
            'clinical_history' => "Right loin pain few months. Found to have Right Lower ureteric calculus. Admitted for elective Right URSL\n\nPast Medical History: DM: Nil | HT: Nil | Asthma: Nil | TB: Nil | Other: Nil\nPast Surgical History: Nil\nAllergies: Nil | Medications: Nil\nPulse: 82 b/min | BP: 110/70 mmHg",
            'examination' => "CVS: NAD | RS: Clear | Abdomen: Soft, no mass, no free fluid, no organomegaly.\nPulse: 82 b/min | BP: 110/70 mmHg",
            'procedure_name' => 'Right URSL & DJ Stenting',
            'procedure_done' => 'Right URSL & DJ Stenting',
            'icd_code' => 'N20.0',
            'surgeon' => 'Dr. N. Jayaprakash',
            'anaesthetist' => 'Dr. S.G. Arun Kumar',
            'assisted_by' => 'Miss. Soundharya',
            'assistant' => 'Miss. Soundharya',
            'anaesthesia_type' => 'SA',
            'procedure_note' => "Under SA, Urethrocystoscopy done with Ureteroscope. Normal bladder and Right ureteric orifice cannulated with guidewire and Right Ureteroscopy done. Calculus in the Right Lower ureter. Using Basket Under C-arm Guidance. ICL (Intra Corporeal Lithotripsy) Done. Stone Brokened completely with lithotripsy. 5F/26cm/DJ Stent inserted, Bladder catheterized with 16F Foley catheter. Clear urine drained.",
            'findings' => 'Calculus in Right Lower Ureter fragmented completely by ICL. DJ stent in situ.',
            'treatment_course' => "A Case of Right Lower Ureteric calculus. Admitted electively and underwent Right URSL. Received Inj.Tetcef 1.5 g IV as peri-operative antibiotic. Uneventful hospital stay.",
            'investigations' => "Hb : 15.5 g/dl\nUrea : 32.2 mg/dl\nCreatinine : 1.14 mg/dl\nUric Acid : 4.72 mg/dl\nRBS : 115.2 mg/dl\nHBs Ag : Negative\nHIV : Negative\nHCV : Negative\nBG : A positive\nUSG of KUB : Right Lower Ureteric Calculus",
            'discharge_condition' => "Stable\n- Pulse: 82 b/min\n- BP: 110/70 mmHg\n- Abdomen: Soft",
            'medication_orders' => "* T. Niftas (1-0-1) - 6 Nos\n* T. P650mg (1-1-1) - 9 Nos\n* C. Cyra-D (1-0-1) - 6 Nos - Before food\n* Zerodol P (1-0-1) - 6 Nos - After food\n* Review after 7 days (14.08.2026)",
            'discharge_advice' => "Implant: Right DJ Stent\nDiet: Normal Diet\nExercise: Normal activity\nEmergency Contact: Reception: 04342-262266, 7418084266\nWhen to Contact: Bleeding Urethra, Fever, Anything unusual\nReview: 21.08.2026 for stent removal\nவயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு 21.08.2026 அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.",
            'follow_up' => '21.08.2026 for stent removal (Review after 7 days on 14.08.2026)',
            'mrd_no' => '2026-1247',
            'payment_mode' => 'Cash / General',
            'handover_name' => 'Dr. N. Jayaprakash',
            'floor_incharge_name' => 'Staff Nurse Anitha',
            'mrd_incharge_name' => 'MRD Officer Kumar',
            'status' => 'approved',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'approved_at' => '2026-08-08 16:30:00',
        ]);

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $this->doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. N. Jayaprakash.',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-08-08 16:30:00',
        ]);
    }
}
