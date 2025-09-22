<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Get orders with filtering
     */
    public function getFilteredOrders(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('customer', 'status', 'products');

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Create an order with products
     */
    public function createWithProducts(array $orderData, array $products = []): Model|Order
    {
        $order = $this->create($orderData);

        if (!empty($products)) {
            $this->attachProducts($order, $products);
        }

        return $order->load('customer', 'status', 'products');
    }

    /**
     * Update an order with products
     */
    public function updateWithProducts(int $id, array $orderData, array $products = []): Model|Order
    {
        $order = $this->findOrFail($id);
        $order->update($orderData);

        if (!empty($products)) {
            $this->syncProducts($order, $products);
        }

        return $order->fresh()->load('customer', 'status', 'products');
    }

    /**
     * Attach products to an order
     */
    public function attachProducts(Order $order, array $products): void
    {
        $pivotData = [];
        foreach ($products as $product) {
            $pivotData[$product['product_id']] = [
                'quantity' => $product['quantity'],
                'unit_price' => $product['price'],
                'line_total' => $product['quantity'] * $product['price'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $order->products()->attach($pivotData);
    }

    /**
     * Sync products with an order
     */
    public function syncProducts(Order $order, array $products): void
    {
        $pivotData = [];
        foreach ($products as $product) {
            $pivotData[$product['product_id']] = [
                'quantity' => $product['quantity'],
                'unit_price' => $product['price'],
                'line_total' => $product['quantity'] * $product['price'],
                'updated_at' => now(),
            ];
        }
        $order->products()->sync($pivotData);
    }

    /**
     * Get orders by customer
     */
    public function getByCustomer(int $customerId): Collection
    {
        return $this->model->where('customer_id', $customerId)->get();
    }

    /**
     * Get orders by status
     */
    public function getByStatus(int $statusId): Collection
    {
        return $this->model->where('status_id', $statusId)->get();
    }

    /**
     * Get orders by date range
     */
    public function getByDateRange(string $from, string $to): Collection
    {
        return $this->model
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get();
    }

    /**
     * Get orders with products
     */
    public function getWithProducts(): Collection
    {
        return $this->model->with('products')->get();
    }

    /**
     * Get orders with all relationships
     */
    public function getWithAllRelations(): Collection
    {
        return $this->model->with('customer', 'status', 'products')->get();
    }

    /**
     * Soft delete an order
     */
    public function softDelete(int $id, ?int $deletedBy = null): bool
    {
        $order = $this->findOrFail($id);
        if ($deletedBy !== null) {
            $order->update(['deleted_by' => $deletedBy]);
        }
        return $order->delete();
    }

    /**
     * Restore a soft deleted order
     */
    public function restore(int $id): bool
    {
        return $this->model->withTrashed()->where('id', $id)->restore();
    }

    /**
     * Get soft deleted orders
     */
    public function getTrashed(): Collection
    {
        return $this->model->onlyTrashed()->get();
    }
}
