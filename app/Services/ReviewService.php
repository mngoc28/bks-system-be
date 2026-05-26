<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Enums\BookingStatus;
use App\Repositories\ReviewRepository\ReviewRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewService
{
    protected ReviewRepositoryInterface $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Get reviews for a room along with statistics
     *
     * @param int $roomId
     * @return array
     */
    public function getRoomReviews(int $roomId): array
    {
        try {
            $reviews = $this->reviewRepository->getRoomReviews($roomId);
            $totalCount = $reviews->count();
            $averageRating = $totalCount > 0 ? round($reviews->avg('rating'), 1) : 0;

            return [
                'success' => true,
                'data' => [
                    'reviews' => $reviews,
                    'average_rating' => $averageRating,
                    'total_count' => $totalCount,
                ],
                'message' => 'Lấy danh sách đánh giá phòng thành công'
            ];
        } catch (Exception $e) {
            Log::error('Get room reviews error: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => 'Lỗi khi tải đánh giá phòng'
            ];
        }
    }

    /**
     * Get reviews for a partner along with statistics
     *
     * @param int $partnerId
     * @return array
     */
    public function getPartnerReviews(int $partnerId): array
    {
        try {
            $partnerInfo = \App\Models\PartnerInfo::find($partnerId);
            $partnerUserId = $partnerInfo ? $partnerInfo->user_id : $partnerId;

            $reviews = $this->reviewRepository->getPartnerReviews((int)$partnerUserId);
            $totalCount = $reviews->count();
            $averageRating = $totalCount > 0 ? round($reviews->avg('rating'), 1) : 0;

            return [
                'success' => true,
                'data' => [
                    'reviews' => $reviews,
                    'average_rating' => $averageRating,
                    'total_count' => $totalCount,
                ],
                'message' => 'Lấy danh sách đánh giá đối tác thành công'
            ];
        } catch (Exception $e) {
            Log::error('Get partner reviews error: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => 'Lỗi khi tải đánh giá đối tác'
            ];
        }
    }

    /**
     * Get latest high rating reviews for landing page
     *
     * @return array
     */
    public function getLandingPageReviews(): array
    {
        try {
            $reviews = $this->reviewRepository->getLandingPageReviews(6);
            return [
                'success' => true,
                'data' => $reviews,
                'message' => 'Lấy danh sách đánh giá trang chủ thành công'
            ];
        } catch (Exception $e) {
            Log::error('Get landing page reviews error: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => 'Lỗi khi tải đánh giá trang chủ'
            ];
        }
    }

    /**
     * Submit reviews for a booking (both room and partner)
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function submitBookingReviews(int $userId, array $data): array
    {
        DB::beginTransaction();
        try {
            $bookingId = (int)$data['booking_id'];
            $booking = Booking::with('room.property')->find($bookingId);

            if (!$booking) {
                return [
                    'success' => false,
                    'message' => 'Đơn đặt phòng không tồn tại'
                ];
            }

            // Verify booking belongs to this user
            if ($booking->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Bạn không có quyền đánh giá đơn đặt phòng này'
                ];
            }

            // Verify booking is completed or stay status is checked out
            $isCompleted = ($booking->status === BookingStatus::COMPLETED->value)
                || ($booking->stay_status === 'checked_out');
            if (!$isCompleted) {
                return [
                    'success' => false,
                    'message' => 'Chỉ có thể đánh giá sau khi hoàn thành kỳ nghỉ'
                ];
            }

            $createdReviews = [];

            // 1. Handle Room Review
            if (isset($data['room_rating'])) {
                if ($this->reviewRepository->hasReviewed($bookingId, 'room')) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Bạn đã đánh giá phòng này cho kỳ nghỉ này rồi'
                    ];
                }

                $roomReview = $this->reviewRepository->create([
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'room_id' => $booking->room_id,
                    'rating' => (int)$data['room_rating'],
                    'comment' => $data['room_comment'] ?? null,
                ]);

                $createdReviews['room'] = $roomReview;
            }

            // 2. Handle Partner Review
            if (isset($data['partner_rating'])) {
                if ($this->reviewRepository->hasReviewed($bookingId, 'partner')) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Bạn đã đánh giá đối tác này cho kỳ nghỉ này rồi'
                    ];
                }

                $partnerUserId = $booking->room->property->user_id ?? null;
                if (!$partnerUserId) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Không tìm thấy thông tin đối tác của phòng này'
                    ];
                }

                $partnerReview = $this->reviewRepository->create([
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'partner_id' => $partnerUserId,
                    'rating' => (int)$data['partner_rating'],
                    'comment' => $data['partner_comment'] ?? null,
                ]);

                $createdReviews['partner'] = $partnerReview;
            }

            DB::commit();
            return [
                'success' => true,
                'data' => $createdReviews,
                'message' => 'Gửi đánh giá thành công!'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Submit booking reviews error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi gửi đánh giá: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get existing reviews for a booking
     *
     * @param int $bookingId
     * @return array
     */
    public function getBookingReviews(int $bookingId): array
    {
        try {
            $reviews = Review::where('booking_id', $bookingId)->get();
            return [
                'success' => true,
                'data' => $reviews,
                'message' => 'Lấy đánh giá đơn đặt phòng thành công'
            ];
        } catch (Exception $e) {
            Log::error('Get booking reviews error: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => 'Lỗi khi tải đánh giá đơn đặt phòng'
            ];
        }
    }
}
