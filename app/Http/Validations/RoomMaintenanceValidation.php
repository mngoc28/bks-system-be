<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomMaintenanceValidation
{
    /**
     * List room maintenance validation
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function listValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'room_id' => ['nullable', 'integer', 'min:1'],
            'property_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:planned,in_progress,completed,cancelled'],
            'maintenance_type' => ['nullable', 'string', 'in:scheduled,emergency'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
    }

    /**
     * Store room maintenance validation
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function storeValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'room_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'maintenance_type' => ['required', 'string', 'in:scheduled,emergency'],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
        ]);
    }
}
