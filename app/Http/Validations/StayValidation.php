<?php

namespace App\Http\Validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class StayValidation
{
    /**
     * Validation for booking detail
     *
     * @param int $id
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function detailBookingValidation(int $id)
    {
        return Validator::make(['id' => $id], [
            'id' => 'required|exists:bookings,id',
        ]);
    }

    /**
     * Validation for extension request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function extendBookingValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'new_end_date' => 'required|date|after:today',
        ]);
    }

    /**
     * Validation for service request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function serviceOrderValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'note'       => 'nullable|string',
        ]);
    }
}
