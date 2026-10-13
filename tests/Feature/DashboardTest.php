<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_returns_successful_response(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->has('kpis')
            ->has('capacity')
            ->has('costDistribution')
            ->has('recentTransactions')
            ->has('healthScore')
            ->has('alerts')
        );
    }
}
