<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\PartnerQaDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Drops Partner Portal QA seed artifacts (QA-named buildings and dependent rows).
 *
 * This mirrors {@see PartnerQaDataSeeder::cleanupPreviousQaData()} without re-seeding.
 */
final class CleanupPartnerQaData extends Command
{
    /**
     * @var string
     */
    protected $signature = 'partner:cleanup-qa-data
                            {--partner=2 : Partner user id (buildings.user_id)}
                            {--dry-run : Only print how many rows would be removed}';

    /**
     * @var string
     */
    protected $description = 'Delete QA-seeded partner data (QA % buildings, rooms, bookings, related rows).';

    /**
     * @return int
     */
    public function handle(): int
    {
        $partnerId = max(1, (int) $this->option('partner'));
        $dryRun = (bool) $this->option('dry-run');

        $qaBuildingIds = DB::table('buildings')
            ->where('user_id', $partnerId)
            ->where('name', 'like', 'QA %')
            ->pluck('id')
            ->values()
            ->all();

        $qaRoomIds = [];
        if ($qaBuildingIds !== []) {
            $qaRoomIds = DB::table('rooms')
                ->whereIn('building_id', $qaBuildingIds)
                ->pluck('id')
                ->values()
                ->all();
        }

        $bookingCount = $qaRoomIds !== []
            ? (int) DB::table('bookings')->whereIn('room_id', $qaRoomIds)->count()
            : 0;

        $newsCount = (int) DB::table('news')
            ->where('user_id', $partnerId)
            ->where('title', 'like', 'QA %')
            ->count();

        $this->info(sprintf(
            'Partner id=%d: QA buildings=%d, QA rooms=%d, bookings on those rooms=%d, QA-titled news=%d.',
            $partnerId,
            count($qaBuildingIds),
            count($qaRoomIds),
            $bookingCount,
            $newsCount,
        ));

        if ($dryRun) {
            $this->warn('Dry-run: no data was deleted. Remove --dry-run to execute cleanup.');

            return self::SUCCESS;
        }

        if ($qaBuildingIds === [] && $newsCount === 0) {
            $this->info('Nothing to clean up.');

            return self::SUCCESS;
        }

        (new PartnerQaDataSeeder())->cleanupPreviousQaData($partnerId);
        $this->info('Cleanup finished.');

        return self::SUCCESS;
    }
}
