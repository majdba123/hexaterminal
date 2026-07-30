@extends('layouts.website')

@section('title', 'HexaTerminal - Software Development Company')

@section('content')
<!-- ═══ HERO SECTION ═══ -->
<section id="home" class="section hero-section" style="padding: 0; min-height: 100vh; display: flex; align-items: center; position: relative; overflow: hidden;">
    <!-- Background effects -->
    <div class="hero-bg">
        <div class="glow-dot" style="width:500px;height:500px;background:var(--gold);top:-200px;right:-150px;"></div>
        <div class="glow-dot" style="width:400px;height:400px;background:var(--gold);bottom:-150px;left:-100px;"></div>
        <!-- Luxury grid pattern -->
        <div class="hero-grid-pattern"></div>
        <!-- Decorative rings (hidden on mobile via CSS) -->
        <div class="hero-deco-ring hero-deco-ring-1"></div>
    </div>

    <div class="container" style="position: relative; z-index: 2; padding-top: 6rem; padding-bottom: 4rem;">
        <div style="display:grid;grid-template-columns:1.1fr 0.9fr;gap:4rem;align-items:center;">
            <!-- Left content -->
            <div data-aos="fade-right" data-aos-duration="900">
                <div style="margin-bottom:1.5rem;">
                    <span class="section-badge" style="animation:fadeInUp 0.6s ease;" data-i18n="hero_badge">
                        <span class="hero-badge-dot"></span>
                Welcome to HexaTerminal
                    </span>
                </div>
                <h1 class="hero-title">
                    <span style="display:block;color:var(--text-primary);margin-bottom:0.25rem;" data-i18n="hero_title_1">Building the</span>
                    <span class="text-gradient-gold" style="display:block;font-size:clamp(3rem, 6vw, 4.5rem);" data-i18n="hero_title_2">Digital Future</span>
                    <span style="display:block;color:var(--text-secondary);font-size:clamp(1.3rem,2.5vw,1.8rem);font-weight:500;margin-top:0.5rem;" data-i18n="hero_title_3">
                        with Modern Software Solutions
                    </span>
            </h1>
                <p style="font-size:1.05rem;color:var(--text-secondary);margin:2rem 0 2.5rem;line-height:1.85;max-width:540px;" data-i18n="hero_desc">
                    We deliver cutting-edge software solutions with innovative technologies,
                    helping businesses transform and grow in the digital landscape.
                </p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <a href="#projects" class="btn btn-gold" style="padding:1rem 2.25rem;font-size:0.95rem;">
                        <span data-i18n="hero_btn_projects">Explore Projects</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="#contact" class="btn btn-outline" style="padding:1rem 2.25rem;font-size:0.95rem;">
                        <i class="fas fa-envelope" style="font-size:0.85rem;"></i>
                        <span data-i18n="hero_btn_contact">Get in Touch</span>
                    </a>
                </div>

                <!-- Trusted brands / mini stats -->
                <div class="hero-mini-stats" data-aos="fade-up" data-aos-delay="400">
                    <div class="hero-stat-item" id="stat-projects">
                        <span class="hero-stat-number">0+</span>
                        <span class="hero-stat-label" data-i18n="hero_stat_projects">Projects</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat-item" id="stat-services">
                        <span class="hero-stat-number">0+</span>
                        <span class="hero-stat-label" data-i18n="hero_stat_services">Services</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat-item" id="stat-team">
                        <span class="hero-stat-number">0+</span>
                        <span class="hero-stat-label" data-i18n="hero_stat_team">Team Members</span>
                    </div>
                </div>
            </div>

            <!-- Right visual — Premium hexagonal design -->
            <div data-aos="fade-left" data-aos-duration="900" data-aos-delay="200" style="display:flex;justify-content:center;">
                <div class="hero-visual">
                    <div class="hero-hex-container">
                        <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" class="hero-hex-svg">
                            <defs>
                                <linearGradient id="heroGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#D4AF37;stop-opacity:0.5"/>
                                    <stop offset="100%" style="stop-color:#4A8FE7;stop-opacity:0.5"/>
                                </linearGradient>
                                <linearGradient id="heroGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#D4AF37;stop-opacity:0.15"/>
                                    <stop offset="100%" style="stop-color:#4A8FE7;stop-opacity:0.15"/>
                                </linearGradient>
                                <linearGradient id="heroGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#D4AF37"/>
                                    <stop offset="100%" style="stop-color:#4A8FE7"/>
                                </linearGradient>
                            </defs>
                            <!-- Outer hex -->
                            <polygon points="200,30 350,110 350,270 200,350 50,270 50,110" fill="none" stroke="url(#heroGrad2)" stroke-width="1.5" class="hero-hex-outer"/>
                            <!-- Middle hex -->
                            <polygon points="200,65 320,130 320,260 200,325 80,260 80,130" fill="url(#heroGrad2)" stroke="url(#heroGrad1)" stroke-width="1.5" class="hero-hex-mid"/>
                            <!-- Inner hex -->
                            <polygon points="200,100 290,150 290,250 200,300 110,250 110,150" fill="rgba(212,175,55,0.03)" stroke="url(#heroGrad1)" stroke-width="1" class="hero-hex-inner"/>
                            <!-- Center terminal with gold -->
                            <text x="200" y="190" font-family="'Courier New', monospace" font-weight="700" font-size="60" fill="url(#heroGrad3)" text-anchor="middle" dominant-baseline="middle" opacity="0.9">&gt;_</text>
                            <text x="200" y="235" font-family="Inter, sans-serif" font-weight="600" font-size="14" fill="rgba(212,175,55,0.5)" text-anchor="middle" letter-spacing="4">SOFTWARE DEVELOPMENT</text>
                        </svg>
                        <!-- Floating dots -->
                        <div class="hero-float-dot" style="top:10%;left:15%;width:5px;height:5px;animation-delay:0s;background:var(--gold);"></div>
                        <div class="hero-float-dot" style="top:75%;right:10%;width:6px;height:6px;animation-delay:1s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="scroll-indicator" data-aos="fade-up" data-aos-delay="800">
        <div class="scroll-mouse">
            <div class="scroll-wheel"></div>
        </div>
        <span data-i18n="scroll_down">Scroll Down</span>
    </div>
