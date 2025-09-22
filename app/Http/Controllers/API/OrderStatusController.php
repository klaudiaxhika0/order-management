<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\OrderStatusRepository;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    protected OrderStatusRepository $orderStatusRepository;

    public function __construct(OrderStatusRepository $orderStatusRepository)
    {
        $this->orderStatusRepository = $orderStatusRepository;
    }

    /**
     * Display a listing of order statuses.
     */
    public function index(): JsonResponse
    {
        $statuses = $this->orderStatusRepository->getAllOrdered();
        
        return response()->json([
            'success' => true,
            'data' => $statuses
        ]);
    }
}
