<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Newborn;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ManjupriyaOperationNotesSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('role', 'nursing_staff')->first() ?? User::first();
        $doctor = User::where('role', 'doctor')->first() ?? User::first();

        $admission = Admission::updateOrCreate(
            ['ip_no' => '2026/778'],
            [
                'patient_name' => 'Mrs. MANJUPRIYA',
                'uhid' => '2026/2952',
                'age' => '34',
                'sex' => 'Female',
                'address' => 'W/O KAMALGANESH',
                'contact' => '9677371431',
                'admission_at' => Carbon::parse('2026-10-16 08:30:00'),
                'procedure_at' => Carbon::parse('2026-10-16 13:26:00'),
                'discharge_at' => Carbon::parse('2026-10-19 16:00:00'),
                'consultant' => 'Dr.Ambika MS(OG)',
                'department' => 'Obstetrics & Gynaecology',
                'ward_room_no' => 'Post-Op Ward',
                'diagnosis' => 'G2A1/GDM/',
                'format' => 'general',
                'surgery_type' => 'delivery',
                'clinical_history' => '34 Yrs Female, G2A1 with Gestational Diabetes Mellitus admitted for safe confinement.',
                'examination' => 'O/E: Conscious, Oriented, BP: 120/80 mmHg, PR: 84 bpm, Spo2: 99%',
                'investigations' => "Hb : 11.2 mgs%\nRBS : 124 mg/dl\nBlood Group : O Positive\nHIV : Negative\nHBsAg : Negative\nVDRL : Negative",
                'treatment_course' => 'Treated with IV Fluids, Antibiotics, Analgesics and PPI post LSCS.',
                'discharge_condition' => 'Pt general condition good. Afebrile. LSCS wound healthy.',
                'medication_orders' => "*Tab. Clavam 625mg (1-0-1) x 5 days\n*Tab. Zerodol-SP (1-0-1) x 5 days\n*Cap. Pan-D (1-0-0) x 5 days",
                'discharge_advice' => "*Exclusive breast feeding for 6 months\n*Review for suture removal after 7 days",
                'follow_up' => 'Review in OPD on 23/10/2026',

                // Operation Theatre Exact Details
                'procedure_name' => 'ELECTIVE LSCS',
                'procedure_done' => '',
                'icd_code' => '',
                'surgeon' => 'Dr.Ambika MS(OG)',
                'anaesthetist' => 'Dr.S.G.Arun kumar',
                'assisted_by' => 'Mr. Vijayakumar',
                'assistant' => 'Mr. Vijayakumar',
                'anaesthesia_type' => 'Spinal',
                'procedure_note' => "Under SA, abdomen opened in layers by SPT incision. LSCS done in the usual way to delivery alive male baby,\nCried after birth. AMTSL followed. PPIUCD kept after getting consent from patient & husband. Uterus wound\nclosed in 2 layers. Uterus contracted well After securing perfect Hemostasis, Abdomen closed in layers. No\nundue bleeding P/V. No intra operative complications.",
                'incision' => 'SPT incision',
                'findings' => '',
                'complications' => '',
                'blood_loss_ml' => null,
                'specimen' => '',
                'drain' => '',
                'postoperative_orders' => '',

                'mrd_no' => 'MRD-2026-778',
                'payment_mode' => 'Cash',
                'status' => 'approved',
                'created_by' => $creator->id,
                'approved_by' => $doctor->id,
                'approved_at' => Carbon::parse('2026-10-16 15:00:00'),
            ]
        );

        Newborn::updateOrCreate(
            ['admission_id' => $admission->id],
            [
                'sex' => 'Female',
                'birth_at' => Carbon::parse('2026-10-16 13:26:00'),
                'birth_weight_kg' => 2.650,
                'apgar_1_min' => 8,
                'apgar_5_min' => 9,
                'neonatal_care' => '',
                'notes' => 'Alive Female baby',
            ]
        );
    }
}

