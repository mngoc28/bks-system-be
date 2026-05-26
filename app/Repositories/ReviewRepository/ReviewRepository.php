<?php

declare(strict_types=1);

namespace App\Repositories\ReviewRepository;

use App\Models\Review;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Review::class;
    }

    /**
     * Get reviews by room ID
     *
     * @param int $roomId
     * @return Collection
     */
    public function getRoomReviews(int $roomId): Collection
    {
        return $this->model->newQuery()
            ->with(['user:id,name,avatar'])
            ->where('room_id', $roomId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get reviews by partner ID
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getPartnerReviews(int $partnerId): Collection
    {
        return $this->model->newQuery()
            ->with(['user:id,name,avatar'])
            ->where('partner_id', $partnerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get highest rating reviews for the landing page
     *
     * @param int $limit
     * @return Collection
     */
    public function getLandingPageReviews(int $limit = 6): Collection
    {
        return $this->model->newQuery()
            ->with([
                'user:id,name,avatar',
                'room:id,title',
                'partner.partnerInfo:id,user_id,company_name'
            ])
            ->where('rating', '=', 5)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Check if a review already exists for a booking and a specific type (room or partner)
     *
     * @param int $bookingId
     * @param string $type ('room' or 'partner')
     * @return bool
     */
    public function hasReviewed(int $bookingId, string $type): bool
    {
        $query = $this->model->newQuery()->where('booking_id', $bookingId);

        if ($type === 'room') {
            return $query->whereNotNull('room_id')->exists();
        }

        if ($type === 'partner') {
            return $query->whereNotNull('partner_id')->exists();
        }

        return false;
    }
}
