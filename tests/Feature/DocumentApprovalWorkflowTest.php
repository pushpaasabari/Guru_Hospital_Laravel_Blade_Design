<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $nurse;
    protected User $doctor;
    protected User $auditor;
    protected Admission $admission;

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

        $this->auditor = User::create([
            'name' => 'Auditor Saravanan',
            'email' => 'auditor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'auditor',
            'is_active' => true,
        ]);

        $this->admission = Admission::create([
            'patient_name' => 'Test Patient',
            'uhid' => 'GH-5001',
            'ip_no' => 'IP-5001',
            'age' => '35',
            'sex' => 'Male',
            'admission_at' => now()->subDays(3),
            'discharge_at' => now(),
            'diagnosis' => 'Clinical Case Diagnosis',
            'format' => 'general',
            'surgery_type' => 'other',
            'created_by' => $this->nurse->id,
            'status' => 'draft',
        ]);
    }

    public function test_nurse_can_submit_draft_for_review(): void
    {
        $response = $this->actingAs($this->nurse)
            ->post(route('documents.submit-review', $this->admission));

        $response->assertRedirect(route('documents.preview', $this->admission));
        $this->assertEquals('ready_for_review', $this->admission->fresh()->status);

        $this->assertDatabaseHas('document_approvals', [
            'admission_id' => $this->admission->id,
            'action' => 'submitted_for_review',
            'performed_by' => $this->nurse->id,
        ]);
    }

    public function test_unauthorized_user_cannot_approve_documents(): void
    {
        $this->admission->update(['status' => 'ready_for_review']);

        $response = $this->actingAs($this->nurse)
            ->post(route('documents.approve', $this->admission));

        $response->assertForbidden();
        $this->assertNotEquals('approved', $this->admission->fresh()->status);
    }

    public function test_doctor_can_approve_documents(): void
    {
        $this->admission->update(['status' => 'ready_for_review']);

        $response = $this->actingAs($this->doctor)
            ->post(route('documents.approve', $this->admission));

        $response->assertRedirect(route('documents.preview', $this->admission));

        $fresh = $this->admission->fresh();
        $this->assertEquals('approved', $fresh->status);
        $this->assertEquals($this->doctor->id, $fresh->approved_by);
        $this->assertNotNull($fresh->approved_at);

        $this->assertDatabaseHas('document_approvals', [
            'admission_id' => $this->admission->id,
            'action' => 'approved',
            'performed_by' => $this->doctor->id,
        ]);
    }

    public function test_doctor_can_return_document_for_correction_with_reason(): void
    {
        $this->admission->update(['status' => 'ready_for_review']);

        $response = $this->actingAs($this->doctor)
            ->post(route('documents.return', $this->admission), [
                'correction_reason' => 'Please verify blood loss and provide complete discharge medication dosages.',
            ]);

        $response->assertRedirect(route('documents.preview', $this->admission));

        $fresh = $this->admission->fresh();
        $this->assertEquals('returned_for_correction', $fresh->status);
        $this->assertEquals('Please verify blood loss and provide complete discharge medication dosages.', $fresh->rejection_reason);

        $this->assertDatabaseHas('document_approvals', [
            'admission_id' => $this->admission->id,
            'action' => 'returned_for_correction',
            'performed_by' => $this->doctor->id,
        ]);
    }

    public function test_return_requires_correction_reason(): void
    {
        $this->admission->update(['status' => 'ready_for_review']);

        $response = $this->actingAs($this->doctor)
            ->post(route('documents.return', $this->admission), [
                'correction_reason' => '',
            ]);

        $response->assertSessionHasErrors('correction_reason');
    }

    public function test_draft_document_cannot_be_generated_as_pdf(): void
    {
        $response = $this->actingAs($this->doctor)
            ->get(route('documents.pdf', $this->admission));

        $response->assertForbidden();
    }

    public function test_creating_revision_preserves_snapshot_and_unlocks_admission(): void
    {
        $this->admission->update([
            'status' => 'approved',
            'approved_by' => $this->doctor->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->doctor)
            ->post(route('admissions.revision', $this->admission), [
                'revision_reason' => 'Retrospective pathology report added post-discharge.',
            ]);

        $fresh = $this->admission->fresh();
        $this->assertEquals('draft', $fresh->status);
        $this->assertNull($fresh->approved_by);

        $this->assertDatabaseHas('document_revisions', [
            'admission_id' => $this->admission->id,
            'revision_number' => 1,
            'approved_by' => $this->doctor->id,
        ]);
    }
}

