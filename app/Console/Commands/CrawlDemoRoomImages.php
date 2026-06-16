<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ImageType;
use App\Enums\RoomStatus;
use App\Services\CloudinaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class CrawlDemoRoomImages extends Command
{
    private const DEFAULT_LIMIT = 100;
    private const DEFAULT_PER_ROOM = 4;
    private const PEXELS_PER_PAGE = 80;
    private const TMP_DIR = 'app/tmp/demo-room-images';
    private const PEOPLE_KEYWORDS = [
        'person',
        'people',
        'man ',
        'woman',
        'girl',
        'boy',
        'model',
        'sitting',
        'standing',
        'lying',
        'portrait',
    ];

    /**
     * @var string
     */
    protected $signature = 'demo:crawl-room-images
        {--room-ids= : Comma-separated room IDs. If omitted, landing rooms are used.}
        {--limit=100 : Maximum number of images to import.}
        {--per-room=4 : Maximum images to add for each room.}
        {--dry-run : Fetch candidates and resolve rooms without downloading, uploading, or inserting.}
        {--report-only : Print current image coverage for the target rooms and exit.}
        {--replace-main : Insert a new sort=1 image and shift existing room images down.}
        {--clear-existing : Delete existing room_images rows for target rooms before importing new images.}
        {--delete-cloudinary-existing : Also delete existing Cloudinary assets when used with --clear-existing.}
        {--include-existing-main : Add images even when a room already has a sort=1 image.}
        {--image-types=1,2,8,4,5,7 : Comma-separated image types assigned by slot. Default: main, interior, bedroom, bathroom, kitchen, living room.}';

    /**
     * @var string
     */
    protected $description = 'Crawl demo room photos from Pexels, upload to Cloudinary, and attach them to landing-page rooms.';

    public function __construct(private readonly CloudinaryService $cloudinaryService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit') ?: self::DEFAULT_LIMIT);
        $perRoom = max(1, (int) $this->option('per-room') ?: self::DEFAULT_PER_ROOM);
        $dryRun = (bool) $this->option('dry-run');
        $reportOnly = (bool) $this->option('report-only');
        $replaceMain = (bool) $this->option('replace-main');
        $clearExisting = (bool) $this->option('clear-existing');
        $deleteCloudinaryExisting = (bool) $this->option('delete-cloudinary-existing');
        $includeExistingMain = (bool) $this->option('include-existing-main');
        $imageTypes = $this->resolveImageTypes();

        $apiKey = (string) env('PEXELS_API_KEY', '');
        if ($apiKey === '' && ! $reportOnly) {
            $this->error('Missing PEXELS_API_KEY in .env.');
            return self::FAILURE;
        }

        $roomIds = $this->resolveRoomIds();
        if ($roomIds === []) {
            $this->warn('No target rooms found.');
            return self::SUCCESS;
        }

        if ($reportOnly) {
            $this->reportImageCoverage($roomIds);
            return self::SUCCESS;
        }

        if ($clearExisting) {
            if (! $dryRun) {
                $this->clearExistingImages($roomIds, $deleteCloudinaryExisting);
            }
            $includeExistingMain = true;
        }

        $rooms = $this->filterTargetRooms($roomIds, $includeExistingMain, $replaceMain);
        if ($rooms->isEmpty()) {
            $this->warn('All target rooms already have a main image. Use --include-existing-main or --replace-main if needed.');
            return self::SUCCESS;
        }

        $targetCount = min($limit, $rooms->count() * $perRoom);
        $this->info("Target rooms: {$rooms->count()}");
        $this->info("Target images: {$targetCount}");

        if (! $this->canResolveHost('api.pexels.com')) {
            $this->error('Cannot resolve api.pexels.com. Check internet/DNS/proxy before running this command.');
            return self::FAILURE;
        }

        if (! $dryRun && ! $this->canResolveHost('api.cloudinary.com')) {
            $this->error('Cannot resolve api.cloudinary.com. Check internet/DNS/proxy before uploading to Cloudinary.');
            return self::FAILURE;
        }

        $this->info('Fetching Pexels photo candidates by image type...');
        $photoPools = $this->fetchPexelsPhotoPools($apiKey, $imageTypes, $targetCount);
        if ($photoPools === []) {
            $this->warn('No Pexels photos found.');
            return self::SUCCESS;
        }

        $assignments = $this->buildAssignments($rooms, $photoPools, $targetCount, $perRoom, $imageTypes);
        $manifest = [
            'generated_at' => Carbon::now()->toIso8601String(),
            'dry_run' => $dryRun,
            'source' => 'pexels',
            'target_rooms' => $rooms->pluck('id')->values()->all(),
            'items' => [],
        ];

        if ($dryRun) {
            foreach ($assignments as $assignment) {
                $manifest['items'][] = $this->manifestItem($assignment, 'dry_run');
                $this->line(sprintf(
                    '[dry-run] room_id=%d pexels_id=%s photographer=%s',
                    $assignment['room_id'],
                    (string) $assignment['photo']['id'],
                    (string) ($assignment['photo']['photographer'] ?? '')
                ));
            }

            $this->writeManifest($manifest);
            return self::SUCCESS;
        }

        File::ensureDirectoryExists(storage_path(self::TMP_DIR));

        $inserted = 0;
        $failed = 0;

        foreach ($assignments as $index => $assignment) {
            $this->line(sprintf(
                'Importing %d/%d: room_id=%d pexels_id=%s',
                $index + 1,
                count($assignments),
                (int) $assignment['room_id'],
                (string) data_get($assignment, 'photo.id', '')
            ));

            $item = $this->importAssignment($assignment, $replaceMain);
            $manifest['items'][] = $item;

            if (($item['status'] ?? '') === 'imported') {
                $inserted++;
            } else {
                $failed++;
            }
        }

        $this->writeManifest($manifest);
        $this->info("Imported images: {$inserted}");
        $this->info("Failed images: {$failed}");

        return $inserted > 0 ? self::SUCCESS : self::FAILURE;
    }

    private function canResolveHost(string $host): bool
    {
        $resolved = gethostbyname($host);

        return $resolved !== $host;
    }

    /**
     * @return array<int>
     */
    private function resolveImageTypes(): array
    {
        $types = collect(explode(',', (string) $this->option('image-types')))
            ->map(static fn (string $type): int => (int) trim($type))
            ->filter(static fn (int $type): bool => in_array($type, ImageType::values(), true))
            ->values()
            ->all();

        return $types !== [] ? $types : [
            ImageType::MAIN,
            ImageType::INTERIOR,
            ImageType::BEDROOM,
            ImageType::BATHROOM,
            ImageType::KITCHEN,
            ImageType::LIVING_ROOM,
        ];
    }

    /**
     * @return array<int>
     */
    private function resolveRoomIds(): array
    {
        $roomIdsOption = trim((string) $this->option('room-ids'));
        if ($roomIdsOption !== '') {
            return collect(explode(',', $roomIdsOption))
                ->map(static fn (string $id): int => (int) trim($id))
                ->filter(static fn (int $id): bool => $id > 0)
                ->unique()
                ->values()
                ->all();
        }

        return collect()
            ->merge($this->topRatedLandingRoomIds())
            ->merge($this->touristSpotLandingRoomIds())
            ->merge($this->provinceLandingRoomIds())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int> $roomIds
     */
    private function filterTargetRooms(array $roomIds, bool $includeExistingMain, bool $replaceMain): Collection
    {
        $query = DB::table('rooms')
            ->whereIn('rooms.id', $roomIds)
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->select('rooms.id', 'rooms.title');

        $order = array_flip($roomIds);
        $rooms = $query->get()
            ->sortBy(static fn ($room) => $order[(int) $room->id] ?? PHP_INT_MAX)
            ->values();

        if ($includeExistingMain || $replaceMain) {
            return $rooms;
        }

        $roomsWithMain = DB::table('room_images')
            ->whereIn('room_id', $rooms->pluck('id')->all())
            ->where('sort', 1)
            ->pluck('room_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        return $rooms
            ->reject(static fn ($room): bool => in_array((int) $room->id, $roomsWithMain, true))
            ->values();
    }

    /**
     * @param array<int> $roomIds
     */
    private function reportImageCoverage(array $roomIds): void
    {
        $rooms = DB::table('rooms')
            ->whereIn('id', $roomIds)
            ->select('id', 'title')
            ->get()
            ->keyBy('id');

        $images = DB::table('room_images')
            ->whereIn('room_id', $roomIds)
            ->orderBy('room_id')
            ->orderBy('sort')
            ->get(['id', 'room_id', 'image_type', 'sort', 'image_url', 'id_image_cloudinary']);

        $requiredTypes = [
            ImageType::MAIN => 'MAIN',
            ImageType::INTERIOR => 'INTERIOR',
            ImageType::BEDROOM => 'BEDROOM',
            ImageType::BATHROOM => 'BATHROOM',
            ImageType::KITCHEN => 'KITCHEN',
            ImageType::LIVING_ROOM => 'LIVING_ROOM',
        ];

        foreach ($roomIds as $roomId) {
            $room = $rooms->get($roomId);
            if (! $room) {
                $this->warn("Room {$roomId} not found.");
                continue;
            }

            $roomImages = $images->where('room_id', $roomId)->values();
            $existingTypes = $roomImages
                ->pluck('image_type')
                ->map(static fn ($type): int => (int) $type)
                ->unique()
                ->all();
            $missing = collect($requiredTypes)
                ->reject(static fn (string $name, int $type): bool => in_array($type, $existingTypes, true))
                ->values()
                ->all();

            $this->info(sprintf('Room %d - %s', $roomId, (string) $room->title));
            $this->line('Images: ' . $roomImages->count());
            $this->line('Missing required types: ' . ($missing === [] ? 'none' : implode(', ', $missing)));

            $rows = $roomImages->map(static fn ($image): array => [
                'id' => (int) $image->id,
                'sort' => (int) $image->sort,
                'type' => (int) $image->image_type,
                'cloudinary' => (string) ($image->id_image_cloudinary ?? ''),
            ])->all();

            if ($rows !== []) {
                $this->table(['id', 'sort', 'type', 'cloudinary'], $rows);
            }
        }
    }

    /**
     * @param array<int> $roomIds
     */
    private function clearExistingImages(array $roomIds, bool $deleteCloudinaryExisting): void
    {
        $images = DB::table('room_images')
            ->whereIn('room_id', $roomIds)
            ->get(['id_image_cloudinary']);

        if ($deleteCloudinaryExisting) {
            $publicIds = $images
                ->pluck('id_image_cloudinary')
                ->filter()
                ->values()
                ->all();

            if ($publicIds !== []) {
                $this->info('Deleting existing Cloudinary assets: ' . count($publicIds));
                $this->cloudinaryService->deleteMultipleImages($publicIds);
            }
        }

        $deleted = DB::table('room_images')
            ->whereIn('room_id', $roomIds)
            ->delete();

        $this->info("Deleted existing room_images rows: {$deleted}");
    }

    /**
     * @return array<int>
     */
    private function topRatedLandingRoomIds(): array
    {
        $reviewSubquery = DB::table('reviews')
            ->select('room_id')
            ->selectRaw('COUNT(*) as reviews_count')
            ->selectRaw('ROUND(AVG(rating), 1) as reviews_avg_rating')
            ->groupBy('room_id');

        return DB::table('rooms')
            ->leftJoinSub($reviewSubquery, 'rev', 'rooms.id', '=', 'rev.room_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->orderByRaw('COALESCE(rev.reviews_avg_rating, 0) DESC')
            ->orderByRaw('COALESCE(rev.reviews_count, 0) DESC')
            ->orderBy('rooms.updated_at', 'desc')
            ->limit(12)
            ->pluck('rooms.id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return array<int>
     */
    private function touristSpotLandingRoomIds(): array
    {
        $slugs = [
            'ho-hoan-kiem',
            'cho-ben-thanh',
            'sa-pa',
            'cat-ba',
            'ly-son',
            'ba-na-hill',
            'bai-bien-my-khe',
            'vinh-ha-long',
            'vinwonders-nha-trang',
            'ho-xuan-huong',
            'dai-noi-hue',
            'trang-an',
        ];

        $spotIds = DB::table('tourist_spots')
            ->where('is_active', true)
            ->whereIn('slug', $slugs)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if ($spotIds === []) {
            return [];
        }

        $ranked = DB::table('rooms')
            ->join('properties as b', 'rooms.property_id', '=', 'b.id')
            ->join('room_tourist_spot_maps as rtsm', 'rtsm.room_id', '=', 'rooms.id')
            ->join('tourist_spots as ts', 'ts.id', '=', 'rtsm.tourist_spot_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->where('ts.is_active', true)
            ->whereIn('ts.id', $spotIds)
            ->whereNotNull('ts.province_id')
            ->whereColumn('b.province_id', 'ts.province_id')
            ->whereExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('room_prices')
                    ->whereColumn('room_prices.room_id', 'rooms.id')
                    ->where('room_prices.unit', 'night');
            })
            ->select('rooms.id', 'ts.id as tourist_spot_id')
            ->selectRaw('ROW_NUMBER() OVER (
                PARTITION BY ts.id
                ORDER BY rtsm.is_primary DESC,
                (SELECT COALESCE(ROUND(AVG(rating), 1), 0) FROM reviews WHERE reviews.room_id = rooms.id) DESC,
                (SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) DESC,
                rtsm.travel_time_minutes ASC,
                rooms.updated_at DESC
            ) as tourist_spot_row_num');

        return DB::query()
            ->fromSub($ranked, 'ranked_rooms')
            ->where('tourist_spot_row_num', '<=', 12)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return array<int>
     */
    private function provinceLandingRoomIds(): array
    {
        $provinceNames = ['da nang', 'khanh hoa', 'quang ninh'];
        $provinceIds = DB::table('provinces')
            ->where(function ($query) use ($provinceNames): void {
                foreach ($provinceNames as $name) {
                    $query->orWhereRaw('LOWER(name_en) LIKE ?', ['%' . $name . '%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $name . '%']);
                }
            })
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if ($provinceIds === []) {
            return [];
        }

        $ranked = DB::table('rooms')
            ->join('properties as b', 'rooms.property_id', '=', 'b.id')
            ->join('provinces as p', 'b.province_id', '=', 'p.id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->whereIn('p.id', $provinceIds)
            ->select('rooms.id', 'p.id as province_id')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY p.id ORDER BY rooms.updated_at DESC) as province_row_num');

        return DB::query()
            ->fromSub($ranked, 'ranked_rooms')
            ->where('province_row_num', '<=', 12)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param array<int> $imageTypes
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function fetchPexelsPhotoPools(string $apiKey, array $imageTypes, int $targetCount): array
    {
        $photoPools = [];
        $seen = [];
        $perTypeTarget = max(12, (int) ceil($targetCount / max(1, count(array_unique($imageTypes)))) + 4);

        foreach (array_values(array_unique($imageTypes)) as $imageType) {
            $photoPools[$imageType] = [];

            foreach ($this->queriesForImageType($imageType) as $query) {
                if (count($photoPools[$imageType]) >= $perTypeTarget) {
                    break;
                }

                try {
                    $response = Http::withHeaders(['Authorization' => $apiKey])
                        ->timeout(20)
                        ->retry(2, 500)
                        ->get('https://api.pexels.com/v1/search', [
                            'query' => $query,
                            'orientation' => 'landscape',
                            'per_page' => self::PEXELS_PER_PAGE,
                        ]);
                } catch (\Throwable $e) {
                    $this->warn("Pexels query failed: {$query} ({$e->getMessage()})");
                    continue;
                }

                if (! $response->successful()) {
                    $this->warn("Pexels query failed: {$query} ({$response->status()})");
                    continue;
                }

                foreach ((array) ($response->json('photos') ?? []) as $photo) {
                    $id = (string) ($photo['id'] ?? '');
                    if ($id === '' || isset($seen[$id]) || $this->looksLikePeoplePhoto($photo)) {
                        continue;
                    }

                    $seen[$id] = true;
                    $photo['demo_query'] = $query;
                    $photoPools[$imageType][] = $photo;

                    if (count($photoPools[$imageType]) >= $perTypeTarget) {
                        break;
                    }
                }
            }

            if ($photoPools[$imageType] === []) {
                $this->warn('No photos found for image_type=' . $imageType);
            }
        }

        return array_filter($photoPools);
    }

    /**
     * @return array<int, string>
     */
    private function queriesForImageType(int $imageType): array
    {
        return match ($imageType) {
            ImageType::MAIN => [
                'hotel room interior no people',
                'luxury hotel room interior empty',
                'serviced apartment interior no people',
            ],
            ImageType::INTERIOR => [
                'modern apartment interior no people',
                'hotel suite interior empty',
                'studio apartment interior no people',
            ],
            ImageType::BEDROOM => [
                'hotel bedroom no people',
                'modern bedroom interior empty',
                'luxury bedroom no people',
            ],
            ImageType::BATHROOM => [
                'hotel bathroom no people',
                'modern bathroom interior empty',
                'luxury bathroom no people',
            ],
            ImageType::KITCHEN => [
                'apartment kitchen no people',
                'modern kitchen interior empty',
                'hotel suite kitchenette no people',
            ],
            ImageType::LIVING_ROOM => [
                'apartment living room no people',
                'modern living room interior empty',
                'hotel suite living room no people',
            ],
            ImageType::BALCONY => [
                'hotel balcony no people',
                'apartment balcony empty',
                'balcony view hotel room no people',
            ],
            default => [
                'hotel room interior no people',
                'apartment interior empty',
            ],
        };
    }

    /**
     * Pexels has no reliable "contains people" flag in this API response, so this is a conservative metadata filter.
     *
     * @param array<string, mixed> $photo
     */
    private function looksLikePeoplePhoto(array $photo): bool
    {
        $text = strtolower(implode(' ', array_filter([
            (string) ($photo['alt'] ?? ''),
            (string) ($photo['url'] ?? ''),
            (string) ($photo['photographer'] ?? ''),
        ])));

        foreach (self::PEOPLE_KEYWORDS as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $photoPools
     * @return array<int, array<string, mixed>>
     */
    private function buildAssignments(Collection $rooms, array $photoPools, int $targetCount, int $perRoom, array $imageTypes): array
    {
        $assignments = [];
        $poolIndexes = [];

        for ($round = 0; $round < $perRoom && count($assignments) < $targetCount; $round++) {
            foreach ($rooms as $room) {
                $imageType = $imageTypes[$round % count($imageTypes)];
                $poolIndexes[$imageType] = $poolIndexes[$imageType] ?? 0;
                $photo = $photoPools[$imageType][$poolIndexes[$imageType]] ?? $this->nextFallbackPhoto($photoPools, $poolIndexes);

                if (! $photo || count($assignments) >= $targetCount) {
                    break 2;
                }

                $poolIndexes[$imageType]++;

                $assignments[] = [
                    'room_id' => (int) $room->id,
                    'room_title' => (string) $room->title,
                    'slot' => $round,
                    'image_type' => $imageType,
                    'photo' => $photo,
                ];
            }
        }

        return $assignments;
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $photoPools
     * @param array<int, int> $poolIndexes
     * @return array<string, mixed>|null
     */
    private function nextFallbackPhoto(array $photoPools, array &$poolIndexes): ?array
    {
        foreach ($photoPools as $imageType => $pool) {
            $poolIndexes[$imageType] = $poolIndexes[$imageType] ?? 0;
            if (isset($pool[$poolIndexes[$imageType]])) {
                return $pool[$poolIndexes[$imageType]++];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $assignment
     * @return array<string, mixed>
     */
    private function importAssignment(array $assignment, bool $replaceMain): array
    {
        $photo = (array) $assignment['photo'];
        $roomId = (int) $assignment['room_id'];
        $downloadUrl = (string) data_get($photo, 'src.large2x', data_get($photo, 'src.large', ''));

        if ($downloadUrl === '') {
            return $this->manifestItem($assignment, 'failed', 'Missing downloadable image URL.');
        }

        $tmpPath = storage_path(self::TMP_DIR . '/' . $roomId . '-' . $photo['id'] . '.jpg');

        try {
            $response = Http::timeout(30)->retry(2, 500)->get($downloadUrl);
            if (! $response->successful()) {
                return $this->manifestItem($assignment, 'failed', 'Download failed: HTTP ' . $response->status());
            }

            File::put($tmpPath, $response->body());

            $maxBytes = (int) config('const.CLOUDINARY_MAX_IMAGE_SIZE');
            if (File::size($tmpPath) > $maxBytes) {
                File::delete($tmpPath);
                return $this->manifestItem($assignment, 'failed', 'Downloaded image exceeds max size.');
            }

            if (@getimagesize($tmpPath) === false) {
                File::delete($tmpPath);
                return $this->manifestItem($assignment, 'failed', 'Downloaded file is not a valid image.');
            }

            $uploadedFile = new UploadedFile(
                $tmpPath,
                basename($tmpPath),
                File::mimeType($tmpPath) ?: 'image/jpeg',
                null,
                true
            );

            $uploadResult = $this->cloudinaryService->uploadImage(
                $uploadedFile,
                'rooms/' . $roomId,
                ['public_id' => 'pexels-' . $photo['id'] . '-' . Str::random(6)]
            );

            File::delete($tmpPath);

            if (! $uploadResult['success']) {
                return $this->manifestItem($assignment, 'failed', (string) $uploadResult['message']);
            }

            $sort = $this->nextSort($roomId, $replaceMain);
            $imageType = (int) ($assignment['image_type'] ?? ($sort === 1 ? ImageType::MAIN : ImageType::INTERIOR));
            if ($sort === 1) {
                $imageType = ImageType::MAIN;
            }

            DB::table('room_images')->insert([
                'room_id' => $roomId,
                'image_url' => $uploadResult['url'],
                'id_image_cloudinary' => $uploadResult['public_id'],
                'image_type' => $imageType,
                'sort' => $sort,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return $this->manifestItem($assignment, 'imported', null, [
                'cloudinary_url' => $uploadResult['url'],
                'cloudinary_public_id' => $uploadResult['public_id'],
                'sort' => $sort,
                'image_type' => $imageType,
            ]);
        } catch (\Throwable $e) {
            if (File::exists($tmpPath)) {
                File::delete($tmpPath);
            }

            Log::error('Demo room image import failed: ' . $e->getMessage(), [
                'room_id' => $roomId,
                'pexels_id' => $photo['id'] ?? null,
            ]);

            return $this->manifestItem($assignment, 'failed', $e->getMessage());
        }
    }

    private function nextSort(int $roomId, bool $replaceMain): int
    {
        if ($replaceMain) {
            DB::table('room_images')
                ->where('room_id', $roomId)
                ->increment('sort');

            return 1;
        }

        $maxSort = (int) DB::table('room_images')
            ->where('room_id', $roomId)
            ->max('sort');

        return $maxSort > 0 ? $maxSort + 1 : 1;
    }

    /**
     * @param array<string, mixed> $assignment
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function manifestItem(array $assignment, string $status, ?string $error = null, array $extra = []): array
    {
        $photo = (array) $assignment['photo'];

        return array_merge([
            'status' => $status,
            'error' => $error,
            'room_id' => (int) $assignment['room_id'],
            'room_title' => (string) $assignment['room_title'],
            'pexels_id' => $photo['id'] ?? null,
            'pexels_url' => $photo['url'] ?? null,
            'photographer' => $photo['photographer'] ?? null,
            'photographer_url' => $photo['photographer_url'] ?? null,
            'query' => $photo['demo_query'] ?? null,
        ], $extra);
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function writeManifest(array $manifest): void
    {
        File::ensureDirectoryExists(storage_path(self::TMP_DIR));
        $path = storage_path(self::TMP_DIR . '/manifest.json');
        File::put($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Manifest written: ' . $path);
    }
}
