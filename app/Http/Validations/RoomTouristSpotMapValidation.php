<?php

declare(strict_types=1);

namespace App\Http\Validations;

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
        return Validator::make($request->all(), [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'tourist_spot_id' => ['required', 'integer', 'exists:tourist_spots,id'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'travel_time_minutes' => ['required', 'integer', 'min:1'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'source_type' => ['required', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
        ]);
    }

    public function updateValidation(Request $request, int $id): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'room_id' => ['sometimes', 'required', 'integer', 'exists:rooms,id'],
            'tourist_spot_id' => ['sometimes', 'required', 'integer', 'exists:tourist_spots,id'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'travel_time_minutes' => ['sometimes', 'required', 'integer', 'min:1'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'source_type' => ['sometimes', 'required', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
        ]);
    }
}
