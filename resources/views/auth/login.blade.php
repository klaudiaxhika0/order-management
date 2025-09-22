@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login</h3>
                    </div>
                    <div class="card-body">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        <div id="loginMessage" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('loginMessage');
            const submitButton = document.querySelector('button[type="submit"]');
            
            // Clear previous messages
            messageDiv.innerHTML = '';
            
            // Disable submit button to prevent double submission
            submitButton.disabled = true;
            submitButton.innerHTML = 'Logging in...';
            
            try {
                const response = await axios.post('/login', {
                    email: email,
                    password: password
                });
                
                if (response.data.success) {
                    localStorage.setItem('auth_token', response.data.data.access_token);
                    localStorage.setItem('user', JSON.stringify(response.data.data.user));
                    
                    messageDiv.innerHTML = '<div class="alert alert-success">Login successful! Redirecting...</div>';
                    
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-danger">Login failed: ' + response.data.message + '</div>';
                    // Re-enable submit button
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Login';
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || 'Login failed. Please try again.';
                messageDiv.innerHTML = '<div class="alert alert-danger">' + errorMessage + '</div>';
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Login';
            }
        });

        // Redirect if already logged in
        if (localStorage.getItem('auth_token')) {
            window.location.href = '/dashboard';
        }
    </script>
@endsection
