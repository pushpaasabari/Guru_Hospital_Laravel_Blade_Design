<?php

namespace Tests\Feature;

use App\Models\AnesthesiaType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnesthesiaTypesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $doctor;
    protected User $nurse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Hospital Admin',
            'email' => 'admin@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'administrator',
            'is_active' => true,
        ]);

        $this->doctor = User::create([
            'name' => 'Dr. Karthik MS',
            'email' => 'doctor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $this->nurse = User::create([
            'name' => 'Nurse Anitha',
            'email' => 'nurse@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_anesthesia_types_directory(): void
    {
        $response = $this->actingAs($this->nurse)->get('/anesthesia-types');
        $response->assertStatus(403);

        $docResponse = $this->actingAs($this->doctor)->get('/anesthesia-types');
        $docResponse->assertStatus(403);
    }

    public function test_admin_can_view_anesthesia_types_directory(): void
    {
        AnesthesiaType::create([
            'name' => 'Spinal Anesthesia (SA)',
            'code' => 'SA',
            'description' => 'Subarachnoid block',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/anesthesia-types');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('AnesthesiaTypes/Index')
            ->has('anesthesiaTypes', 1)
            ->where('anesthesiaTypes.0.name', 'Spinal Anesthesia (SA)')
            ->where('anesthesiaTypes.0.code', 'SA')
        );
    }

    public function test_admin_can_add_anesthesia_type(): void
    {
        $payload = [
            'name' => 'Combined Spinal-Epidural (CSE)',
            'code' => 'CSE',
            'description' => 'Dual technique for rapid onset and prolonged analgesia.',
            'sort_order' => 5,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/anesthesia-types', $payload);
        $response->assertRedirect('/anesthesia-types');

        $this->assertDatabaseHas('anesthesia_types', [
            'name' => 'Combined Spinal-Epidural (CSE)',
            'code' => 'CSE',
        ]);
    }

    public function test_admin_can_update_anesthesia_type(): void
    {
        $type = AnesthesiaType::create([
            'name' => 'General Anesthesia',
            'code' => 'GA',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/anesthesia-types/{$type->id}", [
            'name' => 'General Anesthesia (GA)',
            'code' => 'GA',
            'description' => 'Total intravenous & endotracheal intubation',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response->assertRedirect('/anesthesia-types');
        $this->assertDatabaseHas('anesthesia_types', [
            'id' => $type->id,
            'name' => 'General Anesthesia (GA)',
            'description' => 'Total intravenous & endotracheal intubation',
        ]);
    }

    public function test_admin_can_toggle_active_status_and_delete_anesthesia_type(): void
    {
        $type = AnesthesiaType::create([
            'name' => 'Temporary Test Block',
            'code' => 'TTB',
            'is_active' => true,
        ]);

        // Toggle inactive
        $this->actingAs($this->admin)->patch("/anesthesia-types/{$type->id}/toggle");
        $this->assertFalse($type->fresh()->is_active);

        // Delete
        $this->actingAs($this->admin)->delete("/anesthesia-types/{$type->id}");
        $this->assertDatabaseMissing('anesthesia_types', ['id' => $type->id]);
    }

    public function test_admissions_create_page_provides_anesthesia_types_list(): void
    {
        AnesthesiaType::create([
            'name' => 'Spinal Anesthesia (SA)',
            'code' => 'SA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->nurse)->get('/admissions/create');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admissions/Create')
            ->has('anesthesiaTypes', 1)
        );
    }
}
