<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    public function test_user_seeder_creates_admin_safely(): void
    {
        $adminEmail = config('auth.admin.email', 'admin@mymovieapp.com');
        $admin = User::where('email', $adminEmail)->first();

        $this->assertNotNull($admin);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_admin_can_login_and_receive_token(): void
    {
        $adminEmail = config('auth.admin.email', 'admin@mymovieapp.com');
        $adminPassword = config('auth.admin.password', 'admin12345');

        $response = $this->postJson('/api/auth/login', [
            'email' => $adminEmail,
            'password' => $adminPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']])
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_admin_can_access_admin_stats(): void
    {
        $adminEmail = config('auth.admin.email', 'admin@mymovieapp.com');
        $admin = User::where('email', $adminEmail)->first();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/stats');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $user = User::where('role', 'user')->first();
        if (! $user) {
            $user = new User();
            $user->name = 'Test Listener';
            $user->email = 'testlistener@example.com';
            $user->password = Hash::make('password123');
            $user->role = 'user';
            $user->save();
        }

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/admin/stats');
        $response->assertStatus(403);
    }

    public function test_public_registration_never_assigns_admin_role(): void
    {
        $uniqueEmail = 'registrant_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Registrant',
            'email' => $uniqueEmail,
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => 'admin', // Attempts privilege escalation
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.role', 'user');

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);

        // Clean up created test user
        $user->delete();
    }
}
