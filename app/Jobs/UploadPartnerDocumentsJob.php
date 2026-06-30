<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PartnerInfo;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class UploadPartnerDocumentsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    private int $userId;

    /**
     * @var array<string, array{path: string, original_name: string, mime_type: string, existing_path: ?string}>
     */
    private array $files;

    /**
     * @param int $userId
     * @param array<string, array{path: string, original_name: string, mime_type: string, existing_path: ?string}> $files
     */
    public function __construct(int $userId, array $files)
    {
        $this->userId = $userId;
        $this->files = $files;
    }

    /**
     * @return void
     */
    public function handle(CloudinaryService $cloudinaryService): void
    {
        $partnerInfo = PartnerInfo::where('user_id', $this->userId)->first();
        if (!$partnerInfo) {
            Log::warning('UploadPartnerDocumentsJob: partner info not found', [
                'user_id' => $this->userId,
            ]);
            $this->cleanupTempFiles();
            return;
        }

        foreach ($this->files as $column => $fileMeta) {
            $path = storage_path('app/' . ltrim($fileMeta['path'], '/'));
            if (!is_file($path)) {
                Log::warning('UploadPartnerDocumentsJob: temp file missing', [
                    'user_id' => $this->userId,
                    'column' => $column,
                    'path' => $path,
                ]);
                continue;
            }

            try {
                $uploadedFile = new UploadedFile(
                    $path,
                    $fileMeta['original_name'],
                    $fileMeta['mime_type'],
                    null,
                    true
                );

                $uploadResult = $cloudinaryService->uploadPartnerDocument(
                    $uploadedFile,
                    "partners/{$this->userId}/documents"
                );

                if (!$uploadResult['success'] || empty($uploadResult['url'])) {
                    throw new Exception($uploadResult['message']);
                }

                $cloudinaryService->deleteStoredFile($fileMeta['existing_path']);

                $partnerInfo->{$column} = $uploadResult['url'];
                $partnerInfo->save();
            } catch (Exception $e) {
                Log::error('UploadPartnerDocumentsJob failed', [
                    'user_id' => $this->userId,
                    'column' => $column,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->cleanupTempFiles();
    }

    private function cleanupTempFiles(): void
    {
        foreach ($this->files as $fileMeta) {
            $path = storage_path('app/' . ltrim($fileMeta['path'], '/'));
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
