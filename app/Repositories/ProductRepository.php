<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Get products with filtering
     */
    public function getFilteredProducts(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Create a product with auto-generated SKU
     */
    public function createWithSku(array $data): Product
    {
        if (empty($data['sku'])) {
            $data['sku'] = Str::upper(Str::random(8));
        }
        
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        return $this->create($data);
    }


    /**
     * Get products by price range
     */
    public function getByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        return $this->model
            ->where('price', '>=', $minPrice)
            ->where('price', '<=', $maxPrice)
            ->get();
    }


    /**
     * Get products by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }


    /**
     * Soft delete a product
     */
    public function softDelete(int $id, ?int $deletedBy = null): bool
    {
        $product = $this->findOrFail($id);
        if ($deletedBy !== null) {
            $product->update(['deleted_by' => $deletedBy]);
        }
        return $product->delete();
    }

    /**
     * Restore a soft deleted product
     */
    public function restore(int $id): bool
    {
        return $this->model->withTrashed()->where('id', $id)->restore();
    }

    /**
     * Get soft deleted products
     */
    public function getTrashed(): Collection
    {
        return $this->model->onlyTrashed()->get();
    }
}
