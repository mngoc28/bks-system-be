<?php

declare(strict_types=1);

namespace App\Http\Requests\Stay;

use App\Models\CancellationReasonCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StayCancelRequestBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason_code' => [
                'required',
                'string',
                'max:50',
                Rule::exists('cancellation_reason_codes', 'code')->where(
                    static fn ($q) => $q->where('is_active', true),
                ),
            ],
            'reason_text'       => ['nullable', 'string', 'max:2000'],
            'idempotency_key'   => ['required', 'string', 'max:64'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $code = (string) $this->input('reason_code', '');
            if ($code === '') {
                return;
            }

            $row = CancellationReasonCode::query()->where('code', $code)->first();
            if ($row !== null && $row->requires_note && trim((string) $this->input('reason_text', '')) === '') {
                $validator->errors()->add('reason_text', __('booking.bcp.reason_text_required'));
            }
        });
    }
}
