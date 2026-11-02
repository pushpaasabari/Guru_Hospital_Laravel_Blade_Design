<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Database\Seeders\AjithKumarDischargeSummarySeeder;
use Database\Seeders\AshwiniDischargeSummarySeeder;
use Database\Seeders\ManjupriyaOperationNotesSeeder;
use Database\Seeders\AnithaTwinDeliverySeeder;
use Database\Seeders\GopalakrishnanDischargeSummarySeeder;
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
                ->where('admission.patient_name', "Patient {$format}")
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
        $this->seed(AshwiniDischargeSummarySeeder::class);

        $admission = Admission::where('ip_no', '2026/582')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('Mrs. Ashwini', $admission->patient_name);
        $this->assertEquals('2022/151', $admission->uhid);
        $this->assertEquals('Emergency LSCS', $admission->procedure_name);
        $this->assertCount(1, $admission->newborns);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'Mrs. Ashwini')
            ->where('admission.ip_no', '2026/582')
            ->where('admission.uhid', '2022/151')
            ->where('admission.procedure_name', 'Emergency LSCS')
            ->where('admission.surgeon', 'Dr. Ambika M.S (O&G)')
            ->where('admission.anaesthetist', 'Dr. M.S Karthick')
            ->where('admission.assisted_by', 'Miss Soudnharya')
            ->where('admission.pediatrician_name', 'Dr. Nithyapriya (Paediatrician)')
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
        $this->seed(AjithKumarDischargeSummarySeeder::class);

        $admission = Admission::where('ip_no', '2026/488')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('Mr. Ajith kumar', $admission->patient_name);
        $this->assertEquals('2026/2892', $admission->uhid);
        $this->assertEquals('Lap Appendectomy', $admission->procedure_name);
        $this->assertCount(0, $admission->newborns);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'Mr. Ajith kumar')
            ->where('admission.ip_no', '2026/488')
            ->where('admission.uhid', '2026/2892')
            ->where('admission.procedure_name', 'Lap Appendectomy')
            ->where('admission.surgeon', 'Dr. Jayaprakash M.S')
            ->where('admission.anaesthetist', 'Dr. S.G. ArunKumar')
            ->where('admission.assisted_by', 'Mr. Vijaykumar')
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
        $this->seed(ManjupriyaOperationNotesSeeder::class);

        $admission = Admission::where('ip_no', '2026/778')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('Mrs. MANJUPRIYA', $admission->patient_name);
        $this->assertEquals('2026/2952', $admission->uhid);
        $this->assertEquals('ELECTIVE LSCS', $admission->procedure_name);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'Mrs. MANJUPRIYA')
            ->where('admission.ip_no', '2026/778')
            ->where('admission.uhid', '2026/2952')
            ->where('admission.procedure_name', 'ELECTIVE LSCS')
            ->where('admission.surgeon', 'Dr.Ambika MS(OG)')
            ->where('admission.contact', '9677371431')
            ->where('admission.anaesthetist', 'Dr.S.G.Arun kumar')
            ->where('admission.assisted_by', 'Mr. Vijayakumar')
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
        $this->seed(AnithaTwinDeliverySeeder::class);

        $admission = Admission::where('ip_no', '2026/567')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('Mrs. Anitha', $admission->patient_name);
        $this->assertEquals('2023/3931', $admission->uhid);
        $this->assertEquals('G3P1L1A1 / TWINS / Preterm / PROM', $admission->diagnosis);
        $this->assertCount(2, $admission->newborns);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'Mrs. Anitha')
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
        $this->assertEquals('HB', $admission->formatted_investigations[0]['label']);
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
        $this->seed(GopalakrishnanDischargeSummarySeeder::class);

        $admission = Admission::where('ip_no', '2026/507')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('Mr. Gopalakrishnan', $admission->patient_name);
        $this->assertEquals('2026/1247', $admission->uhid);
        $this->assertEquals('Right Lower Ureteric Calculus', $admission->diagnosis);
        $this->assertEquals('other', $admission->surgery_type);

        // Preview Check via Inertia
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('admission.patient_name', 'Mr. Gopalakrishnan')
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
        $this->assertEquals('15.5g/dl', $inv['group1']['Hb']);
        $this->assertEquals('Neg', $inv['group2']['HIV']);

        $advice = $admission->formatted_surgical_advice;
        $this->assertEquals('Right DJ Stent', $advice['implant']);
        $this->assertStringContainsString('வயிற்றுக்குள்', $advice['tamil_note']);

        $drugs = $admission->formatted_surgical_drugs;
        $this->assertGreaterThanOrEqual(4, count($drugs));
        $this->assertEquals('T. Niftas', $drugs[0]['drug']);
        $this->assertEquals('1-0-1', $drugs[0]['dosage']);
    }
}
