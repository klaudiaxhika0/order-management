<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all(): Collection;

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model;

    /**
     * Find a record by ID or fail
     */
    public function findOrFail(int $id): Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int $id): bool;

    /**
     * Get paginated results
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get records with conditions
     */
    public function where(string $column, $value): self;

    /**
     * Get records with multiple conditions
     */
    public function whereIn(string $column, array $values): self;

    /**
     * Order records
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Load relationships
     */
    public function with(array $relations): self;

    /**
     * Get the query builder
     */
    public function getQuery();
}
