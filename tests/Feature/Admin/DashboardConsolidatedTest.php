<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardConsolidatedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_dashboard_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 1,
            'is_email_verified' => true
        ]);

        $this->token = auth('api')->login($this->admin);
    }

    /**
     * Test retrieving consolidated admin dashboard payload.
     */
    public function test_admin_can_retrieve_consolidated_dashboard_data(): void
    {
        $response = $this->getJson('/api/v1/admin/dashboard/consolidated', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'totalUsers',
                'totalPartners',
                'systemRoom',
                'adminStats',
                'bookingsByProperty',
                'bookingsTrend',
                'bookingStatus',
                'occupancyChart',
                'revenuePerformance',
                'settlementDailyReport',
            ]
        ]);
    }
}
