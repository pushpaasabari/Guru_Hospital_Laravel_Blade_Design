<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionInputNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $nurse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nurse = User::create([
            'name' => 'Nurse Anitha',
            'email' => 'nurse.norm@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
            'is_active' => true,
        ]);
    }

    public function test_admission_store_and_update_always_store_specified_fields_in_uppercase(): void
    {
        $response = $this->actingAs($this->nurse)->post(route('admissions.store'), [
            'patient_name' => 'Mrs. Anitha Sarath',
            'uhid' => '2026/1001',
            'ip_no' => 'IP-CAP-01',
            'age' => '24',
            'sex' => 'Female',
            'contact' => '7373667465',
            'address_line1' => 'Door No. 12, West Street',
            'address_line2' => 'Veerasanur Village',
            'address_line3' => 'Dharmapuri - 636701',
            'admission_at' => '2026-11-01 09:30:00',
            'consultant' => 'Dr. Ambika M.S (O&G)',
            'department' => 'Obstetrics & Gynaecology',
            'diagnosis' => 'G3P1L1A1 / TWINS / Preterm',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'picme_no' => '331024567890',
            'surgeon' => 'Dr. Ambika M.S (O&G)',
            'assisted_surgeon' => 'Dr. A. Meenakshi',
            'anaesthetist' => 'Dr. M.S Karthick',
            'assisted_anaesthetist' => 'Dr. R. Vignesh',
            'assistant' => 'Dr. Senthil',
            'assisted_by' => 'Staff Nurse Anitha',
            'staff_nurse' => 'Miss Soudnharya',
            'ot_technician' => 'OT Tech Selvam',
            'pediatrician_name' => 'Dr. Nithyapriya',
            'anaesthesia_type' => 'Spinal Anesthesia (SA)',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('admissions', [
            'ip_no' => 'IP-CAP-01',
            'patient_name' => 'MRS. ANITHA SARATH',
            'address_line1' => 'DOOR NO. 12, WEST STREET',
            'address_line2' => 'VEERASANUR VILLAGE',
            'address_line3' => 'DHARMAPURI - 636701',
            'address' => 'DOOR NO. 12, WEST STREET, VEERASANUR VILLAGE, DHARMAPURI - 636701',
            'contact' => '7373667465',
            'consultant' => 'DR. AMBIKA M.S (O&G)',
            'surgeon' => 'DR. AMBIKA M.S (O&G)',
            'assisted_surgeon' => 'DR. A. MEENAKSHI',
            'anaesthetist' => 'DR. M.S KARTHICK',
            'assisted_anaesthetist' => 'DR. R. VIGNESH',
            'assistant' => 'DR. SENTHIL',
            'assisted_by' => 'STAFF NURSE ANITHA',
            'staff_nurse' => 'MISS SOUDNHARYA',
            'ot_technician' => 'OT TECH SELVAM',
            'pediatrician_name' => 'DR. NITHYAPRIYA',
            'diagnosis' => 'G3P1L1A1 / TWINS / Preterm',
            'anaesthesia_type' => 'Spinal Anesthesia (SA)',
        ]);

        $admission = Admission::where('ip_no', 'IP-CAP-01')->firstOrFail();

        $updateResponse = $this->actingAs($this->nurse)->put(route('admissions.update', $admission), [
            'patient_name' => 'Mrs. Anitha Updated',
            'uhid' => '2026/1001',
            'ip_no' => 'IP-CAP-01',
            'age' => '24',
            'sex' => 'Female',
            'contact' => '9876543210',
            'address_line1' => '18B North Main Road',
            'address_line2' => 'Pennagaram',
            'address_line3' => 'Tamil Nadu - 636810',
            'admission_at' => '2026-11-01 09:30:00',
            'consultant' => 'Dr. Priya Sundaram',
            'diagnosis' => 'Updated mixed case diagnosis',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'picme_no' => '331024567890',
            'next_step' => 'stay',
        ]);

        $updateResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'patient_name' => 'MRS. ANITHA UPDATED',
            'address_line1' => '18B NORTH MAIN ROAD',
            'address_line2' => 'PENNAGARAM',
            'address_line3' => 'TAMIL NADU - 636810',
            'address' => '18B NORTH MAIN ROAD, PENNAGARAM, TAMIL NADU - 636810',
            'contact' => '9876543210',
            'consultant' => 'DR. PRIYA SUNDARAM',
            'diagnosis' => 'Updated mixed case diagnosis',
        ]);
    }

    public function test_surgery_stage_update_always_stores_surgical_and_anaesthesia_team_in_uppercase(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Lakshmi V',
            'uhid' => '2026/1002',
            'ip_no' => 'IP-CAP-02',
            'admission_at' => now()->subDay(),
            'diagnosis' => 'Acute Appendicitis',
            'format' => 'general',
            'surgery_type' => 'other',
            'status' => 'draft',
            'created_by' => $this->nurse->id,
        ]);

        $response = $this->actingAs($this->nurse)->put(route('admissions.surgery.update', $admission), [
            'procedure_at' => now()->toDateTimeString(),
            'procedure_name' => 'Laparoscopic Appendectomy',
            'surgeon' => 'Dr. Karthik Rajan',
            'assisted_surgeon' => 'Dr. K. Senthil',
            'anaesthetist' => 'Dr. M.S Karthick',
            'assisted_anaesthetist' => 'Dr. R. Vignesh',
            'staff_nurse' => 'Staff Nurse Deepa',
            'ot_technician' => 'OT Tech Murugan',
            'pediatrician_name' => 'Dr. S. Ramesh',
            'assistant' => 'Dr. Junior',
            'assisted_by' => 'Staff Nurse Kavitha',
            'anaesthesia_type' => 'General Anaesthesia',
            'next_step' => 'stay',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'surgeon' => 'DR. KARTHIK RAJAN',
            'assisted_surgeon' => 'DR. K. SENTHIL',
            'anaesthetist' => 'DR. M.S KARTHICK',
            'assisted_anaesthetist' => 'DR. R. VIGNESH',
            'staff_nurse' => 'STAFF NURSE DEEPA',
            'ot_technician' => 'OT TECH MURUGAN',
            'pediatrician_name' => 'DR. S. RAMESH',
            'assistant' => 'DR. JUNIOR',
            'assisted_by' => 'STAFF NURSE KAVITHA',
            'anaesthesia_type' => 'General Anaesthesia',
        ]);
    }

    public function test_direct_model_create_normalizes_uppercase_fields_and_digits_only_contact(): void
    {
        $admission = Admission::create([
            'patient_name' => 'selvamani k',
            'address_line1' => 'line one',
            'address_line2' => 'line two',
            'address_line3' => 'line three',
            'consultant' => 'dr. ambika',
            'surgeon' => 'dr. karthik',
            'contact' => '+91-98765 43210',
            'uhid' => '2026/1003',
            'ip_no' => 'IP-CAP-03',
            'admission_at' => now()->subDay(),
            'diagnosis' => 'Test',
            'format' => 'general',
            'surgery_type' => 'other',
            'created_by' => $this->nurse->id,
        ]);

        $this->assertEquals('SELVAMANI K', $admission->patient_name);
        $this->assertEquals('LINE ONE', $admission->address_line1);
        $this->assertEquals('LINE TWO', $admission->address_line2);
        $this->assertEquals('LINE THREE', $admission->address_line3);
        $this->assertEquals('LINE ONE, LINE TWO, LINE THREE', $admission->address);
        $this->assertEquals('DR. AMBIKA', $admission->consultant);
        $this->assertEquals('DR. KARTHIK', $admission->surgeon);
        $this->assertEquals('919876543210', $admission->contact);
    }

    public function test_phone_contact_rejects_non_numeric_values_on_store_and_update(): void
    {
        $invalidContacts = ['98765abc10', '+91-9876543210', '98765 43210', 'not-a-number'];

        foreach ($invalidContacts as $index => $badContact) {
            $storeResponse = $this->actingAs($this->nurse)->post(route('admissions.store'), [
                'patient_name' => 'Patient Phone Test',
                'uhid' => "2026/200{$index}",
                'ip_no' => "IP-PHN-{$index}",
                'address_line1' => 'Test Street',
                'contact' => $badContact,
                'admission_at' => '2026-11-01 09:00:00',
                'diagnosis' => 'Test',
                'format' => 'general',
                'surgery_type' => 'medical',
            ]);

            $storeResponse->assertSessionHasErrors('contact');
        }

        $admission = Admission::create([
            'patient_name' => 'Valid Phone Patient',
            'uhid' => '2026/2099',
            'ip_no' => 'IP-PHN-VALID',
            'contact' => '9876543210',
            'admission_at' => now()->subDay(),
            'diagnosis' => 'Test',
            'format' => 'general',
            'surgery_type' => 'medical',
            'created_by' => $this->nurse->id,
        ]);

        $updateInvalid = $this->actingAs($this->nurse)->put(route('admissions.update', $admission), [
            'patient_name' => 'Valid Phone Patient',
            'uhid' => '2026/2099',
            'ip_no' => 'IP-PHN-VALID',
            'address_line1' => 'Updated Street',
            'contact' => '98765-43210',
            'admission_at' => '2026-11-01 09:00:00',
            'diagnosis' => 'Test',
            'format' => 'general',
            'surgery_type' => 'medical',
            'next_step' => 'stay',
        ]);

        $updateInvalid->assertSessionHasErrors('contact');
        $this->assertEquals('9876543210', $admission->fresh()->contact);
    }
}

