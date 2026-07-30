<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - HexaTerminal</title>

    @vite(['resources/css/website.css'])
    <script src="{{ asset('vendor/axios.min.js') }}"></script>

    <style>
        :root {
            --sb-w: 260px;
            --hd-h: 60px;
            --primary: #2B9BFF;
            --primary-dark: #1a7fe0;
            --accent: #a855f7;
            --green: #00d084;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark-bg: #060918;
            --dark-bg-2: #0a0e23;
            --dark-bg-3: #0e1230;
            --card-bg: rgba(17,22,56,0.65);
            --card-bg-solid: #111638;
            --card-border: rgba(43,155,255,0.08);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-color: rgba(43,155,255,0.1);
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --transition: all 0.25s ease;
        }
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { -webkit-text-size-adjust:100%; }
        body {
            font-family:'Inter','Cairo',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
            background: var(--dark-bg); color: var(--text-primary);
            line-height:1.6; -webkit-font-smoothing:antialiased;
            overflow-x:hidden;
        }
        ::selection { background:rgba(43,155,255,0.3); color:#fff; }
        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-track { background:var(--dark-bg); }
        ::-webkit-scrollbar-thumb { background:var(--primary); border-radius:3px; }

        /* ═══ SIDEBAR ═══ */
        .admin-sidebar {
            position:fixed; top:0; left:0; bottom:0; width:var(--sb-w);
            background:var(--dark-bg-2); border-right:1px solid var(--border-color);
            display:flex; flex-direction:column; z-index:100;
            transition:transform 0.3s ease;
        }
        .sidebar-brand {
            height:var(--hd-h); display:flex; align-items:center; gap:0.65rem;
            padding:0 1.25rem; border-bottom:1px solid var(--border-color);
            text-decoration:none;
        }
        .sidebar-brand span:first-child { font-weight:800; font-size:1.1rem; color:var(--text-primary); }
        .sidebar-brand span:last-child { font-weight:800; font-size:1.1rem; color:var(--primary); }
        .sidebar-nav { flex:1; overflow-y:auto; padding:1rem 0.75rem; }
        .sidebar-label {
            font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:1.5px;
            color:var(--text-muted); padding:0.75rem 0.75rem 0.4rem; margin-top:0.5rem;
        }
        .sidebar-link {
            display:flex; align-items:center; gap:0.75rem; padding:0.65rem 0.85rem;
            color:var(--text-secondary); text-decoration:none; border-radius:var(--radius-sm);
            font-size:0.88rem; font-weight:500; transition:var(--transition); margin-bottom:2px;
        }
        .sidebar-link i { width:18px; text-align:center; font-size:0.85rem; opacity:0.7; }
        .sidebar-link:hover, .sidebar-link.active {
            background:rgba(43,155,255,0.08); color:var(--text-primary);
        }
        .sidebar-link.active { border-left:3px solid var(--primary); padding-left:calc(0.85rem - 3px); }
        .sidebar-link.active i { color:var(--primary); opacity:1; }
        .sidebar-link .badge {
            margin-left:auto; font-size:0.7rem; padding:0.15rem 0.5rem;
            border-radius:999px; font-weight:600;
            background:rgba(43,155,255,0.12); color:var(--primary);
        }
        .sidebar-footer {
            padding:1rem 1.25rem; border-top:1px solid var(--border-color);
            display:flex; align-items:center; gap:0.75rem;
        }
        .sidebar-avatar {
            width:36px; height:36px; border-radius:50%;
            background:linear-gradient(135deg,var(--primary),var(--accent));
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:0.85rem; color:#fff; flex-shrink:0;
        }
        .sidebar-user-name { font-size:0.85rem; font-weight:600; color:var(--text-primary); }
        .sidebar-user-role { font-size:0.72rem; color:var(--text-muted); }

        /* ═══ HEADER ═══ */
        .admin-header {
            position:fixed; top:0; left:var(--sb-w); right:0; height:var(--hd-h);
            background:rgba(6,9,24,0.9); backdrop-filter:blur(8px);
            border-bottom:1px solid var(--border-color);
            display:flex; align-items:center; justify-content:space-between;
            padding:0 1.75rem; z-index:90;
        }
        .admin-header-left { display:flex; align-items:center; gap:1rem; }
        .admin-header-left h1 { font-size:1.1rem; font-weight:700; }
        .admin-header-right { display:flex; align-items:center; gap:0.75rem; }
        .header-btn {
            background:none; border:1px solid var(--border-color); color:var(--text-secondary);
            padding:0.45rem 0.85rem; border-radius:var(--radius-sm); cursor:pointer;
            font-size:0.82rem; font-weight:500; transition:var(--transition);
            display:flex; align-items:center; gap:0.4rem;
        }
        .header-btn:hover { border-color:var(--primary); color:var(--primary); }
        .hamburger-admin {
            display:none; background:none; border:1px solid var(--border-color);
            color:var(--text-primary); padding:0.45rem; border-radius:var(--radius-sm);
            cursor:pointer; font-size:1.1rem;
        }

        /* ═══ MAIN ═══ */
        .admin-main {
            margin-left:var(--sb-w); padding-top:var(--hd-h);
            min-height:100vh;
        }
        .admin-content { padding:1.75rem; }

        /* ═══ CARDS & STATS ═══ */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.25rem; margin-bottom:2rem; }
        .stat-card {
            background:var(--card-bg); border:1px solid var(--card-border);
            border-radius:var(--radius-md); padding:1.25rem 1.5rem;
            display:flex; align-items:center; gap:1rem;
        }
        .stat-icon {
            width:48px; height:48px; border-radius:var(--radius-sm);
            display:flex; align-items:center; justify-content:center;
            font-size:1.2rem; flex-shrink:0;
        }
        .stat-value { font-size:1.75rem; font-weight:800; line-height:1.1; }
        .stat-label { font-size:0.78rem; color:var(--text-muted); font-weight:500; }

        /* ═══ TABLE ═══ */
        .table-card {
            background:var(--card-bg); border:1px solid var(--card-border);
            border-radius:var(--radius-md); overflow:hidden;
        }
        .table-header {
            padding:1rem 1.5rem; display:flex; align-items:center; justify-content:space-between;
            border-bottom:1px solid var(--border-color); flex-wrap:wrap; gap:0.75rem;
        }
        .table-header h2 { font-size:1rem; font-weight:700; }
        .table-actions { display:flex; gap:0.5rem; align-items:center; }
        .search-input {
            padding:0.5rem 0.85rem; background:rgba(6,9,24,0.6);
            border:1px solid var(--border-color); border-radius:var(--radius-sm);
            color:var(--text-primary); font-size:0.82rem; outline:none;
            transition:var(--transition); width:220px;
        }
        .search-input:focus { border-color:var(--primary); }
        .search-input::placeholder { color:var(--text-muted); }

        .admin-table { width:100%; border-collapse:collapse; }
        .admin-table th {
            text-align:left; padding:0.75rem 1rem; font-size:0.72rem;
            text-transform:uppercase; letter-spacing:0.8px; font-weight:700;
            color:var(--text-muted); border-bottom:1px solid var(--border-color);
            background:rgba(6,9,24,0.3);
        }
        .admin-table td {
            padding:0.75rem 1rem; font-size:0.88rem; border-bottom:1px solid rgba(255,255,255,0.03);
            color:var(--text-secondary); vertical-align:middle;
        }
        .admin-table tr:hover td { background:rgba(43,155,255,0.03); }
        .admin-table img.thumb {
            width:40px; height:40px; border-radius:var(--radius-sm); object-fit:cover;
            border:1px solid var(--border-color);
        }

        .btn {
            display:inline-flex; align-items:center; gap:0.4rem;
            padding:0.5rem 1rem; border-radius:var(--radius-sm); font-weight:600;
            font-size:0.82rem; text-decoration:none; border:none; cursor:pointer;
            transition:var(--transition);
        }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-dark); }
        .btn-danger { background:rgba(239,68,68,0.12); color:var(--danger); border:1px solid rgba(239,68,68,0.2); }
        .btn-danger:hover { background:var(--danger); color:#fff; }
        .btn-sm { padding:0.35rem 0.65rem; font-size:0.75rem; }
        .btn-outline {
            background:transparent; color:var(--primary);
            border:1px solid rgba(43,155,255,0.25);
        }
        .btn-outline:hover { background:rgba(43,155,255,0.08); border-color:var(--primary); }
        .btn-success { background:rgba(0,208,132,0.12); color:var(--green); border:1px solid rgba(0,208,132,0.2); }
        .btn-success:hover { background:var(--green); color:#000; }
        .btn-warning { background:rgba(245,158,11,0.12); color:var(--warning); border:1px solid rgba(245,158,11,0.2); }
        .btn-warning:hover { background:var(--warning); color:#000; }

        /* ═══ MODAL ═══ */
        .modal-overlay {
            position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px);
            z-index:200; display:none; align-items:center; justify-content:center; padding:2rem;
        }
        .modal-overlay.open { display:flex; }
        .modal {
            background:var(--dark-bg-2); border:1px solid var(--border-color);
            border-radius:var(--radius-md); width:100%; max-width:600px;
            max-height:85vh; overflow-y:auto; animation:modalIn 0.25s ease;
        }
        @keyframes modalIn { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
        .modal-header {
            display:flex; align-items:center; justify-content:space-between;
            padding:1.25rem 1.5rem; border-bottom:1px solid var(--border-color);
        }
        .modal-header h3 { font-size:1rem; font-weight:700; }
        .modal-close {
            background:none; border:none; color:var(--text-muted); font-size:1.2rem;
            cursor:pointer; padding:0.25rem;
        }
        .modal-close:hover { color:var(--danger); }
        .modal-body { padding:1.5rem; }
        .modal-footer {
            padding:1rem 1.5rem; border-top:1px solid var(--border-color);
            display:flex; justify-content:flex-end; gap:0.5rem;
        }

        /* ═══ FORM ═══ */
        .form-group { margin-bottom:1.15rem; }
        .form-label {
            display:block; margin-bottom:0.35rem; font-size:0.82rem;
            font-weight:600; color:var(--text-primary);
        }
        .form-input, .form-select, .form-textarea {
            width:100%; padding:0.6rem 0.85rem; background:rgba(6,9,24,0.6);
            border:1px solid var(--border-color); border-radius:var(--radius-sm);
            color:var(--text-primary); font-size:0.88rem; font-family:inherit;
            outline:none; transition:var(--transition);
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color:var(--primary); }
        .form-input::placeholder, .form-textarea::placeholder { color:var(--text-muted); }
        .form-textarea { min-height:100px; resize:vertical; }
        .form-select { cursor:pointer; }
        .form-select option { background:var(--dark-bg-2); color:var(--text-primary); }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .form-error { font-size:0.75rem; color:var(--danger); margin-top:0.25rem; }

        /* ═══ TOAST ═══ */
        .toast-container {
            position:fixed; top:1rem; right:1rem; z-index:9999;
            display:flex; flex-direction:column; gap:0.5rem;
        }
        .toast {
            padding:0.75rem 1.25rem; border-radius:var(--radius-sm);
            background:var(--card-bg-solid); border:1px solid var(--border-color);
            box-shadow:0 10px 30px rgba(0,0,0,0.4); color:var(--text-primary);
            display:flex; align-items:center; gap:0.6rem; font-size:0.85rem;
            animation:slideIn 0.3s ease; min-width:280px;
        }
        .toast.success { border-left:3px solid var(--green); }
        .toast.error { border-left:3px solid var(--danger); }
        @keyframes slideIn { from { transform:translateX(100%); opacity:0; } to { transform:translateX(0); opacity:1; } }
        @keyframes slideOut { from { transform:translateX(0); opacity:1; } to { transform:translateX(100%); opacity:0; } }

        /* ═══ PAGINATION ═══ */
        .pagination-bar {
            padding:0.85rem 1.5rem; border-top:1px solid var(--border-color);
            display:flex; align-items:center; justify-content:space-between; font-size:0.82rem;
        }
        .pagination-info { color:var(--text-muted); }
        .pagination-btns { display:flex; gap:0.35rem; }
        .pagination-btns button {
            padding:0.35rem 0.7rem; border-radius:var(--radius-sm);
            border:1px solid var(--border-color); background:transparent;
            color:var(--text-secondary); font-size:0.78rem; cursor:pointer;
            transition:var(--transition);
        }
        .pagination-btns button:hover:not(:disabled) { border-color:var(--primary); color:var(--primary); }
        .pagination-btns button.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .pagination-btns button:disabled { opacity:0.3; cursor:default; }

        /* ═══ STATUS BADGE ═══ */
        .status-badge {
            display:inline-flex; padding:0.2rem 0.6rem; border-radius:999px;
            font-size:0.72rem; font-weight:600;
        }
        .status-pending { background:rgba(245,158,11,0.12); color:var(--warning); }
        .status-completed { background:rgba(0,208,132,0.12); color:var(--green); }
        .status-in_progress { background:rgba(43,155,255,0.12); color:var(--primary); }

        /* ═══ EMPTY STATE ═══ */
        .empty-state {
            text-align:center; padding:3rem 1.5rem; color:var(--text-muted);
        }
        .empty-state i { font-size:2.5rem; margin-bottom:1rem; opacity:0.3; }
        .empty-state p { font-size:0.92rem; }

        /* ═══ LOADING ═══ */
        .loading-row td { text-align:center; padding:2rem; color:var(--text-muted); }
        .spinner {
            display:inline-block; width:20px; height:20px; border:2px solid var(--border-color);
            border-top-color:var(--primary); border-radius:50%;
            animation:spin 0.6s linear infinite; margin-right:0.5rem; vertical-align:middle;
        }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* ═══ RESPONSIVE ═══ */
        @media (max-width:768px) {
            .admin-sidebar { transform:translateX(-100%); }
            .admin-sidebar.open { transform:translateX(0); }
            .admin-header { left:0; }
            .admin-main { margin-left:0; }
            .hamburger-admin { display:block; }
            .form-row { grid-template-columns:1fr; }
            .table-header { flex-direction:column; align-items:flex-start; }
            .search-input { width:100%; }
            .stats-grid { grid-template-columns:1fr 1fr; }
        }

        @stack('styles')
    </style>
</head>
<body>
    <!-- ═══ SIDEBAR ═══ -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <svg width="32" height="32" viewBox="0 0 120 120"><polygon points="60,10 95,30 95,70 60,90 25,70 25,30" fill="none" stroke="#2B9BFF" stroke-width="2.5" stroke-linejoin="round"/><text x="60" y="53" font-family="monospace" font-weight="700" font-size="24" fill="#2B9BFF" text-anchor="middle" dominant-baseline="middle">&gt;_</text></svg>
            <div><span>Hexa</span><span>Terminal</span></div>
        </a>

        <nav class="sidebar-nav">
            <div class="sidebar-label">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <div class="sidebar-label">Content</div>
            <a href="{{ route('admin.teams') }}" class="sidebar-link {{ request()->routeIs('admin.teams') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Teams
            </a>
            <a href="{{ route('admin.services') }}" class="sidebar-link {{ request()->routeIs('admin.services') ? 'active' : '' }}">
                <i class="fas fa-cogs"></i> Services
            </a>
            <a href="{{ route('admin.projects') }}" class="sidebar-link {{ request()->routeIs('admin.projects') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i> Projects
            </a>
            <a href="{{ route('admin.about') }}" class="sidebar-link {{ request()->routeIs('admin.about') ? 'active' : '' }}">
                <i class="fas fa-info-circle"></i> About Us
            </a>

            <div class="sidebar-label">Engagement</div>
            <a href="{{ route('admin.faq') }}" class="sidebar-link {{ request()->routeIs('admin.faq') ? 'active' : '' }}">
                <i class="fas fa-question-circle"></i> FAQ
            </a>
            <a href="{{ route('admin.reviews') }}" class="sidebar-link {{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
                <i class="fas fa-star"></i> Reviews
            </a>
            <a href="{{ route('admin.videos') }}" class="sidebar-link {{ request()->routeIs('admin.videos') ? 'active' : '' }}">
                <i class="fas fa-video"></i> Videos
            </a>
            <a href="{{ route('admin.contacts') }}" class="sidebar-link {{ request()->routeIs('admin.contacts') ? 'active' : '' }}">
                <i class="fas fa-envelope"></i> Messages <span class="badge" id="contacts-badge" style="display:none;">0</span>
            </a>

            <div class="sidebar-label">Website</div>
            <a href="{{ url('/') }}" target="_blank" class="sidebar-link">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-avatar" id="admin-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</div>
            <div>
                <div class="sidebar-user-name">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div class="sidebar-user-role">Administrator</div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin-left:auto;">
                @csrf
                <button type="submit" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:0.95rem;" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- ═══ HEADER ═══ -->
    <header class="admin-header">
        <div class="admin-header-left">
            <button class="hamburger-admin" onclick="document.getElementById('admin-sidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <h1>@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="admin-header-right">
            <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="header-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </header>

    <!-- Toast -->
    <div class="toast-container" id="toast-container"></div>

    <!-- ═══ MAIN ═══ -->
    <div class="admin-main">
        <div class="admin-content">
            @yield('content')
        </div>
    </div>

    <script>
        // ── Globals ──
        var API_BASE = '{{ url("/api") }}';
        var STORAGE_URL = '{{ url("/storage") }}/';
        var ADMIN_TOKEN = localStorage.getItem('admin_token');

        // ── HTML escaping — MUST wrap every user/visitor-supplied value that is
        // interpolated into an innerHTML string. Contact messages and reviews are
        // written by anonymous visitors; without escaping they can execute script
        // in this panel and steal the admin token from localStorage. ──
        function esc(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        function escUrl(value) {
            var s = esc(value);
            if (/^\s*(javascript|data|vbscript):/i.test(s)) return '';
            return s;
        }

        // Axios defaults — CSRF for web routes + Bearer token for API routes
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['Accept'] = 'application/json';
        if (ADMIN_TOKEN) {
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + ADMIN_TOKEN;
        }

        // Toast
        function showToast(msg, type) {
            type = type || 'success';
            var c = document.getElementById('toast-container');
            var icons = { success:'<i class="fas fa-check-circle" style="color:var(--green)"></i>', error:'<i class="fas fa-times-circle" style="color:var(--danger)"></i>' };
            var t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = (icons[type] || '') + '<span>' + esc(msg) + '</span>';
            c.appendChild(t);
            setTimeout(function() { t.style.animation = 'slideOut 0.3s ease forwards'; setTimeout(function() { t.remove(); }, 300); }, 3500);
        }

        // Image helper
        function getImageUrl(path) {
            if (!path) return '';
            if (path.startsWith('http')) return path;
            return STORAGE_URL + path;
        }

        // Handle 401 errors — redirect to login
        axios.interceptors.response.use(function(r) { return r; }, function(err) {
            if (err.response && err.response.status === 401) {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                window.location.href = '{{ route("admin.login") }}';
            }
            return Promise.reject(err);
        });

        // Close sidebar on link click (mobile)
        document.querySelectorAll('.sidebar-link').forEach(function(l) {
            l.addEventListener('click', function() {
                document.getElementById('admin-sidebar').classList.remove('open');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
