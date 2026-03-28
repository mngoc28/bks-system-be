<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get all
     * @return mixed
     */
    public function all();

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @param array $columns
     * @return mixed
     */
    public function findOnlyColumn($id, $columns = ['*']);

    /**
     * Get one
     * @return mixed
     */
    public function first();

    /**
     * Create
     * @param array $attributes
     * @return mixed
     */
    public function create($attributes = []);

    /**
     * Insert
     * @param array $attributes
     * @return mixed
     */
    public function insert(array $attributes);

    /**
     * Update
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, $attributes = []);

    /**
     * Delete
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Show
     * @param $id
     * @return mixed
     */
    public function show($id);

    /**
     * Get query
     * @return mixed
     */
    public function getQuery();

    /**
     * Clear query
     * @return \Illuminate\Database\Query\Builder
     */
    public function clearQuery();

    /**
     * File all
     * @param array $filter
     * @return mixed
     */
    public function findBy(array $filter, bool $toArray = true);

    /**
     * Find one
     * @param array $filter
     * @return mixed
     */
    public function findOneBy(array $filter, bool $toArray = true);

    /**
     * paginate
     * @param $page
     * @return LengthAwarePaginator|mixed
     */
    public function paginate($page);

    public function updateWhere(
        array $attributes = [],
        array $params = []
    ): void;

    /**
     * Update or create
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateOrCreate(
        array $attributes = [],
        array $params = []
    ): void;

    /**
     * Delete by
     * @param array $filter
     * @return void
     */
    public function deleteBy(array $filter): void;

    /**
     * Find where in the record with the given id
     * @param array $filter
     * @param bool $toArray
     * @return array|Collection
     */
    public function findWhereIn(array $filter, bool $toArray = true);

    /**
     * Delete where in
     * @param array $filter
     * @return void
     */
    public function deleteWhereIn(array $filter): void;

    /**
     * Count record
     * @param array $filter
     * @return int
     */
    public function countRecord(array $filter = []): int;

    /**
     * Find by array of ids
     * @param array $ids
     * @param array $filter
     * @param bool $returnOnlyIds
     * @return array
     */
    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array;

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
    ): void;

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
    ): void;

    /**
     * Delete records not in ids
     * @param string $columnName
     * @param int $value
     * @param array $ids
     * @param string $primaryKey
     * @return void
     */
    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void;
}
