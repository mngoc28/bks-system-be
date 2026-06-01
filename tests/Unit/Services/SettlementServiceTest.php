<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\PartnerSettlementPeriod;
use App\Models\SettlementAdjustment;
use App\Models\User;
use App\Services\SettlementService;
use App\Repositories\PartnerSettlementPeriodRepository\PartnerSettlementPeriodRepository;
use App\Repositories\SettlementAdjustmentRepository\SettlementAdjustmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class SettlementServiceTest
 *
 * @package Tests\Unit\Services
 */
final class SettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettlementService $service;
    protected User $partner;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partner = User::create([
            'name' => 'Test Partner',
            'email' => 'partner_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'partner',
            'status' => 1,
            'is_email_verified' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 1,
            'is_email_verified' => true,
        ]);

        $periodRepo = new PartnerSettlementPeriodRepository();
        $adjustmentRepo = new SettlementAdjustmentRepository();

        $this->service = new SettlementService($periodRepo, $adjustmentRepo);
    }

    /**
     * Test phát hành kỳ đối soát thành công.
     */
    public function test_issue_period_successfully(): void
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

        $updated = $this->service->issuePeriod((int) $period->id);

        $this->assertSame(PartnerSettlementPeriod::STATUS_ISSUED, $updated->status);
        $this->assertNotNull($updated->issued_at);
    }

    /**
     * Test khiếu nại kỳ đối soát thành công.
     */
    public function test_dispute_period_successfully(): void
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

        $updated = $this->service->disputePeriod((int) $period->id, 'Sai lệch số lượng phòng');

        $this->assertSame(PartnerSettlementPeriod::STATUS_DISPUTED, $updated->status);
        $this->assertStringContainsString('Sai lệch số lượng phòng', $updated->note);
    }

    /**
     * Test xác nhận thanh toán kỳ đối soát thành công.
     */
    public function test_confirm_payment_successfully(): void
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

        $updated = $this->service->confirmPayment((int) $period->id, [
            'payment_reference' => 'TXN123456',
            'confirmed_by'      => $this->admin->id,
            'note'              => 'Đã nhận chuyển khoản',
        ]);

        $this->assertSame(PartnerSettlementPeriod::STATUS_PAID, $updated->status);
        $this->assertSame('TXN123456', $updated->payment_reference);
        $this->assertSame((int) $this->admin->id, (int) $updated->confirmed_by);
        $this->assertNotNull($updated->paid_at);
    }

    /**
     * Test thêm dòng điều chỉnh công nợ thành công.
     */
    public function test_add_adjustment_successfully(): void
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

        $adjustment = $this->service->addAdjustment((int) $period->id, [
            'amount'     => -50000,
            'reason'     => 'Giảm trừ khuyến mãi dịch vụ',
            'created_by' => $this->admin->id,
        ]);

        $this->assertInstanceOf(SettlementAdjustment::class, $adjustment);
        $this->assertSame(-50000.0, (float) $adjustment->amount);
        $this->assertSame('Giảm trừ khuyến mãi dịch vụ', $adjustment->reason);

        // Check net commission to pay updated
        $this->assertSame(450000.0, (float) $period->fresh()->net_commission_to_pay);
    }
}
