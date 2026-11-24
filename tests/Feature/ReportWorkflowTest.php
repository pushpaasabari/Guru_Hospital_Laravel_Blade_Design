<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Newborn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Dr. Karthik MS',
            'email' => 'doctor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'doctor_reg_no' => 'TNMC-64821',
            'is_active' => true,
        ]);
    }

    protected function createAdmission(array $overrides = []): Admission
    {
        return Admission::create(array_merge([
            'patient_name' => 'Test Patient',
            'uhid' => 'GH-' . rand(1000, 9999),
            'ip_no' => 'IP-' . rand(1000, 9999),
            'age' => '30',
            'sex' => 'Female',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'diagnosis' => 'Clinical Diagnosis',
            'admission_at' => now(),
            'status' => 'approved',
            'created_by' => $this->user->id,
        ], $overrides));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/reports')->assertRedirect('/login');
        $this->get('/reports/birth')->assertRedirect('/login');
        $this->get('/reports/surgery')->assertRedirect('/login');
        $this->get('/reports/medical')->assertRedirect('/login');
        $this->get('/reports/month-wise')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_report_index_and_is_redirected_to_month_wise(): void
    {
        $response = $this->actingAs($this->user)->get('/reports');
        $response->assertRedirect(route('reports.month-wise'));
    }

    public function test_authenticated_user_can_view_birth_report(): void
    {
        $admission = $this->createAdmission([
            'surgery_type' => 'delivery',
            'procedure_done' => 'Emergency LSCS',
        ]);

        Newborn::create([
            'admission_id' => $admission->id,
            'sex' => 'female',
            'birth_at' => now(),
            'birth_weight_kg' => 2.85,
        ]);

        $response = $this->actingAs($this->user)->get('/reports/birth');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/BirthReport')
            ->has('deliveries.data')
            ->has('metrics')
            ->has('filters')
            ->has('clinicalStaff')
        );
    }

    public function test_birth_report_csv_export(): void
    {
        $this->createAdmission([
            'surgery_type' => 'delivery',
            'procedure_done' => 'LSCS',
        ]);

        $response = $this->actingAs($this->user)->get('/reports/birth?export=csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_authenticated_user_can_view_surgery_report(): void
    {
        $this->createAdmission([
            'surgery_type' => 'other',
            'procedure_done' => 'Laparoscopic Appendectomy',
            'procedure_at' => now(),
            'blood_loss_ml' => 50,
        ]);

        $response = $this->actingAs($this->user)->get('/reports/surgery');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/SurgeryReport')
            ->has('surgeries.data')
            ->has('metrics')
            ->has('filters')
            ->has('clinicalStaff')
            ->has('anesthesiaTypes')
        );
    }

    public function test_surgery_report_csv_export(): void
    {
        $this->createAdmission([
            'surgery_type' => 'other',
            'procedure_done' => 'Open Cholecystectomy',
        ]);

        $response = $this->actingAs($this->user)->get('/reports/surgery?export=csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_authenticated_user_can_view_medical_case_report(): void
    {
        $this->createAdmission([
            'surgery_type' => 'medical',
            'diagnosis' => 'Acute Gastroenteritis with moderate dehydration',
            'admission_at' => now()->subDays(2),
            'discharge_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get('/reports/medical');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/MedicalReport')
            ->has('medicalCases.data')
            ->has('metrics')
            ->has('filters')
            ->has('clinicalStaff')
        );
    }

    public function test_medical_report_csv_export(): void
    {
        $this->createAdmission([
            'surgery_type' => 'medical',
            'diagnosis' => 'Viral Fever',
        ]);

        $response = $this->actingAs($this->user)->get('/reports/medical?export=csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_authenticated_user_can_view_month_wise_report(): void
    {
        $this->createAdmission([
            'surgery_type' => 'medical',
        ]);

        $response = $this->actingAs($this->user)->get('/reports/month-wise');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/MonthWiseReport')
            ->has('monthlyStats')
            ->has('yearSummary')
            ->has('admissions.data')
            ->has('filters')
            ->has('availableYears')
        );
    }

    public function test_month_wise_report_csv_export(): void
    {
        $this->createAdmission();

        $response = $this->actingAs($this->user)->get('/reports/month-wise?export=csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }
}
