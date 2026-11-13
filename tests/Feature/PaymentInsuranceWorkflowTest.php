<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PaymentInsuranceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $nurse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nurse = User::create([
            'name' => 'Staff Nurse Kavitha',
            'email' => 'nurse.ins@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
            'is_active' => true,
        ]);
    }

    public function test_cash_admission_clears_any_stale_insurance_fields(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Karthi M',
            'uhid' => 'GH-CASH-01',
            'ip_no' => 'IP-CASH-01',
            'age' => '32',
            'sex' => 'Male',
            'admission_at' => '2026-11-01 10:00:00',
            'diagnosis' => 'Acute Gastritis',
            'format' => 'general',
            'surgery_type' => 'medical',
            'payment_mode' => 'Cash',
            'insurance_company' => 'Should Be Cleared',
            'policy_number' => 'POL-999',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('admissions', [
            'ip_no' => 'IP-CASH-01',
            'payment_mode' => 'Cash',
            'insurance_type' => null,
            'insurance_company' => null,
            'policy_number' => null,
            'pre_auth_no' => null,
        ]);
    }

    public function test_insurance_admission_requires_core_insurance_fields(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Lakshmi V',
            'uhid' => 'GH-INS-VAL-01',
            'ip_no' => 'IP-INS-VAL-01',
            'age' => '28',
            'sex' => 'Female',
            'admission_at' => '2026-11-01 10:00:00',
            'diagnosis' => 'Cholelithiasis',
            'format' => 'private',
            'surgery_type' => 'other',
            'payment_mode' => 'Insurance',
        ]);

        $response->assertSessionHasErrors([
            'insurance_type',
            'insurance_company',
            'policy_number',
        ]);
    }

    public function test_insurance_admission_persists_all_details_and_clears_when_switched_to_cash(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Selvamani K',
            'uhid' => 'GH-INS-01',
            'ip_no' => 'IP-INS-01',
            'age' => '41',
            'sex' => 'Female',
            'admission_at' => '2026-11-01 09:00:00',
            'diagnosis' => 'Fibroid Uterus',
            'format' => 'private',
            'surgery_type' => 'other',
            'payment_mode' => 'Insurance',
            'insurance_type' => 'Private Insurance / TPA',
            'insurance_company' => 'Star Health Insurance',
            'policy_number' => 'SH-88412099',
            'pre_auth_no' => 'PA-2026-1102',
            'pre_auth_status' => 'Approved',
            'approved_amount' => '65000.00',
            'policy_holder_name' => 'Kumar S',
            'policy_holder_relation' => 'Spouse',
            'policy_validity' => '2027-06-30',
        ]);

        $admission = Admission::where('ip_no', 'IP-INS-01')->firstOrFail();
        $response->assertRedirect(route('admissions.test-reports', $admission));

        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'payment_mode' => 'Insurance',
            'insurance_type' => 'Private Insurance / TPA',
            'insurance_company' => 'Star Health Insurance',
            'policy_number' => 'SH-88412099',
            'pre_auth_no' => 'PA-2026-1102',
            'pre_auth_status' => 'Approved',
            'policy_holder_name' => 'Kumar S',
            'policy_holder_relation' => 'Spouse',
        ]);
        $this->assertEquals('65000.00', $admission->approved_amount);
        $this->assertEquals('2027-06-30', $admission->policy_validity->format('Y-m-d'));

        // Render PDF views and confirm insurance metadata appears cleanly
        $dsHtml = view('pdf.discharge-summary', compact('admission'))->render();
        $this->assertStringContainsString('Star Health Insurance', $dsHtml);
        $this->assertStringContainsString('SH-88412099', $dsHtml);
        $this->assertStringContainsString('PA-2026-1102', $dsHtml);
        $this->assertStringContainsString('65,000.00', $dsHtml);

        $mrdHtml = view('pdf.mrd-form', compact('admission'))->render();
        $this->assertStringContainsString('Insurance', $mrdHtml);
        $this->assertStringContainsString('Star Health Insurance', $mrdHtml);
        $this->assertStringContainsString('SH-88412099', $mrdHtml);

        $this->actingAs($this->nurse)
            ->get(route('documents.preview', $admission))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Pack')
                ->where('admission.insurance_company', 'Star Health Insurance')
                ->where('admission.policy_number', 'SH-88412099')
                ->where('admission.pre_auth_no', 'PA-2026-1102')
            );

        $packHtml = view('pdf.pack', compact('admission'))->render();
        $this->assertStringContainsString('Star Health Insurance', $packHtml);
        $this->assertStringContainsString('SH-88412099', $packHtml);
        $this->assertStringContainsString('PA-2026-1102', $packHtml);
        $this->assertStringNotContainsString('GH-MED-', $packHtml);

        // Switching to Cash in Edit clears all insurance metadata
        $editResponse = $this->actingAs($this->nurse)->put(route('admissions.update', $admission), [
            'patient_name' => 'Selvamani K',
            'uhid' => 'GH-INS-01',
            'ip_no' => 'IP-INS-01',
            'age' => '41',
            'sex' => 'Female',
            'admission_at' => '2026-11-01 09:00:00',
            'diagnosis' => 'Fibroid Uterus',
            'format' => 'general',
            'surgery_type' => 'other',
            'payment_mode' => 'Cash',
        ]);

        $editResponse->assertRedirect();
        $fresh = $admission->fresh();
        $this->assertEquals('Cash', $fresh->payment_mode);
        $this->assertNull($fresh->insurance_type);
        $this->assertNull($fresh->insurance_company);
        $this->assertNull($fresh->policy_number);
        $this->assertNull($fresh->pre_auth_no);
        $this->assertNull($fresh->approved_amount);
    }

    public function test_stage_4_discharge_syncs_and_updates_insurance_details(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Murugan R',
            'uhid' => 'GH-CMCHIS-01',
            'ip_no' => 'IP-CMCHIS-01',
            'age' => '52',
            'sex' => 'Male',
            'admission_at' => '2026-11-01 08:00:00',
            'diagnosis' => 'Right Inguinal Hernia',
            'format' => 'cmchistn',
            'surgery_type' => 'other',
            'payment_mode' => 'Insurance',
            'insurance_type' => 'CMCHISTN Govt Scheme',
            'insurance_company' => 'CMCHISTN / United India',
            'policy_number' => 'URN-3301994422',
            'pre_auth_status' => 'Pending',
            'created_by' => $this->nurse->id,
            'status' => 'draft',
        ]);

        // Stage 4 updates Pre-Auth Status & Approved Amount cleanly
        $dischargeResponse = $this->actingAs($this->nurse)->put(route('admissions.discharge.update', $admission), [
            'discharge_at' => '2026-11-04 15:00:00',
            'discharge_condition' => 'Stable',
            'payment_mode' => 'Insurance',
            'insurance_type' => 'CMCHISTN Govt Scheme',
            'insurance_company' => 'CMCHISTN / United India',
            'policy_number' => 'URN-3301994422',
            'pre_auth_no' => 'PA-CMCHIS-771',
            'pre_auth_status' => 'Approved',
            'approved_amount' => '32000',
            'next_step' => 'preview',
        ]);

        $dischargeResponse->assertRedirect(route('documents.preview', $admission));
        $fresh = $admission->fresh();
        $this->assertEquals('PA-CMCHIS-771', $fresh->pre_auth_no);
        $this->assertEquals('Approved', $fresh->pre_auth_status);
        $this->assertEquals('32000.00', $fresh->approved_amount);

        $this->actingAs($this->nurse)
            ->get(route('documents.preview', $fresh))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Pack')
                ->where('admission.policy_number', 'URN-3301994422')
                ->where('admission.pre_auth_no', 'PA-CMCHIS-771')
            );

        $cmchistnHtml = view('pdf.pack', ['admission' => $fresh])->render();
        $this->assertStringContainsString('URN-3301994422', $cmchistnHtml);
        $this->assertStringContainsString('PA-CMCHIS-771', $cmchistnHtml);
        $this->assertStringNotContainsString('TN-CMCHIS-', $cmchistnHtml);

        // Stream real PDF for Insurance admission
        $pdfRes = $this->actingAs($this->nurse)->get(route('documents.pdf', ['admission' => $fresh, 'type' => 'mrd_form']));
        $pdfRes->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfRes->headers->get('Content-Type'));
    }
}

