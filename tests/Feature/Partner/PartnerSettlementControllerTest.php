<?php

declare(strict_types=1);

namespace Tests\Feature\Partner;

use App\Models\PartnerSettlementPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class PartnerSettlementControllerTest
 *
 * @package Tests\Feature\Partner
 */
final class PartnerSettlementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $partner1;
    private User $partner2;
    private string $token1;
    private string $token2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partner1 = User::create([
            'name' => 'Partner One',
            'email' => 'partner1_test@gmail.com',
            'password' => bcrypt('password123'),
            'role'   => 'partner',
            'status' => 1,
            'is_email_verified' => 1
        ]);

        $this->partner2 = User::create([
            'name' => 'Partner Two',
            'email' => 'partner2_test@gmail.com',
            'password' => bcrypt('password123'),
            'role'   => 'partner',
            'status' => 1,
            'is_email_verified' => 1
        ]);

        $this->token1 = auth('api')->login($this->partner1);
        $this->token2 = auth('api')->login($this->partner2);
    }

    /**
     * Test partner chỉ xem được các kỳ đối soát của chính mình.
     */
    public function test_partner_can_only_list_own_settlements(): void
    {
        // Kỳ đối soát của partner 1
        PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner1->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        // Kỳ đối soát của partner 2
        PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner2->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 8000000,
            'total_commission' => 400000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->getJson('/api/v1/partner/settlements', [
            'Authorization' => 'Bearer ' . $this->token1
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame((int) $this->partner1->id, (int) $items[0]['partner_id']);
    }

    /**
     * Test partner bị từ chối truy cập khi xem kỳ đối soát của partner khác.
     */
    public function test_partner_cannot_view_others_settlement_detail(): void
    {
        // Kỳ đối soát của partner 2
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner2->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 8000000,
            'total_commission' => 400000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        // Partner 1 cố tình truy cập kỳ của partner 2
        $response = $this->getJson("/api/v1/partner/settlements/{$period->id}", [
            'Authorization' => 'Bearer ' . $this->token1
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test partner gửi khiếu nại thành công cho kỳ đã phát hành.
     */
    public function test_partner_can_dispute_own_issued_settlement(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner1->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_ISSUED,
            'issued_at'        => now(),
        ]);

        $response = $this->postJson("/api/v1/partner/settlements/{$period->id}/dispute", [
            'reason' => 'Không đúng số liệu doanh thu của booking RM-2026-000100',
        ], [
            'Authorization' => 'Bearer ' . $this->token1
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', PartnerSettlementPeriod::STATUS_DISPUTED);
        $this->assertStringContainsString('Không đúng số liệu doanh thu', $response->json('data.note'));
    }

    /**
     * Test partner can export own settlement to Excel.
     */
    public function test_partner_can_export_own_settlement_excel(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner1->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        \Maatwebsite\Excel\Facades\Excel::fake();

        $response = $this->get("/api/v1/partner/settlements/{$period->id}/export/excel", [
            'Authorization' => 'Bearer ' . $this->token1
        ]);

        $response->assertOk();
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded(
            sprintf('BKS-Đối-Soát-Kỳ-%d.xlsx', $period->id)
        );
    }

    /**
     * Test partner cannot export others settlement to Excel (403).
     */
    public function test_partner_cannot_export_others_settlement_excel(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner2->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->get("/api/v1/partner/settlements/{$period->id}/export/excel", [
            'Authorization' => 'Bearer ' . $this->token1
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test partner can export own settlement to PDF.
     */
    public function test_partner_can_export_own_settlement_pdf(): void
    {
        $period = PartnerSettlementPeriod::create([
            'partner_id'       => $this->partner1->id,
            'period_start'     => '2026-05-01',
            'period_end'       => '2026-05-15',
            'issue_date'       => '2026-05-20',
            'total_gmv'        => 10000000,
            'total_commission' => 500000,
            'commission_rate'  => 0.05,
            'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
        ]);

        $response = $this->get("/api/v1/partner/settlements/{$period->id}/export/pdf", [
            'Authorization' => 'Bearer ' . $this->token1
        ]);

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}

