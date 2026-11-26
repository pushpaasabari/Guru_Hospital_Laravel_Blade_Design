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
        $this->get('/reports/day')->assertRedirect('/login');
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

    public function test_birth_report_counts_and_filters_baby_gender_case_insensitively(): void
    {
        $admBoy = $this->createAdmission([
            'patient_name' => 'Mother Boy',
            'surgery_type' => 'delivery',
            'procedure_done' => 'Emergency LSCS',
        ]);
        Newborn::create([
            'admission_id' => $admBoy->id,
            'sex' => 'Male',
            'birth_at' => now(),
            'birth_weight_kg' => 3.10,
        ]);

        $admGirl = $this->createAdmission([
            'patient_name' => 'Mother Girl',
            'surgery_type' => 'delivery',
            'procedure_done' => 'Normal Delivery',
        ]);
        Newborn::create([
            'admission_id' => $admGirl->id,
            'sex' => 'Female',
            'birth_at' => now(),
            'birth_weight_kg' => 2.85,
        ]);

        // Summary metrics should count both Male and Female accurately
        $allResponse = $this->actingAs($this->user)->get('/reports/birth');
        $allResponse->assertStatus(200);
        $allResponse->assertInertia(fn (Assert $page) => $page
            ->component('Reports/BirthReport')
            ->where('metrics.total_babies', 2)
            ->where('metrics.male_babies', 1)
            ->where('metrics.female_babies', 1)
        );

        // Filtering by baby_sex=male should match 'Male'
        $maleFilter = $this->actingAs($this->user)->get('/reports/birth?baby_sex=male');
        $maleFilter->assertStatus(200);
        $maleFilter->assertInertia(fn (Assert $page) => $page
            ->component('Reports/BirthReport')
            ->where('deliveries.total', 1)
            ->where('deliveries.data.0.patient_name', 'MOTHER BOY')
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

    public function test_day_report_shows_clinical_team_procedure_delivery_and_baby_details_and_exports_csv(): void
    {
        $targetDate = '2026-11-26';

        $delivery = $this->createAdmission([
            'patient_name' => 'DELIVERY DAY MOTHER',
            'uhid' => 'GH-DAY-DEL-01',
            'ip_no' => 'IP-DAY-DEL-01',
            'admission_at' => '2026-11-24 09:00:00',
            'procedure_at' => '2026-11-26 08:30:00',
            'discharge_at' => null,
            'surgery_type' => 'delivery',
            'procedure_done' => 'Emergency LSCS',
            'incision' => 'Pfannenstiel incision',
            'surgeon' => 'DR. AMBIKA',
            'anaesthetist' => 'DR. RAMESH',
            'anaesthesia_type' => 'Spinal Anaesthesia',
            'picme_no' => '123456789012',
        ]);

        Newborn::create([
            'admission_id' => $delivery->id,
            'sex' => 'Female',
            'birth_at' => '2026-11-26 08:45:00',
            'birth_weight_kg' => 3.05,
            'discharge_weight_kg' => 2.95,
            'apgar_1_min' => 8,
            'apgar_5_min' => 9,
            'neonatal_care' => 'Routine newborn care',
            'notes' => 'Cephalic presentation',
            'birth_vaccines' => ['Vitamin K', 'BCG'],
            'vaccine_dates' => [
                'Vitamin K' => '2026-11-24',
                'BCG' => '2026-11-26',
            ],
        ]);

        $otherDay = $this->createAdmission([
            'patient_name' => 'OTHER DAY CASE',
            'uhid' => 'GH-OTHER-02',
            'ip_no' => 'IP-OTHER-02',
            'admission_at' => '2026-11-20 10:00:00',
            'procedure_at' => '2026-11-20 12:00:00',
            'discharge_at' => '2026-11-21 10:00:00',
            'surgery_type' => 'other',
            'procedure_done' => 'Laparoscopic Cholecystectomy',
        ]);

        // Filtering by targetDate should match delivery via DOO even though DOA was 2 days earlier
        $response = $this->actingAs($this->user)->get('/reports/day?date=' . $targetDate);
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/DayReport')
                ->where('records.total', 1)
                ->where('records.data.0.patient_name', 'DELIVERY DAY MOTHER')
                ->where('records.data.0.incision', 'Pfannenstiel incision')
                ->where('records.data.0.newborns.0.notes', 'Cephalic presentation')
                ->where('records.data.0.newborns.0.discharge_weight_kg', 2.95)
                ->where('metrics.deliveries_count', 1)
                ->where('metrics.babies_count', 1)
            );

        // Date basis 'admission' only should exclude admission from 2026-11-24
        $this->actingAs($this->user)
            ->get('/reports/day?date=' . $targetDate . '&date_basis=admission')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('records.total', 0));

        // All dates mode includes both records
        $this->actingAs($this->user)
            ->get('/reports/day?all_dates=1')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('records.total', 2));

        // CSV Export contains exact requested headers and baby/delivery/team values
        $csvResp = $this->actingAs($this->user)->get('/reports/day?date=' . $targetDate . '&export=csv');
        $csvResp->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csvResp->headers->get('content-type'));
        $csv = $csvResp->streamedContent();
        $this->assertStringContainsString('S.No,Name,UHID,IPNO,DOA,DOO,DOD,"Procedure Done & Incision","Surgical & Anesthesia Team","Delivery Details","Baby Details"', $csv);
        $this->assertStringContainsString('DELIVERY DAY MOTHER', $csv);
        $this->assertStringContainsString('Pfannenstiel incision', $csv);
        $this->assertStringContainsString('DR. AMBIKA', $csv);
        $this->assertStringContainsString('Cephalic presentation', $csv);
        $this->assertStringContainsString('Vitamin K (24.11.2026); BCG (26.11.2026)', $csv);
        $this->assertNotContains($otherDay->patient_name, [$csv]);
        $this->assertStringNotContainsString('OTHER DAY CASE', $csv);

        // Blade image view renders complete columns and delivery/baby details
        $imageHtml = view('pdf.day-report-image', [
            'records' => collect([$delivery->load('newborns')]),
            'metrics' => [
                'total_cases' => 1,
                'surgeries_count' => 0,
                'deliveries_count' => 1,
                'babies_count' => 1,
                'medical_count' => 0,
                'discharges_count' => 0,
            ],
            'periodLabel' => 'Date: 26.11.2026',
            'basisLabel' => 'Any (DOA / DOO / DOD)',
            'logoDataUri' => null,
        ])->render();
        $this->assertStringContainsString('GURU HOSPITAL — DAY REPORT', $imageHtml);
        $this->assertStringContainsString('DELIVERY DAY MOTHER', $imageHtml);
        $this->assertStringContainsString('Pfannenstiel incision', $imageHtml);
        $this->assertStringContainsString('DR. AMBIKA', $imageHtml);
        $this->assertStringContainsString('Cephalic presentation', $imageHtml);
        $this->assertStringContainsString('Vitamin K (24.11.2026); BCG (26.11.2026)', $imageHtml);

        // Full Playwright PNG image endpoint returns a valid PNG binary of the complete Day Report
        $imgResp = $this->actingAs($this->user)->get('/reports/day?date=' . $targetDate . '&export=image');
        $imgResp->assertOk();
        $this->assertEquals('image/png', (string) $imgResp->headers->get('content-type'));
        $this->assertStringContainsString('.png', (string) $imgResp->headers->get('content-disposition'));
        $pngBytes = (string) $imgResp->getContent();
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $pngBytes);

        // Full Playwright A4 Landscape PDF endpoint returns a valid PDF binary
        $pdfResp = $this->actingAs($this->user)->get('/reports/day?date=' . $targetDate . '&export=pdf');
        $pdfResp->assertOk();
        $this->assertEquals('application/pdf', (string) $pdfResp->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $pdfResp->headers->get('content-disposition'));
        $pdfBytes = (string) $pdfResp->getContent();
        $this->assertStringStartsWith('%PDF-', $pdfBytes);
    }
}
