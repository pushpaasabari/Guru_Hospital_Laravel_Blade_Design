<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DischargeSummaryFormatsTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::create([
            'name' => 'Dr. Karthik MS',
            'email' => 'doctor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'doctor_reg_no' => 'TNMC-64821',
            'is_active' => true,
        ]);
    }

    public function test_all_four_formats_render_successfully_in_preview(): void
    {
        $formats = ['general', 'cmchistn', 'private', 'employee'];

        foreach ($formats as $format) {
            $admission = Admission::create([
                'patient_name' => "Patient {$format}",
                'uhid' => "GH-{$format}",
                'ip_no' => "IP-{$format}",
                'admission_at' => now()->subDays(2),
                'discharge_at' => now(),
                'diagnosis' => 'Clinical Test Condition',
                'format' => $format,
                'surgery_type' => 'other',
                'created_by' => $this->doctor->id,
                'status' => 'draft',
            ]);

            $response = $this->actingAs($this->doctor)
                ->get(route('documents.preview', $admission));

            $response->assertStatus(200);
            $response->assertSee("Patient {$format}");
            $response->assertSee("IP-{$format}");
            $response->assertSee('OPERATION THEATRE NOTES');
            $response->assertSee('MEDICAL RECORDS DEPARTMENT');
        }
    }

    public function test_delivery_workflow_displays_newborn_table_in_preview(): void
    {
        $admission = Admission::create([
            'patient_name' => 'Mother Revathi',
            'uhid' => 'GH-DEL-01',
            'ip_no' => 'IP-DEL-01',
            'admission_at' => now()->subDays(3),
            'discharge_at' => now(),
            'diagnosis' => 'Full Term Normal Delivery',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'created_by' => $this->doctor->id,
            'status' => 'draft',
        ]);

        $admission->newborns()->create([
            'sex' => 'Female',
            'birth_at' => now()->subDays(2),
            'birth_weight_kg' => '3.15',
            'apgar_1_min' => 9,
            'apgar_5_min' => 10,
            'neonatal_care' => 'Breastfed within 1 hour',
            'notes' => 'Healthy female baby',
        ]);

        $response = $this->actingAs($this->doctor)
            ->get(route('documents.preview', $admission));

        $response->assertStatus(200);
        $response->assertSee('Newborn 1');
        $response->assertSee('3.15');
        $response->assertSee('Healthy female baby');
    }
}
