<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Database\Seeder;

class AjithKumarDischargeSummarySeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::where('role', 'doctor')->first() ?? User::first();

        $admission = Admission::updateOrCreate(
            ['ip_no' => '2026/488'],
            [
                'patient_name' => 'Mr. Ajith kumar',
                'uhid' => '2026/2892',
                'ip_no' => '2026/488',
                'age' => '30',
                'sex' => 'Male',
                'address' => "S/O Madhu\nChinakkampatti,\nDharmapuri(Dt)",
                'contact' => '7604977115',
                'admission_at' => '2026-08-02 23:06:00',
                'procedure_at' => '2026-08-02 12:40:00',
                'discharge_at' => '2026-08-05 15:00:00',
                'consultant' => 'Dr. Jayaprakash M.S',
                'department' => 'General & Laparoscopic Surgery',
                'ward_room_no' => 'Male Surgical Ward Bed #12',
                'format' => 'general',
                'surgery_type' => 'other',
                'diagnosis' => 'Acute Appendicitis',
                'clinical_history' => '30 Years male admitted with C/O Abdomen pain Since one Weeks.',
                'examination' => 'Vital signs on 02/08/2026 : BP-100/60 mmhg, PR-90 bpm, Spo2-98%',
                'procedure_name' => 'Lap Appendectomy',
                'surgeon' => 'Dr. Jayaprakash M.S',
                'anaesthetist' => 'Dr. S.G. ArunKumar',
                'assistant' => 'Mr. Vijaykumar',
                'assisted_by' => 'Mr. Vijaykumar',
                'anaesthesia_type' => 'GA',
                'procedure_note' => 'Under GA, Supine Position. 10 mm Supra Umbilical / Two 5mm Working Port Placed. Appendix Found Perforated with fecolith noted Appendectomy done, DT-Placed, Port sites closed. No Intraoperative Complications.',
                'incision' => '10 mm Supra Umbilical / Two 5mm Working Ports Placed',
                'findings' => 'Appendix Found Perforated with fecolith noted.',
                'drain' => 'DT-Placed',
                'treatment_course' => "Inj-Tetcef 1.5g bd Iv\nInj-Para infusion (SOS) Iv\nInj-Pan 40 mg IV bd\nInj.Diclo Im sos",
                'investigations' => "Hb : 13.8mgs %   BG- A Positive\nRBS : 116.6 mg/dl\nUrea : 28.4 mg/dl\nCreatinine : 0.51 mg/dl\nUric Acid : 4.65 mg/dl\nHIV : Negative\nVDRL : Negative\nHBsAG : Negative",
                'discharge_condition' => "Pt’s general condition good\n- BP: 110/80mmHg\n- Pulse 134 b/mt/\n- Abdomen Soft",
                'medication_orders' => "* C. Cyra-D (1-0-1) x 5 Days- Before food\n* Tab. Clavam 625 mg (1-0-1) x 5 Days- After food\n* T. Zerodol.P (1-0-1) x 5 Days- After food",
                'discharge_advice' => "Review after 7days on OPD (09/08/2026)",
                'follow_up' => 'Review after 7days on OPD (09/08/2026)',
                'mrd_no' => 'MRD-2026/488',
                'payment_mode' => 'Cash / General',
                'handover_name' => 'Staff Nurse Anitha',
                'floor_incharge_name' => 'Nursing Supervisor Mary',
                'mrd_incharge_name' => 'MRD Officer Kumar',
                'status' => 'approved',
                'created_by' => $doctor->id,
                'approved_by' => $doctor->id,
                'approved_at' => '2026-08-05 14:30:00',
            ]
        );

        // Ensure no newborn records exist for 'other' surgery workflow
        $admission->newborns()->delete();

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. Jayaprakash M.S.',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-08-05 14:30:00',
        ]);
    }
}
