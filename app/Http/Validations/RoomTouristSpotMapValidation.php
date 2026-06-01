<?php

declare(strict_types=1);

namespace App\Http\Validations;

use App\Models\RoomTouristSpotMap;
use App\Services\RoomTouristGeographyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class RoomTouristSpotMapValidation
{
    public function indexValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'tourist_spot_id' => ['nullable', 'integer', 'exists:tourist_spots,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    public function storeValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        $validator = Validator::make($request->all(), [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'tourist_spot_id' => [
                'required',
                'integer',
                'exists:tourist_spots,id',
                function ($attribute, $value, $fail) use ($request) {
                    $roomId = $request->input('room_id');
                    if ($roomId) {
                        $exists = \Illuminate\Support\Facades\DB::table('room_tourist_spot_maps')
                            ->where('room_id', $roomId)
                            ->where('tourist_spot_id', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Phòng này đã được gán địa điểm du lịch này rồi.');
                        }
                    }
                }
            ],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'travel_time_minutes' => ['required', 'integer', 'min:1'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'source_type' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
            'apply_to_all_rooms' => ['nullable', 'boolean'],
        ]);

        return $this->appendSameProvinceRule($validator, $request);
    }

    public function updateValidation(Request $request, int $id): \Illuminate\Contracts\Validation\Validator
    {
        $validator = Validator::make($request->all(), [
            'room_id' => ['sometimes', 'required', 'integer', 'exists:rooms,id'],
            'tourist_spot_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:tourist_spots,id',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $map = \App\Models\RoomTouristSpotMap::find($id);
                    if ($map) {
                        $roomId = $request->input('room_id', $map->room_id);
                        $exists = \Illuminate\Support\Facades\DB::table('room_tourist_spot_maps')
                            ->where('room_id', $roomId)
                            ->where('tourist_spot_id', $value)
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail('Phòng này đã được gán địa điểm du lịch này rồi.');
                        }
                    }
                }
            ],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'travel_time_minutes' => ['sometimes', 'required', 'integer', 'min:1'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'source_type' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
            'apply_to_all_rooms' => ['nullable', 'boolean'],
        ]);

        return $this->appendSameProvinceRule($validator, $request, $id);
    }

    private function appendSameProvinceRule(
        \Illuminate\Contracts\Validation\Validator $validator,
        Request $request,
        ?int $mapId = null,
    ): \Illuminate\Contracts\Validation\Validator {
        return $validator->after(function ($validator) use ($request, $mapId): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $existing = null;
            if ($mapId !== null) {
                $existing = RoomTouristSpotMap::query()->find($mapId);
                if (! $existing) {
                    return;
                }
            }

            $roomId = (int) $request->input('room_id', $existing?->room_id);
            $touristSpotId = (int) $request->input('tourist_spot_id', $existing?->tourist_spot_id);

            $geography = app(RoomTouristGeographyService::class);
            if (! $geography->roomMatchesSpotProvince($roomId, $touristSpotId)) {
                $validator->errors()->add(
                    'tourist_spot_id',
                    'Phòng và điểm du lịch phải cùng tỉnh/thành.'
                );
            }
        });
    }
}
