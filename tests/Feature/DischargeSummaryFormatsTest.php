<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Database\Seeders\AjithKumarDischargeSummarySeeder;
use Database\Seeders\AshwiniDischargeSummarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            $response->assertSee("Patient {$format}");
            $response->assertSee("IP-{$format}");
            $response->assertSee('OPERATION THEATRE NOTES');
            $response->assertSee('MEDICAL RECORDS DEPARTMENT');
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
        $response->assertSee('Newborn');
        $response->assertSee('3.15');
        $response->assertSee('Healthy female baby');
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

        // Preview Check
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('Mrs. Ashwini');
        $previewResponse->assertSee('2026/582');
        $previewResponse->assertSee('2022/151');
        $previewResponse->assertSee('Emergency LSCS');
        $previewResponse->assertSee('Dr. Ambika M.S (O&G)');
        $previewResponse->assertSee('Dr. M.S Karthick');
        $previewResponse->assertSee('Miss Soudnharya');
        $previewResponse->assertSee('Dr. Nithyapriya');
        $previewResponse->assertSee('Hb : 10.6 mgs%');
        $previewResponse->assertSee('04342-262266');

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

        // Preview Check
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('Mr. Ajith kumar');
        $previewResponse->assertSee('2026/488');
        $previewResponse->assertSee('2026/2892');
        $previewResponse->assertSee('Lap Appendectomy');
        $previewResponse->assertSee('Dr. Jayaprakash M.S');
        $previewResponse->assertSee('Dr. S.G. ArunKumar');
        $previewResponse->assertSee('Mr. Vijaykumar');
        $previewResponse->assertSee('GA');
        $previewResponse->assertSee('10 mm Supra Umbilical');
        $previewResponse->assertSee('Hb : 13.8mgs %');
        $previewResponse->assertSee('04342-262266');

        // Verify Newborn section is NOT present for 'other' surgery workflow
        $previewResponse->assertDontSee('Baby / Newborn Details');

        // PDF Generation Check
        $pdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $admission));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Guru_Hospital_Discharge_Pack_2026_488.pdf', $pdfResponse->headers->get('Content-Disposition'));
    }

    public function test_mrs_manjupriya_exact_operation_theatre_notes_renders_and_generates_pdf_faithfully(): void
    {
        $this->seed(\Database\Seeders\ManjupriyaOperationNotesSeeder::class);

        $admission = Admission::where('ip_no', '2026/778')->first();
        $this->assertNotNull($admission);
        $this->assertEquals('Mrs. MANJUPRIYA', $admission->patient_name);
        $this->assertEquals('2026/2952', $admission->uhid);
        $this->assertEquals('ELECTIVE LSCS', $admission->procedure_name);

        // Preview Check
        $previewResponse = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('OPERATION THEATRE NOTES');
        $previewResponse->assertSee('Mrs. MANJUPRIYA');
        $previewResponse->assertSee('2026/778');
        $previewResponse->assertSee('2026/2952');
        $previewResponse->assertSee('Dr.Ambika MS(OG)');
        $previewResponse->assertSee('9677371431');
        $previewResponse->assertSee('W/O KAMALGANESH');
        $previewResponse->assertSee('ELECTIVE LSCS');
        $previewResponse->assertSee('Dr.S.G.Arun kumar');
        $previewResponse->assertSee('Mr. Vijayakumar');
        $previewResponse->assertSee('Spinal');
        $previewResponse->assertSee('Under SA, abdomen opened in layers by SPT incision');
        $previewResponse->assertSee('BLOOD LOSS (ML) :');
        $previewResponse->assertSee('SPECIMEN :');
        $previewResponse->assertSee('DRAIN :');
        $previewResponse->assertSee('BABY');
        $previewResponse->assertSee('2.650Kg');
        $previewResponse->assertSee('SURGEON SEAL &amp; SIGNATURE', false);

        // PDF Generation Check
        $pdfResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $admission));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Guru_Hospital_Discharge_Pack_2026_778.pdf', $pdfResponse->headers->get('Content-Disposition'));
    }
}
