<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\PartnerCalendarService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Endpoint trả booking + room block trong khoảng ngày cho Partner Portal 360
 * (Phase 3, T3.7). Range giới hạn 31 ngày để tránh truy vấn nặng — vượt sẽ
 * trả 422.
 */
final class PartnerCalendarController extends Controller
{
    public function __construct(
        private readonly PartnerCalendarService $calendarService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'nullable|integer|min:1',
            'room_id'     => 'nullable|integer|min:1',
            'from'        => 'required|date',
            'to'          => 'required|date|after_or_equal:from',
        ]);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $from = (string) $request->input('from');
        $to   = (string) $request->input('to');

        $rangeDays = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        if ($rangeDays > PartnerCalendarService::MAX_RANGE_DAYS) {
            return $this->validateError(
                ['range' => ['Khoảng ngày tối đa cho calendar là 31 ngày.']],
                'CALENDAR_RANGE_TOO_LARGE',
                HttpStatus::VALIDATION_ERROR,
            );
        }

        $partnerId  = (int) Auth::id();
        $propertyId = $request->filled('property_id') ? (int) $request->input('property_id') : null;
        $roomId     = $request->filled('room_id') ? (int) $request->input('room_id') : null;

        $payload = $this->calendarService->getCalendar($partnerId, $propertyId, $roomId, $from, $to);

        return $this->successResponse($payload, 'OK');
    }
}
