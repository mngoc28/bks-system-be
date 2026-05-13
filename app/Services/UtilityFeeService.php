<?php

namespace App\Services;

use App\Models\UtilityFee;
use Illuminate\Support\Facades\Log;

class UtilityFeeService
{
    /**
     * Save utility fees for a room
     *
     * @param int $roomId
     * @param array $fees
     * @return void
     */
    public function saveUtilityFees($roomId, $fees)
    {
        try {
            if (!empty($fees) && is_array($fees) && $roomId) {
                // Delete existing fees for this room first to refresh
                UtilityFee::where('room_id', $roomId)->delete();

                foreach ($fees as $fee) {
                    // Map frontend method to backend calc_method
                    $calcMethod = 'fixed';
                    if (isset($fee['method'])) {
                        $calcMethod = match ($fee['method']) {
                            'per_unit' => 'index',
                            'per_person' => 'person',
                            default => 'fixed',
                        };
                    }

                    UtilityFee::create([
                        'room_id' => $roomId,
                        'fee_type' => $fee['type'] ?? 'other',
                        'calc_method' => $calcMethod,
                        'unit_price' => (float) ($fee['price'] ?? 0),
                        'is_included' => (bool) ($fee['included'] ?? false),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error saving utility fees for room $roomId: " . $e->getMessage());
            throw $e;
        }
    }
}
