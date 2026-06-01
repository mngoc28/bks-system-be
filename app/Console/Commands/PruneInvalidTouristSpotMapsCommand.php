<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RoomTouristGeographyService;
use Illuminate\Console\Command;

final class PruneInvalidTouristSpotMapsCommand extends Command
{
    protected $signature = 'tourist-spots:prune-invalid-maps
                            {--force : Xóa bản ghi mapping không cùng tỉnh}
                            {--dry-run : Chỉ đếm, không xóa (mặc định nếu không có --force)}';

    protected $description = 'Xóa room_tourist_spot_maps khi phòng và điểm du lịch khác tỉnh';

    public function handle(RoomTouristGeographyService $geographyService): int
    {
        $force = (bool) $this->option('force');
        $dryRun = ! $force || (bool) $this->option('dry-run');

        $count = $geographyService->pruneInvalidMaps($dryRun);

        if ($dryRun) {
            $this->info("Tìm thấy {$count} mapping không hợp lệ (chưa xóa). Chạy với --force để xóa.");

            return self::SUCCESS;
        }

        $this->info("Đã xóa {$count} mapping không cùng tỉnh.");

        return self::SUCCESS;
    }
}
