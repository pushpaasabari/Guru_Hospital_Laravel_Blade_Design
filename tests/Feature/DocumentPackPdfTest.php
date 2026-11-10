<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentPackPdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected Admission $approvedAdmission;

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

        $this->approvedAdmission = Admission::create([
            'patient_name' => 'Ramesh Natarajan',
            'uhid' => 'GH-7701',
            'ip_no' => 'IP-7701',
            'age' => '42',
            'sex' => 'Male',
            'admission_at' => now()->subDays(4),
            'discharge_at' => now()->subDay(),
            'consultant' => 'Dr. Karthik MS',
            'department' => 'Surgical Gastroenterology',
            'diagnosis' => 'Cholelithiasis with Chronic Cholecystitis',
            'format' => 'general',
            'surgery_type' => 'other',
            'procedure_name' => 'Laparoscopic Cholecystectomy',
            'surgeon' => 'Dr. Karthik MS',
            'anaesthetist' => 'Dr. Ramesh DA',
            'findings' => 'Distended gall bladder with multiple cholesterol calculi, cystic duct identified and clipped.',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'approved_at' => now()->subHour(),
            'status' => 'approved',
        ]);
    }

    public function test_approved_admission_generates_valid_pdf_stream(): void
    {
        $response = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $this->approvedAdmission));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="Guru_Hospital_Discharge_Pack_IP-7701.pdf"', $response->headers->get('Content-Disposition'));
        $this->assertNotNull($response->headers->get('Content-Length'));

        // Status is updated to generated
        $this->assertEquals('generated', $this->approvedAdmission->fresh()->status);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'document.generated',
            'auditable_type' => 'Admission',
            'auditable_id' => $this->approvedAdmission->id,
        ]);

        // Test inline preview mode
        $inlineResponse = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', [$this->approvedAdmission, 'inline' => 1]));
        $inlineResponse->assertStatus(200);
        $this->assertStringContainsString('inline; filename="Guru_Hospital_Discharge_Pack_IP-7701.pdf"', $inlineResponse->headers->get('Content-Disposition'));
    }

    public function test_can_download_3_separate_pdfs_individually(): void
    {
        // 1. Discharge Summary (UHID_Patientname_Discharge_Summary)
        $resDischarge = $this->actingAs($this->doctor)
            ->get("/admissions/{$this->approvedAdmission->id}/documents/pdf/discharge-summary");
        $resDischarge->assertStatus(200);
        $this->assertEquals('application/pdf', $resDischarge->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="GH-7701_Ramesh_Natarajan_Discharge_Summary.pdf"', $resDischarge->headers->get('Content-Disposition'));

        // 2. Operation Theatre Notes (UHID_Patentname_OPerationtype)
        $resOt = $this->actingAs($this->doctor)
            ->get("/admissions/{$this->approvedAdmission->id}/documents/pdf/operation-notes");
        $resOt->assertStatus(200);
        $this->assertEquals('application/pdf', $resOt->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="GH-7701_Ramesh_Natarajan_Laparoscopic_Cholecystectomy.pdf"', $resOt->headers->get('Content-Disposition'));

        // 3. MRD Inpatient Form
        $resMrd = $this->actingAs($this->doctor)
            ->get("/admissions/{$this->approvedAdmission->id}/documents/pdf/mrd-form");
        $resMrd->assertStatus(200);
        $this->assertEquals('application/pdf', $resMrd->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="Guru_Hospital_MRD_Form_IP-7701.pdf"', $resMrd->headers->get('Content-Disposition'));
    }

    public function test_admission_can_generate_pdf_directly_without_prior_approval(): void
    {
        $this->approvedAdmission->update(['status' => 'draft']);

        $response = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $this->approvedAdmission));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_guest_cannot_download_pdf(): void
    {
        $response = $this->get(route('documents.pdf', $this->approvedAdmission));
        $response->assertRedirect(route('login'));
    }

    public function test_discharge_summary_uses_uppercase_guru_hospital_and_removes_prepared_by_and_resident_column(): void
    {
        $deliveryAdmission = Admission::create([
            'patient_name' => 'test',
            'uhid' => '2026/0005',
            'ip_no' => '2026/0005',
            'age' => '24',
            'sex' => 'Female',
            'admission_at' => now()->subDays(3),
            'discharge_at' => now(),
            'consultant' => 'Dr. Ambika M.S (O&G)',
            'department' => 'Obstetrics & Gynaecology',
            'diagnosis' => 'Twins / Emergency LSCS',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'created_by' => $this->doctor->id,
            'approved_by' => $this->doctor->id,
            'approved_at' => now(),
            'status' => 'approved',
        ]);

        $pdfService = app(\App\Services\DocumentPackPdfService::class);
        $meta = $pdfService->resolveDocumentMetadata('discharge-summary', $deliveryAdmission);

        $this->assertEquals('discharge_summary', $meta['type']);
        $this->assertStringStartsWith('GURU HOSPITAL', $meta['headerTitle']);

        $html = view('pdf.discharge-summary', ['admission' => $deliveryAdmission])->render();
        $this->assertStringContainsString('GURU HOSPITAL - Discharge Summary (IP: 2026/0005) - test', $html);
        $this->assertStringNotContainsString('Prepared by', $html);
        $this->assertStringNotContainsString('Dr. Karthik Rajan MS, MCh', $html);
        $this->assertStringContainsString('Dr. Ambika M.S (O&amp;G)', $html);
    }

    public function test_investigations_uses_same_document_font_across_all_formats(): void
    {
        $formats = ['cmchistn', 'employee', 'private'];
        $types = ['delivery', 'other'];

        foreach ($formats as $format) {
            foreach ($types as $type) {
                $admission = Admission::create([
                    'patient_name' => 'Font Check Patient',
                    'uhid' => "FONT-{$format}-{$type}",
                    'ip_no' => "IP-FONT-{$format}-{$type}",
                    'age' => '25',
                    'sex' => 'Female',
                    'admission_at' => now()->subDays(2),
                    'discharge_at' => now(),
                    'diagnosis' => 'Test',
                    'format' => $format,
                    'surgery_type' => $type,
                    'investigations' => "Hb : 12.9 mgs %\nBG : O positive",
                    'created_by' => $this->doctor->id,
                    'approved_by' => $this->doctor->id,
                    'approved_at' => now(),
                    'status' => 'approved',
                ]);

                $html = view("documents.discharge-summaries.{$format}.{$type}", ['admission' => $admission])->render();
                $this->assertStringContainsString('INVESTIGATIONS', $html);
                $this->assertStringNotContainsString('rounded font-monospace', $html);
            }
        }
    }
}

