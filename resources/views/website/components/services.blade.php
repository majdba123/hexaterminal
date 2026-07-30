<!-- ═══ SERVICES SECTION — HexaTerminal Style ═══ -->
<section id="services" class="section" style="background: var(--dark-bg); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:300px;height:300px;background:var(--gold);top:-100px;left:-100px;"></div>

    <div class="container">
        <div class="ht-split-layout">
            <!-- Left: Sticky sidebar -->
            <div class="ht-split-left" data-aos="fade-right">
                <div class="ht-sticky-sidebar">
                    <div class="ht-section-title">
                        <span class="section-badge"><i class="fas fa-gem"></i> <span data-i18n="section_services_badge">What We Do</span></span>
                        <h2 class="ht-title" data-i18n="section_services_title">Our Services</h2>
                    </div>
                    <div class="ht-sidebar-body">
                        <p data-i18n="section_services_subtitle">Innovative software solutions tailored to drive your business forward with cutting-edge technology.</p>
                        <div class="ht-rotate-images">
                            <div class="ht-rotate-bg"></div>
                            <div class="ht-rotate-inner">
                                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="ht-decorative-svg">
                                    <defs>
                                        <linearGradient id="svcGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:var(--gold);stop-opacity:0.4"/>
                                            <stop offset="100%" style="stop-color:var(--secondary);stop-opacity:0.4"/>
                                        </linearGradient>
                                    </defs>
                                    <polygon points="100,10 180,50 180,130 100,170 20,130 20,50" fill="none" stroke="url(#svcGrad)" stroke-width="1.5" class="ht-hex-rotate"/>
                                    <polygon points="100,35 155,65 155,125 100,155 45,125 45,65" fill="rgba(212,175,55,0.03)" stroke="url(#svcGrad)" stroke-width="1"/>
                                    <text x="100" y="96" font-family="'Courier New',monospace" font-weight="700" font-size="32" fill="url(#svcGrad)" text-anchor="middle" dominant-baseline="middle" opacity="0.7">⚙</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Service cards in stagger grid -->
            <div class="ht-split-right">
                <!-- Skeleton -->
                <div id="services-skeleton" class="ht-stagger-grid">
                    <div class="ht-stagger-col">
                        <div class="skeleton" style="height:200px;border-radius:10px;"></div>
                        <div class="skeleton" style="height:200px;border-radius:10px;"></div>
                    </div>
                    <div class="ht-stagger-col ht-stagger-offset">
                        <div class="skeleton" style="height:200px;border-radius:10px;"></div>
                        <div class="skeleton" style="height:200px;border-radius:10px;"></div>
                    </div>
                </div>

                <!-- Actual content -->
                <div id="services-grid" class="ht-stagger-grid" style="display:none;">
                    <div id="svc-col-left" class="ht-stagger-col"></div>
                    <div id="svc-col-right" class="ht-stagger-col ht-stagger-offset"></div>
                </div>

                <!-- Empty state -->
                <div id="services-empty" style="display:none;text-align:center;padding:4rem 0;">
                    <div style="width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-gem" style="font-size:2rem;color:var(--gold);"></i>
                    </div>
                    <p style="font-size:1.1rem;color:var(--text-secondary);font-weight:500;">Services coming soon!</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* ═══ SERVICE CARD — Compact ═══ */
.ht-service-card {
    position: relative;
    z-index: 1;
    border-radius: 10px;
    overflow: hidden;
    background: var(--card-bg-solid);
    border: 1px solid var(--card-border);
    padding: 1.5rem 1.25rem;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.ht-service-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--gold), var(--gold-light));
    transform: scaleX(0);
    transition: transform 0.3s ease;
    transform-origin: left;
}
.ht-service-card:hover::before { transform: scaleX(1); }
.ht-service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.18);
    border-color: rgba(212,175,55,0.18);
}

