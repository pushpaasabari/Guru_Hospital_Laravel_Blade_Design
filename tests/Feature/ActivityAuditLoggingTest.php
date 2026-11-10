<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ActivityAuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Audit Admin',
            'email' => 'audit-admin@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'administrator',
            'is_active' => true,
        ]);
    }

    public function test_page_views_are_stored_to_audit_logs_and_prefetch_or_health_are_skipped(): void
    {
        $admission = Admission::create([
            'patient_name' => 'TEST PATIENT',
            'uhid' => 'UHID-LOG-01',
            'ip_no' => 'IP-LOG-01',
            'age' => '30',
            'sex' => 'Female',
            'address_line1' => 'LINE 1',
            'admission_at' => now(),
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'TEST DIAGNOSIS',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        // Prefetch must NOT log a false visit
        $this->actingAs($this->admin)
            ->get(route('admissions.discharge', $admission), ['Purpose' => 'prefetch'])
            ->assertOk();

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'page.viewed',
            'auditable_id' => $admission->id,
        ]);

        // Health endpoint must be skipped
        $this->get('/up')->assertOk();
        $this->assertDatabaseMissing('audit_logs', [
            'description' => 'GET /up [200]',
        ]);

        // Real page visit logs cleanly with entity reference
        $this->actingAs($this->admin)
            ->get(route('admissions.discharge', $admission))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'page.viewed',
            'auditable_type' => 'Admission',
            'auditable_id' => $admission->id,
        ]);
    }

    public function test_granular_ui_clicks_and_control_changes_are_batched_into_database(): void
    {
        $admission = Admission::create([
            'patient_name' => 'CLICK TEST PATIENT',
            'uhid' => 'UHID-CLK-01',
            'ip_no' => 'IP-CLK-01',
            'age' => '27',
            'sex' => 'Female',
            'address_line1' => 'ADDRESS',
            'admission_at' => now(),
            'format' => 'general',
            'surgery_type' => 'delivery',
            'diagnosis' => 'DELIVERY',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('audit.events.store'), [
            'events' => [
                [
                    'action' => 'ui.click',
                    'description' => "Clicked button 'Set All Viral Markers Negative' on /admissions/{$admission->id}/test-reports",
                    'path' => "/admissions/{$admission->id}/test-reports",
                    'element' => 'button',
                    'label' => 'Set All Viral Markers Negative',
                    'metadata' => ['x' => 420, 'y' => 310],
                ],
                [
                    'action' => 'ui.change',
                    'description' => 'Selected \'CMCHISTN Government Scheme\' in \'format\' on /dashboard',
                    'path' => '/dashboard',
                    'element' => 'select',
                    'label' => 'format',
                    'metadata' => ['value' => 'cmchistn'],
                ],
            ],
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'ui.click',
            'auditable_type' => 'Admission',
            'auditable_id' => $admission->id,
            'description' => "Clicked button 'Set All Viral Markers Negative' on /admissions/{$admission->id}/test-reports",
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'ui.change',
            'description' => 'Selected \'CMCHISTN Government Scheme\' in \'format\' on /dashboard',
        ]);
    }

    public function test_login_post_never_stores_raw_password_in_http_audit_log(): void
    {
        $this->post('/login', [
            'email' => $this->admin->email,
            'password' => 'Secret123!',
        ])->assertRedirect(route('dashboard'));

        $httpLog = AuditLog::where('action', 'http.post')->latest('id')->first();
        $this->assertNotNull($httpLog);
        $encoded = json_encode($httpLog->payload_after);
        $this->assertStringNotContainsString('Secret123!', (string) $encoded);
    }

    public function test_audit_index_category_filters_separate_ui_clicks_page_views_and_clinical_logs(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'user_role' => $this->admin->role,
            'action' => 'ui.click',
            'description' => 'Clicked Filter button',
            'created_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'user_role' => $this->admin->role,
            'action' => 'admission.created',
            'description' => 'Created admission record',
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('audit.index', ['category' => 'ui_clicks']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Audit/Index')
                ->where('auditLogs.data', fn ($rows) => collect($rows)->every(
                    fn ($row) => str_starts_with($row['action'], 'ui.')
                ))
            );
    }
}

