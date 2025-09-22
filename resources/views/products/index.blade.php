@extends('../../layouts.app')

@section('title', 'Products List')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Products</h1>
    <a href="/products/create" class="btn btn-success">Add Product</a>
</div>

<div id="message"></div>

<table class="table table-bordered">
    <thead class="table-light">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody id="products-table">
    <tr><td colspan="4" class="text-center">Loading...</td></tr>
    </tbody>
</table>
@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>

    async function loadProducts() {
        try {
            const res = await axios.get("/products");
            const products = res.data.data ?? res.data; // handle both formats

            const table = document.getElementById("products-table");
            table.innerHTML = "";

            if (!products.length) {
                table.innerHTML = `<tr><td colspan="4" class="text-center">No products found</td></tr>`;
                return;
            }

            products.forEach(p => {
                table.innerHTML += `
                    <tr>
                        <td>${p.id}</td>
                        <td>${p.name}</td>
                        <td>${p.price}</td>
                        <td>
                            <a href="/products/${p.id}" class="btn btn-sm btn-primary">Edit</a>
                            <button onclick="deleteProduct(${p.id})" class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                `;
            });
        } catch (err) {
            document.getElementById("products-table").innerHTML =
                `<tr><td colspan="4" class="text-center text-danger">Failed to load products</td></tr>`;
        }
    }

    async function deleteProduct(id) {
        if (!confirm("Are you sure you want to delete this product?")) return;
        try {
            await axios.delete(`/products/${id}`);
            loadProducts();
        } catch (err) {
            document.getElementById('message').innerHTML = '<div class="alert alert-danger">Failed to delete product</div>';
        }
    }

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
        
        loadData();
    }

    async function loadData() {
        await loadProducts();
    }

    setTimeout(() => {
        checkAuthAndLoad();
    }, 100);
</script>
@endpush
@endsection
