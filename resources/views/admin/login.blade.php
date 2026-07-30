<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - HexaTerminal</title>
    @vite(['resources/css/website.css'])
    <script src="{{ asset('vendor/axios.min.js') }}"></script>
    <style>
        :root {
            --primary:#2B9BFF; --accent:#a855f7; --danger:#ef4444;
            --dark-bg:#060918; --dark-bg-2:#0a0e23;
            --card-bg:rgba(17,22,56,0.65); --card-border:rgba(43,155,255,0.08);
            --text-primary:#f1f5f9; --text-secondary:#94a3b8; --text-muted:#64748b;
            --border-color:rgba(43,155,255,0.1); --radius-sm:0.5rem; --radius-md:0.75rem;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter','Cairo',sans-serif;background:var(--dark-bg);color:var(--text-primary);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;-webkit-font-smoothing:antialiased;}
        .login-container{width:100%;max-width:420px;}
        .login-brand{text-align:center;margin-bottom:2.5rem;}
        .login-brand svg{margin-bottom:1rem;}
        .login-brand h1{font-size:1.5rem;font-weight:800;}
        .login-brand h1 span{color:var(--primary);}
        .login-brand p{color:var(--text-muted);font-size:0.88rem;margin-top:0.5rem;}
        .login-card{background:var(--dark-bg-2);border:1px solid var(--card-border);border-radius:var(--radius-md);padding:2rem;}
        .form-group{margin-bottom:1.25rem;}
        .form-label{display:block;margin-bottom:0.35rem;font-size:0.82rem;font-weight:600;color:var(--text-primary);}
        .form-input{width:100%;padding:0.7rem 1rem;background:rgba(6,9,24,0.6);border:1px solid var(--border-color);border-radius:var(--radius-sm);color:var(--text-primary);font-size:0.9rem;outline:none;transition:all 0.2s;}
        .form-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(43,155,255,0.1);}
        .form-input::placeholder{color:var(--text-muted);}
        .btn-login{width:100%;padding:0.75rem;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-size:0.92rem;font-weight:700;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:0.5rem;}
        .btn-login:hover{background:#1a7fe0;}
        .btn-login:disabled{opacity:0.6;cursor:not-allowed;}
        .login-error{background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius-sm);padding:0.65rem 1rem;color:var(--danger);font-size:0.82rem;margin-bottom:1rem;display:none;}
        .login-footer{text-align:center;margin-top:1.5rem;}
        .login-footer a{color:var(--text-muted);text-decoration:none;font-size:0.82rem;transition:color 0.2s;}
        .login-footer a:hover{color:var(--primary);}
        .spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin 0.6s linear infinite;}
        @keyframes spin{to{transform:rotate(360deg);}}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-brand">
            <svg width="64" height="64" viewBox="0 0 120 120"><polygon points="60,10 95,30 95,70 60,90 25,70 25,30" fill="none" stroke="#2B9BFF" stroke-width="2.5" stroke-linejoin="round"/><polygon points="60,22 83,35 83,65 60,78 37,65 37,35" fill="rgba(43,155,255,0.06)" stroke="#2B9BFF" stroke-width="1" opacity="0.6"/><text x="60" y="53" font-family="monospace" font-weight="700" font-size="24" fill="#2B9BFF" text-anchor="middle" dominant-baseline="middle">&gt;_</text></svg>
            <h1>Hexa<span>Terminal</span></h1>
            <p>Admin Panel</p>
        </div>

        <div class="login-card">
            <div class="login-error" id="login-error"></div>

            <form id="login-form" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" id="email" placeholder="admin@example.com" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-input" id="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-login" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            <a href="{{ url('/') }}"><i class="fas fa-arrow-left" style="margin-right:0.3rem;font-size:0.7rem;"></i> Back to Website</a>
        </div>
    </div>

    <script>
        // CSRF token for web route
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function handleLogin(e) {
            e.preventDefault();
            var btn = document.getElementById('login-btn');
            var errEl = document.getElementById('login-error');
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Signing in...';
            errEl.style.display = 'none';

            // POST to web route — creates session + returns Sanctum token
            axios.post('{{ route("admin.authenticate") }}', { email: email, password: password })
                .then(function(res) {
                    var data = res.data;
                    // Store token for Axios API calls in the dashboard
                    localStorage.setItem('admin_token', data.access_token);
                    localStorage.setItem('admin_user', JSON.stringify(data.user));
                    // Redirect — session is now active, middleware will allow access
                    window.location.href = '{{ route("admin.dashboard") }}';
                })
                .catch(function(err) {
                    var msg = 'Invalid email or password.';
                    if (err.response && err.response.data && err.response.data.message) {
                        msg = err.response.data.message;
                    }
                    errEl.textContent = msg;
                    errEl.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
                });
        }
    </script>
</body>
</html>
