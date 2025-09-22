<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My App')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/products">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="/customers">Customers</a></li>
                <li class="nav-item"><a class="nav-link" href="/orders">Orders</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span id="userInfo" class="navbar-text me-3" style="display: none;"></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/login" id="loginLink">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="logoutLink" onclick="logout()" style="display: none;">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    @yield('content')
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Axios for API calls -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- Optional: your custom JS -->
<!-- <script src="{{ asset('js/app.js') }}"></script> -->

@stack('scripts') <!-- for page-specific scripts -->

<script>
    // Authentication handling
    function checkAuth() {
        const token = localStorage.getItem('auth_token');
        const user = localStorage.getItem('user');
        
        if (token && user) {
            // User is logged in
            document.getElementById('loginLink').style.display = 'none';
            document.getElementById('logoutLink').style.display = 'block';
            
            try {
                const userData = JSON.parse(user);
                document.getElementById('userInfo').textContent = `Welcome, ${userData.name}`;
                document.getElementById('userInfo').style.display = 'block';
            } catch (e) {
                // Silent fail
            }
        } else {
            // User is not logged in
            document.getElementById('loginLink').style.display = 'block';
            document.getElementById('logoutLink').style.display = 'none';
            document.getElementById('userInfo').style.display = 'none';
        }
    }

    function logout() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
    }

    // Configure axios to include auth token
    axios.defaults.baseURL = '/api';
    axios.interceptors.request.use(function (config) {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    });

    // Handle token expiration globally
    axios.interceptors.response.use(
        function (response) {
            return response;
        },
        function (error) {
            if (error.response && error.response.status === 401) {
                // Don't redirect if we're already on the login page (to allow error messages to show)
                const currentPath = window.location.pathname;
                if (currentPath !== '/login') {
                    // Token expired or invalid, redirect to login
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                }
            }
            return Promise.reject(error);
        }
    );

    // Check authentication on page load
    document.addEventListener('DOMContentLoaded', function() {
        checkAuth();
        
        // If user is on a protected page and not authenticated, redirect to login
        const protectedPages = ['/dashboard', '/orders', '/customers', '/products'];
        const currentPath = window.location.pathname;
        const token = localStorage.getItem('auth_token');
        
        if (protectedPages.some(page => currentPath.startsWith(page)) && !token) {
            window.location.href = '/login';
        }
    });
</script>
</body>
</html>
