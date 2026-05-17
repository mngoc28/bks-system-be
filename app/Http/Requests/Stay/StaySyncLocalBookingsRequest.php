<?php

declare(strict_types=1);

namespace App\Http\Requests\Stay;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

final class StaySyncLocalBookingsRequest extends FormRequest
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
            'items'               => ['required', 'array', 'min:1', 'max:50'],
            'items.*.local_id'    => ['required', 'string', 'max:64'],
            'items.*.fingerprint' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/'],
            'items.*.room_id'     => ['required', 'integer', 'min:1', 'exists:rooms,id'],
            'items.*.start_date'  => ['required', 'date'],
            'items.*.end_date'    => ['required', 'date'],
            'items.*.email'       => ['nullable', 'string', 'email', 'max:255'],
            'items.*.price_id'    => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            if ($user === null) {
                return;
            }

            $authEmail = strtolower(trim((string) $user->email));
            $items      = $this->input('items', []);

            foreach ($items as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (
                    isset($item['start_date'], $item['end_date'])
                    && is_string($item['start_date'])
                    && is_string($item['end_date'])
                ) {
                    $sd = Carbon::parse($item['start_date'])->startOfDay();
                    $ed = Carbon::parse($item['end_date'])->startOfDay();
                    if ($ed->lt($sd)) {
                        $validator->errors()->add(
                            "items.{$i}.end_date",
                            __('booking.validation.end_date.after_or_equal'),
                        );
                    }
                }
                if (! isset($item['email']) || ! is_string($item['email']) || trim($item['email']) === '') {
                    continue;
                }
                $em = strtolower(trim($item['email']));
                if ($em !== $authEmail) {
                    $validator->errors()->add(
                        "items.{$i}.email",
                        __('booking.sync_local.email_mismatch'),
                    );
                }
            }
        });
    }
}
