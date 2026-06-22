<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class RoomMaintenanceValidation
{
    public function listValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'room_id'          => ['nullable', 'integer', 'min:1'],
            'property_id'      => ['nullable', 'integer', 'min:1'],
            'status'           => ['nullable', 'string', 'in:planned,in_progress,completed,cancelled'],
            'maintenance_type' => ['nullable', 'string', 'in:scheduled,emergency'],
            'from_date'        => ['nullable', 'date'],
            'to_date'          => ['nullable', 'date', 'after_or_equal:from_date'],
            'page'             => ['nullable', 'integer', 'min:1'],
            'per_page'         => ['nullable', 'integer', 'min:1', 'max:100'],
            'pagination'       => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    public function storeValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'room_id'          => ['required', 'integer', 'min:1'],
            'property_id'      => ['nullable', 'integer', 'min:1'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'maintenance_type' => ['required', 'string', 'in:scheduled,emergency'],
            'block_calendar'   => ['nullable', 'boolean'],
            'images'           => ['nullable', 'array', 'max:5'],
            'images.*'         => ['string', 'url', 'max:2048'],
            'start_time'       => ['required_without:start_date', 'date'],
            'start_date'       => ['required_without:start_time', 'date'],
            'end_time'         => [
                Rule::requiredIf(fn () => $request->boolean('block_calendar', true)),
                'nullable',
                'date',
                'after_or_equal:start_time',
            ],
            'end_date' => [
                Rule::requiredIf(fn () => $request->boolean('block_calendar', true) && ! $request->filled('end_time')),
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ]);
    }

    public function conflictPreviewValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'room_id'    => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);
    }

    public function updateValidation(Request $request, int $id)
    {
        return Validator::make($request->all(), [
            'status'              => ['nullable', 'string', 'in:in_progress,completed,cancelled'],
            'cancellation_reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:500'],
            'description'         => ['nullable', 'string', 'max:2000'],
            'end_time'            => ['nullable', 'date'],
            'images'              => ['nullable', 'array', 'max:5'],
            'images.*'            => ['string', 'url', 'max:2048'],
        ]);
    }
}
