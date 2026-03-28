<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums\BookingStatus;

class BookingValidation
{
    /**
     * Summary of search booking validation
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function searchBookingValidation(Request $request): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        return Validator::make($request->all(), [
            'start_date'=> 'nullable|date',
            'end_date'  => 'nullable|date|after_or_equal:start_date',
            'per_page'  => 'nullable|integer|min:1',
            'page'      => 'nullable|integer|min:1',
            'status'    => 'nullable|integer|in:'.implode(
                ',',
                array_map(fn($status) => $status->value, BookingStatus::cases())
            ),
        ], $messages);
    }

    /**
     * Summary of detail booking validation
     * @param $id
     * @return \Illuminate\Validation\Validator
     */
    public function detailBookingValidation($id): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:bookings,id',
        ], $messages);
    }

    /**
     * Summary of create booking validation
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function createBookingValidation(Request $request): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        return Validator::make($request->all(), [
            'user_id'   => 'required|integer|min:1|exists:users,id',
            'room_id'   => 'required|integer|min:1|exists:rooms,id',
            'start_date'=> 'required|date',
            'end_date'  => 'required|date|after_or_equal:start_date',
            'note'      => 'nullable|string',
        ], $messages);
    }

    /**
     * Summary of confirm booking validation
     * @param $id
     * @return \Illuminate\Validation\Validator
     */
    public function confirmBookingValidation($id): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:bookings,id',
        ], $messages);
    }

    /**
     * Summary of cancel booking validation
     * @param $id
     * @return \Illuminate\Validation\Validator
     */
    public function cancelBookingValidation($id): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:bookings,id',
        ], $messages);
    }

    /**
     * Update booking validation (admin)
     * Allows updating start_date, end_date, status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Validation\Validator
     */
    public function updateBookingValidation(Request $request, int $id): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        $data = array_merge(['id' => $id], $request->all());
        return Validator::make($data, [
            'id'         => 'required|integer|exists:bookings,id',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'nullable|int|in:'.implode(
                ',',
                array_map(fn($status) => $status->value, BookingStatus::cases())
            ),
        ], $messages);
    }

    /**
     * Destroy booking validation (admin)
     *
     * @param int $id
     * @return \Illuminate\Validation\Validator
     */
    public function destroyBookingValidation(int $id): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:bookings,id',
        ], $messages);
    }

    /**
     * Summary of getCustomMessages
     * @return array<mixed|string>
     */
    protected function getCustomMessages(): array
    {
        $validation = __('booking.validation');
        $messages = [];
        foreach ($validation as $field => $rules) {
            foreach ($rules as $rule => $msg) {
                $messages[$field.'.'.$rule] = $msg;
            }
        }
        return $messages;
    }

    /**
     * ============================================
     * USER API VALIDATIONS
     * ============================================
     */

    /**
     * Summary of user create booking validation
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function userCreateBookingValidation(Request $request): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        return Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'phone'     => 'required|string|max:20',
            'start_date'=> 'required|date',
            'end_date'  => 'required|date|after:start_date',
            'price_id'  => 'nullable|integer|min:1',
            'note'      => 'nullable|string',
            'services'  => 'nullable|array',
            'services.*'=> 'integer|exists:services,id',
        ], $messages);
    }
}
