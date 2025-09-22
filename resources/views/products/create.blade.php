@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
    <div class="container mt-4">
        <h2>Add New Product</h2>

        <form id="createProductForm">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price ($)</label>
                <input type="number" class="form-control" id="price" step="0.01" required>
            </div>


            <div class="mb-3">
                <label class="form-label">SKU</label>
                <input type="text" class="form-control" id="sku" placeholder="e.g., PROD-001">
                <small class="text-muted">Optional: Product code (letters, numbers, hyphens, underscores only)</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="description" rows="3"></textarea>
            </div>

            <div id="message"></div>

            <button type="submit" class="btn btn-success">Save</button>
            <a href="/products" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Check authentication and load data
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
        }

        document.getElementById("createProductForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const data = {
                name: document.getElementById("name").value,
                price: document.getElementById("price").value,
                sku: document.getElementById("sku").value,
                description: document.getElementById("description").value
            };

            try {
                const res = await axios.post('/products', data);
                if (res.data.success) {
                    document.getElementById('message').innerHTML = '<div class="alert alert-success">Product created successfully!</div>';
                    setTimeout(() => {
                        window.location.href = "/products";
                    }, 1500);
                } else {
                    document.getElementById('message').innerHTML = '<div class="alert alert-danger">Error: ' + (res.data.message || "Failed to create product") + '</div>';
                }
            } catch (err) {
                if (err.response && err.response.data && err.response.data.errors) {
                    let errorText = 'Validation errors:<br>';
                    Object.keys(err.response.data.errors).forEach(field => {
                        errorText += `${field}: ${err.response.data.errors[field].join(', ')}<br>`;
                    });
                    document.getElementById('message').innerHTML = '<div class="alert alert-danger">' + errorText + '</div>';
                } else {
                    document.getElementById('message').innerHTML = '<div class="alert alert-danger">Error: ' + (err.response?.data?.message || err.message || "Failed to create product") + '</div>';
                }
            }
        });

        setTimeout(() => {
            checkAuthAndLoad();
        }, 100);
    </script>
@endsection
