<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $role, bool $isActive = true): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => $role.'@test.local',
            'role' => $role,
            'password' => Hash::make('password123'),
            'is_active' => $isActive,
        ]);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/Login')
        );
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = $this->createUser('admin');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = $this->createUser('admin');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_deactivated_users_cannot_authenticate(): void
    {
        $user = $this->createUser('sales', false);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = $this->createUser('admin');

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_can_access_all_modules(): void
    {
        $superadmin = $this->createUser('superadmin');

        $this->actingAs($superadmin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($superadmin)->get(route('sales.index'))->assertStatus(200);
        $this->actingAs($superadmin)->get(route('inventory.index'))->assertStatus(200);
        $this->actingAs($superadmin)->get(route('simulator.index'))->assertStatus(200);
        $this->actingAs($superadmin)->get(route('users.index'))->assertStatus(200);
    }

    public function test_admin_can_access_input_modules_but_cannot_access_financial_simulator_or_users(): void
    {
        $admin = $this->createUser('admin');

        // Admin can access operational input modules:
        $this->actingAs($admin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('sales.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('inventory.index'))->assertStatus(200);

        // Admin is forbidden from financial simulator & users/payroll
        $this->actingAs($admin)->get(route('simulator.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($admin)->get(route('users.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_sales_cannot_access_admin_only_modules(): void
    {
        $sales = $this->createUser('sales');

        // Can access: Dashboard, Sales, Inventory
        $this->actingAs($sales)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($sales)->get(route('sales.index'))->assertStatus(200);
        $this->actingAs($sales)->get(route('inventory.index'))->assertStatus(200);

        // Forbidden: Users & Simulator
        $this->actingAs($sales)->get(route('users.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($sales)->get(route('simulator.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_craftsman_cannot_access_sales_or_admin_modules(): void
    {
        $craftsman = $this->createUser('craftsman');

        // Can access: Dashboard, Inventory
        $this->actingAs($craftsman)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($craftsman)->get(route('inventory.index'))->assertStatus(200);

        // Forbidden: Sales, Users, Simulator
        $this->actingAs($craftsman)->get(route('sales.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($craftsman)->get(route('users.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($craftsman)->get(route('simulator.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }
}

