<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Newborn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PreviewAndPdfParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_and_newborn_timestamps_serialize_without_utc_z_shift_and_match_a4_pdf(): void
    {
        $doctor = User::create([
            'name' => 'Dr. Parity Check',
            'email' => 'parity@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        // Include late-evening and morning times that previously shifted +5h30m (even across midnight!)
        // when JSON serialization appended trailing "Z" and React parsed them with local browser timezone.
        $admission = Admission::create([
            'patient_name' => 'MRS. TIME PARITY PATIENT',
            'uhid' => 'GH-9100',
            'ip_no' => 'IP-9100',
            'age' => '29',
            'sex' => 'Female',
            'admission_at' => '2026-11-25 20:15:00',
            'procedure_at' => '2026-11-26 08:30:00',
            'discharge_at' => '2026-11-28 19:45:00',
            'diagnosis' => 'Bilateral renal calculi with DJ stent',
            'procedure_name' => 'URSL with DJ Stenting',
            'procedure_done' => 'URSL with Right DJ Stenting',
            'stent_removal_date' => '2026-12-28',
            'format' => 'general',
            'surgery_type' => 'delivery',
            'created_by' => $doctor->id,
            'approved_by' => $doctor->id,
            'approved_at' => now(),
            'status' => 'approved',
        ]);

        Newborn::create([
            'admission_id' => $admission->id,
            'sex' => 'Female',
            'birth_at' => '2026-11-26 22:05:00',
            'birth_weight_kg' => 3.10,
            'neonatal_care' => 'Routine newborn care',
        ]);

        $admission->load('newborns');
        $json = $admission->toArray();

        $this->assertSame('2026-11-25T20:15:00', $json['admission_at']);
        $this->assertSame('2026-11-26T08:30:00', $json['procedure_at']);
        $this->assertSame('2026-11-28T19:45:00', $json['discharge_at']);
        $this->assertStringNotContainsString('Z', $json['admission_at']);
        $this->assertSame('2026-11-26T22:05:00', $json['newborns'][0]['birth_at']);

        $preview = $this->actingAs($doctor)->get(route('documents.preview', $admission));
        $preview->assertOk();
        $preview->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Pack')
            ->where('digitalSummary.admission_at_formatted', '25.11.2026, 08:15 PM')
            ->where('digitalSummary.procedure_at_formatted', '26.11.2026, 08:30 AM')
            ->where('digitalSummary.discharge_at_formatted', '28.11.2026, 07:45 PM')
            ->where('digitalSummary.stent_removal_date_formatted', '28.12.2026')
            ->where('digitalSummary.newborn_cards.0.sex', 'Alive FEMALE baby')
            ->where('digitalSummary.newborn_cards.0.born', '26.11.2026 at 10:05 PM')
        );

        $a4Html = view('pdf.discharge-summary-a4', ['admission' => $admission])->render();
        $this->assertStringContainsString('25.11.2026, 08:15 PM', $a4Html);
        $this->assertStringContainsString('26.11.2026, 08:30 AM', $a4Html);
        $this->assertStringContainsString('28.11.2026, 07:45 PM', $a4Html);
        $this->assertStringContainsString('28.12.2026', $a4Html);
        $this->assertStringContainsString('Alive <strong>FEMALE</strong> baby', $a4Html);
        $this->assertStringContainsString('26.11.2026 at 10:05 PM', $a4Html);
    }
}
