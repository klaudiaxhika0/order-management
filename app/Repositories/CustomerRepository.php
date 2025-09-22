<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    /**
     * Get customers with filtering
     */
    public function getFilteredCustomers(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }

        if (isset($filters['has_orders'])) {
            if ($filters['has_orders']) {
                $query->has('orders');
            } else {
                $query->doesntHave('orders');
            }
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
     * Get customers with their orders
     */
    public function getCustomersWithOrders(): Collection
    {
        return $this->model->with('orders')->get();
    }

    /**
     * Get customers without orders
     */
    public function getCustomersWithoutOrders(): Collection
    {
        return $this->model->doesntHave('orders')->get();
    }

    /**
     * Get customers by email domain
     */
    public function getCustomersByEmailDomain(string $domain): Collection
    {
        return $this->model->where('email', 'like', "%@{$domain}")->get();
    }

    /**
     * Get customers created in date range
     */
    public function getCustomersByDateRange(string $from, string $to): Collection
    {
        return $this->model
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get();
    }

    /**
     * Soft delete a customer
     */
    public function softDelete(int $id, ?int $deletedBy = null): bool
    {
        $customer = $this->findOrFail($id);
        if ($deletedBy !== null) {
            $customer->update(['deleted_by' => $deletedBy]);
        }
        return $customer->delete();
    }

    /**
     * Restore a soft deleted customer
     */
    public function restore(int $id): bool
    {
        return $this->model->withTrashed()->where('id', $id)->restore();
    }

    /**
     * Get soft deleted customers
     */
    public function getTrashed(): Collection
    {
        return $this->model->onlyTrashed()->get();
    }
}
