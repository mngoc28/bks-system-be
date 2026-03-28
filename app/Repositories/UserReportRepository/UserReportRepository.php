<?php

namespace App\Repositories\UserReportRepository;

use App\Models\UserReport;
use App\Repositories\BaseRepository;

class UserReportRepository extends BaseRepository implements UserReportRepositoryInterface
{
    public function getModel(): string
    {
        return UserReport::class;
    }
}
