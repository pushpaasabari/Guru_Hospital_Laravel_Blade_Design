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
        $this->get('/reports/mrd')->assertRedirect('/login');
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

    public function test_day_report_shows_complete_admission_discharge_and_inpatient_sections(): void
    {
        $targetDate = '2026-11-26';

        $newAdmit = $this->createAdmission([
            'patient_name' => 'NEW ADMIT DAY PATIENT',
            'attender_name' => 'RAMESH',
            'attender_relation' => 'Husband',
            'contact' => '9876543210',
            'address' => '12 Main Road, Dharmapuri',
            'uhid' => 'GH-ADM-101',
            'ip_no' => 'IP-ADM-101',
            'admission_at' => '2026-11-26 09:15:00',
            'discharge_at' => null,
            'department' => 'OBG',
            'ward_room_no' => 'W-102',
            'consultant' => 'DR. AMBIKA',
            'diagnosis' => 'Term Pregnancy',
            'vital_bp' => '120/80',
            'vital_pulse' => '82',
            'vital_spo2' => '99%',
            'surgery_type' => 'delivery',
        ]);

        $discharged = $this->createAdmission([
            'patient_name' => 'DISCHARGED DAY PATIENT',
            'uhid' => 'GH-DIS-102',
            'ip_no' => 'IP-DIS-102',
            'admission_at' => '2026-11-23 10:00:00',
            'procedure_at' => '2026-11-24 11:00:00',
            'discharge_at' => '2026-11-26 14:30:00',
            'surgery_type' => 'other',
            'procedure_done' => 'RIGHT URSL',
            'diagnosis' => 'Ureteric Calculus',
            'discharge_condition' => 'Afebrile, vitals stable',
            'discharge_bp' => '118/76',
            'discharge_advice' => 'Low salt soft diet',
            'follow_up' => 'Review after 1 week',
            'suture_removal_date' => '2026-12-03',
            'handover_name' => 'Sister Devi',
            'floor_incharge_name' => 'Nurse Kavitha',
            'mrd_incharge_name' => 'Kala MRD',
            'payment_mode' => 'Insurance',
            'format' => 'cmchistn',
            'insurance_type' => 'CMCHISTN',
        ]);

        $continuingInpatient = $this->createAdmission([
            'patient_name' => 'CONTINUING WARD INPATIENT',
            'uhid' => 'GH-IP-103',
            'ip_no' => 'IP-IP-103',
            'admission_at' => '2026-11-24 08:00:00',
            'procedure_at' => null,
            'discharge_at' => null,
            'surgery_type' => 'medical',
            'ward_room_no' => 'ICU-2',
            'diagnosis' => 'Acute Gastroenteritis',
            'consultant' => 'DR. JAYAPRAKASH',
        ]);

        $this->createAdmission([
            'patient_name' => 'PRIOR DISCHARGED CASE',
            'uhid' => 'GH-OLD-104',
            'ip_no' => 'IP-OLD-104',
            'admission_at' => '2026-11-20 09:00:00',
            'discharge_at' => '2026-11-25 18:00:00',
            'surgery_type' => 'medical',
        ]);

        $resp = $this->actingAs($this->user)->get('/reports/day?date=' . $targetDate);
        $resp->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/DayReport')
                ->where('metrics.admissions_count', 1)
                ->where('metrics.discharges_section_count', 1)
                ->where('metrics.inpatients_count', 2)
                ->where('sections.admissions.0.patient_name', 'NEW ADMIT DAY PATIENT')
                ->where('sections.discharges.0.patient_name', 'DISCHARGED DAY PATIENT')
                ->where('sections.discharges.0.stay_days', 3)
                ->has('sections.inpatients', 2)
            );

        // Verify continuing prior-day inpatient is present under inpatients census with stay days
        $inpatientsCsvResp = $this->actingAs($this->user)->get('/reports/day?date=' . $targetDate . '&section=inpatients&export=csv');
        $inpatientsCsvResp->assertOk();
        $inpatientsCsv = $inpatientsCsvResp->streamedContent();
        $this->assertStringContainsString('CONTINUING WARD INPATIENT', $inpatientsCsv);
        $this->assertStringContainsString('NEW ADMIT DAY PATIENT', $inpatientsCsv);
        $this->assertStringNotContainsString('DISCHARGED DAY PATIENT', $inpatientsCsv);
        $this->assertStringNotContainsString('PRIOR DISCHARGED CASE', $inpatientsCsv);

        $dischargesCsv = $this->actingAs($this->user)
            ->get('/reports/day?date=' . $targetDate . '&section=discharges&export=csv')
            ->streamedContent();
        $this->assertStringContainsString('DISCHARGED DAY PATIENT', $dischargesCsv);
        $this->assertStringContainsString('Afebrile, vitals stable', $dischargesCsv);
        $this->assertStringContainsString('Sister Devi', $dischargesCsv);
        $this->assertStringContainsString('Kala MRD', $dischargesCsv);
        $this->assertStringContainsString('03.12.2026', $dischargesCsv);
        $this->assertStringNotContainsString('CONTINUING WARD INPATIENT', $dischargesCsv);

        $admissionsCsv = $this->actingAs($this->user)
            ->get('/reports/day?date=' . $targetDate . '&section=admissions&export=csv')
            ->streamedContent();
        $this->assertStringContainsString('NEW ADMIT DAY PATIENT', $admissionsCsv);
        $this->assertStringContainsString('RAMESH (Husband)', $admissionsCsv);
        $this->assertStringContainsString('9876543210', $admissionsCsv);
        $this->assertStringContainsString('BP: 120/80', $admissionsCsv);

        // Verify full 3-section Blade HTML renders all sections cleanly
        $html = view('pdf.day-report-image', [
            'sections' => [
                'admissions' => collect([$newAdmit->load('newborns')]),
                'discharges' => collect([$discharged->load('newborns')]),
                'inpatients' => collect([$newAdmit->load('newborns'), $continuingInpatient->load('newborns')]),
            ],
            'sectionFilter' => 'all',
            'metrics' => [
                'admissions_count' => 1,
                'discharges_section_count' => 1,
                'inpatients_count' => 2,
                'deliveries_count' => 1,
                'babies_count' => 0,
                'surgeries_count' => 1,
                'medical_count' => 1,
                'census_date' => $targetDate,
            ],
            'periodLabel' => 'Date: 26.11.2026',
            'basisLabel' => 'Complete Daily Register (Admissions • Discharges • Inpatients)',
            'logoDataUri' => null,
        ])->render();

        $this->assertStringContainsString('1. Complete Admission Details', $html);
        $this->assertStringContainsString('2. Complete Discharge Details', $html);
        $this->assertStringContainsString('3. Complete Inpatient Details', $html);
        $this->assertStringContainsString('NEW ADMIT DAY PATIENT', $html);
        $this->assertStringContainsString('DISCHARGED DAY PATIENT', $html);
        $this->assertStringContainsString('CONTINUING WARD INPATIENT', $html);
    }

    public function test_mrd_report_filters_renders_and_exports_csv_png_and_pdf(): void
    {
        $mrdSigned = $this->createAdmission([
            'patient_name' => 'MRD SIGNED PATIENT',
            'uhid' => 'GH-MRD-001',
            'ip_no' => '2026/9901',
            'mrd_no' => 'MRD-2026/9901',
            'icd_code' => 'N20.1',
            'diagnosis' => 'Ureteric Calculus',
            'surgery_type' => 'other',
            'procedure_done' => 'RIGHT URSL & DJ STENTING',
            'admission_at' => '2026-11-24 09:00:00',
            'discharge_at' => '2026-11-26 15:00:00',
            'payment_mode' => 'Insurance',
            'format' => 'cmchistn',
            'insurance_type' => 'CMCHISTN',
            'insurance_company' => 'Star Health / TN',
            'handover_name' => 'Sister Meena',
            'floor_incharge_name' => 'Matron Lakshmi',
            'mrd_incharge_name' => 'MRD Officer Selvam',
            'status' => 'generated',
        ]);

        $mrdPending = $this->createAdmission([
            'patient_name' => 'MRD PENDING PATIENT',
            'uhid' => 'GH-MRD-002',
            'ip_no' => '2026/9902',
            'diagnosis' => 'Acute Appendicitis',
            'surgery_type' => 'other',
            'payment_mode' => 'Cash',
            'mrd_incharge_name' => null,
            'status' => 'draft',
        ]);

        $this->actingAs($this->user)
            ->get('/reports/mrd')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/MrdReport')
                ->where('metrics.total_files', 2)
                ->where('metrics.mrd_signed_count', 1)
                ->where('metrics.icd_coded_count', 1)
                ->where('metrics.insurance_count', 1)
                ->where('metrics.cash_count', 1)
            );

        // Filter by MRD handover status = signed
        $this->actingAs($this->user)
            ->get('/reports/mrd?handover_status=signed')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('records.total', 1)
                ->where('records.data.0.patient_name', 'MRD SIGNED PATIENT')
                ->where('records.data.0.resolved_mrd_no', 'MRD-2026/9901')
            );

        // Search by ICD code
        $this->actingAs($this->user)
            ->get('/reports/mrd?search=N20.1')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('records.total', 1)
                ->where('records.data.0.id', $mrdSigned->id)
            );

        // CSV export contains exact MRD register columns
        $csvResp = $this->actingAs($this->user)->get('/reports/mrd?handover_status=signed&export=csv');
        $csvResp->assertOk();
        $csv = $csvResp->streamedContent();
        $this->assertStringContainsString('"MRD File No","Patient Name",UHID,IPNO', $csv);
        $this->assertStringContainsString('MRD-2026/9901', $csv);
        $this->assertStringContainsString('N20.1', $csv);
        $this->assertStringContainsString('MRD Officer Selvam', $csv);
        $this->assertStringNotContainsString($mrdPending->patient_name, $csv);

        // Full Playwright PNG image export
        $imgResp = $this->actingAs($this->user)->get('/reports/mrd?handover_status=signed&export=image');
        $imgResp->assertOk();
        $this->assertEquals('image/png', (string) $imgResp->headers->get('content-type'));
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", (string) $imgResp->getContent());

        // Full Playwright A4 Landscape PDF export
        $pdfResp = $this->actingAs($this->user)->get('/reports/mrd?handover_status=signed&export=pdf');
        $pdfResp->assertOk();
        $this->assertEquals('application/pdf', (string) $pdfResp->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', (string) $pdfResp->getContent());
    }
}
