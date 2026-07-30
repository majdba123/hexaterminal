<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HexaTerminal - Professional Software Development Company specializing in innovative digital solutions">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#060918">

    {{-- Prevent flash: apply saved theme + language before any CSS paints --}}
    <script>!function(){var t=localStorage.getItem('ht-theme')||'dark';document.documentElement.setAttribute('data-theme',t);var m=document.querySelector('meta[name="theme-color"]');if(m)m.content=t==='light'?'#F5F3EF':'#070A18';var l=localStorage.getItem('ht-lang')||'en';document.documentElement.lang=l;document.documentElement.dir=l==='ar'?'rtl':'ltr'}()</script>

    <title>@yield('title', 'HexaTerminal - Software Development Company')</title>

    {{-- ═══ ALL ASSETS LOCAL — ZERO CDN REQUESTS ═══ --}}
    {{-- CSS: Fonts + Font Awesome + AOS styles bundled by Vite --}}
    @vite(['resources/css/website.css'])

    {{-- JS: Loaded as regular scripts (not modules) so they're available to inline scripts --}}
    <script src="{{ asset('vendor/axios.min.js') }}"></script>
    <script src="{{ asset('vendor/aos.js') }}"></script>

    <style>
        /* ═══════════════════════════════════════════
           DESIGN SYSTEM — HexaTerminal Pro
           Critical CSS inlined for instant render
           ═══════════════════════════════════════════ */
        :root {
            /* ── Royal Luxury Palette ── */
            --primary: #4A8FE7;
            --primary-dark: #3A75C4;
            --primary-light: #6AABE8;
            --primary-glow: rgba(74, 143, 231, 0.25);
            --secondary: #7B68EE;
            --accent: #D4AF37;
            --accent-2: #C9A84C;
            --green: #00C9A7;
            --green-glow: rgba(0, 201, 167, 0.2);
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gold: #D4AF37;
            --gold-light: #E8C547;
            --gold-glow: rgba(212, 175, 55, 0.15);

            --dark-bg: #070A18;
            --dark-bg-2: #0B0F22;
            --dark-bg-3: #0F132C;
            --card-bg: rgba(15, 19, 44, 0.7);
            --card-bg-solid: #12163A;
            --card-bg-hover: #181E4A;
            --card-border: rgba(212, 175, 55, 0.06);

            --text-primary: #E8E6E3;
            --text-secondary: #9CA3B0;
            --text-muted: #636B7F;
            --border-color: rgba(255, 255, 255, 0.06);
            --border-hover: rgba(212, 175, 55, 0.2);

            --glass: rgba(15, 19, 44, 0.65);
            --glass-border: rgba(212, 175, 55, 0.04);

            --radius-xs: 0.375rem;
            --radius-sm: 0.5rem;
            --radius-md: 0.875rem;
            --radius-lg: 1.25rem;
            --radius-xl: 1.75rem;
            --radius-2xl: 2.5rem;

            --shadow-sm: 0 2px 8px rgba(0,0,0,0.18);
            --shadow-md: 0 8px 30px rgba(0,0,0,0.22);
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.32);
            --shadow-glow: 0 0 40px var(--primary-glow);
            --shadow-card: 0 4px 24px rgba(0,0,0,0.18), 0 0 0 1px rgba(212,175,55,0.04);

            --transition: all 0.3s ease;
            --transition-fast: all 0.2s ease;
            --transition-slow: all 0.5s ease;
        }

        /* ═══════════════════════════════════════════
           LIGHT THEME
           ═══════════════════════════════════════════ */
        /* ── Warm Cream Light Theme ── */
        [data-theme="light"] {
            --primary: #2D5FA8;
            --primary-dark: #234B87;
            --primary-light: #4A7FC5;
            --primary-glow: rgba(45, 95, 168, 0.1);
            --secondary: #5B4FBE;
            --accent: #B8941F;
            --accent-2: #A68419;
            --green: #059669;
            --green-glow: rgba(5, 150, 105, 0.08);
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
            --gold: #B8941F;
            --gold-light: #D4AF37;
            --gold-glow: rgba(184, 148, 31, 0.08);

            --dark-bg: #F5F3EF;
            --dark-bg-2: #FDFCF9;
            --dark-bg-3: #EDE9E1;
            --card-bg: rgba(255, 255, 252, 0.95);
            --card-bg-solid: #FFFFFF;
            --card-bg-hover: #F5F3EF;
            --card-border: rgba(0, 0, 0, 0.06);

            --text-primary: #1A1A2E;
            --text-secondary: #525266;
            --text-muted: #8A8A9A;
            --border-color: rgba(0, 0, 0, 0.08);
            --border-hover: rgba(45, 95, 168, 0.2);

            --glass: rgba(255, 255, 252, 0.9);
            --glass-border: rgba(0, 0, 0, 0.04);

            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.06);
            --shadow-glow: 0 0 30px rgba(45,95,168,0.04);
            --shadow-card: 0 1px 8px rgba(0,0,0,0.04), 0 0 0 1px rgba(0,0,0,0.03);
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

        body {
            font-family: 'Inter', 'Cairo', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.7;
            color: var(--text-primary);
            background: var(--dark-bg);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ─── RESPONSIVE IMAGES ─── */
        img { max-width: 100%; height: auto; }
        video, iframe { max-width: 100%; }

        /* ─── SELECTION ─── */
        ::selection { background: rgba(43, 155, 255, 0.3); color: #fff; }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--dark-bg); }
        ::-webkit-scrollbar-thumb { background: linear-gradient(180deg, var(--primary), var(--accent)); border-radius: 3px; }

        /* ═══════════════════════════════════════════
           HEADER
           ═══════════════════════════════════════════ */
        .site-header {
            background: rgba(6, 9, 24, 0.95);
            border-bottom: 1px solid rgba(255,255,255,0.04);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            padding: 0.85rem 0;
            transition: padding 0.3s ease, background 0.3s ease;
        }
        .site-header.scrolled {
            padding: 0.5rem 0;
            background: rgba(6, 9, 24, 0.98);
            box-shadow: 0 2px 16px rgba(0,0,0,0.4);
        }
        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        /* Logo */
        .logo-container {
            display: flex; align-items: center; gap: 0.75rem;
            text-decoration: none; transition: var(--transition-fast);
        }
        .logo-container:hover { transform: scale(1.02); }
        .logo-hex { width: 48px; height: 48px; position: relative; }
        .logo-text { display: flex; flex-direction: column; line-height: 1.1; }
        .logo-text-part1 { font-size: 1.2rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em; }
        .logo-text-part2 {
            font-size: 1.2rem; font-weight: 800; letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--gold), var(--primary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        /* Navigation */
        .site-nav { margin-left: auto; }
        .nav-menu { display: flex; gap: 0.15rem; list-style: none; align-items: center; }
        .nav-link {
            color: var(--text-secondary); text-decoration: none; font-weight: 500; font-size: 0.88rem;
            transition: var(--transition-fast); padding: 0.55rem 1rem; border-radius: var(--radius-sm);
            position: relative; letter-spacing: 0.01em;
        }
        .nav-link::after {
            content: ''; position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%);
            width: 0; height: 2px; background: var(--primary); border-radius: 1px;
            transition: width 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--text-primary); background: rgba(43, 155, 255, 0.06);
        }
        .nav-link:hover::after, .nav-link.active::after { width: 60%; }

        /* Hamburger */
        .hamburger {
            display: none; background: none; border: 1px solid var(--border-color);
            cursor: pointer; padding: 0.6rem; flex-direction: column; gap: 5px;
            border-radius: var(--radius-sm); transition: var(--transition-fast);
        }
        .hamburger:hover { border-color: var(--primary); background: rgba(43,155,255,0.06); }
        .hamburger span {
            display: block; width: 22px; height: 2px; background: var(--text-primary);
            transition: var(--transition); border-radius: 2px;
        }
        .hamburger.active { border-color: var(--primary); }
        .hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
        .hamburger.active span:nth-child(2) { opacity: 0; }
        .hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

        /* ═══ MAIN ═══ */
        main { padding-top: 76px; min-height: calc(100vh - 200px); }

        /* ═══ SECTIONS (perf: content-visibility for off-screen) ═══ */
        .section { padding: 5.5rem 0; position: relative; content-visibility: auto; contain-intrinsic-size: auto 800px; }
        .section-header { text-align: center; margin-bottom: 3rem; }
        .section-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.45rem 1.25rem;
            background: linear-gradient(135deg, rgba(212,175,55,0.08), rgba(74,143,231,0.06));
            border: 1px solid rgba(212,175,55,0.18);
            border-radius: 999px;
            color: var(--gold); font-size: 0.78rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 2px; margin-bottom: 1.25rem;
        }
        .section-badge i { font-size: 0.7rem; color: var(--gold); }
        .section-title {
            font-size: clamp(2.2rem, 4vw, 3.2rem); font-weight: 800; margin-bottom: 1rem;
            color: var(--text-primary); line-height: 1.2; letter-spacing: -0.03em;
        }
        .section-subtitle {
            font-size: 1.05rem; color: var(--text-secondary); max-width: 580px;
            margin: 0 auto; line-height: 1.75;
        }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        /* ═══ CARDS (perf-optimized) ═══ */
        .card {
            background: var(--card-bg-solid);
            border: 1px solid var(--card-border); border-radius: var(--radius-lg);
            transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden; position: relative;
        }
        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(212,175,55,0.15);
            box-shadow: 0 12px 32px rgba(0,0,0,0.18);
        }

        /* ═══ GRID ═══ */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
        .grid-masonry { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .grid-masonry > :first-child { grid-row: span 2; }

        /* ═══ BUTTONS ═══ */
        .btn {
            display: inline-flex; align-items: center; gap: 0.6rem;
            padding: 0.85rem 2rem; border-radius: var(--radius-md); font-weight: 600;
            font-size: 0.92rem; text-decoration: none; border: none; cursor: pointer;
            transition: var(--transition); position: relative; overflow: hidden;
            letter-spacing: 0.01em;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: 0 4px 20px rgba(74,143,231,0.25), inset 0 1px 0 rgba(255,255,255,0.12);
        }
        .btn-primary::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            opacity: 0; transition: opacity 0.4s;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(74,143,231,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
        }
        /* Gold luxury CTA */
        .btn-gold {
            background: linear-gradient(135deg, #D4AF37, #C9A84C, #D4AF37);
            background-size: 200% 200%;
            color: #070A18; font-weight: 700;
            box-shadow: 0 4px 20px rgba(212,175,55,0.3), inset 0 1px 0 rgba(255,255,255,0.2);
            animation: goldShimmer 4s ease infinite;
        }
        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 35px rgba(212,175,55,0.4), inset 0 1px 0 rgba(255,255,255,0.25);
        }
        @keyframes goldShimmer { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        .btn-primary:hover::before { opacity: 1; }
        .btn-primary span, .btn-primary svg { position: relative; z-index: 1; }

        .btn-outline {
            background: transparent; color: var(--primary);
            border: 1.5px solid rgba(43,155,255,0.25);
        }
        .btn-outline:hover {
            background: rgba(43,155,255,0.08);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .btn-green {
            background: linear-gradient(135deg, var(--green), #00b874);
            color: #000; font-weight: 700;
            box-shadow: 0 4px 20px var(--green-glow);
        }
        .btn-green:hover { transform: translateY(-3px); box-shadow: 0 8px 30px var(--green-glow); }

        /* ═══ SKELETON ═══ */
        .skeleton {
            background: var(--card-bg-solid);
            border-radius: var(--radius-sm);
            opacity: 0.6;
            animation: skeleton-pulse 1.5s ease-in-out infinite alternate;
        }
        @keyframes skeleton-pulse { from { opacity: 0.4; } to { opacity: 0.7; } }
        .skeleton-card { height: 300px; border-radius: var(--radius-lg); border: 1px solid var(--card-border); }
        .skeleton-text { height: 1rem; margin-bottom: 0.75rem; }
        .skeleton-text.short { width: 60%; }

        /* ═══ TOAST ═══ */
        .toast-container {
            position: fixed; top: 90px; right: 2rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.75rem;
        }
        .toast {
            padding: 1rem 1.5rem; border-radius: var(--radius-md);
            background: var(--card-bg-solid); border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg); color: var(--text-primary);
            display: flex; align-items: center; gap: 0.75rem; min-width: 320px;
            animation: slideIn 0.4s ease; font-size: 0.9rem;
        }
        .toast.success { border-left: 4px solid var(--green); }
        .toast.error { border-left: 4px solid var(--danger); }
        .toast.info { border-left: 4px solid var(--primary); }
        @keyframes slideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(120%); opacity: 0; } }

        /* ═══ FORM ═══ */
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; margin-bottom: 0.5rem; color: var(--text-primary); font-weight: 600; font-size: 0.88rem; }
        .form-input {
            width: 100%; padding: 0.9rem 1.15rem; background: rgba(6,9,24,0.8);
            border: 1px solid rgba(43,155,255,0.1); border-radius: var(--radius-md);
            color: var(--text-primary); font-size: 0.92rem; font-family: inherit;
            transition: var(--transition); outline: none;
        }
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(43,155,255,0.1), 0 0 20px rgba(43,155,255,0.05);
        }
        .form-input::placeholder { color: var(--text-muted); }

        /* ═══ FOOTER ═══ */
        .site-footer {
            background: var(--dark-bg-2); border-top: 1px solid rgba(255,255,255,0.04);
            position: relative; overflow: hidden;
        }
        .site-footer::before {
            content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%);
            width: 600px; height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }
        .footer-main { padding: 5rem 2rem 3rem; }
        .footer-grid {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr;
            gap: 3rem; max-width: 1400px; margin: 0 auto;
        }
        .footer-title {
            font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem;
            color: var(--text-primary); letter-spacing: 0.02em;
            position: relative; padding-bottom: 0.75rem;
        }
        .footer-title::after {
            content: ''; position: absolute; bottom: 0; left: 0;
            width: 30px; height: 2px; background: var(--primary); border-radius: 1px;
        }
        .footer-link {
            display: flex; align-items: center; gap: 0.5rem;
            color: var(--text-secondary); text-decoration: none;
            padding: 0.4rem 0; font-size: 0.88rem; transition: var(--transition);
        }
        .footer-link:hover { color: var(--primary); transform: translateX(6px); }
        .footer-link i { font-size: 0.65rem; transition: var(--transition); }
        .footer-social { display: flex; gap: 0.75rem; margin-top: 1.25rem; }
        .footer-social a {
            width: 42px; height: 42px; border-radius: var(--radius-sm);
            background: rgba(43,155,255,0.06); border: 1px solid rgba(43,155,255,0.1);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); text-decoration: none;
            transition: var(--transition); font-size: 0.95rem;
        }
        .footer-social a:hover {
            background: var(--primary); color: #fff; border-color: var(--primary);
            transform: translateY(-3px); box-shadow: 0 8px 20px rgba(43,155,255,0.25);
        }
        .footer-newsletter-form { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .footer-newsletter-form input {
            flex: 1; padding: 0.75rem 1rem; background: rgba(6,9,24,0.6);
            border: 1px solid rgba(43,155,255,0.1); border-radius: var(--radius-sm);
            color: var(--text-primary); font-size: 0.88rem; outline: none;
            transition: var(--transition);
        }
        .footer-newsletter-form input:focus { border-color: var(--primary); }
        .footer-newsletter-form button {
            padding: 0.75rem 1.25rem; background: var(--primary);
            border: none; border-radius: var(--radius-sm); color: #fff;
            cursor: pointer; transition: var(--transition); font-size: 0.88rem;
        }
        .footer-newsletter-form button:hover { background: var(--primary-dark); }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.04);
            padding: 1.5rem 2rem; max-width: 1400px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
            color: var(--text-muted); font-size: 0.82rem;
        }

        /* ═══ UTILITIES ═══ */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .text-gradient-gold {
            background: linear-gradient(135deg, #D4AF37 0%, #E8C547 40%, #C9A84C 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .text-gradient-green {
            background: linear-gradient(135deg, var(--green) 0%, #22c55e 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .glow-dot {
            position: absolute; border-radius: 50%;
            filter: blur(80px); pointer-events: none; opacity: 0.06;
            will-change: auto;
        }
        .glow-line {
            position: absolute; pointer-events: none;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            height: 1px; opacity: 0.15;
        }
        .divider {
            width: 100%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(43,155,255,0.15), transparent);
            margin: 2rem 0;
        }

        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes pulse-glow { 0%, 100% { opacity: 0.08; } 50% { opacity: 0.15; } }
        @keyframes rotate-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ═══════════════════════════════════════════
           CRITICAL: AOS MOBILE FIX
           When AOS is disabled on mobile, its CSS still
           hides [data-aos] elements (opacity:0). This rule
           ensures all elements are always visible on phones.
           ═══════════════════════════════════════════ */
        @media (max-width: 767px) {
            [data-aos] {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }

        /* ═══ RESPONSIVE ═══ */
        @media (max-width: 1200px) {
            .container { max-width: 100%; padding: 0 1.5rem; }
        }
        @media (max-width: 1024px) {
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-masonry { grid-template-columns: repeat(2, 1fr); }
            .grid-masonry > :first-child { grid-row: span 1; }
            .section { padding: 4rem 0; }
        }
        @media (max-width: 768px) {
            .hamburger { display: flex; }
            .site-nav {
                position: fixed; top: 76px; left: 0; right: 0;
                background: rgba(6,9,24,0.99);
                padding: 1rem 1.5rem 1.5rem;
                transform: translateY(-120%);
                transition: transform 0.3s ease;
                border-bottom: 1px solid var(--border-color);
                box-shadow: 0 12px 24px rgba(0,0,0,0.3);
                z-index: 999;
                max-height: calc(100vh - 76px);
                overflow-y: auto;
            }
            .site-nav.open { transform: translateY(0); }
            .nav-menu { flex-direction: column; gap: 0.15rem; }
            .nav-link {
                width: 100%; padding: 0.85rem 1.15rem;
                border-radius: var(--radius-sm);
                font-size: 0.95rem;
                min-height: 44px; /* Touch target */
                display: flex; align-items: center;
            }
            .nav-link::after { display: none; }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .footer-main { padding: 3rem 1.25rem 2rem; }
            .footer-bottom { flex-direction: column; gap: 0.75rem; text-align: center; padding: 1.25rem; }
            .section { padding: 3.5rem 0; }
            .section-header { margin-bottom: 2rem; }
            .section-title { font-size: clamp(1.5rem, 6vw, 2.2rem); }
            .section-subtitle { font-size: 0.92rem; }
            .grid-2 { grid-template-columns: 1fr; gap: 1.25rem; }
            .grid-3 { grid-template-columns: 1fr; gap: 1.25rem; }
            .grid-4 { grid-template-columns: 1fr; gap: 1.25rem; }
            .grid-masonry { grid-template-columns: 1fr; gap: 1.25rem; }
            .container { padding: 0 1rem; }
            .header-inner { padding: 0 1rem; }
            .header-right { gap: 0.4rem; }
            .theme-toggle { width: 38px; height: 38px; font-size: 0.95rem; }
            .lang-toggle { height: 38px; padding: 0 0.6rem; font-size: 0.72rem; }
            .btn { padding: 0.75rem 1.5rem; font-size: 0.88rem; min-height: 44px; }
            .btn-gold { padding: 0.75rem 1.5rem; }
            .form-input { padding: 0.85rem 1rem; font-size: 0.9rem; min-height: 44px; }
            .toast { min-width: auto; max-width: calc(100vw - 2rem); font-size: 0.85rem; }
            .toast-container { right: 1rem; left: 1rem; }
            main { padding-top: 68px; }
            .glow-dot { display: none; }
            .logo-hex { width: 38px; height: 38px; }
            .logo-text-part1, .logo-text-part2 { font-size: 1rem; }
        }
        @media (max-width: 480px) {
            .section { padding: 2.5rem 0; }
            .section-title { font-size: 1.5rem; }
            .section-subtitle { font-size: 0.85rem; }
            .container { padding: 0 0.85rem; }
            .section-badge { font-size: 0.7rem; padding: 0.35rem 0.85rem; letter-spacing: 1.5px; }
        }

        /* ═══ PERFORMANCE ═══ */
        .site-header { transform: translateZ(0); }
        /* Reduce motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }

        .btn-nav-cta {
            background: linear-gradient(135deg, rgba(43,155,255,0.12), rgba(168,85,247,0.08)) !important;
            border: 1px solid rgba(43,155,255,0.2) !important;
            color: var(--primary) !important; font-weight: 600 !important;
        }
        .btn-nav-cta:hover {
            background: linear-gradient(135deg, rgba(43,155,255,0.2), rgba(168,85,247,0.12)) !important;
            border-color: var(--primary) !important;
        }

        /* ═══════════════════════════════════════════
           THEME TOGGLE BUTTON
           ═══════════════════════════════════════════ */
        .theme-toggle {
            width: 42px; height: 42px; border-radius: 50%;
            border: 1px solid var(--border-color); background: var(--card-bg);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); font-size: 1.05rem;
            transition: all 0.35s cubic-bezier(0.25,0.46,0.45,0.94);
            position: relative; overflow: hidden; flex-shrink: 0;
        }
        .theme-toggle:hover {
            border-color: var(--primary); color: var(--primary);
            background: rgba(43,155,255,0.08); transform: rotate(20deg) scale(1.05);
            box-shadow: 0 0 20px var(--primary-glow);
        }
        .theme-icon-sun, .theme-icon-moon {
            position: absolute;
            transition: transform 0.5s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s ease;
        }
        [data-theme="dark"] .theme-icon-sun  { opacity: 1; transform: rotate(0) scale(1); }
        [data-theme="dark"] .theme-icon-moon { opacity: 0; transform: rotate(90deg) scale(0); }
        [data-theme="light"] .theme-icon-sun  { opacity: 0; transform: rotate(-90deg) scale(0); }
        [data-theme="light"] .theme-icon-moon { opacity: 1; transform: rotate(0) scale(1); }

        /* ═══════════════════════════════════════════
           LANGUAGE SWITCHER
           ═══════════════════════════════════════════ */
        .lang-toggle {
            height: 42px; border-radius: 999px; padding: 0 0.75rem;
            border: 1px solid var(--border-color); background: var(--card-bg);
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem;
            color: var(--text-secondary); font-size: 0.78rem; font-weight: 600;
            transition: all 0.35s cubic-bezier(0.25,0.46,0.45,0.94);
            flex-shrink: 0; letter-spacing: 0.5px; font-family: inherit;
        }
        .lang-toggle:hover {
            border-color: var(--gold); color: var(--gold);
            background: rgba(212,175,55,0.06);
            box-shadow: 0 0 20px var(--gold-glow);
        }
        .lang-toggle i { font-size: 0.85rem; }

        /* ═══════════════════════════════════════════
           RTL SUPPORT
           ═══════════════════════════════════════════ */
        [dir="rtl"] { font-family: 'Cairo', 'Inter', -apple-system, sans-serif; }
        [dir="rtl"] .site-nav { margin-left: 0; margin-right: auto; }
        [dir="rtl"] .nav-link i { margin-right: 0; margin-left: 0.3rem; }
        [dir="rtl"] .footer-link:hover { transform: translateX(-6px); }
        [dir="rtl"] .footer-link i { transform: rotate(180deg); }
        [dir="rtl"] .footer-title::after { left: auto; right: 0; }
        [dir="rtl"] .process-step { direction: rtl; }
        [dir="rtl"] .btn i { margin-right: 0; margin-left: 0.5rem; }
        [dir="rtl"] .section-badge { direction: rtl; }
        [dir="rtl"] .contact-info-item { direction: rtl; }
        [dir="rtl"] .member-info-item { direction: rtl; }
        [dir="rtl"] .feature-item { direction: rtl; }

        /* ═══════════════════════════════════════════
           LUXURY BACKGROUND TEXTURES (perf-optimized)
           ═══════════════════════════════════════════ */
        body::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 100vh;
            z-index: -1; pointer-events: none;
            background:
                radial-gradient(ellipse 80% 50% at 20% 40%, rgba(74,143,231,0.03), transparent),
                radial-gradient(ellipse 60% 60% at 80% 20%, rgba(212,175,55,0.02), transparent);
        }
        [data-theme="light"] body::before {
            background:
                radial-gradient(ellipse 80% 50% at 20% 40%, rgba(45,95,168,0.02), transparent),
                radial-gradient(ellipse 60% 60% at 80% 20%, rgba(184,148,31,0.015), transparent);
        }

        /* ═══════════════════════════════════════════
           LIGHT THEME — Element Overrides
           ═══════════════════════════════════════════ */
        [data-theme="light"] ::selection { background: rgba(45,95,168,0.15); color: #1A1A2E; }
        [data-theme="light"] ::-webkit-scrollbar-track { background: #EDE9E1; }
        [data-theme="light"] ::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #2D5FA8, #B8941F); }

        /* Header */
        [data-theme="light"] .site-header { background: rgba(253,252,249,0.97); border-bottom-color: rgba(0,0,0,0.05); }
        [data-theme="light"] .site-header.scrolled { background: rgba(253,252,249,0.99); box-shadow: 0 1px 8px rgba(0,0,0,0.04); }
        [data-theme="light"] .site-nav { background: rgba(253,252,249,0.99); border-bottom-color: rgba(0,0,0,0.05); box-shadow: 0 8px 20px rgba(0,0,0,0.03); }
        [data-theme="light"] .nav-link { color: var(--text-secondary); }
        [data-theme="light"] .nav-link:hover, [data-theme="light"] .nav-link.active { color: #1A1A2E; background: rgba(45,95,168,0.05); }
        [data-theme="light"] .hamburger { border-color: rgba(0,0,0,0.1); }
        [data-theme="light"] .hamburger:hover { border-color: var(--primary); background: rgba(45,95,168,0.03); }
        [data-theme="light"] .hamburger span { background: #1A1A2E; }

        /* Cards */
        [data-theme="light"] .card { box-shadow: var(--shadow-card); background: var(--card-bg); }
        [data-theme="light"] .card:hover { border-color: rgba(184,148,31,0.12); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }

        /* Skeleton */
        [data-theme="light"] .skeleton { background: linear-gradient(90deg, #EDE9E1 25%, #FDFCF9 50%, #EDE9E1 75%); background-size: 200% 100%; }

        /* Glow effects — hidden in light mode */
        [data-theme="light"] .glow-dot { opacity: 0.02 !important; }
        [data-theme="light"] .glow-line { opacity: 0.06 !important; }

        /* Forms */
        [data-theme="light"] .form-input { background: #FDFCF9; border-color: rgba(0,0,0,0.1); }
        [data-theme="light"] .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(45,95,168,0.06), 0 0 15px rgba(45,95,168,0.03); }
        [data-theme="light"] .form-input::placeholder { color: #8A8A9A; }

        /* Footer */
        [data-theme="light"] .site-footer { background: #EDE9E1; border-top-color: rgba(0,0,0,0.05); }
        [data-theme="light"] .site-footer::before { background: linear-gradient(90deg, transparent, var(--gold), transparent); opacity: 0.25; }
        [data-theme="light"] .footer-social a { background: rgba(45,95,168,0.04); border-color: rgba(45,95,168,0.08); }
        [data-theme="light"] .footer-social a:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        [data-theme="light"] .footer-newsletter-form input { background: #FDFCF9; border-color: rgba(0,0,0,0.08); }
        [data-theme="light"] .footer-newsletter-form button { background: var(--primary); }
        [data-theme="light"] .footer-newsletter-form button:hover { background: var(--primary-dark); }

        /* Toast */
        [data-theme="light"] .toast { background: #FDFCF9; border-color: rgba(0,0,0,0.06); box-shadow: 0 4px 20px rgba(0,0,0,0.08); }

        /* Buttons */
        [data-theme="light"] .btn-primary { box-shadow: 0 4px 15px rgba(45,95,168,0.18); }
        [data-theme="light"] .btn-primary:hover { box-shadow: 0 6px 25px rgba(45,95,168,0.22); }
        [data-theme="light"] .btn-outline { border-color: rgba(45,95,168,0.2); color: var(--primary); }
        [data-theme="light"] .btn-outline:hover { background: rgba(45,95,168,0.04); border-color: var(--primary); }
        [data-theme="light"] .btn-green { color: #fff; }
        [data-theme="light"] .btn-gold { color: #1A1A2E; }
        [data-theme="light"] .btn-nav-cta { background: linear-gradient(135deg, rgba(45,95,168,0.05), rgba(91,79,190,0.03)) !important; border-color: rgba(45,95,168,0.12) !important; }
        [data-theme="light"] .lang-toggle { background: rgba(255,255,252,0.8); }
        [data-theme="light"] .lang-toggle:hover { background: rgba(184,148,31,0.04); border-color: var(--gold); color: var(--gold); }

        /* Misc */
        [data-theme="light"] .section-badge { background: linear-gradient(135deg, rgba(184,148,31,0.06), rgba(45,95,168,0.04)); border-color: rgba(184,148,31,0.12); color: var(--gold); }
        [data-theme="light"] .section-badge i { color: var(--gold); }
        [data-theme="light"] .divider { background: linear-gradient(90deg, transparent, rgba(184,148,31,0.1), transparent); }
        [data-theme="light"] .text-gradient { background: linear-gradient(135deg, #2D5FA8 0%, #5B4FBE 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        [data-theme="light"] .text-gradient-gold { background: linear-gradient(135deg, #B8941F 0%, #A68419 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

    @stack('styles')
    </style>
</head>
<body>
    <!-- ═══ HEADER ═══ -->
    <header class="site-header" id="site-header">
        <div class="header-inner">
            <a href="{{ url('/') }}" class="logo-container">
                <svg class="logo-hex" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#D4AF37"/>
                            <stop offset="100%" style="stop-color:#4A8FE7"/>
                        </linearGradient>
                    </defs>
                    <polygon points="60,10 95,30 95,70 60,90 25,70 25,30" fill="none" stroke="url(#logoGrad)" stroke-width="2.5" stroke-linejoin="round"/>
                    <polygon points="60,22 83,35 83,65 60,78 37,65 37,35" fill="rgba(212,175,55,0.05)" stroke="url(#logoGrad)" stroke-width="1" stroke-linejoin="round" opacity="0.6"/>
                    <text x="60" y="53" font-family="'Courier New', monospace" font-weight="700" font-size="24" fill="url(#logoGrad)" text-anchor="middle" dominant-baseline="middle">&gt;_</text>
                </svg>
                <div class="logo-text">
                    <span class="logo-text-part1">Hexa</span>
                    <span class="logo-text-part2">Terminal</span>
                </div>
            </a>

            <nav class="site-nav" id="main-nav">
                <ul class="nav-menu">
                    <li><a href="{{ url('/') }}#home" class="nav-link"><i class="fas fa-home" style="font-size:0.7rem;margin-right:0.3rem;opacity:0.6;"></i> <span data-i18n="nav_home">Home</span></a></li>
                    <li><a href="{{ url('/') }}#team" class="nav-link"><i class="fas fa-users" style="font-size:0.7rem;margin-right:0.3rem;opacity:0.6;"></i> <span data-i18n="nav_team">Team</span></a></li>
                    <li><a href="{{ url('/') }}#services" class="nav-link"><i class="fas fa-cogs" style="font-size:0.7rem;margin-right:0.3rem;opacity:0.6;"></i> <span data-i18n="nav_services">Services</span></a></li>
                    <li><a href="{{ url('/') }}#projects" class="nav-link"><i class="fas fa-briefcase" style="font-size:0.7rem;margin-right:0.3rem;opacity:0.6;"></i> <span data-i18n="nav_projects">Projects</span></a></li>
                    <li><a href="{{ url('/') }}#about" class="nav-link"><i class="fas fa-info-circle" style="font-size:0.7rem;margin-right:0.3rem;opacity:0.6;"></i> <span data-i18n="nav_about">About</span></a></li>
                    <li><a href="{{ url('/') }}#reviews" class="nav-link"><i class="fas fa-star" style="font-size:0.7rem;margin-right:0.3rem;opacity:0.6;"></i> <span data-i18n="nav_reviews">Reviews</span></a></li>
                    <li><a href="{{ url('/') }}#faq" class="nav-link"><i class="fas fa-question-circle" style="font-size:0.7rem;margin-right:0.3rem;opacity:0.6;"></i> <span data-i18n="nav_faq">FAQ</span></a></li>
                    <li><a href="{{ url('/') }}#contact" class="nav-link btn-nav-cta"><i class="fas fa-envelope" style="font-size:0.7rem;margin-right:0.3rem;"></i> <span data-i18n="nav_contact">Contact</span></a></li>
                </ul>
            </nav>

            <div class="header-right">
                <button class="lang-toggle" id="lang-toggle" onclick="toggleLang()" aria-label="Switch language" title="العربية / English">
                    <i class="fas fa-globe"></i>
                    <span id="lang-label">AR</span>
                </button>
                <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" aria-label="Toggle light/dark mode" title="Toggle theme">
                    <i class="fas fa-sun theme-icon-sun"></i>
                    <i class="fas fa-moon theme-icon-moon"></i>
                </button>
                <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Toast container -->
    <div class="toast-container" id="toast-container"></div>

    <main>
        @yield('content')
    </main>

    <!-- ═══ FOOTER ═══ -->
    <footer class="site-footer">
        <!-- Luxury gold line accent at top -->
        <div class="footer-main">
            <div class="footer-grid">
                <!-- Brand -->
                <div>
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem;">
                        <svg width="40" height="40" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                            <defs><linearGradient id="footerGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#D4AF37"/><stop offset="100%" style="stop-color:#4A8FE7"/></linearGradient></defs>
                            <polygon points="60,10 95,30 95,70 60,90 25,70 25,30" fill="none" stroke="url(#footerGrad)" stroke-width="2.5" stroke-linejoin="round"/>
                            <text x="60" y="53" font-family="'Courier New', monospace" font-weight="700" font-size="24" fill="url(#footerGrad)" text-anchor="middle" dominant-baseline="middle">&gt;_</text>
                        </svg>
                        <div>
                            <span style="font-size:1.1rem;font-weight:800;color:var(--text-primary);">Hexa</span><span style="font-size:1.1rem;font-weight:800;color:var(--gold);">Terminal</span>
                        </div>
                    </div>
                    <p style="color:var(--text-secondary);font-size:0.88rem;line-height:1.9;max-width:350px;margin-bottom:1.5rem;" data-i18n="footer_desc">
                        Professional software development company delivering innovative digital solutions with cutting-edge technologies and creative design.
                    </p>
                    <div class="footer-social">
                        <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="footer-title" data-i18n="quick_links">Quick Links</h4>
                    <a href="{{ url('/') }}#team" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="nav_team">Our Team</span></a>
                    <a href="{{ url('/') }}#services" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="nav_services">Services</span></a>
                    <a href="{{ url('/') }}#projects" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="nav_projects">Projects</span></a>
                    <a href="{{ url('/') }}#reviews" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="nav_reviews">Reviews</span></a>
                    <a href="{{ url('/') }}#faq" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="nav_faq">FAQ</span></a>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="footer-title" data-i18n="company">Company</h4>
                    <a href="{{ url('/') }}#about" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="nav_about">About Us</span></a>
                    <a href="{{ url('/') }}#contact" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="nav_contact">Contact</span></a>
                    <a href="#" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="privacy">Privacy Policy</span></a>
                    <a href="#" class="footer-link"><i class="fas fa-chevron-right"></i> <span data-i18n="terms">Terms of Service</span></a>
                </div>

                <!-- Newsletter -->
                <div>
                    <h4 class="footer-title" data-i18n="newsletter">Newsletter</h4>
                    <p style="color:var(--text-secondary);font-size:0.85rem;line-height:1.7;margin-bottom:0.5rem;" data-i18n="newsletter_desc">
                        Stay updated with our latest news and projects.
                    </p>
                    <div class="footer-newsletter-form">
                        <input type="email" placeholder="Your email..." data-i18n-placeholder="email_placeholder" />
                        <button type="button"><i class="fas fa-paper-plane"></i></button>
                    </div>
                    <div style="margin-top:1.5rem;">
                        <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.6rem;">
                            <i class="fas fa-envelope" style="color:var(--gold);font-size:0.8rem;width:16px;"></i>
                            <span style="color:var(--text-secondary);font-size:0.85rem;">contact@hexaterminal.com</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <i class="fas fa-map-marker-alt" style="color:var(--gold);font-size:0.8rem;width:16px;"></i>
                            <span style="color:var(--text-secondary);font-size:0.85rem;">Damascus, Syria</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} HexaTerminal. <span data-i18n="rights">All rights reserved.</span></p>
            <p style="display:flex;align-items:center;gap:0.5rem;"><span data-i18n="crafted">Crafted with</span> <span style="color:var(--danger);">&#10084;</span> <span data-i18n="by">by</span> <span style="color:var(--gold);font-weight:600;">HexaTerminal</span></p>
        </div>
    </footer>

    {{-- ═══ INLINE JS — Globals needed by component scripts ═══ --}}
    <script>
        // API base URL — uses Laravel url() for correct domain on all devices
        const API_BASE = '{{ rtrim(url("/api"), "/") }}';
        const STORAGE_URL = '{{ rtrim(url("/storage"), "/") }}/';

        // ── HTML escaping — MUST wrap every API/user-supplied value that is
        // interpolated into an innerHTML string (XSS protection). ──
        function esc(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        // Escape a value used inside an attribute URL (also blocks javascript: URIs)
        function escUrl(value) {
            var s = esc(value);
            if (/^\s*(javascript|data|vbscript):/i.test(s)) return '';
            return s;
        }

        // Initialize AOS — optimized for performance
        AOS.init({ duration: 500, once: true, offset: 50, easing: 'ease-out', disable: window.innerWidth < 768 });

        // ── Single unified scroll handler (rAF-throttled, passive) ──
        var _scrollTicking = false;
        var _header = document.getElementById('site-header');
        var _navLinks = document.querySelectorAll('.nav-link');
        var _sections = null; // lazy-cached

        function _onScroll() {
            var sy = window.scrollY;

            // Header shrink
            if (sy > 50) { _header.classList.add('scrolled'); } else { _header.classList.remove('scrolled'); }

            // Active nav highlight
            if (!_sections) _sections = document.querySelectorAll('section[id]');
            var scrollPos = sy + 140;
            _sections.forEach(function(sec) {
                var top = sec.offsetTop;
                var id = sec.getAttribute('id');
                var link = document.querySelector('.nav-link[href*="#' + id + '"]');
                if (link) {
                    if (scrollPos >= top && scrollPos < top + sec.offsetHeight) {
                        _navLinks.forEach(function(l) { l.classList.remove('active'); });
                        link.classList.add('active');
                    }
                }
            });
            _scrollTicking = false;
        }

        window.addEventListener('scroll', function() {
            if (!_scrollTicking) { requestAnimationFrame(_onScroll); _scrollTicking = true; }
        }, { passive: true });

        // Mobile menu
        function toggleMobileMenu() {
            document.getElementById('hamburger').classList.toggle('active');
            document.getElementById('main-nav').classList.toggle('open');
        }

        // Close mobile menu on link click
        _navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                document.getElementById('hamburger').classList.remove('active');
                document.getElementById('main-nav').classList.remove('open');
            });
        });

        // Toast
        function showToast(message, type) {
            type = type || 'info';
            var container = document.getElementById('toast-container');
            var icons = { success: '<i class="fas fa-check-circle"></i>', error: '<i class="fas fa-times-circle"></i>', info: '<i class="fas fa-info-circle"></i>' };
            var colorMap = { success: 'var(--green)', error: 'var(--danger)', info: 'var(--primary)' };
            var toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.innerHTML = '<span style="font-size:1.1rem;color:' + colorMap[type] + ';">' + (icons[type] || icons.info) + '</span><span>' + esc(message) + '</span>';
            container.appendChild(toast);
            setTimeout(function() {
                toast.style.animation = 'slideOut 0.4s ease forwards';
                setTimeout(function() { toast.remove(); }, 400);
            }, 4000);
        }

        // ── Theme Toggle ──
        function toggleTheme() {
            var html = document.documentElement;
            var current = html.getAttribute('data-theme') || 'dark';
            var next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('ht-theme', next);
            document.querySelector('meta[name="theme-color"]').content = next === 'dark' ? '#070A18' : '#F5F3EF';
        }

        // ── Translation System ──
        var _translations = {
            en: {
                nav_home: 'Home', nav_team: 'Team', nav_services: 'Services', nav_projects: 'Projects',
                nav_about: 'About', nav_reviews: 'Reviews', nav_faq: 'FAQ', nav_contact: 'Contact',
                // Hero
                hero_badge: 'Welcome to HexaTerminal', hero_title_1: 'Building the',
                hero_title_2: 'Digital Future', hero_title_3: 'with Modern Software Solutions',
                hero_desc: 'We deliver cutting-edge software solutions with innovative technologies, helping businesses transform and grow in the digital landscape.',
                hero_btn_projects: 'Explore Projects', hero_btn_contact: 'Get in Touch',
                hero_stat_projects: 'Projects', hero_stat_services: 'Services', hero_stat_team: 'Team Members',
                scroll_down: 'Scroll Down',
                // Sections
                section_team_badge: 'Our People', section_team_title: 'Meet the Team',
                section_team_subtitle: 'The talented professionals driving our innovation and success',
                section_services_badge: 'What We Do', section_services_title: 'Our Services',
                section_services_subtitle: 'Innovative software solutions tailored to drive your business forward',
                section_projects_badge: 'Our Work', section_projects_title: 'Latest Projects',
                section_projects_subtitle: 'Explore our innovative projects that deliver exceptional digital experiences',
                section_about_badge: 'Who We Are', section_about_title: 'About Us',
                section_reviews_badge: 'Testimonials', section_reviews_title: 'Client Reviews',
                section_reviews_subtitle: 'What our clients say about their experience working with us',
                section_faq_badge: 'FAQ', section_faq_title: 'Frequently Asked Questions',
                section_faq_subtitle: 'Find answers to the most common questions about our services, process, and how we work.',
                section_faq_btn: 'Still Have Questions?',
                section_videos_badge: 'Media', section_videos_title: 'Our Videos',
                section_videos_subtitle: 'Watch our latest content, demos, and behind-the-scenes footage',
                // Contact
                contact_badge: 'Get in Touch', contact_title: "Let's Work Together",
                contact_subtitle: "Have a project in mind? Let's discuss how we can bring your vision to life.",
                contact_email_title: 'Email Us', contact_email_reply: 'Reply within 24 hours',
                contact_phone_title: 'Call Us', contact_phone_hours: 'Mon - Fri, 9am - 6pm',
                contact_visit_title: 'Visit Us', contact_visit_hq: 'HexaTerminal HQ',
                contact_process: 'Our Process', contact_step1: 'Initial Consultation',
                contact_step2: 'Planning & Design', contact_step3: 'Development & Launch',
                contact_form_title: 'Send us a Message',
                contact_form_desc: "Fill out the form below and we'll get back to you as soon as possible.",
                contact_name: 'Full Name', contact_email: 'Email', contact_phone: 'Phone',
                contact_subject: 'Subject', contact_message: 'Message',
                contact_name_ph: 'John Doe', contact_email_ph: 'john@example.com',
                contact_phone_ph: '+1 234 567 890', contact_subject_ph: 'How can we help?',
                contact_message_ph: 'Tell us about your project...',
                contact_send: 'Send Message', contact_sending: 'Sending...',
                // Footer
                quick_links: 'Quick Links', company: 'Company', newsletter: 'Newsletter',
                newsletter_desc: 'Stay updated with our latest news and projects.',
                email_placeholder: 'Your email...', privacy: 'Privacy Policy', terms: 'Terms of Service',
                footer_desc: 'Professional software development company delivering innovative digital solutions with cutting-edge technologies and creative design.',
                rights: 'All rights reserved.', crafted: 'Crafted with', by: 'by',
                view_all_projects: 'View All Projects', view_profile: 'View Profile',
                view_service: 'View Service', view_details: 'View Details',
                no_desc: 'No description available.', loading: 'Loading...',
                back_team: 'Back to Team', back_projects: 'All Projects', back_services: 'All Services',
                start_project: 'Start a Project Like This', start_project_short: 'Start a Project',
                expert_team: 'Expert Team', modern_tech: 'Modern Tech',
                support_247: '24/7 Support', support_24: '24/7 Support', fast_delivery: 'Fast Delivery',
                trusted_by: 'Trusted by', clients: 'Clients', our_mission: 'Our Mission',
                // Sub-pages
                email: 'Email', phone: 'Phone', specialization: 'Specialization', position: 'Position',
                download_cv: 'Download CV', send_email: 'Send Email',
                back_to_team: 'Back to Team', get_in_touch: 'Get in Touch',
                projects_in: 'Projects in', no_projects_yet: 'No projects yet',
                projects_appear_soon: 'Projects will appear here as they are added.',
                all_services: 'All Services', all_projects: 'All Projects',
                images: 'Images', features: 'Features', projects: 'Projects',
                related_service: 'Related Service', project_features: 'Project Features',
                start_project_like: 'Start a Project Like This',
                portfolio: 'Portfolio', our: 'Our',
                projects_page_subtitle: 'Explore our complete portfolio of innovative digital solutions built with cutting-edge technologies',
                back_to_home: 'Back to Home', page: 'Page',
                still_have_questions: 'Still Have Questions?',
                reviews_coming_soon: 'Reviews coming soon!',
                videos_coming_soon: 'Videos coming soon!',
                faq_coming_soon: 'FAQs coming soon!',
                member_not_found: 'Member Not Found',
                member_not_found_desc: "The team member you're looking for doesn't exist or may have been removed.",
                view_all_team: 'View All Team',
                service_not_found: 'Service Not Found',
                service_not_found_desc: "The service you're looking for doesn't exist or may have been removed.",
                view_all_services: 'View All Services',
                project_not_found: 'Project Not Found',
                project_not_found_desc: "The project you're looking for doesn't exist or may have been removed.",
                browse_all_projects: 'Browse All Projects',
                projects_coming_soon_desc: "We're working on something amazing. Check back soon!"
            },
            ar: {
                nav_home: 'الرئيسية', nav_team: 'الفريق', nav_services: 'الخدمات', nav_projects: 'المشاريع',
                nav_about: 'من نحن', nav_reviews: 'التقييمات', nav_faq: 'الأسئلة', nav_contact: 'تواصل',
                hero_badge: 'مرحباً بكم في هيكسا تيرمنال', hero_title_1: 'نبني',
                hero_title_2: 'المستقبل الرقمي', hero_title_3: 'بحلول برمجية متطورة',
                hero_desc: 'نقدم حلولاً برمجية متطورة بتقنيات مبتكرة، نساعد الشركات على التحول والنمو في العالم الرقمي.',
                hero_btn_projects: 'استكشف المشاريع', hero_btn_contact: 'تواصل معنا',
                hero_stat_projects: 'مشاريع', hero_stat_services: 'خدمات', hero_stat_team: 'أعضاء الفريق',
                scroll_down: 'مرر للأسفل',
                section_team_badge: 'فريقنا', section_team_title: 'تعرف على الفريق',
                section_team_subtitle: 'المحترفون الموهوبون الذين يقودون ابتكارنا ونجاحنا',
                section_services_badge: 'ماذا نفعل', section_services_title: 'خدماتنا',
                section_services_subtitle: 'حلول برمجية مبتكرة مصممة لدفع أعمالك للأمام',
                section_projects_badge: 'أعمالنا', section_projects_title: 'أحدث المشاريع',
                section_projects_subtitle: 'اكتشف مشاريعنا المبتكرة التي تقدم تجارب رقمية استثنائية',
                section_about_badge: 'من نحن', section_about_title: 'عنا',
                section_reviews_badge: 'شهادات العملاء', section_reviews_title: 'تقييمات العملاء',
                section_reviews_subtitle: 'ما يقوله عملاؤنا عن تجربتهم في العمل معنا',
                section_faq_badge: 'الأسئلة الشائعة', section_faq_title: 'الأسئلة الشائعة',
                section_faq_subtitle: 'اعثر على إجابات للأسئلة الأكثر شيوعاً حول خدماتنا وعملياتنا وكيف نعمل.',
                section_faq_btn: 'لا تزال لديك أسئلة؟',
                section_videos_badge: 'الوسائط', section_videos_title: 'فيديوهاتنا',
                section_videos_subtitle: 'شاهد أحدث محتوانا والعروض التوضيحية وكواليس العمل',
                contact_badge: 'تواصل معنا', contact_title: 'لنعمل معاً',
                contact_subtitle: 'هل لديك مشروع؟ دعنا نناقش كيف يمكننا تحقيق رؤيتك.',
                contact_email_title: 'راسلنا', contact_email_reply: 'الرد خلال 24 ساعة',
                contact_phone_title: 'اتصل بنا', contact_phone_hours: 'الاثنين - الجمعة، 9ص - 6م',
                contact_visit_title: 'زرنا', contact_visit_hq: 'مقر هيكسا تيرمنال',
                contact_process: 'عمليتنا', contact_step1: 'الاستشارة الأولية',
                contact_step2: 'التخطيط والتصميم', contact_step3: 'التطوير والإطلاق',
                contact_form_title: 'أرسل لنا رسالة',
                contact_form_desc: 'املأ النموذج أدناه وسنعود إليك في أقرب وقت ممكن.',
                contact_name: 'الاسم الكامل', contact_email: 'البريد الإلكتروني', contact_phone: 'الهاتف',
                contact_subject: 'الموضوع', contact_message: 'الرسالة',
                contact_name_ph: 'أحمد محمد', contact_email_ph: 'ahmed@example.com',
                contact_phone_ph: '+963 9XX XXX XXX', contact_subject_ph: 'كيف يمكننا مساعدتك؟',
                contact_message_ph: 'أخبرنا عن مشروعك...',
                contact_send: 'إرسال الرسالة', contact_sending: 'جاري الإرسال...',
                quick_links: 'روابط سريعة', company: 'الشركة', newsletter: 'النشرة البريدية',
                newsletter_desc: 'ابق على اطلاع بأحدث أخبارنا ومشاريعنا.',
                email_placeholder: 'بريدك الإلكتروني...', privacy: 'سياسة الخصوصية', terms: 'شروط الخدمة',
                footer_desc: 'شركة تطوير برمجيات احترافية تقدم حلولاً رقمية مبتكرة بأحدث التقنيات والتصميم الإبداعي.',
                rights: 'جميع الحقوق محفوظة.', crafted: 'صُنع بـ', by: 'بواسطة',
                view_all_projects: 'عرض جميع المشاريع', view_profile: 'عرض الملف',
                view_service: 'عرض الخدمة', view_details: 'عرض التفاصيل',
                no_desc: 'لا يوجد وصف متاح.', loading: 'جاري التحميل...',
                back_team: 'العودة للفريق', back_projects: 'جميع المشاريع', back_services: 'جميع الخدمات',
                start_project: 'ابدأ مشروعاً مثل هذا', start_project_short: 'ابدأ مشروعاً',
                expert_team: 'فريق خبير', modern_tech: 'تقنيات حديثة',
                support_247: 'دعم على مدار الساعة', support_24: 'دعم على مدار الساعة', fast_delivery: 'تسليم سريع',
                trusted_by: 'موثوق من قبل', clients: 'عميل', our_mission: 'مهمتنا',
                email: 'البريد الإلكتروني', phone: 'الهاتف', specialization: 'التخصص', position: 'المنصب',
                download_cv: 'تحميل السيرة الذاتية', send_email: 'إرسال بريد',
                back_to_team: 'العودة للفريق', get_in_touch: 'تواصل معنا',
                projects_in: 'مشاريع في', no_projects_yet: 'لا توجد مشاريع بعد',
                projects_appear_soon: 'ستظهر المشاريع هنا عند إضافتها.',
                all_services: 'جميع الخدمات', all_projects: 'جميع المشاريع',
                images: 'صور', features: 'ميزات', projects: 'مشاريع',
                related_service: 'الخدمة المرتبطة', project_features: 'ميزات المشروع',
                start_project_like: 'ابدأ مشروعاً مثل هذا',
                portfolio: 'معرض الأعمال', our: 'لدينا',
                projects_page_subtitle: 'استكشف محفظتنا الكاملة من الحلول الرقمية المبتكرة المبنية بأحدث التقنيات',
                back_to_home: 'العودة للرئيسية', page: 'صفحة',
                still_have_questions: 'لا تزال لديك أسئلة؟',
                reviews_coming_soon: 'التقييمات قادمة قريباً!',
                videos_coming_soon: 'الفيديوهات قادمة قريباً!',
                faq_coming_soon: 'الأسئلة الشائعة قادمة قريباً!',
                member_not_found: 'العضو غير موجود',
                member_not_found_desc: 'عضو الفريق الذي تبحث عنه غير موجود أو ربما تمت إزالته.',
                view_all_team: 'عرض جميع الفريق',
                service_not_found: 'الخدمة غير موجودة',
                service_not_found_desc: 'الخدمة التي تبحث عنها غير موجودة أو ربما تمت إزالتها.',
                view_all_services: 'عرض جميع الخدمات',
                project_not_found: 'المشروع غير موجود',
                project_not_found_desc: 'المشروع الذي تبحث عنه غير موجود أو ربما تمت إزالته.',
                browse_all_projects: 'تصفح جميع المشاريع',
                projects_coming_soon_desc: 'نعمل على شيء مذهل. تحقق مرة أخرى قريباً!'
            }
        };

        function _currentLang() { return localStorage.getItem('ht-lang') || 'en'; }
        function t(key) { var lang = _currentLang(); return (_translations[lang] && _translations[lang][key]) || (_translations.en[key]) || key; }

        function toggleLang() {
            var current = _currentLang();
            var next = current === 'en' ? 'ar' : 'en';
            localStorage.setItem('ht-lang', next);
            document.documentElement.lang = next;
            document.documentElement.dir = next === 'ar' ? 'rtl' : 'ltr';
            applyTranslations();
        }

        function applyTranslations() {
            var lang = _currentLang();
            // Update lang label
            var label = document.getElementById('lang-label');
            if (label) label.textContent = lang === 'en' ? 'AR' : 'EN';
            // Update all data-i18n elements
            document.querySelectorAll('[data-i18n]').forEach(function(el) {
                var key = el.getAttribute('data-i18n');
                if (_translations[lang] && _translations[lang][key]) {
                    el.textContent = _translations[lang][key];
                }
            });
            // Update all data-i18n-placeholder elements
            document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) {
                var key = el.getAttribute('data-i18n-placeholder');
                if (_translations[lang] && _translations[lang][key]) {
                    el.placeholder = _translations[lang][key];
                }
            });
        }

        // Apply on load
        applyTranslations();

        // Image URL helper
        function getImageUrl(path) {
            if (!path) return '';
            if (path.startsWith('http')) return path;
            return STORAGE_URL + path;
        }

        // ── Smooth scroll — scrolls so the section title is visible below the header ──
        function scrollToSection(hash) {
            var target = document.getElementById(hash);
            if (!target) return false;
            var headerH = _header.offsetHeight + 20; // header + 20px breathing room
            var top = target.getBoundingClientRect().top + window.pageYOffset - headerH;
            window.scrollTo({ top: top, behavior: 'smooth' });
            history.replaceState(null, '', '#' + hash);
            return true;
        }

        document.addEventListener('click', function(e) {
            var link = e.target.closest('a[href*="#"]');
            if (!link) return;
            var href = link.getAttribute('href');
            var hash = href.split('#')[1];
            if (!hash) return;
            // Only intercept if target section exists on this page
            if (document.getElementById(hash)) {
                e.preventDefault();
                scrollToSection(hash);
            }
        });

        // Handle initial hash in URL (e.g. arriving from another page)
        if (window.location.hash) {
            var initHash = window.location.hash.substring(1);
            // Wait for DOM + Axios renders to settle, then scroll
            setTimeout(function() { scrollToSection(initHash); }, 350);
        }

        // Counter animation helper
        function animateCounter(el, target) {
            var current = 0;
            var increment = target / 40;
            var timer = setInterval(function() {
                current += increment;
                if (current >= target) { current = target; clearInterval(timer); }
                el.textContent = Math.floor(current) + '+';
            }, 30);
        }
    </script>

    @stack('scripts')
</body>
</html>