</section>

<!-- All sections -->
@include('website.components.team')
@include('website.components.services')
@include('website.components.projects')
@include('website.components.about')
@include('website.components.reviews')
@include('website.components.videos')
@include('website.components.faq')

<!-- ═══ CONTACT SECTION ═══ -->
<section id="contact" class="section" style="background: linear-gradient(180deg, var(--dark-bg) 0%, var(--dark-bg-2) 100%); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:300px;height:300px;background:var(--gold);top:-80px;right:-80px;"></div>

    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge"><i class="fas fa-paper-plane"></i> <span data-i18n="contact_badge">Get in Touch</span></span>
            <h2 class="section-title" data-i18n="contact_title">Let's Work Together</h2>
            <p class="section-subtitle" data-i18n="contact_subtitle">Have a project in mind? Let's discuss how we can bring your vision to life.</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1.3fr;gap:3rem;align-items:start;" data-aos="fade-up" data-aos-delay="100">
            <!-- Contact Info Sidebar -->
            <div class="contact-info-sidebar">
                <div class="contact-info-card">
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                <div>
                            <h4 data-i18n="contact_email_title">Email Us</h4>
                            <p>contact@hexaterminal.com</p>
                            <span data-i18n="contact_email_reply">Reply within 24 hours</span>
                        </div>
                </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
                <div>
                            <h4 data-i18n="contact_phone_title">Call Us</h4>
                            <p>+963 935 027 218</p>
                            <span data-i18n="contact_phone_hours">Mon - Fri, 9am - 6pm</span>
                        </div>
                </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                            <h4 data-i18n="contact_visit_title">Visit Us</h4>
                            <p>Damascus, Syria</p>
                            <span data-i18n="contact_visit_hq">HexaTerminal HQ</span>
                        </div>
                    </div>
                </div>

                <!-- Process steps -->
                <div class="contact-process">
                    <h4 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;">
                        <i class="fas fa-route" style="color:var(--gold);margin-right:0.5rem;"></i>
                        <span data-i18n="contact_process">Our Process</span>
                    </h4>
                    <div class="process-step">
                        <span class="process-num">01</span>
                        <span data-i18n="contact_step1">Initial Consultation</span>
                    </div>
                    <div class="process-step">
                        <span class="process-num">02</span>
                        <span data-i18n="contact_step2">Planning & Design</span>
                    </div>
                    <div class="process-step">
                        <span class="process-num">03</span>
                        <span data-i18n="contact_step3">Development & Launch</span>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="card contact-form-card" style="padding:2.5rem;">
                <h3 style="font-size:1.3rem;font-weight:700;color:var(--text-primary);margin-bottom:0.5rem;" data-i18n="contact_form_title">
                    Send us a Message
                </h3>
                <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:2rem;line-height:1.7;" data-i18n="contact_form_desc">
                    Fill out the form below and we'll get back to you as soon as possible.
                </p>
                <form id="contact-form" onsubmit="submitContactForm(event)">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user" style="margin-right:0.4rem;color:var(--gold);font-size:0.75rem;"></i><span data-i18n="contact_name">Full Name</span></label>
                        <input type="text" name="name" class="form-input" placeholder="John Doe" data-i18n-placeholder="contact_name_ph" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-envelope" style="margin-right:0.4rem;color:var(--gold);font-size:0.75rem;"></i><span data-i18n="contact_email">Email</span></label>
                            <input type="email" name="email" class="form-input" placeholder="john@example.com" data-i18n-placeholder="contact_email_ph" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-phone" style="margin-right:0.4rem;color:var(--gold);font-size:0.75rem;"></i><span data-i18n="contact_phone">Phone</span></label>
                            <input type="tel" name="phone" class="form-input" placeholder="+1 234 567 890" data-i18n-placeholder="contact_phone_ph" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-tag" style="margin-right:0.4rem;color:var(--gold);font-size:0.75rem;"></i><span data-i18n="contact_subject">Subject</span></label>
                        <input type="text" name="subject" class="form-input" placeholder="How can we help?" data-i18n-placeholder="contact_subject_ph" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-comment" style="margin-right:0.4rem;color:var(--gold);font-size:0.75rem;"></i><span data-i18n="contact_message">Message</span></label>
                        <textarea name="message" rows="5" class="form-input" placeholder="Tell us about your project..." data-i18n-placeholder="contact_message_ph" required style="resize:vertical;min-height:120px;"></textarea>
                </div>
                    <button type="submit" id="contact-btn" class="btn btn-gold" style="width:100%;justify-content:center;padding:1rem;font-size:0.95rem;">
                        <span id="contact-btn-text"><i class="fas fa-paper-plane" style="margin-right:0.5rem;"></i><span data-i18n="contact_send">Send Message</span></span>
                        <span id="contact-btn-loading" style="display:none;">
                            <i class="fas fa-spinner fa-spin" style="margin-right:0.5rem;"></i> <span data-i18n="contact_sending">Sending...</span>
                        </span>
                </button>
            </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
