<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .auth-form {
            transition: all 0.3s ease;
        }
        .hidden-form {
            opacity: 0;
            height: 0;
            overflow: hidden;
        }
        .active-tab {
            background-color: #3b82f6;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="flex mb-6">
            <button id="login-tab" class="flex-1 py-2 px-4 rounded-l-lg active-tab">Login</button>
            <button id="register-tab" class="flex-1 py-2 px-4 rounded-r-lg bg-gray-200">Register</button>
        </div>

        <!-- Login Form -->
        <form id="login-form" class="auth-form" method="POST" action="/api/auth/login">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="login-email">Email</label>
                <input type="email" id="login-email" name="email" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 mb-2" for="login-password">Password</label>
                <input type="password" id="login-password" name="password" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
                Login
            </button>
        </form>

        <!-- Register Form -->
        <form id="register-form" class="auth-form hidden-form" method="POST" action="/api/auth/register">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="register-name">Name</label>
                <input type="text" id="register-name" name="name" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="register-email">Email</label>
                <input type="email" id="register-email" name="email" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="register-phone">Phone Number</label>
                <input type="tel" id="register-phone" name="phone_number" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="register-password">Password</label>
                <input type="password" id="register-password" name="password" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 mb-2" for="register-password-confirm">Confirm Password</label>
                <input type="password" id="register-password-confirm" name="password_confirmation" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">
                Register
            </button>
        </form>

        <div id="response-message" class="mt-4 text-center hidden"></div>
    </div>

    <script>
        // API utility functions
        const API = {
            // Get CSRF token from meta tag
            getCSRFToken() {
                const token = document.querySelector('meta[name="csrf-token"]');
                return token ? token.getAttribute('content') : '';
            },

            // Get auth token from cookie
           getAuthToken() {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'auth_token') {  // This is correct as it matches your backend cookie name
            return value;
        }
    }
    return null;
},

            // Make authenticated API request
            async request(url, options = {}) {
                const defaultHeaders = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                };

                // Add auth token to headers if available
                const authToken = this.getAuthToken();
                if (authToken) {
                    defaultHeaders['Authorization'] = `Bearer ${authToken}`;
                }

                const config = {
                    ...options,
                    headers: {
                        ...defaultHeaders,
                        ...options.headers
                    },
                    credentials: 'include' // Include cookies in requests
                };

                return fetch(url, config);
            }
        };

        // Toggle between forms
        document.getElementById('login-tab').addEventListener('click', () => {
            document.getElementById('login-form').classList.remove('hidden-form');
            document.getElementById('register-form').classList.add('hidden-form');
            document.getElementById('login-tab').classList.add('active-tab');
            document.getElementById('register-tab').classList.remove('active-tab');
        });

        document.getElementById('register-tab').addEventListener('click', () => {
            document.getElementById('register-form').classList.remove('hidden-form');
            document.getElementById('login-form').classList.add('hidden-form');
            document.getElementById('register-tab').classList.add('active-tab');
            document.getElementById('login-tab').classList.remove('active-tab');
        });

        // Handle login form submission
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await API.request('/api/auth/login', {
                    method: 'POST',
                    body: JSON.stringify({
                        email: formData.get('email'),
                        password: formData.get('password')
                    })
                });

                const data = await response.json();
                showResponse(data);
                
                if (data.success) {
                    // Cookie is automatically set by the server
                    // Optionally redirect to dashboard
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 1500);
                }
            } catch (error) {
                showResponse({
                    success: false,
                    message: 'Network error. Please try again.'
                });
            }
        });

        // Handle register form submission
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await API.request('/api/auth/register', {
                    method: 'POST',
                    body: JSON.stringify({
                        name: formData.get('name'),
                        email: formData.get('email'),
                        phone_number: formData.get('phone_number'),
                        password: formData.get('password'),
                        password_confirmation: formData.get('password_confirmation')
                    })
                });

                const data = await response.json();
                showResponse(data);
                
                if (data.success) {
                    // Cookie is automatically set by the server
                    // Switch to login form after successful registration
                    setTimeout(() => {
                        document.getElementById('login-tab').click();
                        showResponse({
                            success: true,
                            message: 'Registration successful! Please login.'
                        });
                    }, 1500);
                }
            } catch (error) {
                showResponse({
                    success: false,
                    message: 'Network error. Please try again.'
                });
            }
        });

        // Display response messages
        function showResponse(data) {
            const responseEl = document.getElementById('response-message');
            responseEl.classList.remove('hidden');
            responseEl.textContent = data.message;
            responseEl.className = `mt-4 text-center ${data.success ? 'text-green-500' : 'text-red-500'}`;
            
            // Auto-hide success messages after 3 seconds
            if (data.success) {
                setTimeout(() => {
                    responseEl.classList.add('hidden');
                }, 3000);
            }
        }

        // Example function to make authenticated API calls elsewhere in your app
        async function makeAuthenticatedRequest(endpoint, options = {}) {
            try {
                const response = await API.request(endpoint, options);
                const data = await response.json();
                
                if (response.status === 401) {
                    // Token expired or invalid, redirect to login
                    window.location.href = '/login';
                    return;
                }
                
                return data;
            } catch (error) {
                console.error('API request failed:', error);
                throw error;
            }
        }

        // Check if user is already authenticated on page load
        window.addEventListener('DOMContentLoaded', () => {
            const authToken = API.getAuthToken();
            if (authToken) {
                // User is authenticated, you might want to redirect to dashboard
                // or show different UI
                console.log('User is authenticated');
            }
        });
    </script>
</body>
</html>