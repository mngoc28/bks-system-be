<?php

declare(strict_types=1);

namespace App\Repositories\ServiceRepository;

use App\Models\Service;
use App\Repositories\BaseRepository;
use App\Repositories\ServiceRepository\ServiceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class ServiceRepository
 *
 * @package App\Repositories\ServiceRepository
 */
class ServiceRepository extends BaseRepository implements ServiceRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Service::class;
    }
    /**
     * Get all services or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearch($request): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Add search filters if provided
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        // sort
        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');

        if (
            $sortField
            && in_array($sortField, ['id', 'name', 'price', 'created_at', 'updated_at'])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination parameters
        $perPage = (int) $request->input('per_page', config('const.DEFAULT_PER_PAGE_BUILDING'));
        $page    = (int) $request->input('page', config('const.DEFAULT_PAGE'));
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all services without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllServices(): \Illuminate\Support\Collection
    {
        return $this->model->select('id', 'name')->get();
    }
}
