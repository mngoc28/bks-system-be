declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseRepository
 *
 * @package App\Repositories
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The query builder instance
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->setModel();
        $this->query = $this->model->newQuery();
    }

    /**
     * Get the associated model class name
     *
     * @return string
     */
    abstract public function getModel();

    /**
     * Set the associated model instance
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setModel()
    {
        $this->model = app()->make($this->getModel());
    }

    /**
     * Get all instances of the model
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Create a new record in the database
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|$this
     */
    public function create($attributes = [])
    {
        return $this->model->create($attributes);
    }

    /**
     * Insert a new record into the database
     *
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes)
    {
        return $this->model->insert($attributes);
    }

    /**
     * Update a record in the database
     *
     * @param mixed $id
     * @param array $attributes
     * @return bool|mixed
     */
    public function update($id, $attributes = [])
    {
        $record = $this->find($id);
        // If record not found, return false
        if (!$record) {
            return false;
        }
        return $record->update($attributes);
    }

    /**
     * Remove a record from the database
     *
     * @param mixed $id
     * @return int
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Display the specified record or throw exception if not found
     *
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[]
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find a record with the given ID
     *
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[]|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Find a record with the given ID and selected columns
     *
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function findOnlyColumn($id, $columns = ['*'])
    {
        return $this->model->select($columns)->where('id', $id)->first();
    }

    /**
     * Find the first record matching the current query
     *
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function first()
    {
        return $this->model->first();
    }

    /**
     * Eager load database relationships
     *
     * @param array|string $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with($relations)
    {
        return $this->model->with($relations);
    }

    /**
     * Get the current query builder instance
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        return $this->query->getQuery();
    }

    /**
     * Reset the query builder instance
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function clearQuery()
    {
        $this->query = $this->model->newQuery();
        return $this->query->getQuery();
    }

    /**
     * Find multiple records by custom filters
     *
     * @param array $filter
     * @param bool $toArray
     * @return array|null|\Illuminate\Database\Eloquent\Collection|static[]
     */
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

    /**
     * Find a single record by custom filters
     *
     * @param array $filter
     * @param bool $toArray
     * @return array|\Illuminate\Database\Eloquent\Model|static|null
     */
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
     * Get paginated results
     *
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|mixed
     */
    public function paginate($page)
    {
        return $this->query->paginate($page);
    }

    /**
     * Update records matching specific criteria
     *
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateWhere(
        array $attributes = [],
        array $params = []
    ): void {
        $this->model->where($attributes)->update($params);
    }

    /**
     * Delete records matching specific criteria
     *
     * @param array $filter
     * @return void
     */
    public function deleteBy(array $filter): void
    {
        $this->model->where($filter)->delete();
    }

    /**
     * Find records where a column is in a given array
     *
     * @param array $filter
     * @param bool $toArray
     * @return \Illuminate\Support\Collection|array
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
     * Delete records where a column is in a given array
     *
     * @param array $filter
     * @return void
     */
    public function deleteWhereIn(array $filter): void
    {
        $this->model->whereIn($filter['column'], $filter['values'])->delete();
    }

    /**
     * Update or create a record matching the criteria
     *
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateOrCreate(array $attributes = [], array $params = []): void
    {
        $this->model->updateOrCreate($attributes, $params);
    }

    /**
     * Count records matching specific criteria
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
     * Find multiple records by an array of IDs
     *
     * @param array $ids
     * @param array $filter
     * @param bool $returnOnlyIds
     * @return array
     */
    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array
    {
        // Return empty array if no IDs provided
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

        // If ids is greater than chunk size, use chunk to avoid database limits
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

        // Return array of IDs or array of record attributes
        return $returnOnlyIds
            ? $results->pluck('id')->toArray()
            : $results->toArray();
    }

    /**
     * Update records where a column is in a given array
     *
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
     * Update records where a column is NOT in a given array
     *
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
     * Delete records not in a given array of IDs
     *
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
