declare(strict_types=1);

namespace App\Repositories;

/**
 * Interface RepositoryInterface
 *
 * @package App\Repositories
 */
interface RepositoryInterface
{
    /**
     * Get all instances of model
     *
     * @return mixed
     */
    public function all();

    /**
     * Find a record by its primary key
     *
     * @param mixed $id
     * @return mixed
     */
    public function find($id);

    /**
     * Find a record by its primary key and selected columns
     *
     * @param mixed $id
     * @param array $columns
     * @return mixed
     */
    public function findOnlyColumn($id, $columns = ['*']);

    /**
     * Get the first record matching the current query
     *
     * @return mixed
     */
    public function first();

    /**
     * Create a new record in the database
     *
     * @param array $attributes
     * @return mixed
     */
    public function create($attributes = []);

    /**
     * Insert a new record into the database
     *
     * @param array $attributes
     * @return mixed
     */
    public function insert(array $attributes);

    /**
     * Update a record in the database
     *
     * @param mixed $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, $attributes = []);

    /**
     * Delete a record from the database
     *
     * @param mixed $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Display the specified record
     *
     * @param mixed $id
     * @return mixed
     */
    public function show($id);

    /**
     * Get the current query builder instance
     *
     * @return mixed
     */
    public function getQuery();

    /**
     * Reset the query builder instance
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function clearQuery();

    /**
     * Find multiple records by custom filters
     *
     * @param array $filter
     * @param bool $toArray
     * @return mixed
     */
    public function findBy(array $filter, bool $toArray = true);

    /**
     * Find a single record by custom filters
     *
     * @param array $filter
     * @param bool $toArray
     * @return mixed
     */
    public function findOneBy(array $filter, bool $toArray = true);

    /**
     * Get paginated results
     *
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|mixed
     */
    public function paginate($page);

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
    ): void;

    /**
     * Update or create a record matching the criteria
     *
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateOrCreate(
        array $attributes = [],
        array $params = []
    ): void;

    /**
     * Delete records matching specific criteria
     *
     * @param array $filter
     * @return void
     */
    public function deleteBy(array $filter): void;

    /**
     * Find records where a column is in a given array
     *
     * @param array $filter
     * @param bool $toArray
     * @return \Illuminate\Support\Collection|array
     */
    public function findWhereIn(array $filter, bool $toArray = true);

    /**
     * Delete records where a column is in a given array
     *
     * @param array $filter
     * @return void
     */
    public function deleteWhereIn(array $filter): void;

    /**
     * Count records matching specific criteria
     *
     * @param array $filter
     * @return int
     */
    public function countRecord(array $filter = []): int;

    /**
     * Find multiple records by an array of IDs
     *
     * @param array $ids
     * @param array $filter
     * @param bool $returnOnlyIds
     * @return array
     */
    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array;

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
    ): void;

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
    ): void;

    /**
     * Delete records not in a given array of IDs
     *
     * @param string $columnName
     * @param int $value
     * @param array $ids
     * @param string $primaryKey
     * @return void
     */
    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void;
}
