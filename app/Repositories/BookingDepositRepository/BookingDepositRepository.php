<?php

declare(strict_types=1);

namespace App\Repositories\BookingDepositRepository;

use App\Models\BookingDeposit;
use App\Repositories\BaseRepository;

/**
 * Class BookingDepositRepository
 *
 * @package App\Repositories\BookingDepositRepository
 */
final class BookingDepositRepository extends BaseRepository implements BookingDepositRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return BookingDeposit::class;
    }
}
