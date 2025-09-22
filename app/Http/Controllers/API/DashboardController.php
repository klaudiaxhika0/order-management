<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        $totalOrders = Order::count();
        $ordersByStatus = Order::select('order_statuses.name as status', DB::raw('count(*) as count'))
            ->join('order_statuses', 'orders.status_id', '=', 'order_statuses.id')
            ->groupBy('order_statuses.id', 'order_statuses.name')
            ->get();

        $recentOrders = Order::with('customer', 'status', 'products')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
                    'status' => $order->status->name,
                    'total' => $order->total,
                    'created_at' => $order->created_at,
                    'product_count' => $order->products->count()
                ];
            });

        $totalCustomers = Customer::count();
        $newCustomersThisMonth = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalProducts = Product::count();

        $totalRevenue = Order::sum('total');
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => [
                    'total' => $totalOrders,
                    'by_status' => $ordersByStatus,
                    'recent' => $recentOrders
                ],
                'customers' => [
                    'total' => $totalCustomers,
                    'new_this_month' => $newCustomersThisMonth
                ],
                'products' => [
                    'total' => $totalProducts,
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'this_month' => $monthlyRevenue
                ]
            ]
        ]);
    }

    /**
     * Get order status summary
     */
    public function orderStatusSummary(): JsonResponse
    {
        $statusSummary = OrderStatus::withCount('orders')->get();

        return response()->json([
            'success' => true,
            'data' => $statusSummary
        ]);
    }
}