<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    protected Builder $query;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $model->newQuery();
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);
        return $model->update($data);
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query->paginate($perPage);
    }

    public function where(string $column, $value): self
    {
        $this->query->where($column, $value);
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $this->query->whereIn($column, $values);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    public function with(array $relations): self
    {
        $this->query->with($relations);
        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Reset the query builder
     */
    protected function resetQuery(): self
    {
        $this->query = $this->model->newQuery();
        return $this;
    }

    /**
     * Execute the query and get results
     */
    public function get(): Collection
    {
        $results = $this->query->get();
        $this->resetQuery();
        return $results;
    }

    /**
     * Execute the query and get first result
     */
    public function first(): ?Model
    {
        $result = $this->query->first();
        $this->resetQuery();
        return $result;
    }

    /**
     * Count records
     */
    public function count(): int
    {
        $count = $this->query->count();
        $this->resetQuery();
        return $count;
    }

    /**
     * Check if record exists
     */
    public function exists(): bool
    {
        $exists = $this->query->exists();
        $this->resetQuery();
        return $exists;
    }
}
