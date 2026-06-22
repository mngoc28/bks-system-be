<?php

declare(strict_types=1);

namespace App\Http\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RoomMaintenance
 */
final class RoomMaintenanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $roomName = null;
        if ($this->relationLoaded('room') && $this->room !== null) {
            $roomName = $this->room->title ?: $this->room->room_number;
        }

        return [
            'id'                  => (int) $this->id,
            'room_id'             => (int) $this->room_id,
            'property_id'         => (int) $this->property_id,
            'title'               => (string) $this->title,
            'description'         => $this->description,
            'maintenance_type'    => (string) $this->maintenance_type,
            'maintenance_type_label' => __('room_maintenance.types.' . $this->maintenance_type),
            'start_time'          => optional($this->start_time)->toIso8601String(),
            'end_time'            => optional($this->end_time)->toIso8601String(),
            'status'              => (string) $this->status,
            'status_label'        => __('room_maintenance.statuses.' . $this->status),
            'room_block_id'       => $this->room_block_id !== null ? (int) $this->room_block_id : null,
            'block_calendar'      => (bool) $this->block_calendar,
            'source'              => (string) $this->source,
            'cancellation_reason' => $this->cancellation_reason,
            'images'              => $this->images ?? [],
            'started_at'          => optional($this->started_at)->toIso8601String(),
            'completed_at'        => optional($this->completed_at)->toIso8601String(),
            'cancelled_at'        => optional($this->cancelled_at)->toIso8601String(),
            'created_by'          => (int) $this->created_by,
            'created_at'          => optional($this->created_at)->toIso8601String(),
            'updated_at'          => optional($this->updated_at)->toIso8601String(),
            'room_name'           => $roomName,
            'property_name'       => $this->relationLoaded('property') ? $this->property?->name : null,
        ];
    }
}
