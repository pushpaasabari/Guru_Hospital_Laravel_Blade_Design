<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SecurityAndRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $doctor;
    protected User $nurse;
    protected User $auditor;

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
            'doctor_reg_no' => 'TNMC-64821',
            'is_active' => true,
        ]);

        $this->nurse = User::create([
            'name' => 'Nurse Anitha',
            'email' => 'nurse@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
            'is_active' => true,
        ]);

        $this->auditor = User::create([
            'name' => 'Auditor Saravanan',
            'email' => 'auditor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'auditor',
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admissions.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $this->nurse->update(['is_active' => false]);

        $response = $this->post(route('login'), [
            'email' => 'nurse@guruhospital.local',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admin_cannot_access_template_management(): void
    {
        $response = $this->actingAs($this->nurse)->get(route('templates.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->doctor)->get(route('templates.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->get(route('templates.index'));
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->nurse)->get(route('users.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertStatus(200);
    }

    public function test_auditor_has_read_only_access_and_cannot_create_admissions(): void
    {
        $response = $this->actingAs($this->auditor)->get(route('admissions.create'));
        $response->assertForbidden();

        $response = $this->actingAs($this->auditor)->post(route('admissions.store'), [
            'patient_name' => 'Test',
            'uhid' => '123',
            'ip_no' => '456',
        ]);
        $response->assertForbidden();

        // Auditor CAN view audit logs
        $response = $this->actingAs($this->auditor)->get(route('audit.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_staff_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Dr. New Doctor',
            'email' => 'newdoc@guruhospital.local',
            'password' => 'SecurePass2026!',
            'role' => 'doctor',
            'doctor_reg_no' => 'TNMC-99999',
            'department' => 'Paediatrics',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newdoc@guruhospital.local',
            'doctor_reg_no' => 'TNMC-99999',
            'role' => 'doctor',
        ]);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->nurse)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('counts')
            ->has('admissions')
        );
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/up')->assertStatus(200);
    }

    public function test_frontend_routes_use_react_inertia_and_no_legacy_ui_blades_remain(): void
    {
        $bladeFiles = collect(
            glob(resource_path('views/{,*/,*/*/,*/*/*/}*.blade.php'), GLOB_BRACE) ?: []
        )->map(fn (string $path) => str_replace('\\', '/', ltrim(str_replace(resource_path('views'), '', $path), '/\\')));

        $nonPdfBlades = $bladeFiles->reject(
            fn (string $relative) => $relative === 'app.blade.php' || str_starts_with($relative, 'pdf/')
        );

        $this->assertTrue(
            $nonPdfBlades->isEmpty(),
            'Found unexpected legacy frontend Blade views: '.$nonPdfBlades->implode(', ')
        );

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));

        $this->actingAs($this->admin)
            ->get(route('admissions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admissions/Index'));

        $this->actingAs($this->admin)
            ->get(route('templates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Templates/Index'));

        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Users/Index'));

        $this->actingAs($this->admin)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Audit/Index'));

        $this->actingAs($this->admin)
            ->get(route('clinical-staff.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ClinicalStaff/Index'));

        $this->actingAs($this->admin)
            ->get(route('anesthesia-types.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('AnesthesiaTypes/Index'));
    }
}

