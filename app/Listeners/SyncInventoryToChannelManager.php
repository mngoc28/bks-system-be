<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\RoomInventoryReleased;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncInventoryToChannelManager implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param RoomInventoryReleased $event
     * @return void
     */
    public function handle(RoomInventoryReleased $event): void
    {
        $roomId = $event->roomId;
        Log::info("SyncInventoryToChannelManager: Synchronizing room inventory for room ID: {$roomId} to Channel Manager.");

        try {
            // In a real application, we would call the Channel Manager sync API (e.g. Siteminder, Channex, etc.)
            // We simulate a mock HTTP request with a 5-second timeout requirement.
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.channel_manager.api_key', 'mock-key-12345'),
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.channel_manager.endpoint', 'https://api.bks-stay-channel-manager.mock/v1/sync'), [
                    'room_id' => $roomId,
                    'available' => 1, // release room -> 1 more room available
                    'timestamp' => now()->toIso8601String(),
                ]);

            if ($response->successful()) {
                Log::info("SyncInventoryToChannelManager: Sync successfully for room #{$roomId}. Response: " . json_encode($response->json()));
            } else {
                Log::error("SyncInventoryToChannelManager: Sync failed for room #{$roomId}. HTTP Status: {$response->status()}, Response: {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::error("SyncInventoryToChannelManager: Sync error for room #{$roomId}: " . $e->getMessage());
        }
    }
}
