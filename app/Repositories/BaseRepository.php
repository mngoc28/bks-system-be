<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    // Model property on class instances
    protected $model;

    /** @var Builder */
    protected $query;

    // Constructor to bind model to repo
    public function __construct()
    {
        $this->setModel();
        $this->query = $this->model->newQuery();
    }

    // Get the associated model
    abstract public function getModel();

    // Set the associated model
    public function setModel()
    {
        $this->model = app()->make($this->getModel());
    }

    // Get all instances of model
    public function all()
    {
        return $this->model->all();
    }

    // create a new record in the database
    public function create($attributes = [])
    {
        return $this->model->create($attributes);
    }

    /**
     * Insert
     * @param array $attributes
     * @return mixed
     */
    public function insert(array $attributes)
    {
        return $this->model->insert($attributes);
    }

    // update record in the database
    public function update($id, $attributes = [])
    {
        $record = $this->find($id);
        // If record not found, return false
        if (!$record) {
            return false;
        }
        return $record->update($attributes);
    }

    // remove record from the database
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    // show the record with the given id
    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    // find the record with the given id
    public function find($id)
    {
        return $this->model->find($id);
    }

    // find the record with the given id and selected columns
    public function findOnlyColumn($id, $columns = ['*'])
    {
        return $this->model->select($columns)->where('id', $id)->first();
    }

    // find the first record
    public function first()
    {
        return $this->model->first();
    }

    // Eager load database relationships
    public function with($relations)
    {
        return $this->model->with($relations);
    }

    // phpcs:ignore
    public function getQuery()
    {
        return $this->query->getQuery();
    }

    public function clearQuery()
    {
        $this->query = $this->model->newQuery();
        return $this->query->getQuery();
    }

    public function findBy(array $filter, bool $toArray = true)
    {
        $builder = $this->model->newQuery();
        foreach ($filter as $key => $val) {
            $builder->where($key, $val);
        }
        $find = $builder->get();

        if (!$toArray) {
            return $find;
        }
        return $find ? $find->toArray() : null;
    }

    public function findOneBy(array $filter, bool $toArray = true)
    {
        $builder = $this->model->newQuery();
        foreach ($filter as $key => $val) {
            $builder->where($key, $val);
        }
        $data = $builder->first();

        if (!$toArray) {
            return $data;
        }
        return $data ? $data->toArray() : [];
    }

    /**
     * paginate
     * @param $page
     * @return LengthAwarePaginator|mixed
     */
    public function paginate($page)
    {
        return $this->query->paginate($page);
    }

    public function updateWhere(
        array $attributes = [],
        array $params = []
    ): void {
        $this->model->where($attributes)->update($params);
    }

    /**
     * deleteBy
     * @param array $filter
     * @return void
     */
    public function deleteBy(array $filter): void
    {
        $this->model->where($filter)->delete();
    }

    /**
     * Find where in the record with the given id
     * @param array $filter
     * @param bool $toArray
     * @return array|Collection
     */
    public function findWhereIn(array $filter, bool $toArray = true)
    {
        $data = $this->model->whereIn($filter['column'], $filter['values'])->get();

        if (!$toArray) {
            return $data;
        }
        return $data ? $data->toArray() : [];
    }

    /**
     * delete where in
     * @param array $filter
     * @return void
     */
    public function deleteWhereIn(array $filter): void
    {
        $this->model->whereIn($filter['column'], $filter['values'])->delete();
    }

    /**
     * Update or create
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateOrCreate(array $attributes = [], array $params = []): void
    {
        $this->model->updateOrCreate($attributes, $params);
    }

    /**
     * Count record
     *
     * @param array $filter
     * @return int
     */
    public function countRecord(array $filter = []): int
    {
        $query = $this->model->newQuery();

        foreach ($filter as $key => $value) {
            if (is_array($value) && isset($value['operator']) && isset($value['value'])) {
                $query->where($key, $value['operator'], $value['value']);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->count();
    }

    /**
     * Find records by array of ids
     * @param array $ids
     * @param array $filter
     * @param bool $returnOnlyIds
     * @return array
     */
    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array
    {
        //
        if (empty($ids)) {
            return [];
        }

        $chunkSize = config('const.CHUNK_SIZE');

        // Apply filter to query
        $applyFilter = function ($query) use ($filter) {
            foreach ($filter as $key => $value) {
                $query->where($key, $value);
            }
            return $query;
        };

        $results = collect();

        // If ids is greater than chunk size, use chunk to update
        if (count($ids) > $chunkSize) {
            $results = collect($ids)
                ->chunk($chunkSize)
                ->flatMap(function ($chunk) use ($applyFilter) {
                    $query = $applyFilter($this->model->newQuery()->whereIn('id', $chunk));
                    return $query->get();
                });
        } else {
            $query = $this->model->newQuery()->whereIn('id', $ids);
            $results = $applyFilter($query)->get();
        }

        // If return only ids, return array of ids
        return $returnOnlyIds
            ? $results->pluck('id')->toArray()
            : $results->toArray();
    }

    /**
     * Update where in
     * @param string $column
     * @param array $values
     * @param array $attributes
     * @param array $whereConditions
     * @return void
     */
    public function updateWhereIn(
        string $column,
        array $values,
        array $attributes,
        array $whereConditions = []
    ): void {
        $query = $this->model->whereIn($column, $values);
        if (!empty($whereConditions)) {
            $query->where($whereConditions);
        }
        $query->update($attributes);
    }

    /**
     * Update where not in with where conditions
     * @param string $column
     * @param array $values
     * @param array $attributes
     * @param array $whereConditions
     * @return void
     */
    public function updateWhereNotIn(
        string $column,
        array $values,
        array $attributes,
        array $whereConditions = []
    ): void {
        $query = $this->model->whereNotIn($column, $values);
        if (!empty($whereConditions)) {
            $query->where($whereConditions);
        }
        $query->update($attributes);
    }

    /**
     * Delete records not in ids
     * @param string $columnName Column name to filter (e.g. class_id, course_id, ...)
     * @param int $value Value of the column
     * @param array $ids List of IDs to keep
     * @param string $primaryKey Primary key column name (default is 'id')
     * @return void
     */
    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void
    {
        $this->model->where($columnName, $value)
            ->whereNotIn($primaryKey, $ids)
            ->delete();
    }
}
