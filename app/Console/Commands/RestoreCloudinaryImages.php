<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Admin\AdminApi;
use Carbon\Carbon;

final class RestoreCloudinaryImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudinary:restore {--folder= : Thư mục chứa ảnh trên Cloudinary}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lấy danh sách các ảnh đã upload trên Cloudinary và nạp lại vào bảng room_images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Đang khởi tạo cấu hình kết nối tới Cloudinary...');

        // Cấu hình Cloudinary SDK từ Laravel config
        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            $this->error('Thiếu cấu hình Cloudinary trong file .env (CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET)!');
            return self::FAILURE;
        }

        Configuration::instance([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key'    => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        try {
            $adminApi = new AdminApi();
            $options = [
                'resource_type' => 'image',
                'max_results' => 100,
            ];

            $folder = $this->option('folder');
            if ($folder) {
                $options['prefix'] = $folder;
                $this->info("Đang lọc ảnh trong thư mục: {$folder}");
            }

            $this->info('Đang truy vấn danh sách ảnh từ Cloudinary API...');
            $response = $adminApi->assets($options);
            $resources = $response['resources'] ?? [];

            if (empty($resources)) {
                $this->warn('Không tìm thấy ảnh nào trên tài khoản Cloudinary của bạn.');
                return self::SUCCESS;
            }

            $totalAssets = count($resources);
            $this->info("Tìm thấy {$totalAssets} ảnh trên Cloudinary.");

            // Lấy danh sách ID phòng hiện tại trong DB
            $roomIds = DB::table('rooms')->pluck('id')->toArray();
            if (empty($roomIds)) {
                $this->error('Không có phòng nào trong DB! Vui lòng chạy seed phòng trước (php artisan db:seed).');
                return self::FAILURE;
            }

            $inserted = 0;
            $skipped = 0;
            $header = (string) config('const.CLOUDINARY_HEADER_IMAGE_URL'); // https://res.cloudinary.com/dc2ivo5ez/image/upload

            // Random user roles 'admin' hoặc 'partner' để làm người tạo ảnh
            $adminPartnerIds = DB::table('users')
                ->whereIn('role', ['admin', 'partner'])
                ->pluck('id')
                ->toArray();

            if (empty($adminPartnerIds)) {
                $adminPartnerIds = [1];
            }

            foreach ($resources as $index => $resource) {
                $publicId = $resource['public_id'];
                $secureUrl = $resource['secure_url'];

                // Loại bỏ phần header để lấy URL tương đối như hệ thống đang lưu trữ
                $relativeUrl = str_replace($header, '', $secureUrl);

                // Gán tạm ảnh vào các phòng theo Modulo
                $roomId = $roomIds[$index % count($roomIds)];

                // Check trùng public ID tránh chèn lặp
                $exists = DB::table('room_images')->where('id_image_cloudinary', $publicId)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Gán ngẫu nhiên loại ảnh (image_type) dựa trên danh sách trong config const.php
                // 0: OTHER/MAIN_ROOM, 2: INTERIOR, 3: EXTERIOR, 4: BATHROOM, 5: KITCHEN
                $imageTypes = [0, 2, 3, 4, 5];
                $imageType = $imageTypes[$index % count($imageTypes)];

                DB::table('room_images')->insert([
                    'room_id'             => $roomId,
                    'image_url'           => $relativeUrl,
                    'id_image_cloudinary' => $publicId,
                    'image_type'          => $imageType,
                    'sort'                => 1,
                    'created_by'          => $adminPartnerIds[array_rand($adminPartnerIds)],
                    'updated_by'          => $adminPartnerIds[array_rand($adminPartnerIds)],
                    'created_at'          => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at'          => Carbon::now()->subDays(rand(1, 30)),
                ]);

                $inserted++;
            }

            $this->info("Đã đồng bộ thành công!");
            $this->info("- Số ảnh thêm mới vào DB: {$inserted}");
            $this->info("- Số ảnh đã tồn tại trước đó (bỏ qua): {$skipped}");
        } catch (\Exception $e) {
            $this->error('Đã xảy ra lỗi khi đồng bộ ảnh từ Cloudinary: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
