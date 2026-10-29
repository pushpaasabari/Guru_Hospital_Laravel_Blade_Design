<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnithaTwinDeliverySeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::where('role', 'doctor')->first() ?? User::first();

        $admission = Admission::updateOrCreate(
            ['ip_no' => '2026/567'],
            [
                'patient_name' => 'Mrs. Anitha',
                'uhid' => '2023/3931',
                'ip_no' => '2026/567',
                'age' => '23',
                'sex' => 'Female',
                'address' => "W/O Sarath\nVeerasanur",
                'contact' => '7373667465',
                'admission_at' => '2026-08-26 02:45:00',
                'procedure_at' => '2026-08-26 03:18:00',
                'discharge_at' => '2026-08-29 16:00:00',
                'consultant' => 'Dr. Ambika MS OG',
                'department' => 'Obstetrics & Gynaecology',
                'ward_room_no' => 'Postnatal Ward Bed #2',
                'format' => 'general',
                'surgery_type' => 'delivery',
                'diagnosis' => 'G3P1L1A1 / TWINS / Preterm / PROM',
                'clinical_history' => "G3P1L1A1 / TWINS / EDD 22-9-2026 / FH Good / admitted for safe confinement\nC/O abdominal Pain",
                'examination' => "O/E – A febrile, No Pallor, No Pedal edema / CVS & RS – NAD\nP/A: Uterus Overdistended, Not Acting, Both FH good",
                'procedure_name' => 'Labour natural with episiotomy (Twin A) & Emergency LSCS (Twin B)',
                'procedure_done' => 'Natural breech delivery with episiotomy (Twin A) + Emergency LSCS (Twin B)',
                'surgeon' => 'Dr. Ambika MS OG',
                'anaesthetist' => 'Dr. M.S Karthick',
                'assisted_by' => 'Staff Nurse Anitha',
                'anaesthesia_type' => 'SA',
                'procedure_note' => "Labour natural with episiotomy with good uterine contraction, full cervix dilated. After putting episiotomy, Patient Delivered a alive Twin A male baby By Breech, Baby Handed over to pediatrician. Twin B baby not progressed Delivered Hence Plaanned To proceed C-Section\nUnder SA, Abdomen opened in layers by SPT incision. LSCS done to deliver a alive Twin B male baby, cried soon after birth. Baby Handed over to pediatrician AMTSL followed. Uterine wound closed in 2 layers. After securing perfect Hemostasis, Abdomen closed in layer. AMTSL Followed. Uterus firm & contracted well. Episiotomy wound closed in layers. No Undue Bleeding P/V.",
                'incision' => 'Episiotomy (Twin A) & Suprapubic Transverse (SPT) incision (Twin B)',
                'findings' => 'Labour natural with episiotomy. Twin A delivered by Breech. Twin B obstructed labor requiring emergency LSCS. Alive twin male babies delivered.',
                'treatment_course' => "Treated with IV Fluids\nInj-Pipzo 4.5 g BD Iv\nInj-Metrogyl 100mg BD\nInj-Pan 40 Iv BD",
                'investigations' => "Hb : 12.9 mgs %\nBG : O positive\nHCV : Negative\nHIV : Negative\nVDRL : Negative\nHBsAG : Negative\nUREA : 9.7 g/dl\nCREATININE : 0.86 mg/dl\nURIC ACID : 4.3 mg/dl\nRBS : 100.8 mg/dl",
                'discharge_condition' => "Pt’s general condition good\n- BP: 110/70 mmHg\n- Pulse 86 b/mt\nClinical Examination : PA - Uterus contracted well\n- No undue Bleeding pv",
                'medication_orders' => "* Tab. Cefixime + sulbactum (1-0-1) x 7 Days - After food\n* Tab. Acendol-P (1-0-1) x 7 Days - After food\n* Cap. Cyra-D (1-0-1) x 7 Days - Before food",
                'discharge_advice' => "• Immunisation\n• Exclusive breast feeding for 6 months\n• Post Natal Exercise\n• Review after one week (03/09/2026)",
                'follow_up' => 'Review after one week (03/09/2026)',
                'mrd_no' => 'MRD-2026/567',
                'payment_mode' => 'Cash / General',
                'handover_name' => 'Staff Nurse Anitha',
                'floor_incharge_name' => 'Nursing Supervisor Mary',
                'mrd_incharge_name' => 'MRD Officer Kumar',
                'status' => 'approved',
                'created_by' => $doctor->id,
                'approved_by' => $doctor->id,
                'approved_at' => '2026-08-29 15:30:00',
            ]
        );

        $admission->newborns()->delete();

        // Twin A
        $admission->newborns()->create([
            'sex' => 'Male',
            'birth_at' => '2026-08-26 03:18:00',
            'birth_weight_kg' => 2.25,
            'apgar_1_min' => 7,
            'apgar_5_min' => 8,
            'neonatal_care' => 'RDS(+), Twin A Baby shifted to NICU',
            'notes' => 'Baby Twin A: Alive Male baby on 26/08/2026 at 03.18 AM delivered by Breech with episiotomy. Handed over to pediatrician.',
        ]);

        // Twin B
        $admission->newborns()->create([
            'sex' => 'Male',
            'birth_at' => '2026-08-26 03:44:00',
            'birth_weight_kg' => 2.25,
            'apgar_1_min' => 8,
            'apgar_5_min' => 9,
            'neonatal_care' => 'Cried soon after birth. Baby Handed over to pediatrician AMTSL followed.',
            'notes' => 'Baby Twin B: Alive Male baby on 26/08/2026 at 03.44 AM delivered by Emergency LSCS under SA.',
        ]);

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. Ambika MS OG.',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-08-29 15:30:00',
        ]);
    }
}

