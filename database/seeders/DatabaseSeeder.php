<?php

namespace Database\Seeders;

use App\Models\TemplateVersion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Hospital Users
        $users = [
            [
                'name' => 'Hospital Administrator',
                'email' => 'admin@guruhospital.local',
                'password' => Hash::make('Admin@Guru2026'),
                'role' => 'administrator',
                'department' => 'Administration',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Karthik Rajan MS, MCh',
                'email' => 'dr.karthik@guruhospital.local',
                'password' => Hash::make('Doctor@Guru2026'),
                'role' => 'doctor',
                'doctor_reg_no' => 'TNMC-64821',
                'department' => 'General & Laparoscopic Surgery',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Priya Sundaram MD, DGO',
                'email' => 'dr.priya@guruhospital.local',
                'password' => Hash::make('Doctor@Guru2026'),
                'role' => 'doctor',
                'doctor_reg_no' => 'TNMC-71934',
                'department' => 'Obstetrics & Gynaecology',
                'is_active' => true,
            ],
            [
                'name' => 'Staff Nurse Anitha',
                'email' => 'nurse.anitha@guruhospital.local',
                'password' => Hash::make('Nurse@Guru2026'),
                'role' => 'nursing_staff',
                'department' => 'Inpatient Ward',
                'is_active' => true,
            ],
            [
                'name' => 'OT Tech Selvam',
                'email' => 'ot.selvam@guruhospital.local',
                'password' => Hash::make('OtTech@Guru2026'),
                'role' => 'ot_staff',
                'department' => 'Operation Theatre',
                'is_active' => true,
            ],
            [
                'name' => 'MRD Officer Kumar',
                'email' => 'mrd.kumar@guruhospital.local',
                'password' => Hash::make('MrdStaff@Guru2026'),
                'role' => 'mrd_staff',
                'department' => 'Medical Records',
                'is_active' => true,
            ],
            [
                'name' => 'Auditor Saravanan',
                'email' => 'auditor.saravanan@guruhospital.local',
                'password' => Hash::make('Auditor@Guru2026'),
                'role' => 'auditor',
                'department' => 'Quality & Audit',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }

        // 2. Hospital Template Versions
        $templates = [
            [
                'template_code' => 'general',
                'version_tag' => 'v1.0-draft',
                'title' => 'General Discharge Summary (Standard)',
                'description' => 'Standard inpatient discharge summary for cash and private billing.',
                'is_approved_by_hospital' => false,
                'effective_date' => now()->toDateString(),
            ],
            [
                'template_code' => 'cmchistn',
                'version_tag' => 'v1.0-draft',
                'title' => 'CMCHISTN Government Scheme Discharge Summary',
                'description' => 'Chief Minister Comprehensive Health Insurance Scheme Tamil Nadu compliant format.',
                'is_approved_by_hospital' => false,
                'effective_date' => now()->toDateString(),
            ],
            [
                'template_code' => 'private',
                'version_tag' => 'v1.0-draft',
                'title' => 'Private Insurance / TPA Discharge Summary',
                'description' => 'Comprehensive clinical layout for private mediclaim and corporate insurance audits.',
                'is_approved_by_hospital' => false,
                'effective_date' => now()->toDateString(),
            ],
            [
                'template_code' => 'employee',
                'version_tag' => 'v1.0-draft',
                'title' => 'Hospital Employee Healthcare Discharge Summary',
                'description' => 'Internal employee and dependent clinical discharge summary.',
                'is_approved_by_hospital' => false,
                'effective_date' => now()->toDateString(),
            ],
            [
                'template_code' => 'ot_notes',
                'version_tag' => 'v1.0-draft',
                'title' => 'Operation Theatre Notes & Record of Surgery',
                'description' => 'Comprehensive surgical procedure log with anesthesia, incision, findings, and postoperative orders.',
                'is_approved_by_hospital' => false,
                'effective_date' => now()->toDateString(),
            ],
            [
                'template_code' => 'mrd',
                'version_tag' => 'v1.0-draft',
                'title' => 'MRD Inpatient Record Handover & Checklist Form',
                'description' => 'Medical Records Department archive form with case-sheet verification checklist and page counts.',
                'is_approved_by_hospital' => false,
                'effective_date' => now()->toDateString(),
            ],
        ];

        foreach ($templates as $tpl) {
            TemplateVersion::firstOrCreate(['template_code' => $tpl['template_code']], $tpl);
        }

        // 3. Seed Guru Hospital Real Patient Cases
        $this->call(AshwiniDischargeSummarySeeder::class);
        $this->call(AjithKumarDischargeSummarySeeder::class);
        $this->call(ManjupriyaOperationNotesSeeder::class);
        $this->call(AnithaTwinDeliverySeeder::class);
    }
}

