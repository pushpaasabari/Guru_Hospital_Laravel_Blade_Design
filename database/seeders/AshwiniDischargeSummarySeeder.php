<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Database\Seeder;

class AshwiniDischargeSummarySeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::where('role', 'doctor')->first() ?? User::first();

        $admission = Admission::updateOrCreate(
            ['ip_no' => '2026/582'],
            [
                'patient_name' => 'Mrs. Ashwini',
                'uhid' => '2022/151',
                'ip_no' => '2026/582',
                'age' => '26',
                'sex' => 'Female',
                'address' => "W/O Thirukumaran\nOdappatti, Dharmapuri",
                'contact' => '9952517589',
                'admission_at' => '2026-08-29 20:51:00',
                'procedure_at' => '2026-08-29 21:25:00',
                'discharge_at' => '2026-09-01 16:00:00',
                'consultant' => 'Dr. Ambika M.S (O&G)',
                'department' => 'Obstetrics & Gynaecology',
                'ward_room_no' => 'Postnatal Ward Bed #4',
                'format' => 'general',
                'surgery_type' => 'delivery',
                'diagnosis' => 'G3P1L1A1 / Previous LSCS at term',
                'clinical_history' => "G3P1L1A1 / Previous LSCS / EDD 18.09.2026 / FH Good / Admitted For Safe Confinement",
                'examination' => "O/E – No Pallor, No Pedal edema, CVS & RS – NAD\nP/A: Uterus term, Not acting, Head Mobile, FH good, SPT scar (+)",
                'procedure_name' => 'Emergency LSCS',
                'surgeon' => 'Dr. Ambika M.S (O&G)',
                'pediatrician_name' => 'Dr. Nithyapriya (Paediatrician)',
                'anaesthetist' => 'Dr. M.S Karthick',
                'assistant' => 'Miss Soudnharya',
                'assisted_by' => 'Miss Soudnharya',
                'anaesthesia_type' => 'SA',
                'procedure_note' => "Under SA, Abdomen opened in layers by SPT incision. LSCS done to deliver alive Female baby, cried soon after birth. Baby Handed over to pediatrician AMTSL followed. Uterine wound closed in 2 layers. After securing perfect Hemostasis, Abdomen closed in layers. No undue bleeding P/V.",
                'incision' => 'Suprapubic Transverse (SPT) incision',
                'findings' => 'Lower uterine segment thinned out, previous scar intact, alive female baby delivered.',
                'treatment_course' => "Treated with IV Fluids\nInj. Cefbact 1.5g BD IV\nInj. Diclo IM (SOS)\nInj. Metrogyl 100mg TDS IV\nInj. Pan 40mg IV BD",
                'investigations' => "Hb : 10.6 mgs%\nHIV : Negative\nVDRL : Negative\nHBsAG : Negative\nBG : B positive\nECG : Normal\nRBS : 101.6 mg/dl\nUrea : 24.3 mg/dl\nCreatinine : 0.56 mg/dl\nUric Acid : 2.69 mg/dl",
                'discharge_condition' => "Pt’s general condition good\n- BP: 120/80 mmHg / Pulse 88 b/min / SpO2 98%\n- LSCS wound healthy\n- Uterus contracted well. No undue bleeding per Vagina",
                'medication_orders' => "* Tab. Clavam 625 mg (1-1-1) x 7 Days - After food\n* Tab. Alonac-Sp (1-0-1) x 7 Days - After food\n* Cap. Cyra D (1-1-1) x 7 Days - Before food",
                'discharge_advice' => "• Immunisation\n• Exclusive breast feeding for 6 months\n• Review after one week for suture removal (06/09/2026)\n• Post natal exercise",
                'follow_up' => 'Review After one week For Suture Removal (06/09/2026)',
                'mrd_no' => 'MRD-2026/582',
                'payment_mode' => 'Cash / General',
                'handover_name' => 'Staff Nurse Anitha',
                'floor_incharge_name' => 'Nursing Supervisor Mary',
                'mrd_incharge_name' => 'MRD Officer Kumar',
                'status' => 'approved',
                'created_by' => $doctor->id,
                'approved_by' => $doctor->id,
                'approved_at' => '2026-09-01 15:45:00',
            ]
        );

        $admission->newborns()->delete();
        $admission->newborns()->create([
            'sex' => 'Female',
            'birth_at' => '2026-08-29 21:25:00',
            'birth_weight_kg' => '2.80',
            'apgar_1_min' => 7,
            'apgar_5_min' => 8,
            'neonatal_care' => 'Baby kept in O2 warmer for 13 hrs; cried soon after birth.',
            'notes' => 'Alive Female baby on 29.08.2026 at 09.25 PM, handed over to Paediatrician Dr. Nithyapriya.',
        ]);

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. Ambika M.S (O&G).',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-09-01 15:45:00',
        ]);
    }
}

