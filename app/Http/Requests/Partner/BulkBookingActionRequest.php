<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

final class BulkBookingActionRequest extends FormRequest
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
        $rules = [
            'ids'   => ['required', 'array', 'min:1', 'max:20'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:bookings,id'],
        ];

        if ($this->is('api/v1/partner/bookings/bulk-cancel')) {
            $rules['reason'] = ['required', 'string', 'min:5', 'max:500'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Vui lòng chọn ít nhất một booking.',
            'ids.array'    => 'Danh sách booking không hợp lệ.',
            'ids.min'      => 'Vui lòng chọn ít nhất một booking.',
            'ids.max'      => 'Chỉ được xử lý tối đa 20 booking mỗi lần.',
            'ids.*.exists' => 'Một hoặc nhiều booking không tồn tại.',
            'ids.*.distinct' => 'Danh sách booking bị trùng.',
            'reason.required' => 'Vui lòng nhập lý do huỷ.',
            'reason.min'      => 'Lý do huỷ tối thiểu 5 ký tự.',
            'reason.max'      => 'Lý do huỷ tối đa 500 ký tự.',
        ];
    }
}
