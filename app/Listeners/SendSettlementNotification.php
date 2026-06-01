<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SettlementPeriodIssued;
use App\Mail\SettlementPeriodIssuedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Listener tự động gửi email thông báo cho đối tác khi kỳ đối soát được phát hành.
 */
class SendSettlementNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Số lần retry khi job thất bại.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Số giây chờ giữa các lần retry.
     *
     * @var int
     */
    public int $backoff = 10;

    /**
     * Xử lý gửi email bất đồng bộ.
     *
     * @param \App\Events\SettlementPeriodIssued $event
     * @return void
     */
    public function handle(SettlementPeriodIssued $event): void
    {
        $period = $event->period;

        try {
            // Đảm bảo partner được load đầy đủ
            $period->loadMissing('partner');
            $partner = $period->partner;

            if (!$partner || empty($partner->email)) {
                Log::warning("SendSettlementNotification: Không thể gửi email do không tìm thấy đối tác hoặc đối tác không có email.", [
                    'period_id' => $period->id,
                ]);
                return;
            }

            $partnerName = $partner->name;
            $bankInfo = config('billing.bank_info', [
                'bank_name' => 'Vietcombank',
                'account_number' => '1023456789',
                'account_holder' => 'CONG TY CP BKS STAY',
                'transfer_syntax_prefix' => 'BKSBILL',
            ]);

            Mail::to($partner->email)->send(
                new SettlementPeriodIssuedMail($partnerName, $period, $bankInfo)
            );

            Log::info("SendSettlementNotification: Đã gửi email thông báo phát hành kỳ đối soát #{$period->id} tới đối tác #{$partner->id} ({$partner->email}).");
        } catch (Throwable $e) {
            Log::error("SendSettlementNotification: Gửi email thất bại cho kỳ đối soát #{$period->id}.", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Cho phép job được đưa vào hàng đợi lỗi để retry sau
            throw $e;
        }
    }
}
