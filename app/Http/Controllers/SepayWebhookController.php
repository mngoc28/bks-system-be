<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\DepositService;
use App\Services\LeaseDepositGateService;
use App\Services\BookingPaymentStatusService;
use App\Events\BookingConfirmed;
use App\Jobs\SendBooking;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SepayWebhookController extends Controller
{
    public function __construct(
        private readonly DepositService $depositService,
    ) {
    }

    /**
     * Handle the incoming SePay transaction webhook.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        // 1. Verify Authorization Header token
        $authorizationHeader = $request->header('Authorization');

        $token = null;
        if ($authorizationHeader && preg_match('/^(Bearer|Apikey)\s+(.+)$/i', $authorizationHeader, $matches)) {
            $token = $matches[2];
        }

        $expectedToken = config('sepay.api_key');

        Log::info('SePay Webhook Header received: ' . ($authorizationHeader ?? 'NULL'));
        Log::info('SePay Webhook Extracted token: ' . ($token ?? 'NULL'));
        Log::info('SePay Webhook Expected: ' . $expectedToken);

        if (!$token || $token !== $expectedToken) {
            Log::warning('SePay Webhook: Unauthorized request attempt.');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. Parse transaction payload
        Log::info('SePay Webhook Request Payload: ' . json_encode($request->all()));

        $transactionContent = $request->input('content')
            ?? $request->input('transactionContent')
            ?? $request->input('description');

        if (!$transactionContent) {
            Log::warning('SePay Webhook: Missing transaction content.');
            return response()->json(['error' => 'Missing transactionContent'], 400);
        }

        // Try to match and retrieve the Booking using multiple patterns
        $booking = null;
        $bookingId = null;
        $isRemainderPayment = false;

        // Pattern 0: BKS REMAIN {booking_id}
        if (preg_match('/BKS\s*REMAIN\s*(\d+)/i', $transactionContent, $matches)) {
            $bookingId = (int) $matches[1];
            $booking = Booking::with(['user', 'room.property', 'price', 'services'])->find($bookingId);
            $isRemainderPayment = true;
        }

        // Pattern 1: BKS DEPOSIT {booking_id}
        if (!$booking && preg_match('/BKS\s*DEPOSIT\s*(\d+)/i', $transactionContent, $matches)) {
            $bookingId = (int)$matches[1];
            $booking = Booking::with(['user', 'room.property', 'price', 'services'])->find($bookingId);
        }

        // Pattern 2: RM-YYYY-ID (booking_code digits, supporting optional hyphens)
        if (!$booking && preg_match('/RM-?\d{4}-?(\d+)/i', $transactionContent, $matches)) {
            $bookingId = (int)$matches[1];
            $booking = Booking::with(['user', 'room.property'])->find($bookingId);
        }

        // Pattern 3: Direct search by booking_code (exact text matching, supporting optional hyphens)
        if (!$booking && preg_match('/RM-?(\d{4})-?(\d+)/i', $transactionContent, $matches)) {
            $reconstructedCode = sprintf('RM-%04d-%06d', (int)$matches[1], (int)$matches[2]);
            $booking = Booking::with(['user', 'room.property'])->where('booking_code', $reconstructedCode)->first();
        }

        if (!$booking) {
            Log::error('SePay Webhook: Booking not found for transaction content: ' . $transactionContent);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $depositBlockReason = LeaseDepositGateService::depositBlockReason($booking);
        $isDepositPayment = (bool) preg_match('/BKS\s*DEPOSIT/i', (string) $transactionContent);
        $shouldProcessRemainder = $isRemainderPayment
            || (BookingPaymentStatusService::isRemainderPaymentPhase($booking) && !$isDepositPayment);

        if ($depositBlockReason !== null && !$shouldProcessRemainder) {
            Log::warning('SePay Webhook: Deposit blocked — lease not signed for booking ID: ' . $booking->id);
            return response()->json(['error' => $depositBlockReason], 422);
        }

        if ($shouldProcessRemainder) {
            if ((string) $booking->payment_status === PaymentStatus::PAID->value) {
                return response()->json(['success' => true, 'message' => 'Booking balance already paid']);
            }

            BookingPaymentStatusService::markFullyPaid($booking);
            Log::info("SePay Webhook: Remainder payment recorded for booking ID: {$booking->id}");

            return response()->json(['success' => true, 'message' => 'Remainder payment recorded successfully']);
        }

        if ($booking->status === 1) {
            Log::info('SePay Webhook: Booking already confirmed: ' . $bookingId);
            return response()->json(['success' => true, 'message' => 'Booking was already confirmed']);
        }

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $hasDeposit = (float) ($booking->deposit_amount ?? 0) > 0;

            $booking->update([
                'status'               => 1,
                'payment_collected_at' => $now,
            ]);

            if ($hasDeposit) {
                $this->depositService->confirmReceiptByPartner((int) $booking->id);
                $booking->refresh();
                BookingPaymentStatusService::sync($booking);
            } else {
                BookingPaymentStatusService::markFullyPaid($booking);
            }

            // Broadcast confirm event
            $scope = $this->resolveBroadcastScope($booking);
            if ($scope['partner_id'] !== null && $scope['property_id'] !== null) {
                event(new BookingConfirmed($booking, $scope['partner_id'], $scope['property_id'], null));
            }

            DB::commit();

            // Trigger Email dispatch for user
            try {
                $this->sendConfirmationMail($booking);
            } catch (\Exception $mailEx) {
                Log::error('SePay Webhook: Failed to send confirmation email: ' . $mailEx->getMessage());
            }

            Log::info("SePay Webhook: Successfully processed payment and confirmed booking ID: {$bookingId}");
            return response()->json(['success' => true, 'message' => 'Booking confirmed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SePay Webhook: Failed to process: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    private function resolveBroadcastScope(Booking $booking): array
    {
        $room = $booking->room;
        if (!$room) {
            return ['partner_id' => null, 'property_id' => null];
        }
        $property = $room->property;
        if (!$property) {
            return ['partner_id' => null, 'property_id' => null];
        }
        return [
            'partner_id'  => (int)$property->user_id,
            'property_id' => (int)$property->id,
        ];
    }

    private function sendConfirmationMail(Booking $booking): void
    {
        $booking->loadMissing(['user', 'price']);
        $user = $booking->user;
        if ($user && $user->email) {
            $room = Booking::with(['room.property.user.partnerInfo', 'services'])->find($booking->id)->room;
            $selectedServices = $booking->services()->select('name', 'price')->get();
            $servicesTotal = (float) $selectedServices->sum(fn ($service) => (float) ($service->price ?? 0));
            $services = $selectedServices->map(fn ($service) => [
                'name'   => $service->name,
                'amount' => (float) ($service->price ?? 0),
            ])->toArray();

            $startDate = Carbon::parse($booking->start_date);
            $endDate = Carbon::parse($booking->end_date);

            $roomPrice = $booking->price;
            $pricingFields = \App\Services\BookingStayAmountCalculator::buildEmailPricingFields(
                $startDate->toDateString(),
                $endDate->toDateString(),
                $roomPrice,
                $servicesTotal,
                (float) ($booking->deposit_amount ?? 0),
            );

            $emailInfo = array_merge([
                'booking_code'       => $booking->booking_code,
                'booking_created_at' => Carbon::parse($booking->created_at)
                    ->timezone('Asia/Ho_Chi_Minh')
                    ->format('d/m/Y H:i:s'),
                'room_title'         => $room->title,
                'room_description'   => $room->description,
                'amenities'          => $room->amenities ?? [],
                'services'           => $services,
                'room_url'           => config('app.url_frontend') . '/rooms/' . $booking->room_id,
                'bookings_url'       => config('app.url_frontend') . '/bks-stay/bookings/' . $booking->id,
                'is_first_time'      => false,
                'company_name'       => $room->property?->user?->partnerInfo?->company_name ?? '',
                'company_phone'      => $room->property?->user?->partnerInfo?->phone ?? '',
                'partner_address'    => $room->property?->user?->partnerInfo?->address ?? '',
                'property_name'      => $room->property?->name ?? '',
                'property_address'   => $room->property?->address_detail ?? '',
                'start_time'         => $startDate->format('d/m/Y'),
                'end_time'           => $endDate->format('d/m/Y'),
                'deposit_deadline'   => Carbon::now()->addHours(2)->toIso8601String(),
                'cancellation_policy' => 'Refundable up to 24 hours before check-in',
                'goline_phone'       => '0243 795 7250',
                'token'              => '',
                'is_paid'            => true,
                'paid_amount'        => (float) ($booking->deposit_amount ?? 0) > 0
                    ? (float) $booking->deposit_amount
                    : $pricingFields['total_amount'],
            ], $pricingFields);

            SendBooking::dispatch($user->email, $user->name, $emailInfo);
        }
    }
}
