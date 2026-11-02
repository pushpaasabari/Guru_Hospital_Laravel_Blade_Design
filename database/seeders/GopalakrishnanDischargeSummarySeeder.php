<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Database\Seeder;

class GopalakrishnanDischargeSummarySeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::where('name', 'like', '%Jayaprakash%')->first();
        if (!$doctor) {
            $doctor = User::create([
                'name' => 'Dr. N. Jayaprakash MS',
                'email' => 'dr.jayaprakash@guruhospital.local',
                'password' => bcrypt('Doctor@Guru2026'),
                'role' => 'doctor',
                'doctor_reg_no' => 'TNMC-51240',
                'is_active' => true,
            ]);
        }

        $admission = Admission::updateOrCreate(
            ['ip_no' => '2026/507'],
            [
                'patient_name' => 'Mr. Gopalakrishnan',
                'uhid' => '2026/1247',
                'ip_no' => '2026/507',
                'age' => '37',
                'sex' => 'Male',
                'address' => "S/O Narasimman\nMittahanahalli",
                'address_line1' => 'S/O Narasimman',
                'address_line2' => 'Mittahanahalli',
                'contact' => '9578559426',
                'admission_at' => '2026-08-07 15:14:00',
                'procedure_at' => '2026-08-07 17:00:00',
                'discharge_at' => '2026-08-08 17:00:00',
                'consultant' => 'Dr. N. Jayaprakash',
                'department' => 'Urology & General Surgery',
                'ward_room_no' => 'Surgical Ward Bed #08',
                'format' => 'general',
                'surgery_type' => 'other',
                'diagnosis' => 'Right Lower Ureteric Calculus',
                'clinical_history' => "Right loin pain few months. Found to have Right Lower ureteric calculus. Admitted for elective Right URSL\n\nPast Medical History: DM: Nil | HT: Nil | Asthma: Nil | TB: Nil | Other: Nil\nPast Surgical History: Nil\nAllergies: Nil | Medications: Nil\nPulse: 82 b/min | BP: 110/70 mmHg",
                'examination' => "CVS: NAD | RS: Clear | Abdomen: Soft, no mass, no free fluid, no organomegaly.\nPulse: 82 b/min | BP: 110/70 mmHg",
                'procedure_name' => 'Right URSL & DJ Stenting',
                'procedure_done' => 'Right URSL & DJ Stenting',
                'icd_code' => 'N20.0',
                'surgeon' => 'Dr. N. Jayaprakash',
                'anaesthetist' => 'Dr. S.G. Arun Kumar',
                'assisted_by' => 'Miss. Soundharya',
                'assistant' => 'Miss. Soundharya',
                'anaesthesia_type' => 'SA',
                'procedure_note' => "Under SA, Urethrocystoscopy done with Ureteroscope. Normal bladder and Right ureteric orifice cannulated with guidewire and Right Ureteroscopy done. Calculus in the Right Lower ureter. Using Basket Under C-arm Guidance. ICL (Intra Corporeal Lithotripsy) Done. Stone Brokened completely with lithotripsy. 5F/26cm/DJ Stent inserted, Bladder catheterized with 16F Foley catheter. Clear urine drained.",
                'findings' => 'Calculus in Right Lower Ureter fragmented completely by ICL. DJ stent in situ.',
                'treatment_course' => "A Case of Right Lower Ureteric calculus. Admitted electively and underwent Right URSL. Received Inj.Tetcef 1.5 g IV as peri-operative antibiotic. Uneventful hospital stay.",
                'investigations' => "Hb : 15.5 g/dl\nUrea : 32.2 mg/dl\nCreatinine : 1.14 mg/dl\nUric Acid : 4.72 mg/dl\nRBS : 115.2 mg/dl\nHBs Ag : Negative\nHIV : Negative\nHCV : Negative\nBG : A positive\nUSG of KUB : Right Lower Ureteric Calculus",
                'discharge_condition' => "Stable\n- Pulse: 82 b/min\n- BP: 110/70 mmHg\n- Abdomen: Soft",
                'medication_orders' => "* T. Niftas (1-0-1) - 6 Nos\n* T. P650mg (1-1-1) - 9 Nos\n* C. Cyra-D (1-0-1) - 6 Nos - Before food\n* Zerodol P (1-0-1) - 6 Nos - After food\n* Review after 7 days (14.08.2026)",
                'discharge_advice' => "Implant: Right DJ Stent\nDiet: Normal Diet\nExercise: Normal activity\nEmergency Contact: Reception: 04342-262266, 7418084266\nWhen to Contact: Bleeding Urethra, Fever, Anything unusual\nReview: 21.08.2026 for stent removal\nவயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு 21.08.2026 அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.",
                'follow_up' => '21.08.2026 for stent removal (Review after 7 days on 14.08.2026)',
                'mrd_no' => '2026-1247',
                'payment_mode' => 'Cash / General',
                'handover_name' => 'Dr. N. Jayaprakash',
                'floor_incharge_name' => 'Staff Nurse Anitha',
                'mrd_incharge_name' => 'MRD Officer Kumar',
                'status' => 'approved',
                'created_by' => $doctor->id,
                'approved_by' => $doctor->id,
                'approved_at' => '2026-08-08 16:30:00',
            ]
        );

        $admission->newborns()->delete();

        DocumentApproval::create([
            'admission_id' => $admission->id,
            'action' => 'approved',
            'performed_by' => $doctor->id,
            'user_role' => 'doctor',
            'notes_or_correction_reason' => 'Clinically reviewed and approved by Dr. N. Jayaprakash.',
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-08-08 16:30:00',
        ]);
    }
}
