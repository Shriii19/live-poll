<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Live Poll Platform')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .poll-option {
            cursor: pointer;
            transition: all 0.2s;
        }
        .poll-option:hover {
            background-color: #e9ecef;
        }
        .poll-option.selected {
            background-color: #0d6efd;
            color: white;
        }
        .poll-option.voted {
            background-color: #198754;
            color: white;
        }
        .progress {
            height: 25px;
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
        .vote-count {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .alert-floating {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
        .history-timeline {
            border-left: 3px solid #0d6efd;
            padding-left: 20px;
        }
        .history-item {
            position: relative;
            padding-bottom: 15px;
        }
        .history-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
        }
        .history-item.released::before {
            background: #dc3545;
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/polls">
                <i class="fas fa-poll me-2"></i>Live Poll
            </a>
            @auth
            <div class="navbar-nav ms-auto">
                @if(Auth::user()->is_admin)
                <a class="nav-link" href="/admin">
                    <i class="fas fa-cog me-1"></i>Admin
                </a>
                @endif
                <span class="nav-link text-light">
                    <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                </span>
                <form action="/logout" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm ms-2">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </nav>

    <div id="alert-container"></div>

    <main class="container py-4">
        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CSRF token setup for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Global alert function
        function showAlert(message, type = 'success') {
            const alertHtml = `
                <div class="alert alert-${type} alert-floating alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#alert-container').html(alertHtml);
            setTimeout(() => {
                $('.alert-floating').alert('close');
            }, 4000);
        }
    </script>
    @yield('scripts')
</body>
</html>
