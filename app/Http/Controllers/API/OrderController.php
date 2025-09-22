<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderIndexRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of orders with advanced filtering, sorting, and pagination.
     */
    public function index(OrderIndexRequest $request): JsonResponse
    {
        $filters = [
            'customer_id' => $request->getCustomerId(),
            'status_id' => $request->getStatusId(),
            'created_from' => $request->getCreatedFrom(),
            'created_to' => $request->getCreatedTo(),
            'sort_by' => $request->getSortBy(),
            'sort_direction' => $request->getSortDirection(),
            'per_page' => $request->getPerPage(),
        ];

        $orders = $this->orderRepository->getFilteredOrders($filters);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
            'filters' => $request->getFilters()
        ]);
    }


    public function store(OrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $products = $data['products'] ?? [];
        unset($data['products']);

        $order = $this->orderRepository->createWithProducts($data, $products);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load('customer', 'status', 'products');
        
        $orderData = $order->toArray();
        $orderData['calculated_total'] = $this->calculateOrderTotal($order);
        $orderData['product_count'] = $order->products->count();
        $orderData['total_quantity'] = $order->products->sum('pivot.quantity');

        return response()->json([
            'success' => true,
            'data' => $orderData
        ]);
    }

    public function update(OrderRequest $request, Order $order): JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $products = $data['products'] ?? [];
        unset($data['products']);

        $order = $this->orderRepository->updateWithProducts($order->getKey(), $data, $products);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->orderRepository->softDelete($order->getKey(), Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(OrderStatusUpdateRequest $request, Order $order): JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $currentStatus = $order->status;
        $newStatusId = $data['status_id'];
        
        if ($currentStatus && $currentStatus->id === $newStatusId) {
            return response()->json([
                'success' => false,
                'message' => 'Order is already in this status'
            ], 400);
        }

        $this->orderRepository->update($order->getKey(), $data);
        $order = $this->orderRepository->findOrFail($order->getKey());

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order->load('customer', 'status', 'products')
        ]);
    }

    /**
     * Calculate order total from products
     */
    private function calculateOrderTotal(Order $order): float
    {
        return $order->products->sum(function ($product) {
            return $product->pivot->line_total;
        });
    }
}
