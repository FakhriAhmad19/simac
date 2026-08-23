<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_admin_can_reach_management_pages(): void
    {
        Service::factory()->create();
        $admin = $this->admin();

        foreach (['dashboard', 'bookings', 'customers', 'services', 'technicians', 'users', 'bookings/create'] as $path) {
            $this->actingAs($admin)->get("/$path")->assertOk();
        }
    }

    public function test_owner_can_view_reports_and_dashboard(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get('/dashboard')->assertOk();
        $this->actingAs($owner)->get('/reports')->assertOk();
    }

    public function test_owner_cannot_manage_users(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get('/users')->assertForbidden();
    }

    public function test_technician_dashboard_renders_and_admin_areas_are_blocked(): void
    {
        $technician = Technician::factory()->create();

        $this->actingAs($technician->user)->get('/dashboard')->assertOk();
        $this->actingAs($technician->user)->get('/users')->assertForbidden();
        $this->actingAs($technician->user)->get('/reports')->assertForbidden();
    }

    public function test_login_and_logout_flow(): void
    {
        $admin = User::factory()->admin()->create(['password' => bcrypt('secret123')]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'secret123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
