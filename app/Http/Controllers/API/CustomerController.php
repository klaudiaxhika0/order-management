<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerIndexRequest;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of customers with advanced filtering, sorting, and pagination.
     */
    public function index(CustomerIndexRequest $request): JsonResponse
    {
        $filters = [
            'email' => $request->getEmail(),
            'has_orders' => $request->hasOrders(),
            'created_from' => $request->getCreatedFrom(),
            'created_to' => $request->getCreatedTo(),
            'sort_by' => $request->getSortBy(),
            'sort_direction' => $request->getSortDirection(),
            'per_page' => $request->getPerPage(),
        ];

        $customers = $this->customerRepository->getFilteredCustomers($filters);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ],
            'filters' => $request->getFilters()
        ]);
    }

    public function store(CustomerRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $customer = $this->customerRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer): JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $this->customerRepository->update($customer->getKey(), $data);
        $customer = $this->customerRepository->findOrFail($customer->getKey());

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerRepository->softDelete($customer->getKey(), Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }
}
