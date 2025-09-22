@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Edit Product</h1>

        <form id="editProductForm">
            <input type="hidden" id="product_id" name="product_id">

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <!-- Price -->
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required>
            </div>


            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control"></textarea>
                <small class="text-muted">Optional: can be empty</small>
            </div>

            <div id="message"></div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const productId = window.location.pathname.split("/")[2];

        async function loadProduct() {
            try {
                const productRes = await axios.get(`/products/${productId}`);
                const product = productRes.data.data;

                // Populate fields
                document.getElementById("product_id").value = product.id;
                document.getElementById("name").value = product.name;
                document.getElementById("price").value = product.price;
                document.getElementById("description").value = product.description ?? "";


            } catch (err) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Failed to load product data.</div>';
            }
        }

        document.getElementById("editProductForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            try {
                const data = {
                    name: document.getElementById("name").value,
                    price: document.getElementById("price").value,
                    description: document.getElementById("description").value
                };

                const res = await axios.put(`/products/${productId}`, data);
                document.getElementById('message').innerHTML = '<div class="alert alert-success">Product updated successfully!</div>';
                setTimeout(() => {
                    window.location.href = "/products";
                }, 1500);
            } catch (err) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Failed to update product.</div>';
            }
        });

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
            
            loadProduct();
        }

        // Load data after a short delay to ensure layout scripts are loaded
        setTimeout(() => {
            checkAuthAndLoad();
        }, 100);
    </script>
@endsection