/* Service icon */
.ht-service-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, rgba(212,175,55,0.08), rgba(74,143,231,0.06));
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; margin-bottom: 1rem;
    transition: background 0.3s ease, transform 0.3s ease;
    position: relative;
}
.ht-service-card:hover .ht-service-icon {
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    border-color: transparent;
    transform: scale(1.05);
}
.ht-service-card:hover .ht-service-icon i { color: #070A18 !important; }

/* Service text */
.ht-service-title {
    font-size: 1.05rem; font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 0.4rem; line-height: 1.3;
}
.ht-service-desc {
    color: var(--text-secondary); font-size: 0.85rem;
    line-height: 1.7; margin-bottom: 0.75rem;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}
.ht-service-meta {
    display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
    margin-top: auto;
}
.ht-service-badge {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    background: rgba(212,175,55,0.06);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 999px;
    font-size: 0.72rem; font-weight: 600; color: var(--gold);
}
.ht-service-arrow {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(212,175,55,0.06);
    border: 1px solid rgba(212,175,55,0.1);
    display: flex; align-items: center; justify-content: center;
    color: var(--gold); font-size: 0.85rem;
    opacity: 0; transition: all 0.3s;
}
.ht-service-card:hover .ht-service-arrow {
    opacity: 1;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #070A18;
    border-color: transparent;
}

/* ═══ LIGHT THEME ═══ */
[data-theme="light"] .ht-service-card { background: #FDFCF9; border-color: rgba(0,0,0,0.06); }
[data-theme="light"] .ht-service-card:hover {
    box-shadow: 0 20px 50px rgba(0,0,0,0.08), 0 0 0 1px rgba(184,148,31,0.1);
    border-color: rgba(184,148,31,0.15);
}
[data-theme="light"] .ht-service-icon { background: linear-gradient(135deg, rgba(184,148,31,0.06), rgba(45,95,168,0.04)); border-color: rgba(184,148,31,0.08); }
[data-theme="light"] .ht-service-card:hover .ht-service-icon { box-shadow: 0 8px 20px rgba(184,148,31,0.15); }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 640px) {
    .ht-service-card { padding: 1.25rem 1rem; }
    .ht-service-icon { width: 48px; height: 48px; font-size: 1.2rem; margin-bottom: 0.75rem; }
    .ht-service-title { font-size: 0.95rem; }
    .ht-service-desc { font-size: 0.82rem; -webkit-line-clamp: 2; }
    .ht-service-badge { font-size: 0.68rem; padding: 0.25rem 0.6rem; }
    .ht-service-arrow { width: 32px; height: 32px; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var serviceIcons = [
        '<i class="fas fa-code" style="color:var(--gold);"></i>',
        '<i class="fas fa-paint-brush" style="color:var(--gold);"></i>',
        '<i class="fas fa-mobile-alt" style="color:var(--gold);"></i>',
        '<i class="fas fa-cloud" style="color:var(--gold);"></i>',
        '<i class="fas fa-shield-alt" style="color:var(--gold);"></i>',
        '<i class="fas fa-rocket" style="color:var(--gold);"></i>',
        '<i class="fas fa-database" style="color:var(--gold);"></i>',
        '<i class="fas fa-chart-line" style="color:var(--gold);"></i>',
        '<i class="fas fa-cog" style="color:var(--gold);"></i>'
    ];

    axios.get(API_BASE + '/services/index?all=1')
        .then(function(response) {
            var services = response.data.data || [];
            var skeleton = document.getElementById('services-skeleton');
            var grid = document.getElementById('services-grid');
            var empty = document.getElementById('services-empty');
            var leftCol = document.getElementById('svc-col-left');
            var rightCol = document.getElementById('svc-col-right');

            skeleton.style.display = 'none';
            if (typeof _updateHeroStat === 'function') _updateHeroStat('services', services.length);
            if (services.length === 0) { empty.style.display = 'block'; return; }
            grid.style.display = 'grid';

            services.forEach(function(service, index) {
                var card = document.createElement('div');
                card.className = 'ht-service-card';
                card.style.cursor = 'pointer';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', (index * 100).toString());
                card.onclick = function() { window.location.href = '/service/' + encodeURIComponent(service.id); };

                var projectCount = (service.projects || []).length;
                var projectBadge = projectCount > 0
                    ? '<span class="ht-service-badge"><i class="fas fa-briefcase" style="font-size:0.65rem;"></i> ' + projectCount + ' Project' + (projectCount > 1 ? 's' : '') + '</span>'
                    : '';

                card.innerHTML =
                    // service.icon is admin-authored HTML (icon markup) — kept as-is; text fields escaped
                    '<div class="ht-service-icon">' + (service.icon || serviceIcons[index % serviceIcons.length]) + '</div>' +
                    '<h3 class="ht-service-title">' + esc(service.title || service.name || 'Service') + '</h3>' +
                    '<p class="ht-service-desc">' + esc(service.description || t('no_desc')) + '</p>' +
                    '<div class="ht-service-meta">' +
                        projectBadge +
                        '<div class="ht-service-arrow"><i class="fas fa-arrow-right"></i></div>' +
                    '</div>';

                // Alternate columns
                if (index % 2 === 0) {
                    leftCol.appendChild(card);
                } else {
                    rightCol.appendChild(card);
                }
            });

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load services:', error);
            if (typeof _updateHeroStat === 'function') _updateHeroStat('services', 0);
            document.getElementById('services-skeleton').style.display = 'none';
            document.getElementById('services-empty').style.display = 'block';
        });
})();
</script>
@endpush