/* ═══ HERO ═══ */
.hero-section { background: var(--dark-bg); }
.hero-grid-pattern {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(212,175,55,0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212,175,55,0.02) 1px, transparent 1px);
    background-size: 60px 60px;
    mask-image: radial-gradient(ellipse 60% 60% at 50% 50%, black, transparent);
}
/* Decorative ring */
.hero-deco-ring {
    position: absolute; border-radius: 50%; pointer-events: none;
    border: 1px solid rgba(212,175,55,0.05);
}
.hero-deco-ring-1 {
    width: 400px; height: 400px; top: -80px; right: -80px;
    animation: rotate-slow 40s linear infinite;
}
.hero-title {
    font-size: clamp(2.5rem, 5vw, 3.8rem); font-weight: 900;
    line-height: 1.08; letter-spacing: -0.03em;
}
.hero-badge-dot {
    width: 8px; height: 8px; background: var(--gold); border-radius: 50%;
    display: inline-block; animation: pulse-glow 2s ease-in-out infinite;
    box-shadow: 0 0 10px var(--gold);
}
.hero-mini-stats {
    display: flex; align-items: center; gap: 2rem;
    margin-top: 3rem; padding: 1.5rem 0;
    border-top: 1px solid rgba(212,175,55,0.08);
}
.hero-stat-item { text-align: center; }
.hero-stat-number {
    display: block; font-size: 1.75rem; font-weight: 800; color: var(--gold);
    line-height: 1.2; animation: countUp 0.5s ease;
}
.hero-stat-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }
.hero-stat-divider { width: 1px; height: 40px; background: rgba(212,175,55,0.12); }

/* Hero visual */
.hero-visual { position: relative; width: 100%; max-width: 420px; }
.hero-hex-container { position: relative; }
.hero-hex-svg { width: 100%; height: auto; animation: float 6s ease-in-out infinite; }
.hero-hex-outer { animation: rotate-slow 30s linear infinite; transform-origin: center; }
.hero-float-dot {
    position: absolute; background: var(--gold); border-radius: 50%;
    animation: float 4s ease-in-out infinite;
    box-shadow: 0 0 10px rgba(212,175,55,0.3);
}

