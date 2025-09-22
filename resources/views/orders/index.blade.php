@extends('layouts.app')

@section('title', 'Orders List')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Orders</h1>
        <a href="/orders/create" class="btn btn-success">Create Order</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filters</h5>
            <div class="row">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="customerFilter" class="form-label">Customer</label>
                    <select id="customerFilter" class="form-select">
                        <option value="">All Customers</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button id="applyFilters" class="btn btn-primary mt-4">Apply Filters</button>
                    <button id="clearFilters" class="btn btn-secondary mt-4">Clear</button>
                </div>
            </div>
        </div>
    </div>

    <div id="message"></div>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Total</th>
            <th>Products</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="orders-table">
        <tr>
            <td colspan="7" class="text-center">Loading...</td>
        </tr>
        </tbody>
    </table>

    @push('scripts')
        <script>
            let orders = [];
            let customers = [];
            let statuses = [];

            if (typeof axios !== 'undefined') {
                axios.defaults.baseURL = '/api';
                axios.interceptors.request.use(function (config) {
                    const token = localStorage.getItem('auth_token');
                    if (token) {
                        config.headers.Authorization = `Bearer ${token}`;
                    }
                    return config;
                });
                
                axios.interceptors.response.use(
                    function (response) {
                        return response;
                    },
                    function (error) {
                        if (error.response && error.response.status === 401) {
                            localStorage.removeItem('auth_token');
                            localStorage.removeItem('user');
                            window.location.href = '/login';
                        }
                        return Promise.reject(error);
                    }
                );
            }

            async function loadCustomers() {
                try {
                    const res = await axios.get("/customers");
                    customers = res.data.data || res.data;
                    const select = document.getElementById("customerFilter");
                    select.innerHTML = '<option value="">All Customers</option>';
                    customers.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.first_name} ${c.last_name}</option>`;
                    });
                } catch (err) {
                    // Silent fail
                }
            }

            async function loadStatuses() {
                try {
                    const res = await axios.get("/order-statuses");
                    statuses = res.data.data || res.data;
                    const select = document.getElementById("statusFilter");
                    select.innerHTML = '<option value="">All Statuses</option>';
                    statuses.forEach(s => {
                        select.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                    });
                } catch (err) {
                    // Silent fail
                }
            }

            async function loadOrders() {
                try {
                    const statusFilter = document.getElementById("statusFilter").value;
                    const customerFilter = document.getElementById("customerFilter").value;
                    
                    let url = "/orders";
                    const params = new URLSearchParams();
                    if (statusFilter) params.append('status_id', statusFilter);
                    if (customerFilter) params.append('customer_id', customerFilter);
                    if (params.toString()) url += '?' + params.toString();

                    const res = await axios.get(url);
                    orders = res.data.data || res.data;
                    const table = document.getElementById("orders-table");
                    table.innerHTML = "";

                    if (!orders.length) {
                        table.innerHTML = `<tr><td colspan="7" class="text-center">No orders found</td></tr>`;
                        return;
                    }

                    orders.forEach(order => {
                        const customerName = order.customer ? 
                            `${order.customer.first_name} ${order.customer.last_name}` : 
                            'Unknown';
                        
                        const statusName = order.status ? order.status.name : 'Unknown';
                        const statusColor = getStatusColor(statusName);
                        
                        const productCount = order.products ? order.products.length : 0;
                        
                        table.innerHTML += `
                            <tr>
                                <td>${order.id}</td>
                                <td>${customerName}</td>
                                <td><span class="badge bg-${statusColor}">${statusName}</span></td>
                                <td>$${parseFloat(order.total).toFixed(2)}</td>
                                <td>${productCount} items</td>
                                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                <td>
                                    <a href="/orders/${order.id}" class="btn btn-sm btn-primary">View</a>
                                    <button onclick="deleteOrder(${order.id})" class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                } catch (err) {
                    document.getElementById("orders-table").innerHTML =
                        `<tr><td colspan="7" class="text-center text-danger">Failed to load orders</td></tr>`;
                }
            }

            function getStatusColor(status) {
                const colors = {
                    'Processing': 'warning',
                    'Shipped': 'info',
                    'Delivered': 'success',
                    'Canceled': 'danger'
                };
                return colors[status] || 'secondary';
            }

            async function deleteOrder(id) {
                if (!confirm("Are you sure you want to delete this order?")) return;
                try {
                    await axios.delete(`/orders/${id}`);
                    loadOrders();
                } catch (err) {
                    document.getElementById('message').innerHTML = '<div class="alert alert-danger">Failed to delete order</div>';
                }
            }

            document.getElementById("applyFilters").addEventListener("click", loadOrders);
            document.getElementById("clearFilters").addEventListener("click", function() {
                document.getElementById("statusFilter").value = "";
                document.getElementById("customerFilter").value = "";
                loadOrders();
            });

            function checkAuthAndLoad() {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }
                
                setTimeout(() => {
                    loadData();
                }, 100);
            }

            async function loadData() {
                await loadCustomers();
                await loadStatuses();
                await loadOrders();
            }

            setTimeout(() => {
                checkAuthAndLoad();
            }, 100);
        </script>
    @endpush
@endsection
