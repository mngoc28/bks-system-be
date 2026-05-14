<?php

declare(strict_types=1);

namespace App\Repositories\PropertyRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PropertyRepositoryInterface extends RepositoryInterface
{
    public function getAllOrSearchProperties(Request $request, array $sort = []): LengthAwarePaginator;

    public function getAllPropertyTypes(): Collection;

    public function getPropertyById(int $id): object | null;

    public function getAllPropertyNames(): Collection;

    public function getPropertiesForPartner(int $partnerId, Request $request, array $sort = []): LengthAwarePaginator;

    public function getPropertyByIdForPartner(int $id, int $partnerId): object|null;

    public function getPropertyNamesForPartner(int $partnerId): Collection;
}
