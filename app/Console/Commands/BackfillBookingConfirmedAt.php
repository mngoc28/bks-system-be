<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\BookingTimelineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * One-shot backfill so KPI dashboards can compute time-to-confirm baselines.
 *
 * For every booking currently in CONFIRMED status without `confirmed_at`,
 * we copy `updated_at` into `confirmed_at` and append a single timeline
 * event tagged `metadata.backfilled = true` so analytics can exclude these
 * rows from "real" SLA calculations.
 *
 * Idempotent: re-running the command is a no-op once all rows are filled.
 */
final class BackfillBookingConfirmedAt extends Command
{
    /**
     * @var string
     */
    protected $signature = 'partner:backfill-confirmed-at
                            {--dry-run : Show how many rows would be updated without writing}
                            {--chunk=500 : Number of bookings processed per batch}';

    /**
     * @var string
     */
    protected $description = 'Backfill bookings.confirmed_at for legacy CONFIRMED rows so KPI baselines work.';

    public function __construct(
        private readonly BookingTimelineService $timelineService,
    ) {
        parent::__construct();
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));

        $base = Booking::query()
            ->where('status', BookingStatus::CONFIRMED->value)
            ->whereNull('confirmed_at');

        $total = (clone $base)->count();
        $this->info(sprintf('Found %d CONFIRMED bookings without confirmed_at.', $total));

        if ($total === 0) {
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn('Dry-run: no rows will be updated.');
            return self::SUCCESS;
        }

        $progress = $this->output->createProgressBar($total);
        $progress->start();

        $updated = 0;
        $base->orderBy('id')
            ->chunkById($chunk, function ($bookings) use (&$updated, $progress): void {
                foreach ($bookings as $booking) {
                    try {
                        DB::transaction(function () use ($booking): void {
                            $booking->confirmed_at = $booking->updated_at;
                            $booking->save();

                            $this->timelineService->recordBackfilled((int) $booking->id, [
                                'confirmed_at_source' => 'updated_at',
                            ]);
                        });

                        $updated++;
                    } catch (Throwable $e) {
                        $this->error(sprintf(
                            'Failed booking %d: %s',
                            (int) $booking->id,
                            $e->getMessage(),
                        ));
                    }
                    $progress->advance();
                }
            });

        $progress->finish();
        $this->newLine();
        $this->info(sprintf('Backfilled %d / %d bookings.', $updated, $total));

        return self::SUCCESS;
    }
}
