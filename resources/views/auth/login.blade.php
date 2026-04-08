<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Live Poll Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0c1a2b;
            overflow: hidden;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #ff6a3d 0%, #ff8f3f 45%, #ffd166 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -200px;
            left: -200px;
            animation: float 8s ease-in-out infinite;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -100px;
            right: -100px;
            animation: float 6s ease-in-out infinite reverse;
        }

        .login-left .grid-overlay {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.16) 1px, transparent 1px);
            background-size: 34px 34px;
            opacity: 0.16;
            pointer-events: none;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 30px) scale(1.05); }
        }

        .login-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }

        .login-left-content h1 {
            font-size: 3rem;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .login-left-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 400px;
            line-height: 1.6;
        }

        .poll-icon-large {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2); }
            50% { transform: scale(1.05); box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3); }
        }

        .login-right {
            width: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: radial-gradient(circle at top, #223951 0%, #152637 60%, #101d2b 100%);
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            animation: loginReveal 0.65s ease;
        }

        @keyframes loginReveal {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h2 {
            color: #f1f5f9;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #94a3b8;
        }

        .form-label {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            background: #334155;
            border: 2px solid #334155;
            border-right: none;
            color: #94a3b8;
            border-radius: 12px 0 0 12px;
            padding: 0.875rem 1rem;
        }

        .form-control {
            background: #334155;
            border: 2px solid #334155;
            border-left: none;
            color: #f1f5f9;
            padding: 0.875rem 1rem;
            border-radius: 0 12px 12px 0;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .form-control:focus {
            background: #334155;
            border-color: #ff8f3f;
            color: #f1f5f9;
            box-shadow: 0 0 0 4px rgba(255, 143, 63, 0.18);
        }

        .input-group:focus-within .input-group-text {
            border-color: #ff8f3f;
            color: #ff8f3f;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(130deg, #ff6a3d 0%, #ff8f3f 50%, #ffd166 100%);
            border: none;
            padding: 1rem;
            font-weight: 700;
            font-size: 1rem;
            border-radius: 12px;
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 28px -14px rgba(255, 143, 63, 0.8);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px -12px rgba(255, 143, 63, 0.9);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .demo-accounts {
            margin-top: 2rem;
            padding: 1.25rem;
            background: rgba(255, 143, 63, 0.12);
            border-radius: 14px;
            border: 1px solid rgba(255, 143, 63, 0.28);
        }

        .demo-accounts h6 {
            color: #ff8f3f;
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .demo-account {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }

        .demo-account:last-child {
            border-bottom: none;
        }

        .demo-account .role {
            color: #f1f5f9;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .demo-account .credentials {
            color: #94a3b8;
            font-size: 0.8rem;
            font-family: monospace;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }

        @media (max-width: 992px) {
            .login-left {
                display: none;
            }
            .login-right {
                width: 100%;
            }
            body {
                background: linear-gradient(135deg, #ff6a3d 0%, #ff8f3f 50%, #ffd166 100%);
            }
            .login-right {
                background: transparent;
            }
            .login-card {
                background: #1e293b;
                padding: 2.5rem;
                border-radius: 24px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="login-left">
        <div class="grid-overlay"></div>
        <div class="login-left-content">
            <div class="poll-icon-large">
                <i class="fas fa-poll"></i>
            </div>
            <h1>Live Poll</h1>
            <p>Create engaging polls, gather real-time feedback, and visualize results instantly with our powerful polling platform.</p>
        </div>
    </div>
    
    <div class="login-right">
        <div class="login-card">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to continue to your dashboard</p>
            </div>

            <div id="alert-box"></div>

            <form id="loginForm">
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="demo-accounts">
                <h6><i class="fas fa-info-circle me-2"></i>Demo Accounts</h6>
                <div class="demo-account">
                    <span class="role"><i class="fas fa-user-shield me-2"></i>Admin</span>
                    <span class="credentials">admin@poll.com / password</span>
                </div>
                <div class="demo-account">
                    <span class="role"><i class="fas fa-user me-2"></i>User</span>
                    <span class="credentials">user@poll.com / password</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const $btn = $('#loginBtn');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Signing in...');

            $.ajax({
                url: '/login',
                method: 'POST',
                data: {
                    email: $('#email').val(),
                    password: $('#password').val()
                },
                success: function(response) {
                    if (response.success) {
                        $btn.html('<i class="fas fa-check me-2"></i>Success!');
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 500);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Login failed. Please try again.';
                    $('#alert-box').html(`
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${message}
                        </div>
                    `);
                    $btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                }
            });
        });
    </script>
</body>
</html>
