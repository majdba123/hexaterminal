<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HexaTerminal API - Comprehensive RESTful API for managing teams, services, projects, and more">

    <title>HexaTerminal API - Professional API Documentation</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary-color: #2B9BFF;
            --secondary-color: #667eea;
            --accent-color: #764ba2;
            --dark-bg: #0a0e27;
            --card-bg: #1a1f3a;
            --text-primary: #ffffff;
            --text-secondary: #d4d6d7;
            --text-muted: #8b8d9e;
            --border-color: #2a2f4a;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--dark-bg);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background: rgba(10, 14, 39, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
        }

        .logo-svg {
            width: 60px;
            height: 60px;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-text-part1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .logo-text-part2 {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .nav-actions {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .nav-link:hover {
            color: var(--primary-color);
            background: rgba(43, 155, 255, 0.1);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #0a0e27 100%);
            padding: 6rem 0 4rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(43, 155, 255, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--primary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-weight: 400;
        }

        .api-base-url {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            color: var(--primary-color);
            margin-top: 1rem;
        }

        .copy-btn {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            margin-left: 1rem;
        }

        .copy-btn:hover {
            background: #1a7fd9;
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            text-align: center;
            color: var(--text-primary);
        }

        /* Route Groups */
        .route-groups {
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        .route-group {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            padding: 2rem;
            transition: all 0.3s;
        }

        .route-group:hover {
            border-color: var(--primary-color);
            box-shadow: 0 10px 40px rgba(43, 155, 255, 0.1);
        }

        .group-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .group-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .group-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            text-transform: capitalize;
        }

        .routes-list {
            display: grid;
            gap: 1rem;
        }

        /* Route Card */
        .route-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .route-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary-color);
            transform: translateX(5px);
        }

        .method-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            min-width: 70px;
            text-align: center;
            flex-shrink: 0;
        }

        .method-get {
            background: rgba(59, 130, 246, 0.2);
            color: var(--info);
            border: 1px solid var(--info);
        }

        .method-post {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .method-put {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .method-delete {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .route-info {
            flex: 1;
        }

        .route-path {
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            word-break: break-all;
        }

        .route-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .route-meta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .meta-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .auth-badge {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .admin-badge {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(43, 155, 255, 0.2);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
        }

        .feature-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            padding: 3rem 2rem;
            text-align: center;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-text {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.25rem;
            }

            .header-inner {
                padding: 0 1rem;
            }

            .logo-text-part1,
            .logo-text-part2 {
                font-size: 1.25rem;
            }

            .main-content {
                padding: 2rem 1rem;
            }

            .route-card {
                flex-direction: column;
            }

            .method-badge {
                align-self: flex-start;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #1a7fd9;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="/" class="logo-container">
                <svg class="logo-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                    <polygon
                        points="60,20 85,35 85,65 60,80 35,65 35,35"
                        fill="#FFFFFF"
                        stroke="#2B9BFF"
                        stroke-width="4"
                        stroke-linejoin="round"
                    />
                    <text
                        x="60"
                        y="55"
                        font-family="monospace"
                        font-weight="700"
                        font-size="24"
                        fill="#000000"
                        text-anchor="middle"
                        dominant-baseline="middle"
                    >
                        &gt;_
                    </text>
                </svg>
                <div class="logo-text">
                    <span class="logo-text-part1">Hexa</span>
                    <span class="logo-text-part2">Terminal</span>
                </div>
            </a>
            <div class="nav-actions">
                <a href="#endpoints" class="nav-link">Endpoints</a>
                <a href="#features" class="nav-link">Features</a>
                @auth
                    <a href="{{ url('/home') }}" class="nav-link">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="nav-link">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>HexaTerminal API</h1>
            <p class="hero-subtitle">Professional RESTful API for managing teams, services, projects, and more</p>
            <div class="api-base-url">
                <span>Base URL:</span>
                <strong id="baseUrl">{{ $baseUrl }}/api</strong>
                <button class="copy-btn" onclick="copyBaseUrl()">Copy</button>
            </div>
        </div>
    </section>

    <main class="main-content">
        <section id="endpoints">
            <h2 class="section-title">API Endpoints</h2>

            <div class="route-groups">
                @forelse($routes as $prefix => $groupRoutes)
                    <div class="route-group">
                        <div class="group-header">
                            <div class="group-icon">
                                {{ strtoupper(substr($prefix, 0, 1)) }}
                            </div>
                            <h3 class="group-title">{{ str_replace('_', ' ', $prefix) }}</h3>
                        </div>
                        <div class="routes-list">
                            @foreach($groupRoutes as $route)
                                <div class="route-card" loading="lazy">
                                    <span class="method-badge method-{{ strtolower($route['method']) }}">
                                        {{ $route['method'] }}
                                    </span>
                                    <div class="route-info">
                                        <div class="route-path">
                                            {{ $baseUrl }}/{{ $route['uri'] }}
                                        </div>
                                        <div class="route-description">
                                            {{ $route['description'] }}
                                        </div>
                                        <div class="route-meta">
                                            @if($route['requiresAuth'] ?? false)
                                                <span class="meta-badge auth-badge">Requires Auth</span>
                                            @endif
                                            @if($route['requiresAdmin'] ?? false)
                                                <span class="meta-badge admin-badge">Admin Only</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="route-group">
                        <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                            No API routes found. Please check your route configuration.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <section id="features" style="margin-top: 6rem;">
            <h2 class="section-title">Features</h2>
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3 class="feature-title">Secure Authentication</h3>
                    <p class="feature-description">
                        Protected endpoints using Laravel Sanctum for secure API access with token-based authentication
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">RESTful Design</h3>
                    <p class="feature-description">
                        Clean REST API following industry best practices with proper HTTP methods and status codes
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Fast & Reliable</h3>
                    <p class="feature-description">
                        Built on Laravel framework for optimal performance and scalability
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛠️</div>
                    <h3 class="feature-title">Easy Integration</h3>
                    <p class="feature-description">
                        Simple JSON responses for easy integration with any frontend framework or mobile app
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📝</div>
                    <h3 class="feature-title">Comprehensive</h3>
                    <p class="feature-description">
                        Complete API covering teams, services, projects, reviews, FAQs, and more
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌐</div>
                    <h3 class="feature-title">Production Ready</h3>
                    <p class="feature-description">
                        Built with production best practices including error handling and validation
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p class="footer-text">
                &copy; {{ date('Y') }} HexaTerminal. All rights reserved.
            </p>
            <p class="footer-text" style="margin-top: 0.5rem; opacity: 0.7;">
                Built with Laravel {{ Illuminate\Foundation\Application::VERSION }} | PHP {{ PHP_VERSION }}
            </p>
        </div>
    </footer>

    <script>
        function copyBaseUrl() {
            const baseUrl = document.getElementById('baseUrl').textContent;
            navigator.clipboard.writeText(baseUrl).then(() => {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                btn.style.background = '#10b981';
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '';
                }, 2000);
            });
        }

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
