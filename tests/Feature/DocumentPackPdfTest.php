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
        // 1. Discharge Summary
        $resDischarge = $this->actingAs($this->doctor)
            ->get("/admissions/{$this->approvedAdmission->id}/documents/pdf/discharge-summary");
        $resDischarge->assertStatus(200);
        $this->assertEquals('application/pdf', $resDischarge->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="Guru_Hospital_Discharge_Summary_IP-7701.pdf"', $resDischarge->headers->get('Content-Disposition'));

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

    public function test_unapproved_admission_cannot_generate_pdf(): void
    {
        $this->approvedAdmission->update(['status' => 'draft']);

        $response = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $this->approvedAdmission));

        $response->assertForbidden();
    }

    public function test_guest_cannot_download_pdf(): void
    {
        $response = $this->get(route('documents.pdf', $this->approvedAdmission));
        $response->assertRedirect(route('login'));
    }
}

