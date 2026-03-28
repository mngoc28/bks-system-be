<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserReportValidation
{
    /**
     * Validation for creating a user report.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function storeValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'reporter_id' => ['required', 'integer', 'min:1'],
            'reported_user_id' => ['required', 'integer', 'min:1'],
            'booking_id' => ['nullable', 'integer', 'min:1'],
            'type' => ['required', 'string', Rule::in([
                'behavior',
                'harassment',
                'fraud',
                'property_damage',
                'scam',
                'noise',
                'payment_issue',
                'other',
            ])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', 'string', Rule::in(['pending', 'reviewing', 'resolved', 'rejected'])],
            'admin_note' => ['nullable', 'string'],
        ]);
    }
}
