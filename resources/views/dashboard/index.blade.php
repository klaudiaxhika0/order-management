@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Admin Dashboard</h1>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white clickable-card" data-url="/orders" style="cursor: pointer; transition: transform 0.2s;">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <h2 id="totalOrders">-</h2>
                        <small class="opacity-75">Click to view all orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white clickable-card" data-url="/customers" style="cursor: pointer; transition: transform 0.2s;">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <h2 id="totalCustomers">-</h2>
                        <small class="opacity-75">Click to view all customers</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white clickable-card" data-url="/products" style="cursor: pointer; transition: transform 0.2s;">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <h2 id="totalProducts">-</h2>
                        <small class="opacity-75">Click to view all products</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <h2 id="totalRevenue">-</h2>
                        <small class="opacity-75">Total sales amount</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Summary -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Orders by Status</h5>
                    </div>
                    <div class="card-body">
                        <div id="orderStatusChart">
                            <div class="text-center">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Recent Orders</h5>
                <a href="/orders" class="btn btn-primary btn-sm">View All Orders</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Products</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersTable">
                            <tr>
                                <td colspan="7" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="/orders/create" class="btn btn-success w-100 mb-2">Create New Order</a>
                            </div>
                            <div class="col-md-3">
                                <a href="/customers/create" class="btn btn-primary w-100 mb-2">Add Customer</a>
                            </div>
                            <div class="col-md-3">
                                <a href="/products/create" class="btn btn-info w-100 mb-2">Add Product</a>
                            </div>
                            <div class="col-md-3">
                                <a href="/orders" class="btn btn-secondary w-100 mb-2">Manage Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .clickable-card {
                transition: all 0.3s ease;
            }
            .clickable-card:hover {
                transform: translateY(-5px) !important;
                box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function checkAuthAndLoad() {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }
                
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
                
                loadDashboardData();
            }

            async function loadDashboardData() {
                await Promise.all([
                    loadDashboardStats(),
                    loadOrderStatusSummary(),
                    loadRecentOrders()
                ]);
            }

            async function loadDashboardStats() {
                try {
                    const res = await axios.get("/dashboard/stats");
                    const data = res.data.data;

                    document.getElementById("totalOrders").textContent = data.orders.total || 0;
                    document.getElementById("totalCustomers").textContent = data.customers.total || 0;
                    document.getElementById("totalProducts").textContent = data.products.total || 0;
                    document.getElementById("totalRevenue").textContent = "$" + parseFloat(data.revenue.total || 0).toFixed(2);
                } catch (err) {
                    if (err.response?.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    document.getElementById("totalOrders").textContent = "Error";
                    document.getElementById("totalCustomers").textContent = "Error";
                    document.getElementById("totalProducts").textContent = "Error";
                    document.getElementById("totalRevenue").textContent = "Error";
                }
            }

            async function loadOrderStatusSummary() {
                try {
                    const res = await axios.get("/dashboard/order-status-summary");
                    const statuses = res.data.data;
                    
                    const container = document.getElementById("orderStatusChart");
                    let html = '';
                    
                    if (Array.isArray(statuses)) {
                        const totalOrders = statuses.reduce((sum, status) => sum + status.orders_count, 0);
                        
                        statuses.forEach(status => {
                            const percentage = totalOrders > 0 ? (status.orders_count / totalOrders * 100).toFixed(1) : 0;
                            html += `
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>${status.name}</span>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="width: 100px; height: 20px;">
                                            <div class="progress-bar" style="width: ${percentage}%"></div>
                                        </div>
                                        <span class="badge bg-secondary">${status.orders_count}</span>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    
                    container.innerHTML = html || '<p class="text-muted">No data available</p>';
                } catch (err) {
                    document.getElementById("orderStatusChart").innerHTML = '<p class="text-danger">Failed to load data</p>';
                }
            }

            async function loadRecentOrders() {
                try {
                    const res = await axios.get("/orders?per_page=5");
                    const orders = res.data.data || res.data;

                    const table = document.getElementById("recentOrdersTable");
                    
                    if (!Array.isArray(orders) || !orders.length) {
                        table.innerHTML = '<tr><td colspan="7" class="text-center">No orders found</td></tr>';
                        return;
                    }

                    let html = '';
                    orders.forEach(order => {
                        const productCount = order.products ? order.products.length : 0;
                        const statusBadge = getStatusBadge(order.status?.name || 'Unknown');
                        
                        html += `
                            <tr>
                                <td>#${order.id}</td>
                                <td>${order.customer ? `${order.customer.first_name} ${order.customer.last_name}` : 'Unknown'}</td>
                                <td>${statusBadge}</td>
                                <td>$${parseFloat(order.total).toFixed(2)}</td>
                                <td>${productCount} items</td>
                                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                <td>
                                    <a href="/orders/${order.id}" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        `;
                    });
                    
                    table.innerHTML = html;
                } catch (err) {
                    document.getElementById("recentOrdersTable").innerHTML = 
                        '<tr><td colspan="7" class="text-center text-danger">Failed to load orders</td></tr>';
                }
            }

            function getStatusBadge(status) {
                const badges = {
                    'Processing': 'bg-warning',
                    'Shipped': 'bg-info',
                    'Delivered': 'bg-success',
                    'Canceled': 'bg-danger'
                };
                const badgeClass = badges[status] || 'bg-secondary';
                return `<span class="badge ${badgeClass}">${status}</span>`;
            }

            function setupClickableCards() {
                const clickableCards = document.querySelectorAll('.clickable-card');
                
                clickableCards.forEach(card => {
                    card.addEventListener('click', function() {
                        const url = this.getAttribute('data-url');
                        if (url) {
                            window.location.href = url;
                        }
                    });
                });
            }

            setTimeout(() => {
                checkAuthAndLoad();
                setupClickableCards();
            }, 100);
        </script>
    @endpush
@endsection
