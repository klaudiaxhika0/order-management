@extends('layouts.app')

@section('title', 'Customers List')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Customers</h1>
        <a href="/customers/create" class="btn btn-success">Add Customer</a>
    </div>

    <div id="message"></div>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="customers-table">
        <tr>
            <td colspan="7" class="text-center">Loading...</td>
        </tr>
        </tbody>
    </table>

    @push('scripts')
        <script>
            async function loadCustomers() {
                try {
                    const res = await axios.get("/customers");
                    const customers = res.data.data || res.data; // Handle both nested and direct formats
                    const table = document.getElementById("customers-table");
                    table.innerHTML = "";

                    if (!customers.length) {
                        table.innerHTML = `<tr><td colspan="7" class="text-center">No customers found</td></tr>`;
                        return;
                    }

                    customers.forEach(c => {
                        table.innerHTML += `
                <tr>
                    <td>${c.id}</td>
                    <td>${c.first_name}</td>
                    <td>${c.last_name}</td>
                    <td>${c.email}</td>
                    <td>${c.phone}</td>
                    <td>${c.address}</td>
                    <td>
                        <a href="/customers/${c.id}" class="btn btn-sm btn-primary">Edit</a>
                        <button onclick="deleteCustomer(${c.id})" class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            `;
                    });
                } catch (err) {
                    document.getElementById("customers-table").innerHTML =
                        `<tr><td colspan="7" class="text-center text-danger">Failed to load customers</td></tr>`;
                }
            }

            async function deleteCustomer(id) {
                if (!confirm("Are you sure you want to delete this customer?")) return;
                try {
                    await axios.delete(`/customers/${id}`);
                    loadCustomers();
                } catch (err) {
                    document.getElementById('message').innerHTML = '<div class="alert alert-danger">Failed to delete customer</div>';
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
                
                loadCustomers();
            }

            setTimeout(() => {
                checkAuthAndLoad();
            }, 100);
        </script>
    @endpush
@endsection
