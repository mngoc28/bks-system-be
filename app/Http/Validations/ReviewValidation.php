<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewValidation
{
    /**
     * Validation for submitting booking reviews.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function storeValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'room_rating' => ['nullable', 'integer', 'between:1,5'],
            'room_comment' => ['nullable', 'string', 'max:1000'],
            'partner_rating' => ['nullable', 'integer', 'between:1,5'],
            'partner_comment' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