/* Scroll indicator */
.scroll-indicator {
    position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    color: var(--text-muted); font-size: 0.72rem; letter-spacing: 1.5px; text-transform: uppercase;
}
.scroll-mouse {
    width: 24px; height: 38px; border: 2px solid rgba(212,175,55,0.2);
    border-radius: 12px; position: relative;
}
.scroll-wheel {
    width: 3px; height: 8px; background: var(--gold); border-radius: 2px;
    position: absolute; top: 6px; left: 50%; transform: translateX(-50%);
    animation: scrollWheel 2s ease-in-out infinite;
}
@keyframes scrollWheel {
    0% { opacity: 1; transform: translateX(-50%) translateY(0); }
    100% { opacity: 0; transform: translateX(-50%) translateY(12px); }
}

/* ═══ CONTACT ═══ */
.contact-info-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }
.contact-info-card {
    background: var(--card-bg-solid);
    border: 1px solid var(--card-border); border-radius: var(--radius-lg);
    padding: 1.75rem; display: flex; flex-direction: column; gap: 1.5rem;
}
.contact-info-item { display: flex; gap: 1rem; align-items: flex-start; }
.contact-info-icon {
    width: 48px; height: 48px; border-radius: var(--radius-md);
    background: linear-gradient(135deg, rgba(212,175,55,0.1), rgba(212,175,55,0.04));
    border: 1px solid rgba(212,175,55,0.15);
    display: flex; align-items: center; justify-content: center;
    color: var(--gold); font-size: 1rem; flex-shrink: 0;
}
.contact-info-item h4 { font-size: 0.92rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.15rem; }
.contact-info-item p { font-size: 0.88rem; color: var(--text-secondary); margin: 0; }
.contact-info-item span { font-size: 0.75rem; color: var(--text-muted); }

