<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $user = \App\Models\User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $authenticatedResponse = $this->actingAs($user)->get('/');
        $authenticatedResponse->assertStatus(302);
        $authenticatedResponse->assertRedirect('/dashboard');
    }
}
