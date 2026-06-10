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
            'start_date_mode' => 'nullable|string|in:exact,from',
            'end_date_mode' => 'nullable|string|in:exact,to',
            'stay_status' => 'nullable|string|in:pending,checked_in,checked_out,no_show',
            'deposit_status' => 'nullable|string|in:none,pending,payment_submitted,confirmed_by_partner,held_in_escrow,refunded,forfeited,expired_cancelled',
            'payment_status' => 'nullable|string|in:unpaid,partially_paid,paid,refunded',
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
     * Partner cancel booking validation. The reason is mandatory and stored
     * into `bookings.cancellation_reason` for later audit / customer support.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Validation\Validator
     */
    public function partnerCancelBookingValidation(Request $request, int $id): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        $data = array_merge(['id' => $id], $request->all());
        return Validator::make($data, [
            'id'     => 'required|integer|exists:bookings,id',
            'reason' => 'required|string|min:5|max:500',
        ], $messages);
    }

    /**
     * Partner mark booking as no-show. Only requires booking id; eligibility
     * (status / start_date) is enforced in the service layer because business
     * rules are centralized there.
     *
     * @param int $id
     * @return \Illuminate\Validation\Validator
     */
    public function partnerNoShowBookingValidation(int $id): \Illuminate\Validation\Validator
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
     *
     * @param \Illuminate\Http\Request $request
     * @param int $roomId Room id from route (must exist)
     * @return \Illuminate\Validation\Validator
     */
    public function userCreateBookingValidation(Request $request, int $roomId): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();
        $data = array_merge(['room_id' => $roomId], $request->all());

        return Validator::make($data, [
            'room_id'       => 'required|integer|exists:rooms,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'required|string|max:20',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'price_id'      => 'nullable|integer|min:1',
            'note'          => 'nullable|string',
            'service_ids'   => 'nullable|array',
            'service_ids.*' => 'integer|exists:services,id',
            'payment_method'=> 'required|string|in:online,pay_at_counter',
        ], $messages);
    }

    /**
     * Public lookup: email + booking code (no auth).
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function publicBookingLookupValidation(Request $request): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();

        return Validator::make($request->all(), [
            'email'         => 'required|email|max:255',
            'booking_code'  => 'required|string|max:32|regex:/^RM-\d{4}-\d{1,9}$/i',
        ], $messages);
    }

    /**
     * Public update guest email: validation.
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function publicUpdateBookingEmailValidation(Request $request): \Illuminate\Validation\Validator
    {
        $messages = $this->getCustomMessages();

        return Validator::make($request->all(), [
            'booking_code' => 'required|string|max:32|regex:/^RM-\d{4}-\d{1,9}$/i',
            'old_email'    => 'required|email|max:255',
            'new_email'    => 'required|email|max:255|different:old_email',
        ], $messages);
    }
}
