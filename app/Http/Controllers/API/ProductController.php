<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of products with advanced filtering, sorting, and pagination.
     */
    public function index(ProductIndexRequest $request): JsonResponse
    {
        $filters = [
            'status' => $request->getStatus(),
            'sort_by' => $request->getSortBy(),
            'sort_direction' => $request->getSortDirection(),
            'per_page' => $request->getPerPage(),
        ];

        $products = $this->productRepository->getFilteredProducts($filters);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'filters' => $request->getFilters()
        ]);
    }


    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['sku'] = $request->getSku();
        $data['status'] = $request->getStatus();
        $data['created_by'] = Auth::id();

        $product = $this->productRepository->createWithSku($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();
        
        $this->productRepository->update($product->getKey(), $data);
        $product = $this->productRepository->findOrFail($product->getKey());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productRepository->softDelete($product->getKey(), Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
