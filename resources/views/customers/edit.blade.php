@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Edit Customer</h3>
                    </div>
                    <div class="card-body">
                        <form id="editCustomerForm">
                            <input type="hidden" id="customer_id">
                            <div id="error-message" class="alert alert-danger" style="display: none;"></div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input
                                            type="text"
                                            id="first_name"
                                            class="form-control"
                                            placeholder="Enter first name"
                                            required
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input
                                            type="text"
                                            id="last_name"
                                            class="form-control"
                                            placeholder="Enter last name"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input
                                            type="email"
                                            id="email"
                                            class="form-control"
                                            placeholder="Enter email address"
                                            required
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input
                                            type="text"
                                            id="phone"
                                            class="form-control"
                                            placeholder="+1234567890"
                                            maxlength="20"
                                            required
                                        />
                                        <div class="form-text">Format: +1234567890 (must start with + followed by numbers only)</div>
                                        <div id="phone-error" class="text-danger" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea
                                    id="address"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Enter customer address"
                                ></textarea>
                            </div>

                            <div id="message"></div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>
                                    Save Changes
                                </button>
                                <a href="/customers" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Back to Customers
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const customerId = window.location.pathname.split("/")[2];

        // Load customer data
        async function loadCustomer() {
            try {
                const res = await axios.get(`/customers/${customerId}`);
                const c = res.data.data || res.data; // Handle both nested and direct formats

                document.getElementById("customer_id").value = c.id;
                document.getElementById("first_name").value = c.first_name;
                document.getElementById("last_name").value = c.last_name;
                document.getElementById("email").value = c.email;
                document.getElementById("phone").value = c.phone;
                document.getElementById("address").value = c.address ?? "";
            } catch (err) {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Failed to load customer.</div>';
            }
        }

        // Validate phone number format
        function validatePhoneNumber(phone) {
            if (!phone) return true; // Allow empty phone
            const phoneRegex = /^\+[1-9]\d{0,15}$/;
            return phoneRegex.test(phone);
        }

        // Clean phone number format
        function cleanPhoneNumber(phone) {
            if (!phone) return phone;
            // Remove all non-digit characters except +
            let cleaned = phone.replace(/[^\d+]/g, '');
            // If it doesn't start with +, add it
            if (cleaned && !cleaned.startsWith('+')) {
                cleaned = '+' + cleaned;
            }
            return cleaned;
        }

        // Real-time phone validation
        function setupPhoneValidation() {
            const phoneInput = document.getElementById('phone');
            const phoneError = document.getElementById('phone-error');
            
            phoneInput.addEventListener('input', function(e) {
                const value = e.target.value;
                
                // Only allow + at start and numbers
                const filtered = value.replace(/[^+\d]/g, '');
                if (filtered !== value) {
                    e.target.value = filtered;
                }
                
                // Validate format
                if (value && !validatePhoneNumber(value)) {
                    phoneError.textContent = 'Phone must start with + followed by numbers only';
                    phoneError.style.display = 'block';
                    phoneInput.classList.add('is-invalid');
                } else {
                    phoneError.style.display = 'none';
                    phoneInput.classList.remove('is-invalid');
                }
            });
        }

        // Submit updates
        document.getElementById("editCustomerForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const data = {
                first_name: document.getElementById("first_name").value,
                last_name: document.getElementById("last_name").value,
                email: document.getElementById("email").value,
                phone: cleanPhoneNumber(document.getElementById("phone").value),
                address: document.getElementById("address").value
            };

            try {
                const res = await axios.put(`/customers/${customerId}`, data);
                document.getElementById('message').innerHTML = '<div class="alert alert-success">Customer updated successfully!</div>';
                setTimeout(() => {
                    window.location.href = "/customers";
                }, 1500);
            } catch (err) {
                const errorDiv = document.getElementById('error-message');
                if (err.response && err.response.data && err.response.data.errors) {
                    let errorText = 'Validation errors:<br>';
                    Object.keys(err.response.data.errors).forEach(field => {
                        errorText += `${field}: ${err.response.data.errors[field].join(', ')}<br>`;
                    });
                    errorDiv.innerHTML = errorText;
                    errorDiv.style.display = 'block';
                } else {
                    errorDiv.innerHTML = 'Failed to update customer. Please try again.';
                    errorDiv.style.display = 'block';
                }
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
            
            loadCustomer();
        }

        // Load data after a short delay to ensure layout scripts are loaded
        setTimeout(() => {
            checkAuthAndLoad();
            setupPhoneValidation();
        }, 100);
    </script>

@endsection
