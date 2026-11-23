<?php

namespace Database\Seeders;

use App\Models\ClinicalStaff;
use Illuminate\Database\Seeder;

class ClinicalStaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffMembers = [
            [
                'name' => 'Dr. N. Jayaprakash',
                'category' => 'consultant_doctor',
                'roles' => ['consultant_doctor', 'surgeon'],
                'qualification' => 'M.S (Gen Surg), M.Ch (Uro)',
                'doctor_reg_no' => 'TNMC-38912',
                'department' => 'General Surgery & Urology',
                'phone' => '9443224466',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Ambika M.S (O&G)',
                'category' => 'consultant_doctor',
                'roles' => ['consultant_doctor', 'surgeon'],
                'qualification' => 'M.S (O&G)',
                'doctor_reg_no' => 'TNMC-45920',
                'department' => 'Obstetrics & Gynaecology',
                'phone' => '9443212345',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Karthik Rajan MS, MCh',
                'category' => 'surgeon',
                'roles' => ['consultant_doctor', 'surgeon'],
                'qualification' => 'MS, MCh (Surg Gastro)',
                'doctor_reg_no' => 'TNMC-64821',
                'department' => 'General & Laparoscopic Surgery',
                'phone' => '9842154321',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Priya Sundaram MD, DGO',
                'category' => 'consultant_doctor',
                'roles' => ['consultant_doctor', 'surgeon'],
                'qualification' => 'MD, DGO',
                'doctor_reg_no' => 'TNMC-71934',
                'department' => 'Obstetrics & Gynaecology',
                'phone' => '9843367890',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Nithyapriya (Paediatrician)',
                'category' => 'consultant_pediatrician',
                'roles' => ['consultant_pediatrician', 'consultant_doctor'],
                'qualification' => 'MD (Paediatrics)',
                'doctor_reg_no' => 'TNMC-58210',
                'department' => 'Paediatrics & Neonatology',
                'phone' => '9944112233',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. S. Ramesh MD (Pediatrics)',
                'category' => 'consultant_pediatrician',
                'roles' => ['consultant_pediatrician'],
                'qualification' => 'MBBS, MD (Pediatrics)',
                'doctor_reg_no' => 'TNMC-63118',
                'department' => 'Paediatrics & Neonatology',
                'phone' => '9842198765',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. M.S Karthick',
                'category' => 'anaesthetist',
                'roles' => ['anaesthetist'],
                'qualification' => 'MD (Anaesthesia), DA',
                'doctor_reg_no' => 'TNMC-53109',
                'department' => 'Anaesthesiology',
                'phone' => '9789012345',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. R. Vignesh MBBS, DA',
                'category' => 'anaesthetist',
                'roles' => ['anaesthetist', 'assisted_anaesthetist'],
                'qualification' => 'MBBS, DA',
                'doctor_reg_no' => 'TNMC-61402',
                'department' => 'Anaesthesiology',
                'phone' => '9677890123',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. K. Senthil MBBS',
                'category' => 'assisted_surgeon',
                'roles' => ['assisted_surgeon'],
                'qualification' => 'MBBS, Resident Surgeon',
                'doctor_reg_no' => 'TNMC-78901',
                'department' => 'General Surgery',
                'phone' => '9566781234',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. A. Meenakshi MBBS',
                'category' => 'assisted_surgeon',
                'roles' => ['assisted_surgeon'],
                'qualification' => 'MBBS, Resident Medical Officer',
                'doctor_reg_no' => 'TNMC-81230',
                'department' => 'Obstetrics & Gynaecology',
                'phone' => '9442119988',
                'is_active' => true,
            ],
            [
                'name' => 'Staff Nurse Anitha',
                'category' => 'staff_nurse',
                'roles' => ['staff_nurse', 'assisted_surgeon'],
                'qualification' => 'B.Sc Nursing',
                'doctor_reg_no' => 'TNC-84210',
                'department' => 'Inpatient Ward',
                'phone' => '9842233445',
                'is_active' => true,
            ],
            [
                'name' => 'Miss Soudnharya',
                'category' => 'staff_nurse',
                'roles' => ['staff_nurse', 'assisted_surgeon'],
                'qualification' => 'GNM, Staff Nurse',
                'doctor_reg_no' => 'TNC-91204',
                'department' => 'Operation Theatre',
                'phone' => '9788112244',
                'is_active' => true,
            ],
            [
                'name' => 'Staff Nurse Deepa',
                'category' => 'staff_nurse',
                'roles' => ['staff_nurse', 'assisted_anaesthetist'],
                'qualification' => 'B.Sc Nursing',
                'doctor_reg_no' => 'TNC-95612',
                'department' => 'Operation Theatre',
                'phone' => '9629445566',
                'is_active' => true,
            ],
            [
                'name' => 'Staff Nurse Kavitha',
                'category' => 'staff_nurse',
                'roles' => ['staff_nurse'],
                'qualification' => 'B.Sc Nursing',
                'doctor_reg_no' => 'TNC-88419',
                'department' => 'Postnatal Ward',
                'phone' => '9597334455',
                'is_active' => true,
            ],
            [
                'name' => 'OT Tech Selvam',
                'category' => 'ot_technician',
                'roles' => ['ot_technician'],
                'qualification' => 'Diploma in OT Technology',
                'doctor_reg_no' => 'OTT-1029',
                'department' => 'Operation Theatre',
                'phone' => '9488556677',
                'is_active' => true,
            ],
            [
                'name' => 'OT Tech Murugan',
                'category' => 'ot_technician',
                'roles' => ['ot_technician'],
                'qualification' => 'Diploma in OT Technology',
                'doctor_reg_no' => 'OTT-1084',
                'department' => 'Operation Theatre',
                'phone' => '9345778899',
                'is_active' => true,
            ],
        ];

        foreach ($staffMembers as $staff) {
            ClinicalStaff::updateOrCreate(
                ['name' => $staff['name']],
                $staff
            );
        }
    }
}

