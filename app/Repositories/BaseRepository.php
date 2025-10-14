<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Base Repository Implementation
 * 
 * Provides common data access functionality for all repositories.
 * This abstract class implements the RepositoryInterface and provides
 * reusable methods for CRUD operations, filtering, and pagination.
 * 
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Relationships to eager load.
     *
     * @var array
     */
    protected array $with = [];

    /**
     * Scopes to apply to queries.
     *
     * @var array
     */
    protected array $scopes = [];

    /**
     * Order by clauses.
     *
     * @var array
     */
    protected array $orderBy = [];

    /**
     * Limit clause.
     *
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    /**
     * Create a new instance of the model.
     *
     * @return Model
     */
    abstract protected function makeModel(): Model;

    /**
     * {@inheritDoc}
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->buildQuery($relations);
        
        return $query->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        $query = $this->buildQuery($relations);
        
        return $query->paginate($perPage, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->buildQuery($relations);
        
        return $query->find($id, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail($id, array $columns = ['*'], array $relations = []): Model
    {
        $query = $this->buildQuery($relations);
        
        return $query->findOrFail($id, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(string $field, $value, array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->buildQuery($relations);
        
        return $query->where($field, $value)->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(string $field, $value, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->buildQuery($relations);
        
        return $query->where($field, $value)->first($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findWhere(array $criteria, array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->buildQuery($relations);
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                // Handle array values (e.g., whereIn)
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneWhere(array $criteria, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->buildQuery($relations);
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->first($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update($id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        
        return $model->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function updateOrCreate(array $criteria, array $data): Model
    {
        return $this->model->updateOrCreate($criteria, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id): bool
    {
        $model = $this->findOrFail($id);
        
        return $model->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteWhere(array $criteria): int
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $criteria = []): int
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->count();
    }

    /**
     * {@inheritDoc}
     */
    public function exists(array $criteria): bool
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function search(array $filters = [], array $sorts = [], ?int $perPage = null, array $relations = [])
    {
        $query = $this->buildQuery($relations);
        
        // Apply filters
        $query = $this->applyFilters($query, $filters);
        
        // Apply sorting
        $query = $this->applySorting($query, $sorts);
        
        // Return paginated or all results
        if ($perPage !== null) {
            return $query->paginate($perPage);
        }
        
        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): void
    {
        DB::commit();
    }

    /**
     * {@inheritDoc}
     */
    public function rollback(): void
    {
        DB::rollBack();
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * {@inheritDoc}
     */
    public function with(array $relations): self
    {
        $this->with = array_merge($this->with, $relations);
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withScopes(array $scopes): self
    {
        $this->scopes = array_merge($this->scopes, $scopes);
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->orderBy[] = ['field' => $field, 'direction' => $direction];
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): self
    {
        $this->with = [];
        $this->scopes = [];
        $this->orderBy = [];
        $this->limit = null;
        
        return $this;
    }

    /**
     * Build the base query with eager loading and scopes.
     *
     * @param array $relations Additional relationships to eager load
     * @return Builder
     */
    protected function buildQuery(array $relations = []): Builder
    {
        $query = $this->model->newQuery();
        
        // Apply eager loading
        $allRelations = array_merge($this->with, $relations);
        if (!empty($allRelations)) {
            $query->with($allRelations);
        }
        
        // Apply scopes
        foreach ($this->scopes as $scope => $parameters) {
            if (is_numeric($scope)) {
                // Scope without parameters
                $query->{$parameters}();
            } else {
                // Scope with parameters
                $query->{$scope}(...$parameters);
            }
        }
        
        // Apply order by
        foreach ($this->orderBy as $order) {
            $query->orderBy($order['field'], $order['direction']);
        }
        
        // Apply limit
        if ($this->limit !== null) {
            $query->limit($this->limit);
        }
        
        return $query;
    }

    /**
     * Apply filters to the query.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value === null) {
                continue;
            }
            
            // Handle special filter operators
            if (is_array($value)) {
                if (isset($value['operator']) && isset($value['value'])) {
                    // Custom operator (e.g., ['operator' => '>=', 'value' => 100])
                    $query->where($field, $value['operator'], $value['value']);
                } else {
                    // Array of values (whereIn)
                    $query->whereIn($field, $value);
                }
            } elseif (strpos($field, '_like') !== false) {
                // LIKE search (e.g., 'name_like' => 'john')
                $actualField = str_replace('_like', '', $field);
                $query->where($actualField, 'LIKE', "%{$value}%");
            } elseif (strpos($field, '_from') !== false) {
                // Range start (e.g., 'created_at_from' => '2024-01-01')
                $actualField = str_replace('_from', '', $field);
                $query->where($actualField, '>=', $value);
            } elseif (strpos($field, '_to') !== false) {
                // Range end (e.g., 'created_at_to' => '2024-12-31')
                $actualField = str_replace('_to', '', $field);
                $query->where($actualField, '<=', $value);
            } else {
                // Exact match
                $query->where($field, $value);
            }
        }
        
        return $query;
    }

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     * @param array $sorts
     * @return Builder
     */
    protected function applySorting(Builder $query, array $sorts): Builder
    {
        foreach ($sorts as $field => $direction) {
            if (is_numeric($field)) {
                // Simple field name without direction
                $query->orderBy($direction);
            } else {
                // Field with direction
                $query->orderBy($field, $direction);
            }
        }
        
        return $query;
    }

    /**
     * Execute a callback within a database transaction.
     *
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    protected function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    /**
     * Get a new query builder instance.
     *
     * @return Builder
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Clone the repository instance.
     *
     * @return static
     */
    public function clone(): self
    {
        return clone $this;
    }
}