.contact-process {
    background: var(--card-bg-solid);
    border: 1px solid var(--card-border); border-radius: var(--radius-lg);
    padding: 1.5rem;
}
.process-step {
    display: flex; align-items: center; gap: 1rem;
    padding: 0.75rem 0; color: var(--text-secondary); font-size: 0.88rem;
}
.process-step:not(:last-child) { border-bottom: 1px solid rgba(255,255,255,0.03); }
.process-num {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, var(--gold), #C9A84C);
    color: #070A18; font-weight: 800; font-size: 0.75rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

.contact-form-card { background: var(--card-bg-solid) !important; }

/* ═══ LIGHT THEME OVERRIDES ═══ */
[data-theme="light"] .hero-section { background: #F5F3EF; }
[data-theme="light"] .hero-grid-pattern {
    background-image:
        linear-gradient(rgba(184,148,31,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(184,148,31,0.03) 1px, transparent 1px);
}
[data-theme="light"] .hero-deco-ring { border-color: rgba(184,148,31,0.04); }
[data-theme="light"] .hero-mini-stats { border-top-color: rgba(184,148,31,0.08); }
[data-theme="light"] .hero-stat-divider { background: rgba(184,148,31,0.1); }
[data-theme="light"] .hero-float-dot { box-shadow: 0 0 10px rgba(184,148,31,0.2); }
[data-theme="light"] .scroll-mouse { border-color: rgba(184,148,31,0.15); }
[data-theme="light"] .contact-info-card { background: #FDFCF9; border-color: rgba(0,0,0,0.05); box-shadow: 0 2px 12px rgba(0,0,0,0.03); }
[data-theme="light"] .contact-process { background: #FDFCF9; border-color: rgba(0,0,0,0.05); box-shadow: 0 2px 12px rgba(0,0,0,0.03); }
[data-theme="light"] .contact-info-icon { background: linear-gradient(135deg, rgba(184,148,31,0.06), rgba(184,148,31,0.02)); border-color: rgba(184,148,31,0.1); }
[data-theme="light"] .process-step:not(:last-child) { border-bottom-color: rgba(0,0,0,0.04); }
[data-theme="light"] .contact-form-card { background: #FDFCF9 !important; box-shadow: 0 2px 12px rgba(0,0,0,0.04) !important; }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1024px) {
    .hero-section > .container > div { grid-template-columns: 1fr !important; text-align: center; gap: 2rem !important; }
    .hero-section .hero-visual { max-width: 260px; margin: 0 auto; }
    .hero-mini-stats { justify-content: center; }
    .hero-title span { display: block; }
    .hero-section p { margin-left: auto; margin-right: auto; }
    .hero-section div[style*="display:flex;gap:1rem"] { justify-content: center; }
}
@media (max-width: 768px) {
    .hero-section { min-height: auto !important; padding-top: 0 !important; }
    .hero-section > .container { padding-top: 2rem !important; padding-bottom: 1.5rem !important; }
    .hero-visual { display: none !important; }
    .hero-deco-ring { display: none !important; }
    .hero-grid-pattern { display: none !important; }
    .hero-title { font-size: clamp(1.8rem, 8vw, 2.5rem) !important; }
    .text-gradient-gold { font-size: clamp(2rem, 9vw, 3rem) !important; }
    .hero-mini-stats { flex-direction: row; flex-wrap: wrap; gap: 1.25rem; margin-top: 2rem; padding: 1rem 0; }
    .hero-stat-number { font-size: 1.4rem; }
    .hero-stat-label { font-size: 0.7rem; }
    .hero-stat-divider { display: none; }
    .scroll-indicator { display: none; }
    #contact > .container > div { grid-template-columns: 1fr !important; gap: 2rem !important; }
    .contact-info-sidebar { order: 2; }
    .contact-form-card { order: 1; padding: 1.5rem !important; }
}
@media (max-width: 480px) {
    .hero-section > .container { padding-top: 1.5rem !important; }
    .hero-title { font-size: 1.5rem !important; }
    .text-gradient-gold { font-size: 1.8rem !important; }
    .hero-mini-stats { gap: 1rem; }
    .hero-stat-number { font-size: 1.2rem; }
    .hero-section div[style*="display:flex;gap:1rem"] { flex-direction: column; align-items: stretch; }
    .hero-section div[style*="display:flex;gap:1rem"] .btn { width: 100%; justify-content: center; }
}
@media (max-width: 640px) {
    #contact .contact-form-card form div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
    .contact-info-card { padding: 1.25rem !important; gap: 1rem !important; }
    .contact-info-icon { width: 40px; height: 40px; font-size: 0.85rem; }
    .contact-process { padding: 1.25rem; }
}
</style>
@endpush

@push('scripts')
<script>
// Hero stats — populated by section scripts via global hooks (no extra API calls)
window._heroStats = { projects: 0, services: 0, team: 0, loaded: 0 };
window._updateHeroStat = function(key, val) {
    window._heroStats[key] = val;
    window._heroStats.loaded++;
    if (window._heroStats.loaded >= 3) {
        var items = [
            { el: document.querySelector('#stat-projects .hero-stat-number'), val: window._heroStats.projects },
            { el: document.querySelector('#stat-services .hero-stat-number'), val: window._heroStats.services },
            { el: document.querySelector('#stat-team .hero-stat-number'), val: window._heroStats.team }
        ];
        items.forEach(function(item) {
            if (item.el) animateCounter(item.el, item.val);
        });
    }
};

// Contact form submit
function submitContactForm(e) {
    e.preventDefault();
    var form = document.getElementById('contact-form');
    var btn = document.getElementById('contact-btn');
    var btnText = document.getElementById('contact-btn-text');
    var btnLoading = document.getElementById('contact-btn-loading');

    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-flex';

    var formData = new FormData(form);
    var data = {};
    formData.forEach(function(value, key) { data[key] = value; });

    axios.post(API_BASE + '/contact_us/store', data)
        .then(function(response) {
            showToast('Message sent successfully! We\'ll get back to you soon.', 'success');
            form.reset();
        })
        .catch(function(error) {
            var msg = 'Failed to send message. Please try again.';
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    var errors = error.response.data.errors;
                    msg = Object.values(errors).flat().join(', ');
                } else if (error.response.data.message) {
                    msg = error.response.data.message;
                }
            }
            showToast(msg, 'error');
        })
        .finally(function() {
            btn.disabled = false;
            btnText.style.display = 'inline-flex';
            btnLoading.style.display = 'none';
        });
}

// Re-apply translations after dynamic content loaded (debounced)
var _applyTimer;
window._scheduleTranslationRefresh = function() {
    clearTimeout(_applyTimer);
    _applyTimer = setTimeout(function() {
        if (typeof applyTranslations === 'function') applyTranslations();
    }, 200);
};
setTimeout(function() { window._scheduleTranslationRefresh(); }, 400);
</script>
@endpush
