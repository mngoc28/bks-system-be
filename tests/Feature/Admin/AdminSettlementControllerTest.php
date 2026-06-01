<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\PartnerSettlementPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AdminSettlementControllerTest
 *
 * @package Tests\Feature\Admin
 */
final class AdminSettlementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $partner;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 1,
            'is_email_verified' => true
        ]);

        $this->partner = User::create([
            'name' => 'Partner User',
            'email' => 'partner_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'partner',
            'status' => 1,
            'is_email_verified' => true
        ]);

        // Tạo JWT Token cho Admin bằng cách đăng nhập qua api guard
        $this->token = auth('api')->login($this->admin);
    }

    /**
     * Test lấy danh sách kỳ đối soát.
     */
    public function test_admin_can_list_settlement_periods(): void
    {
        PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->getJson('/api/v1/admin/settlements', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'items',
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]
        ]);
    }

    /**
     * Test lấy chi tiết kỳ đối soát.
     */
    public function test_admin_can_view_settlement_detail(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->getJson("/api/v1/admin/settlements/{$period->id}", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.id', $period->id);
    }

    /**
     * Test phát hành kỳ đối soát.
     */
    public function test_admin_can_issue_settlement_period(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->postJson("/api/v1/admin/settlements/{$period->id}/issue", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', PartnerSettlementPeriod::STATUS_ISSUED);
    }

    /**
     * Test xác nhận thanh toán.
     */
    public function test_admin_can_confirm_settlement_payment(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_ISSUED,
            'issued_at'        => now(),
        ]);

        $response = $this->postJson("/api/v1/admin/settlements/{$period->id}/confirm-payment", [
            'payment_reference' => 'TXN99999',
            'note'              => 'Nhận đủ tiền hoa hồng qua VCB',
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', PartnerSettlementPeriod::STATUS_PAID);
        $response->assertJsonPath('data.payment_reference', 'TXN99999');
    }

    /**
     * Test thêm dòng điều chỉnh.
     */
    public function test_admin_can_add_adjustment(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->postJson("/api/v1/admin/settlements/{$period->id}/adjustments", [
            'amount' => 120000,
            'reason' => 'Phạt phí đền bù hư hỏng cơ sở vật chất phòng',
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('settlement_adjustments', [
            'settlement_period_id' => $period->id,
            'amount'               => 120000
        ]);
    }

    /**
     * Test admin can export settlement period to Excel.
     */
    public function test_admin_can_export_settlement_excel(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        \Maatwebsite\Excel\Facades\Excel::fake();

        $response = $this->get("/api/v1/admin/settlements/{$period->id}/export/excel", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded(
            sprintf('BKS-Đối-Soát-Kỳ-%d.xlsx', $period->id)
        );
    }

    /**
     * Test admin can export settlement period to PDF.
     */
    public function test_admin_can_export_settlement_pdf(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->get("/api/v1/admin/settlements/{$period->id}/export/pdf", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}

