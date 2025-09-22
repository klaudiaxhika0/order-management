@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
    <div class="container mt-4">
        <h2>Create New Order</h2>

        <form id="createOrderForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <select id="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="status_id" class="form-select" required>
                            <option value="">Select Status</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total</label>
                        <input type="number" id="total" class="form-control" step="0.01" readonly>
                        <small class="text-muted">Total will be calculated automatically</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>Add Products <span class="text-danger">*</span></h5>
                    <p class="text-muted small">At least one product is required to create an order</p>
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select id="productSelect" class="form-select">
                            <option value="">Select Product</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" id="quantity" class="form-control" min="1" value="1">
                    </div>
                    <button type="button" id="addProduct" class="btn btn-primary">Add Product</button>

                    <div class="mt-4">
                        <h6>Order Items</h6>
                        <div id="orderItems" class="border p-3" style="min-height: 200px;">
                            <p class="text-danger fw-bold">No products added yet - At least one product is required</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div id="message"></div>
                <button type="submit" class="btn btn-success">Create Order</button>
                <a href="/orders" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        let products = [];
        let customers = [];
        let statuses = [];
        let orderItems = [];

        async function loadCustomers() {
            try {
                const res = await axios.get("/customers");
                customers = res.data.data || res.data;
                const select = document.getElementById("customer_id");
                select.innerHTML = '<option value="">Select Customer</option>';
                customers.forEach(c => {
                    select.innerHTML += `<option value="${c.id}">${c.first_name} ${c.last_name} (${c.email})</option>`;
                });
            } catch (err) {
                // Silent fail
            }
        }

        async function loadStatuses() {
            try {
                const res = await axios.get("/order-statuses");
                statuses = res.data.data || res.data;
                const select = document.getElementById("status_id");
                select.innerHTML = '<option value="">Select Status</option>';
                statuses.forEach(s => {
                    select.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                });
            } catch (err) {
                // Silent fail
            }
        }

        async function loadProducts() {
            try {
                const res = await axios.get("/products");
                products = res.data.data || res.data;
                const select = document.getElementById("productSelect");
                select.innerHTML = '<option value="">Select Product</option>';
                products.forEach(p => {
                    select.innerHTML += `<option value="${p.id}" data-price="${p.price}">${p.name} - $${p.price}</option>`;
                });
            } catch (err) {
                // Silent fail
            }
        }

        function addProductToOrder() {
            const productSelect = document.getElementById("productSelect");
            const quantity = parseInt(document.getElementById("quantity").value);
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            
            if (!selectedOption.value || quantity < 1) {
                document.getElementById('message').innerHTML = '<div class="alert alert-warning">Please select a product and enter a valid quantity</div>';
                return;
            }

            const productId = parseInt(selectedOption.value);
            const productName = selectedOption.text.split(' - ')[0];
            const price = parseFloat(selectedOption.dataset.price);

            const existingItem = orderItems.find(item => item.product_id === productId);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                orderItems.push({
                    product_id: productId,
                    name: productName,
                    price: price,
                    quantity: quantity
                });
            }

            updateOrderItemsDisplay();
            calculateTotal();
            
            productSelect.value = "";
            document.getElementById("quantity").value = 1;
        }

        function removeProductFromOrder(productId) {
            orderItems = orderItems.filter(item => item.product_id !== productId);
            updateOrderItemsDisplay();
            calculateTotal();
        }

        function updateOrderItemsDisplay() {
            const container = document.getElementById("orderItems");
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (orderItems.length === 0) {
                container.innerHTML = '<p class="text-danger fw-bold">No products added yet - At least one product is required</p>';
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Add Products First';
                submitBtn.className = 'btn btn-warning';
                return;
            }

            let html = '';
            orderItems.forEach(item => {
                const lineTotal = item.price * item.quantity;
                html += `
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small>$${item.price} x ${item.quantity} = $${lineTotal.toFixed(2)}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeProductFromOrder(${item.product_id})">Remove</button>
                    </div>
                `;
            });
            container.innerHTML = html;
            
            // Enable submit button when products are added
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Create Order';
            submitBtn.className = 'btn btn-success';
        }

        function calculateTotal() {
            const total = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.getElementById("total").value = total.toFixed(2);
        }

        // Event listeners
        function attachEventListeners() {
            document.getElementById("addProduct").addEventListener("click", addProductToOrder);

            document.getElementById("createOrderForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            // Clear previous messages
            document.getElementById('message').innerHTML = '';

            // Validate required fields
            const customerId = document.getElementById("customer_id").value;
            const statusId = document.getElementById("status_id").value;
            const total = document.getElementById("total").value;

            if (!customerId) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Please select a customer</div>';
                return;
            }

            if (!statusId) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Please select an order status</div>';
                return;
            }

            if (!total || parseFloat(total) <= 0) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Please enter a valid total amount</div>';
                return;
            }

            if (orderItems.length === 0) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">At least one product is required to create an order</div>';
                return;
            }

            const orderData = {
                customer_id: parseInt(customerId),
                status_id: parseInt(statusId),
                total: parseFloat(total),
                notes: document.getElementById("notes").value,
                products: orderItems.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    price: item.price
                }))
            };

            try {
                const res = await axios.post("/orders", orderData);
                document.getElementById('message').innerHTML = '<div class="alert alert-success">Order created successfully!</div>';
                setTimeout(() => {
                    window.location.href = "/orders";
                }, 1500);
            } catch (err) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Failed to create order: ' + (err.response?.data?.message || "Unknown error") + '</div>';
            }
        });
        }

        function checkAuthAndLoad() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }
            
            // Ensure axios is configured before making requests
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
            
            loadData();
        }

        async function loadData() {
            await loadCustomers();
            await loadStatuses();
            await loadProducts();
            updateOrderItemsDisplay(); // Initialize the order items display
        }

        // Load data after a short delay to ensure layout scripts are loaded
        setTimeout(() => {
            attachEventListeners();
            checkAuthAndLoad();
        }, 100);
    </script>
@endsection
