<?php

declare(strict_types=1);

namespace App\Repositories\UserReportRepository;

use App\Models\UserReport;
use App\Repositories\BaseRepository;

/**
 * Class UserReportRepository
 *
 * @package App\Repositories\UserReportRepository
 */
class UserReportRepository extends BaseRepository implements UserReportRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return UserReport::class;
    }
}
