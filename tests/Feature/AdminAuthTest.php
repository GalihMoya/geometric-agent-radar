<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test login page displays correctly.
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('ADMIN CONSOLE');
        $response->assertSee('RADAR TULUNGAGUNG');
        $response->assertSee('placeholder="nama@gmail.com"', false);
        $response->assertDontSee('value="admin@radar.com"', false);
    }

    /**
     * Test admin can login with valid credentials.
     */
    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.agents.index'));
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@radar.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test regular non-admin user cannot access admin console.
     */
    public function test_non_admin_user_cannot_login_to_admin(): void
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test guest cannot access protected admin routes.
     */
    public function test_guest_is_redirected_when_accessing_admin_routes(): void
    {
        $response = $this->get('/admin/agents');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test admin can logout.
     */
    public function test_admin_can_logout(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
