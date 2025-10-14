<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Base Repository Interface
 * 
 * Defines the contract for all repository implementations in the application.
 * This interface ensures consistent data access patterns across all repositories.
 * 
 * @package App\Repositories\Contracts
 * @author DriveLink Development Team
 * @since 2.0.0
 */
interface RepositoryInterface
{
    /**
     * Get all records from the repository.
     *
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Get paginated records from the repository.
     *
     * @param int $perPage Number of records per page
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    /**
     * Find a record by its primary key.
     *
     * @param int|string $id Primary key value
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return Model|null
     */
    public function find($id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find a record by its primary key or throw an exception.
     *
     * @param int|string $id Primary key value
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Find records by a specific field value.
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return Collection
     */
    public function findBy(string $field, $value, array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find a single record by a specific field value.
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return Model|null
     */
    public function findOneBy(string $field, $value, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find records matching multiple criteria.
     *
     * @param array $criteria Array of field => value pairs
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return Collection
     */
    public function findWhere(array $criteria, array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find a single record matching multiple criteria.
     *
     * @param array $criteria Array of field => value pairs
     * @param array $columns Columns to select
     * @param array $relations Relationships to eager load
     * @return Model|null
     */
    public function findOneWhere(array $criteria, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Create a new record.
     *
     * @param array $data Data to create the record
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update a record by its primary key.
     *
     * @param int|string $id Primary key value
     * @param array $data Data to update
     * @return Model
     */
    public function update($id, array $data): Model;

    /**
     * Update or create a record matching the criteria.
     *
     * @param array $criteria Criteria to match
     * @param array $data Data to update or create
     * @return Model
     */
    public function updateOrCreate(array $criteria, array $data): Model;

    /**
     * Delete a record by its primary key.
     *
     * @param int|string $id Primary key value
     * @return bool
     */
    public function delete($id): bool;

    /**
     * Delete records matching the criteria.
     *
     * @param array $criteria Criteria to match
     * @return int Number of deleted records
     */
    public function deleteWhere(array $criteria): int;

    /**
     * Count records matching the criteria.
     *
     * @param array $criteria Criteria to match
     * @return int
     */
    public function count(array $criteria = []): int;

    /**
     * Check if a record exists matching the criteria.
     *
     * @param array $criteria Criteria to match
     * @return bool
     */
    public function exists(array $criteria): bool;

    /**
     * Get records with advanced filtering, sorting, and pagination.
     *
     * @param array $filters Filters to apply
     * @param array $sorts Sorting criteria
     * @param int|null $perPage Number of records per page (null for all)
     * @param array $relations Relationships to eager load
     * @return Collection|LengthAwarePaginator
     */
    public function search(array $filters = [], array $sorts = [], ?int $perPage = null, array $relations = []);

    /**
     * Begin a database transaction.
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit the database transaction.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback the database transaction.
     *
     * @return void
     */
    public function rollback(): void;

    /**
     * Get the underlying model instance.
     *
     * @return Model
     */
    public function getModel(): Model;

    /**
     * Set the relationships to eager load.
     *
     * @param array $relations Relationships to eager load
     * @return self
     */
    public function with(array $relations): self;

    /**
     * Apply scopes to the query.
     *
     * @param array $scopes Scopes to apply
     * @return self
     */
    public function withScopes(array $scopes): self;

    /**
     * Order results by a field.
     *
     * @param string $field Field to order by
     * @param string $direction Order direction (asc|desc)
     * @return self
     */
    public function orderBy(string $field, string $direction = 'asc'): self;

    /**
     * Limit the number of results.
     *
     * @param int $limit Number of results to return
     * @return self
     */
    public function limit(int $limit): self;

    /**
     * Reset the repository to its initial state.
     *
     * @return self
     */
    public function reset(): self;
}
