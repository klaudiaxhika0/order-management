<?php

namespace App\Repositories;

use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Collection;

class OrderStatusRepository extends BaseRepository
{
    public function __construct(OrderStatus $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all order statuses ordered by sort_order
     */
    public function getAllOrdered(): Collection
    {
        return $this->model->ordered()->get();
    }

    /**
     * Get order status by slug
     */
    public function getBySlug(string $slug): ?OrderStatus
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Get order status by name
     */
    public function getByName(string $name): ?OrderStatus
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Get order statuses with their orders count
     */
    public function getWithOrdersCount(): Collection
    {
        return $this->model->withCount('orders')->get();
    }

    /**
     * Get order statuses by color
     */
    public function getByColor(string $color): Collection
    {
        return $this->model->where('color', $color)->get();
    }

    /**
     * Get order statuses with orders
     */
    public function getWithOrders(): Collection
    {
        return $this->model->with('orders')->get();
    }

    /**
     * Get the next sort order
     */
    public function getNextSortOrder(): int
    {
        $lastStatus = $this->model->orderBy('sort_order', 'desc')->first();
        return $lastStatus ? $lastStatus->sort_order + 1 : 1;
    }

    /**
     * Update sort order for a status
     */
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        return $this->update($id, ['sort_order' => $sortOrder]);
    }

    /**
     * Reorder statuses
     */
    public function reorder(array $statusIds): bool
    {
        foreach ($statusIds as $index => $statusId) {
            $this->updateSortOrder($statusId, $index + 1);
        }
        return true;
    }

    /**
     * Get statuses used in orders
     */
    public function getUsedStatuses(): Collection
    {
        return $this->model->has('orders')->get();
    }

    /**
     * Get unused statuses
     */
    public function getUnusedStatuses(): Collection
    {
        return $this->model->doesntHave('orders')->get();
    }

    /**
     * Create a status with auto-generated slug
     */
    public function createWithSlug(array $data): OrderStatus
    {
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        if (empty($data['sort_order'])) {
            $data['sort_order'] = $this->getNextSortOrder();
        }

        return $this->create($data);
    }

    /**
     * Check if status name exists
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = $this->model->where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if status slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->model->where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
