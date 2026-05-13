<?php

declare(strict_types=1);

namespace App\Http\Validations;

use App\Models\RoomBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;

/**
 * Validation cho endpoint Partner room-blocks (Phase 3).
 *
 * Theo convention dự án (xem `BookingValidation`), validator class trả về
 * `Illuminate\\Validation\\Validator` để controller xử lý lỗi qua
 * `validateError()` của base controller.
 */
class RoomBlockValidation
{
    public function createRoomBlockValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'room_id'    => 'required|integer|min:1|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'block_type' => 'required|string|in:' . implode(',', RoomBlock::BLOCK_TYPES),
            'reason'     => 'required|string|max:255',
            'note'       => 'nullable|string|max:1000',
        ], $this->messages());
    }

    public function listRoomBlockValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'property_id' => 'nullable|integer|min:1',
            'room_id'     => 'nullable|integer|min:1',
            'from'        => 'required|date',
            'to'          => 'required|date|after_or_equal:from',
        ], $this->messages());
    }

    public function deleteRoomBlockValidation(int $id): IlluminateValidator
    {
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:room_blocks,id',
        ], $this->messages());
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        $validation = __('room_block.validation');
        $messages = [];
        if (is_array($validation)) {
            foreach ($validation as $field => $rules) {
                if (! is_array($rules)) {
                    continue;
                }
                foreach ($rules as $rule => $msg) {
                    $messages[$field . '.' . $rule] = $msg;
                }
            }
        }

        return $messages;
    }
}
