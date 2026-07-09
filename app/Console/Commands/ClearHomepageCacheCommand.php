<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\HomePageCacheService;
use Illuminate\Console\Command;

final class ClearHomepageCacheCommand extends Command
{
    protected $signature = 'homepage:clear-cache
                            {--metadata-only : Chỉ xóa cache bootstrap metadata}
                            {--rooms-only : Chỉ xóa cache phòng trang chủ}';

    protected $description = 'Xóa cache Laravel của API trang chủ (bootstrap metadata, top-rated, gợi ý theo điểm du lịch)';

    public function handle(HomePageCacheService $homePageCacheService): int
    {
        $metadataOnly = (bool) $this->option('metadata-only');
        $roomsOnly = (bool) $this->option('rooms-only');

        if ($metadataOnly && $roomsOnly) {
            $this->error('Không thể dùng đồng thời --metadata-only và --rooms-only.');

            return self::FAILURE;
        }

        if (! $roomsOnly) {
            $homePageCacheService->bumpMetadataCacheVersion();
            $this->info('Đã xóa cache bootstrap metadata (provinces, property types, tourist spots).');
        }

        if (! $metadataOnly) {
            $homePageCacheService->bumpRoomsCacheVersion();
            $this->info('Đã xóa cache phòng trang chủ (top-rated, rooms-by-tourist-spot).');
        }

        $this->comment('Lưu ý: cache HTTP trên trình duyệt (max-age ~1h) vẫn có thể giữ response cũ. Dùng Incognito hoặc xóa site data của bks-system-be.onrender.com nếu cần.');

        return self::SUCCESS;
    }
}
