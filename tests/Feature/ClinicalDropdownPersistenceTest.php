<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\ClinicalDropdownOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClinicalDropdownPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role = 'doctor', string $email = 'doctor@guruhospital.local'): User
    {
        return User::create([
            'name' => ucfirst($role) . ' User',
            'email' => $email,
            'password' => bcrypt('Secret123!'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createAdmission(User $owner, string $ipSuffix): Admission
    {
        return Admission::create([
            'patient_name' => "Patient {$ipSuffix}",
            'uhid' => "UHID-{$ipSuffix}",
            'ip_no' => "IP-{$ipSuffix}",
            'age' => '32',
            'sex' => 'Female',
            'admission_at' => now()->subDays(2),
            'diagnosis' => 'Ureteric calculus',
            'format' => 'general',
            'surgery_type' => 'general',
            'created_by' => $owner->id,
            'status' => 'draft',
        ]);
    }

    public function test_new_medication_entered_during_discharge_persists_for_subsequent_admissions(): void
    {
        $doctor = $this->createUser('doctor');
        $first = $this->createAdmission($doctor, '5001');
        $second = $this->createAdmission($doctor, '5002');

        $response = $this->actingAs($doctor)->put(route('admissions.discharge.update', $first), [
            'discharge_at' => now()->format('Y-m-d\TH:i'),
            'medication_orders' => implode("\n", [
                '* Tab. Dolo-650 Extended (1-0-1) x 5 Days - After food',
                '* Tab. Paracetamol 650mg (1-1-1) x 3 Days - After food',
            ]),
            'next_step' => 'stay',
        ]);

        $response->assertRedirect(route('admissions.discharge', $first));

        $this->assertDatabaseHas('clinical_dropdown_options', [
            'type' => ClinicalDropdownOption::TYPE_MEDICATION,
            'label' => 'Tab. Dolo-650 Extended',
            'normalized_key' => 'tab. dolo-650 extended',
            'is_active' => 1,
        ]);

        // Built-in defaults should not create duplicate DB rows.
        $this->assertDatabaseMissing('clinical_dropdown_options', [
            'type' => ClinicalDropdownOption::TYPE_MEDICATION,
            'normalized_key' => 'tab. paracetamol 650mg',
        ]);

        $nextPage = $this->actingAs($doctor)->get(route('admissions.discharge', $second));
        $nextPage->assertOk();
        $nextPage->assertInertia(fn (Assert $page) => $page
            ->component('Admissions/Discharge')
            ->where('medicationOptions', function ($options): bool {
                $list = is_array($options) ? $options : $options->toArray();
                return in_array('Tab. Dolo-650 Extended', $list, true)
                    && in_array('Tab. Paracetamol 650mg', $list, true);
            })
        );
    }

    public function test_new_postoperative_order_entered_during_surgery_persists_for_subsequent_admissions(): void
    {
        $doctor = $this->createUser('doctor');
        $first = $this->createAdmission($doctor, '6001');
        $second = $this->createAdmission($doctor, '6002');

        $response = $this->actingAs($doctor)->put(route('admissions.surgery.update', $first), [
            'procedure_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'procedure_done' => 'URSL',
            'postoperative_orders' => implode("\n", [
                '1. Inj. Ceftriaxone 1g IV BD',
                '2. NPO for 6 hours',
                '3. Check DJ stent position on X-ray KUB tomorrow',
            ]),
            'next_step' => 'stay',
        ]);

        $response->assertRedirect(route('admissions.surgery', $first));

        $this->assertDatabaseHas('clinical_dropdown_options', [
            'type' => ClinicalDropdownOption::TYPE_POSTOP_ORDER,
            'label' => 'Inj. Ceftriaxone 1g IV BD',
            'normalized_key' => 'inj. ceftriaxone 1g iv bd',
        ]);
        $this->assertDatabaseHas('clinical_dropdown_options', [
            'type' => ClinicalDropdownOption::TYPE_POSTOP_ORDER,
            'label' => 'Check DJ stent position on X-ray KUB tomorrow',
        ]);
        $this->assertDatabaseMissing('clinical_dropdown_options', [
            'type' => ClinicalDropdownOption::TYPE_POSTOP_ORDER,
            'normalized_key' => 'npo for 6 hours',
        ]);

        $nextPage = $this->actingAs($doctor)->get(route('admissions.surgery', $second));
        $nextPage->assertOk();
        $nextPage->assertInertia(fn (Assert $page) => $page
            ->component('Admissions/Surgery')
            ->where('postopOrderGroups', function ($groups): bool {
                $arr = is_array($groups) ? $groups : $groups->toArray();
                $all = [];
                foreach ($arr as $grp) {
                    foreach ($grp['options'] ?? [] as $opt) {
                        $all[] = $opt;
                    }
                }
                return in_array('Inj. Ceftriaxone 1g IV BD', $all, true)
                    && in_array('Check DJ stent position on X-ray KUB tomorrow', $all, true)
                    && in_array('NPO for 6 hours', $all, true);
            })
        );
    }

    public function test_quick_dropdown_option_endpoint_validates_deduplicates_and_enforces_roles(): void
    {
        $doctor = $this->createUser('doctor');
        $auditor = $this->createUser('auditor', 'auditor@guruhospital.local');

        $this->actingAs($auditor)
            ->postJson(route('admissions.quick-dropdown-option'), [
                'type' => ClinicalDropdownOption::TYPE_MEDICATION,
                'label' => 'Tab. Montelukast 10mg',
            ])
            ->assertStatus(403);

        $this->actingAs($doctor)
            ->postJson(route('admissions.quick-dropdown-option'), [
                'type' => ClinicalDropdownOption::TYPE_MEDICATION,
                'label' => '---',
            ])
            ->assertStatus(422);

        $createMed = $this->actingAs($doctor)->postJson(route('admissions.quick-dropdown-option'), [
            'type' => ClinicalDropdownOption::TYPE_MEDICATION,
            'label' => 'Tab. Montelukast 10mg',
        ]);
        $createMed->assertStatus(201)
            ->assertJsonPath('created', true)
            ->assertJsonPath('option.label', 'Tab. Montelukast 10mg');

        // Case-insensitive + extra whitespace duplicate check
        $dupMed = $this->actingAs($doctor)->postJson(route('admissions.quick-dropdown-option'), [
            'type' => ClinicalDropdownOption::TYPE_MEDICATION,
            'label' => '  tab.   montelukast 10mg ',
        ]);
        $dupMed->assertStatus(200)
            ->assertJsonPath('created', false)
            ->assertJsonPath('option.label', 'Tab. Montelukast 10mg');

        $this->assertEquals(
            1,
            ClinicalDropdownOption::ofType(ClinicalDropdownOption::TYPE_MEDICATION)->count()
        );

        // Built-in default returns cleanly without duplicating in DB
        $defaultOrder = $this->actingAs($doctor)->postJson(route('admissions.quick-dropdown-option'), [
            'type' => ClinicalDropdownOption::TYPE_POSTOP_ORDER,
            'label' => 'inj - cefbact 1.5g bd iv',
        ]);
        $defaultOrder->assertStatus(200)
            ->assertJsonPath('created', false)
            ->assertJsonPath('option.is_default', true)
            ->assertJsonPath('option.label', 'Inj - Cefbact 1.5g BD IV');

        $this->assertEquals(
            0,
            ClinicalDropdownOption::ofType(ClinicalDropdownOption::TYPE_POSTOP_ORDER)->count()
        );

        $createOrder = $this->actingAs($doctor)->postJson(route('admissions.quick-dropdown-option'), [
            'type' => ClinicalDropdownOption::TYPE_POSTOP_ORDER,
            'label' => 'Inj. Piperacillin + Tazobactam 4.5g IV TDS',
        ]);
        $createOrder->assertStatus(201)
            ->assertJsonPath('created', true);
    }

    public function test_medication_extractor_preserves_parentheses_in_drug_names(): void
    {
        $extracted = ClinicalDropdownOption::extractMedicationNames(implode("\n", [
            '* Tab. Acendol-P (Aceclofenac + Paracetamol) (1-0-1) x 7 Days - After food',
            '* Tab. Nitrofurantoin (100mg) (1-0-1) x 5 Days - After food',
        ]));

        $this->assertSame([
            'Tab. Acendol-P (Aceclofenac + Paracetamol)',
            'Tab. Nitrofurantoin (100mg)',
        ], $extracted);
    }
}
