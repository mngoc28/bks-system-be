<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\DepositService;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Events\BookingConfirmed;
use App\Jobs\SendBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

final class CheckoutController extends Controller
{
    public function __construct(
        private readonly DepositService $depositService,
    ) {
    }

    /**
     * Render the checkout page.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function checkoutPage(Request $request)
    {
        $bookingId = $request->query('booking_id');
        if (!$bookingId) {
            return response('Thiếu mã đặt phòng (booking_id)', 400);
        }

        $booking = Booking::with(['user', 'room.property'])->find((int)$bookingId);
        if (!$booking) {
            return response('Không tìm thấy đơn đặt phòng', 404);
        }

        if ($booking->status === 1) {
            $frontendUrl = config('app.url_frontend', 'http://localhost:3000') . '/booking-success';
            $redirectUrl = $frontendUrl . '?' . http_build_query([
                'bookingId'     => $booking->id,
                'bookingCode'   => $booking->booking_code,
                'email'         => $booking->user?->email,
                'paymentStatus' => 'success',
            ]);
            return redirect($redirectUrl);
        }

        $room = $booking->room;
        $property = $room?->property;
        $user = $booking->user;
        $totalPrice = $booking->total_amount;
        $depositAmount = $booking->deposit_amount ?? 0.0;

        // Dynamic QR code generation
        $bankId = env('VIETQR_BANK_ID', 'MB');
        $accountNo = env('VIETQR_ACCOUNT_NO', '0333494850');
        $accountName = env('VIETQR_ACCOUNT_NAME', 'HO MINH NGOC');
        $qrAmount = $depositAmount > 0 ? (int)$depositAmount : (int)$totalPrice;
        $paymentTypeLabel = $depositAmount > 0 ? "Số tiền đặt cọc cần đóng" : "Tổng tiền cần thanh toán";

        $qrUrlEncodedName = rawurlencode($accountName);
        $qrDescription = rawurlencode((string)$booking->booking_code);
        $qrImageUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact.jpg?amount={$qrAmount}&addInfo={$qrDescription}&accountName={$qrUrlEncodedName}";

        $startDateFormatted = Carbon::parse($booking->getRawOriginal('start_date'))->format('d/m/Y');
        $endDateFormatted = Carbon::parse($booking->getRawOriginal('end_date'))->format('d/m/Y');

        $errorMessage = $request->session()->get('error');
        $errorHtml = '';
        if ($errorMessage) {
            $errorHtml = <<<HTML
            <div class="mb-5 bg-rose-500/20 border border-rose-500/30 rounded-2xl p-4 text-xs font-bold text-rose-300 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{$errorMessage}</span>
            </div>
HTML;
        }

        $isLocal = config('app.env') === 'local';
        $simulateButtonHtml = '';
        if ($isLocal) {
            $simulateButtonHtml = <<<HTML
            <button type="submit" name="status" value="simulate_success" class="w-full bg-[#10b981] hover:bg-[#059669] text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg hover:shadow-emerald-500/10 flex items-center justify-center gap-2">
                Xác nhận thanh toán (Simulate Success - Dev Only)
            </button>
HTML;
        }

        $titleBadge = $isLocal ? 'BKS Stay Sandbox' : 'BKS Stay Checkout';
        $titleHeader = $isLocal ? 'CỔNG THANH TOÁN MÔ PHỎNG' : 'THANH TOÁN ĐƠN PHÒNG';
        $disclaimerText = $isLocal
            ? 'Đây là môi trường thử nghiệm (Sandbox) của BKS Stay.<br>Không thực hiện trừ tiền thật từ bất kỳ thẻ hoặc tài khoản nào.'
            : 'Vui lòng quét đúng mã QR và giữ nguyên nội dung chuyển khoản.<br>Hệ thống sẽ tự động xác nhận đơn phòng sau khi nhận được tiền.';

        // Render inline HTML styled with Tailwind CSS CDN
        $html = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKS STAY - Cổng Thanh Toán</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 text-slate-100 flex items-center justify-center p-4">
    <!-- Glow effects -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative w-full max-w-lg bg-slate-800/90 border border-slate-700/80 rounded-3xl shadow-2xl p-8 backdrop-blur-md">
        <!-- Logo -->
        <div class="text-center mb-6">
            <span class="text-xs uppercase tracking-widest text-sky-400 font-extrabold">{$titleBadge}</span>
            <h1 class="text-2xl font-black text-white mt-1">{$titleHeader}</h1>
        </div>

        <div class="h-px bg-slate-700/50 my-5"></div>

        {$errorHtml}

        <!-- Details -->
        <div class="space-y-4 mb-8">
            <div class="bg-slate-900/60 rounded-2xl p-4 border border-slate-700/30">
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Mã đặt phòng</p>
                <p class="text-lg font-mono font-black text-sky-300 mt-0.5">{$booking->booking_code}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-900/40 rounded-xl p-3 border border-slate-700/20">
                    <p class="text-[10px] uppercase text-slate-400 font-semibold">Khách hàng</p>
                    <p class="text-sm font-bold text-slate-200 mt-0.5 truncate">{$user->name}</p>
                </div>
                <div class="bg-slate-900/40 rounded-xl p-3 border border-slate-700/20">
                    <p class="text-[10px] uppercase text-slate-400 font-semibold">Email</p>
                    <p class="text-sm font-bold text-slate-200 mt-0.5 truncate">{$user->email}</p>
                </div>
            </div>

            <div class="bg-slate-900/40 rounded-xl p-4 border border-slate-700/20 space-y-2 text-sm text-slate-300">
                <div class="flex justify-between">
                    <span>Phòng:</span>
                    <span class="font-bold text-slate-100">{$room->title}</span>
                </div>
                <div class="flex justify-between">
                    <span>Cơ sở:</span>
                    <span class="font-medium text-slate-300">{$property->name}</span>
                </div>
                <div class="flex justify-between">
                    <span>Thời gian:</span>
                    <span class="font-medium text-slate-200">{$startDateFormatted} - {$endDateFormatted}</span>
                </div>
            </div>

            <!-- Dynamic VietQR Code Block -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 flex flex-col items-center justify-center space-y-3">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">Quét mã VietQR để thanh toán nhanh</p>
                <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-inner flex items-center justify-center">
                    <img src="{$qrImageUrl}" alt="VietQR" class="w-60 h-60 object-contain" />
                </div>
                <div class="text-center space-y-1">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Chủ tài khoản</p>
                    <p class="text-sm font-extrabold text-slate-800">{$accountName}</p>
                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mt-1">Nội dung chuyển khoản</p>
                    <p class="text-sm font-mono font-bold text-sky-600 bg-sky-50 px-3 py-1 rounded-xl inline-block border border-sky-100 select-all">{$booking->booking_code}</p>
                </div>
            </div>

            <div class="bg-sky-950/40 border border-sky-800/40 rounded-2xl p-5 flex flex-col justify-between">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-sky-300">{$paymentTypeLabel}</span>
                    <span class="text-2xl font-black text-sky-400">
                        <!-- Format Currency directly in php -->
                        {$this->formatPrice($qrAmount)} VNĐ
                    </span>
                </div>
                <div class="h-px bg-sky-900/30 my-3"></div>
                <div class="flex justify-between text-xs text-sky-400/80">
                    <span>Tổng tiền phòng:</span>
                    <span class="font-bold">{$this->formatPrice($totalPrice)} VNĐ</span>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <form method="POST" action="/api/v1/payments/checkout" class="space-y-3">
            <!-- CSRF Protection in Laravel requires token -->
            <input type="hidden" name="booking_id" value="{$booking->id}">
            <input type="hidden" name="_token" value="{$request->session()->token()}">
            
            <button type="submit" name="status" value="check_payment" class="w-full bg-sky-600 hover:bg-sky-500 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg hover:shadow-sky-500/10 flex items-center justify-center gap-2">
                Tôi đã chuyển khoản (Xác thực qua SePay)
            </button>
            
            {$simulateButtonHtml}
            
            <button type="submit" name="status" value="cancel" class="w-full bg-slate-700/50 hover:bg-slate-700 text-slate-300 py-3 px-6 rounded-2xl transition-all border border-slate-600/30">
                Hủy giao dịch
            </button>
        </form>

        <p class="text-[10px] text-center text-slate-500 mt-6 leading-relaxed">
            {$disclaimerText}
        </p>
    </div>
</body>
</html>
HTML;

        return response($html);
    }

    /**
     * Handle the checkout submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handlePayment(Request $request)
    {
        $bookingId = $request->input('booking_id');
        $status = $request->input('status');

        $booking = Booking::with(['user', 'room'])->find((int)$bookingId);
        if (!$booking) {
            return response('Không tìm thấy đơn đặt phòng', 404);
        }

        $frontendUrl = config('app.url_frontend', 'http://localhost:3000') . '/booking-success';

        // 1. Handle Cancel / Fail status
        if ($status === 'cancel') {
            return redirect($frontendUrl . '?' . http_build_query([
                'bookingId'     => $booking->id,
                'bookingCode'   => $booking->booking_code,
                'email'         => $booking->user?->email,
                'paymentStatus' => 'failed',
            ]));
        }

        // 2. Handle SePay Verification Check
        if ($status === 'check_payment') {
            if ($booking->status === 1) {
                return redirect($frontendUrl . '?' . http_build_query([
                    'bookingId'     => $booking->id,
                    'bookingCode'   => $booking->booking_code,
                    'email'         => $booking->user?->email,
                    'paymentStatus' => 'success',
                ]));
            }

            return redirect()->back()->with('error', 'Hệ thống chưa ghi nhận được thanh toán chuyển khoản của bạn qua SePay. Vui lòng quét mã VietQR để chuyển khoản và đợi từ 10-30 giây để hệ thống đồng bộ.');
        }

        // 3. Handle Simulate Success (Local Development Only)
        if ($status === 'simulate_success') {
            if (config('app.env') !== 'local') {
                return response('Unauthorized simulation in non-local environment', 403);
            }

            DB::beginTransaction();
            try {
                $now = Carbon::now();

                // Update booking payment timestamp and confirm
                $booking->update([
                    'status'               => 1, // CONFIRMED
                    'payment_collected_at' => $now,
                ]);

                // Confirm deposit receipt
                $this->depositService->confirmReceiptByPartner((int)$booking->id);

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
                    Log::error('Payment simulation: Failed to send confirmation email: ' . $mailEx->getMessage());
                }

                return redirect($frontendUrl . '?' . http_build_query([
                    'bookingId'     => $booking->id,
                    'bookingCode'   => $booking->booking_code,
                    'email'         => $booking->user?->email,
                    'paymentStatus' => 'success',
                ]));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Payment failed to process simulation: ' . $e->getMessage());
                return response('Lỗi xử lý thanh toán giả lập: ' . $e->getMessage(), 500);
            }
        }

        // Fallback for unexpected status values
        return redirect()->back()->with('error', 'Yêu cầu không hợp lệ.');
    }

    private function formatPrice($amount): string
    {
        return number_format((float)$amount, 0, ',', '.');
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
        // Re-dispatch booking mail if user has email
        $user = $booking->user;
        if ($user && $user->email) {
            // Fetch necessary mail info
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
            $totalDays = \App\Services\BookingStayAmountCalculator::countStayDays(
                $startDate->toDateString(),
                $endDate->toDateString(),
            );
            $roomStayTotal = \App\Services\BookingStayAmountCalculator::computeRoomStayTotalForRoomPrice(
                $startDate->toDateString(),
                $endDate->toDateString(),
                $roomPrice,
            );

            $grandTotal = round($roomStayTotal + $servicesTotal, 2);

            $emailInfo = [
                'booking_code'       => $booking->booking_code,
                'booking_created_at' => Carbon::parse($booking->created_at)
                    ->timezone('Asia/Ho_Chi_Minh')
                    ->format('d/m/Y H:i:s'),
                'room_title'         => $room->title,
                'room_description'   => $room->description,
                'room_deposit'       => $room->deposit ?? 0,
                'amenities'          => $room->amenities ?? [],
                'services'           => $services,
                'room_url'           => config('app.url_frontend') . '/rooms/' . $booking->room_id,
                'bookings_url'       => config('app.url_frontend') . '/bks-stay/bookings/' . $booking->id,
                'is_first_time'      => false, // assume existing user since they just checked out payment
                'company_name'       => $room->property?->user?->partnerInfo?->company_name ?? '',
                'company_phone'      => $room->property?->user?->partnerInfo?->phone ?? '',
                'partner_address'    => $room->property?->user?->partnerInfo?->address ?? '',
                'property_name'      => $room->property?->name ?? '',
                'property_address'   => $room->property?->address_detail ?? '',
                'start_time'         => $startDate->format('d/m/Y'),
                'end_time'           => $endDate->format('d/m/Y'),
                'total_days'         => $totalDays,
                'room_stay_amount'   => $roomStayTotal,
                'services_total'     => $servicesTotal,
                'unit_price'         => (float) ($roomPrice?->price ?? 0),
                'price_unit'         => (string) ($roomPrice?->unit ?? 'day'),
                'deposit_deadline'   => Carbon::now()->addHours(2)->toIso8601String(), // Mock deadline
                'cancellation_policy' => 'Refundable up to 24 hours before check-in',
                'total_amount'       => $grandTotal,
                'goline_phone'       => '0243 795 7250',
                'token'              => '',
            ];

            SendBooking::dispatch($user->email, $user->name, $emailInfo);
        }
    }
}
