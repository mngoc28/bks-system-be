<?php

declare(strict_types=1);

namespace App\Repositories\ReviewRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface extends RepositoryInterface
{
    /**
     * Get reviews by room ID
     *
     * @param int $roomId
     * @return Collection
     */
    public function getRoomReviews(int $roomId): Collection;

    /**
     * Get reviews by partner ID
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getPartnerReviews(int $partnerId): Collection;

    /**
     * Get highest rating reviews for the landing page
     *
     * @param int $limit
     * @return Collection
     */
    public function getLandingPageReviews(int $limit = 6): Collection;

    /**
     * Check if a review already exists for a booking and a specific type (room or partner)
     *
     * @param int $bookingId
     * @param string $type ('room' or 'partner')
     * @return bool
     */
    public function hasReviewed(int $bookingId, string $type): bool;
}
