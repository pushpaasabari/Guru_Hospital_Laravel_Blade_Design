<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoHardcodedDischargeDefaultsTest extends TestCase
{
    use RefreshDatabase;

    private function createDoctor(): User
    {
        return User::create([
            'name' => 'Dr. Test Surgeon',
            'email' => 'surgeon.test@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'is_active' => true,
        ]);
    }

    public function test_empty_delivery_admission_does_not_leak_hardcoded_defaults(): void
    {
        $doctor = $this->createDoctor();

        $admission = Admission::create([
            'patient_name' => 'Blank Delivery Patient',
            'uhid' => 'GH-9001',
            'ip_no' => 'IP-9001',
            'age' => '26',
            'sex' => 'Female',
            'admission_at' => now()->subDays(2),
            'discharge_at' => now(),
            'diagnosis' => 'Primigravida at term',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'created_by' => $doctor->id,
            'approved_by' => $doctor->id,
            'approved_at' => now(),
            'status' => 'approved',
        ]);

        $condition = $admission->formatted_condition;
        $this->assertEquals('—', $condition['general']);
        $this->assertEquals('—', $condition['bp']);
        $this->assertEquals('—', $condition['pulse']);
        $this->assertEquals('—', $condition['pa']);

        $advice = $admission->formatted_advice_and_review;
        $this->assertSame([], $advice['advice_items']);
        $this->assertSame('', $advice['review_text']);
        $this->assertSame('', $advice['review_date']);
        $this->assertNull($advice['tamil_note']);

        $this->assertSame([], $admission->formatted_treatment_course);
        $this->assertSame([], $admission->formatted_investigations);
        $this->assertSame([], $admission->formatted_medications);
        $this->assertSame([], $admission->formatted_newborns);

        $html = view('pdf.discharge-summary', ['admission' => $admission])->render();
        $this->assertStringContainsString('BLANK DELIVERY PATIENT', $html);

        foreach ([
            'Mrs. Anitha',
            '2026/567',
            '2023/3931',
            '03/09/2026',
            '21.08.2026',
            'TNMC-64821',
            '110/70',
            '86 b/m',
            'Dr. Ambika',
            'Dr. M.S Karthick',
            'Obstetrics & Gynaecology',
            'Tab. Cefixime',
            'Inj. Pipzo',
            'Immunisation',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $html);
        }

        foreach (['general', 'cmchistn', 'employee', 'private'] as $format) {
            $previewHtml = view("documents.discharge-summaries.{$format}.delivery", ['admission' => $admission])->render();
            $this->assertStringNotContainsString('Dr. Ambika', $previewHtml);
            $this->assertStringNotContainsString('Dr. M.S Karthick', $previewHtml);
            $this->assertStringNotContainsString('Staff Nurse', $previewHtml);
            $this->assertStringNotContainsString('TNMC-64821', $previewHtml);
        }
    }

    public function test_empty_surgical_admission_does_not_leak_hardcoded_defaults(): void
    {
        $doctor = $this->createDoctor();

        $admission = Admission::create([
            'patient_name' => 'Blank Surgical Patient',
            'uhid' => 'GH-9002',
            'ip_no' => 'IP-9002',
            'age' => '45',
            'sex' => 'Male',
            'admission_at' => now()->subDays(2),
            'discharge_at' => now(),
            'diagnosis' => 'Simple Surgical Case',
            'format' => 'general',
            'surgery_type' => 'other',
            'created_by' => $doctor->id,
            'approved_by' => $doctor->id,
            'approved_at' => now(),
            'status' => 'approved',
        ]);

        $hist = $admission->formatted_surgical_past_history;
        $this->assertEquals('—', $hist['pulse']);
        $this->assertEquals('—', $hist['bp']);

        $inv = $admission->formatted_surgical_investigations;
        foreach ($inv['group1'] as $val) {
            $this->assertEquals('—', $val);
        }
        foreach ($inv['group2'] as $val) {
            $this->assertEquals('—', $val);
        }
        $this->assertSame([], $inv['imaging']);

        $this->assertSame([], $admission->formatted_surgical_drugs);

        $advice = $admission->formatted_surgical_advice;
        $this->assertEquals('Nil', $advice['implant']);
        $this->assertEquals('—', $advice['diet']);
        $this->assertEquals('—', $advice['exercise']);
        $this->assertSame('', $advice['review']);
        $this->assertSame('', $advice['tamil_note']);

        $html = view('pdf.discharge-summary', ['admission' => $admission])->render();
        $this->assertStringContainsString('BLANK SURGICAL PATIENT', $html);

        foreach ([
            'DR. N. JAYAPRAKASH',
            'DR. S.G. ARUN KUMAR',
            'MISS. SOUNDHARYA',
            '2026-1247',
            'RIGHT URSL',
            '07.08.2026',
            '21.08.2026',
            'N20.0',
            'Admitted for elective',
            'Admitted electively',
            'CVS: NAD',
            'T.Niftas',
            '15.5g/dl',
            '32.2mg/dl',
            'RIGHT LOWER URETERIC CALCULUS',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $html);
        }

        foreach (['general', 'cmchistn', 'employee', 'private'] as $format) {
            $previewHtml = view("documents.discharge-summaries.{$format}.other", ['admission' => $admission])->render();
            $this->assertStringNotContainsString('DR. N. JAYAPRAKASH', $previewHtml);
            $this->assertStringNotContainsString('DR. S.G. ARUN KUMAR', $previewHtml);
            $this->assertStringNotContainsString('MISS. SOUNDHARYA', $previewHtml);
            $this->assertStringNotContainsString('OT Staff Nurse', $previewHtml);
            $this->assertStringNotContainsString('Staff Nurse', $previewHtml);
        }
    }

    public function test_stent_admission_without_date_uses_date_placeholder_in_tamil_notice(): void
    {
        $doctor = $this->createDoctor();

        $admission = Admission::create([
            'patient_name' => 'Stent Patient',
            'uhid' => 'GH-9003',
            'ip_no' => 'IP-9003',
            'age' => '38',
            'sex' => 'Male',
            'admission_at' => now()->subDays(2),
            'discharge_at' => now(),
            'diagnosis' => 'Ureteric Calculus',
            'format' => 'general',
            'surgery_type' => 'other',
            'procedure_name' => 'URSL with DJ Stenting',
            'procedure_note' => 'Right DJ stent placed.',
            'created_by' => $doctor->id,
        ]);

        $advice = $admission->formatted_surgical_advice;
        $this->assertEquals('DJ Stent', $advice['implant']);
        $this->assertNotNull($advice['tamil_note']);
        $this->assertStringContainsString('(Date)', $advice['tamil_note']);
        $this->assertStringNotContainsString('21.08.2026', $advice['tamil_note']);
    }

    public function test_minimal_admission_discharge_summary_pdf_downloads_cleanly(): void
    {
        $doctor = $this->createDoctor();

        $admission = Admission::create([
            'patient_name' => 'PDF Render Test Patient',
            'uhid' => 'GH-9004',
            'ip_no' => 'IP-9004',
            'age' => '29',
            'sex' => 'Female',
            'admission_at' => now()->subDays(2),
            'discharge_at' => now(),
            'diagnosis' => 'Observation',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'created_by' => $doctor->id,
        ]);

        $response = $this->actingAs($doctor)
            ->get("/admissions/{$admission->id}/documents/pdf/discharge-summary");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}

