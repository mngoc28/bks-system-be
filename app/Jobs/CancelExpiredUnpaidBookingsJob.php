<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Booking;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class CancelExpiredUnpaidBookingsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @param BookingService $bookingService
     * @return void
     */
    public function handle(BookingService $bookingService): void
    {
        Log::info('Running CancelExpiredUnpaidBookingsJob...');

        // Fetch bookings that are PENDING and have a pending deposit status
        $bookings = Booking::where('status', 0) // PENDING
            ->where('deposit_status', 'pending')
            ->get();

        $now = Carbon::now();

        foreach ($bookings as $booking) {
            $createdAt = Carbon::parse($booking->getRawOriginal('created_at'));
            $startDate = Carbon::parse($booking->getRawOriginal('start_date'));

            // Difference between check-in (start_date) and creation time (created_at) in hours
            $hoursBeforeCheckIn = $startDate->diffInHours($createdAt, false);
            // diffInHours returns negative if created_at is before start_date, which it should be.
            // Let's use absolute value or check direct difference.
            $hoursBeforeCheckIn = abs($hoursBeforeCheckIn);

            // Determine grace period (in hours)
            $gracePeriodHours = $hoursBeforeCheckIn <= 48 ? 2 : 12;

            $expirationTime = $createdAt->copy()->addHours($gracePeriodHours);

            if ($now->greaterThanOrEqualTo($expirationTime)) {
                Log::info("Booking {$booking->id} has expired unpaid deposit. Cancelling...");
                $bookingService->handleSystemCancelBooking(
                    (int) $booking->id,
                    "Hủy tự động do quá hạn {$gracePeriodHours} giờ thanh toán đặt cọc giữ phòng."
                );
            }
        }

        Log::info('CancelExpiredUnpaidBookingsJob completed.');
    }
}
