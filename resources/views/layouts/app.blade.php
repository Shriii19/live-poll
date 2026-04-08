<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Live Poll Platform')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(130deg, #ff6a3d 0%, #ff8f3f 45%, #ffd166 100%);
            --secondary-gradient: linear-gradient(130deg, #00b4d8 0%, #0096c7 100%);
            --success-gradient: linear-gradient(130deg, #2ec4b6 0%, #29b6a8 100%);
            --info-gradient: linear-gradient(130deg, #118ab2 0%, #06d6a0 100%);
            --warning-gradient: linear-gradient(130deg, #f48c06 0%, #ffbe0b 100%);
            --card-shadow: 0 14px 40px -20px rgba(23, 28, 40, 0.45);
            --card-hover-shadow: 0 30px 60px -28px rgba(18, 22, 32, 0.5);
            --glass-bg: rgba(255, 255, 255, 0.82);
            --glass-border: rgba(255, 255, 255, 0.38);
            --bg-primary: #fff8f2;
            --bg-secondary: #ffe8d8;
            --text-primary: #243447;
            --text-secondary: #5d6d7e;
        }

        [data-bs-theme="dark"] {
            --glass-bg: rgba(24, 33, 45, 0.86);
            --glass-border: rgba(255, 255, 255, 0.12);
            --bg-primary: #0b1420;
            --bg-secondary: #152030;
            --text-primary: #f4f7fb;
            --text-secondary: #a2b2c3;
        }

        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        body {
            background: var(--bg-primary);
            background-image: 
                radial-gradient(circle at 12% 18%, rgba(255, 143, 63, 0.2) 0%, transparent 42%),
                radial-gradient(circle at 78% 8%, rgba(0, 180, 216, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 84% 72%, rgba(46, 196, 182, 0.16) 0%, transparent 45%),
                radial-gradient(circle at 14% 88%, rgba(255, 209, 102, 0.2) 0%, transparent 42%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text-primary);
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            opacity: 0.18;
            background-image: radial-gradient(rgba(36, 52, 71, 0.25) 0.6px, transparent 0.6px);
            background-size: 14px 14px;
        }

        .navbar {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 0;
            transition: padding 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .navbar.scrolled {
            padding: 0.45rem 0;
            box-shadow: 0 12px 30px -16px rgba(18, 22, 32, 0.45);
            background: color-mix(in srgb, var(--glass-bg) 92%, #ffffff 8%) !important;
        }

        .navbar-brand {
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: 0.02em;
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: transform 0.3s ease, filter 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            -webkit-text-fill-color: initial;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .nav-link {
            color: var(--text-primary) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            left: 0.9rem;
            right: 0.9rem;
            bottom: 0.25rem;
            height: 2px;
            border-radius: 999px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.25s ease;
        }

        .nav-link:hover {
            background: rgba(255, 106, 61, 0.14);
        }

        .nav-link:hover::after {
            transform: scaleX(1);
        }

        .card {
            border: 1px solid var(--glass-border);
            border-radius: 22px;
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }

        .card-header {
            background: var(--primary-gradient) !important;
            color: white !important;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .poll-option {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 14px;
            margin-bottom: 0.75rem;
            padding: 1.25rem 1.5rem;
            border: 2px solid transparent;
            background: rgba(241, 245, 249, 0.8);
            position: relative;
            overflow: hidden;
        }

        [data-bs-theme="dark"] .poll-option {
            background: rgba(30, 41, 59, 0.8);
        }

        .poll-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .poll-option:hover {
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateX(8px) scale(1.01);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.15);
        }

        .poll-option.selected {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .poll-option.selected i {
            animation: bounce 0.5s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }

        .poll-option.voted {
            background: var(--success-gradient);
            color: white;
            border-color: transparent;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .progress {
            height: 32px;
            border-radius: 16px;
            background: rgba(226, 232, 240, 0.8);
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: visible;
            position: relative;
        }

        [data-bs-theme="dark"] .progress {
            background: rgba(30, 41, 59, 0.8);
        }

        .progress-bar {
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--primary-gradient);
            border-radius: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: progressShine 2s infinite;
        }

        @keyframes progressShine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 0.875rem 1.75rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 24px -10px rgba(255, 106, 61, 0.75);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px -10px rgba(255, 106, 61, 0.85);
            filter: brightness(1.05);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            padding: 0.875rem 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .btn-success::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-success:hover::before {
            left: 100%;
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
        }

        .btn-danger {
            background: var(--secondary-gradient);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(244, 114, 182, 0.3);
            padding: 0.875rem 1.75rem;
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(244, 114, 182, 0.5);
        }

        .btn-outline-light {
            border-radius: 10px;
            font-weight: 500;
            border-width: 2px;
            transition: all 0.3s ease;
        }

        .btn-outline-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .list-group-item.active {
            background: var(--primary-gradient);
            border-color: transparent;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .list-group-item {
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            color: var(--text-primary);
        }

        .list-group-item:hover {
            background: rgba(99, 102, 241, 0.08);
            transform: translateX(5px);
            border-left: 3px solid #6366f1;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
        }

        .badge.bg-success {
            background: var(--success-gradient) !important;
        }

        .badge.bg-light {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px);
        }

        .alert-floating {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 1050;
            min-width: 320px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: slideInRight 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(52, 211, 153, 0.95));
            color: white;
            border: none;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(248, 113, 113, 0.95));
            color: white;
            border: none;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .history-timeline {
            border-left: 3px solid;
            border-image: var(--primary-gradient) 1;
            padding-left: 25px;
            margin-left: 10px;
        }

        .history-item {
            position: relative;
            padding-bottom: 25px;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .history-item::before {
            content: '';
            position: absolute;
            left: -32px;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--primary-gradient);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.6), 0 0 30px rgba(99, 102, 241, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 15px rgba(99, 102, 241, 0.6), 0 0 30px rgba(99, 102, 241, 0.3); }
            50% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.8), 0 0 40px rgba(99, 102, 241, 0.4); }
        }

        .history-item.released::before {
            background: var(--secondary-gradient);
            box-shadow: 0 0 15px rgba(244, 114, 182, 0.6), 0 0 30px rgba(244, 114, 182, 0.3);
        }

        .modal-content {
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            border: none;
            padding: 1.25rem 1.5rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            padding: 0.875rem 1rem;
            transition: all 0.3s ease;
            background: var(--glass-bg);
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: white;
        }

        [data-bs-theme="dark"] .form-control {
            background: rgba(30, 41, 59, 0.8);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        [data-bs-theme="dark"] .form-control:focus {
            background: rgba(30, 41, 59, 0.95);
        }

        .table {
            border-radius: 16px;
            overflow: hidden;
            background: var(--glass-bg);
        }

        .table thead {
            background: var(--primary-gradient);
            color: white;
        }

        .table thead th {
            border: none;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, 0.05);
        }

        .table-hover tbody tr {
            transition: all 0.3s ease;
        }

        .table-hover tbody tr:hover {
            background: rgba(99, 102, 241, 0.08);
            transform: scale(1.01);
        }

        .theme-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: 0 12px 30px -10px rgba(255, 106, 61, 0.75);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(15deg);
            box-shadow: 0 16px 35px -10px rgba(255, 106, 61, 0.85);
        }

        .theme-toggle.spin {
            animation: toggleSpin 0.5s ease;
        }

        @keyframes toggleSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(180deg); }
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 50px;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .live-indicator::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: livePulse 1.5s infinite;
        }

        .reveal-up {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            transition-delay: calc(var(--reveal-order, 0) * 85ms);
        }

        .reveal-up.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .page-shell {
            animation: pageEnter 0.55s ease-out;
        }

        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes livePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.5); }
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
        }

        /* Container padding */
        .container {
            max-width: 1320px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }

            .reveal-up {
                opacity: 1;
                transform: none;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/polls">
                <i class="fas fa-poll"></i>
                <span>Live Poll</span>
            </a>
            @auth
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center gap-2">
                @if(Auth::user()->is_admin)
                <a class="nav-link" href="/admin">
                    <i class="fas fa-cog me-1"></i>Admin
                </a>
                @endif
                <span class="nav-link">
                    <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                </span>
                <form action="/logout" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 10px; font-weight: 500; padding: 0.5rem 1rem;">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </nav>

    <div id="alert-container"></div>

    <main class="container py-5 page-shell">
        @yield('content')
    </main>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" title="Toggle dark mode">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            themeToggle.classList.remove('spin');
            window.requestAnimationFrame(() => themeToggle.classList.add('spin'));
        });
        
        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        function handleNavbarScroll() {
            const navbar = document.querySelector('.navbar');
            if (!navbar) {
                return;
            }

            if (window.scrollY > 12) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }

        function initRevealAnimations() {
            const revealTargets = document.querySelectorAll('.card, .poll-list-item, .poll-admin-item, .empty-state, .empty-admin-state');
            if (!revealTargets.length) {
                return;
            }

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            revealTargets.forEach((element, index) => {
                element.classList.add('reveal-up');
                element.style.setProperty('--reveal-order', String(index % 8));

                if (reducedMotion) {
                    element.classList.add('is-visible');
                }
            });

            if (reducedMotion) {
                return;
            }

            const observer = new IntersectionObserver((entries, localObserver) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        localObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            revealTargets.forEach((element) => observer.observe(element));
        }

        // CSRF token setup for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Global alert function with enhanced styling
        function showAlert(message, type = 'success') {
            const icons = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            const alertHtml = `
                <div class="alert alert-${type} alert-floating alert-dismissible fade show" role="alert">
                    <i class="fas ${icons[type] || icons.info} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#alert-container').html(alertHtml);
            setTimeout(() => {
                $('.alert-floating').alert('close');
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initRevealAnimations();
            handleNavbarScroll();
            window.addEventListener('scroll', handleNavbarScroll, { passive: true });
        });
    </script>
    @yield('scripts')
</body>
</html>
