<!-- ═══ TEAM SECTION — HexaTerminal Style ═══ -->
<section id="team" class="section" style="background: var(--dark-bg); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:300px;height:300px;background:var(--gold);top:50%;left:-80px;"></div>

    <div class="container">
        <div class="ht-split-layout">
            <!-- Left: Sticky sidebar -->
            <div class="ht-split-left" data-aos="fade-right">
                <div class="ht-sticky-sidebar">
                    <div class="ht-section-title">
                        <span class="section-badge"><i class="fas fa-crown"></i> <span data-i18n="section_team_badge">Our People</span></span>
                        <h2 class="ht-title" data-i18n="section_team_title">Meet Our Team</h2>
                    </div>
                    <div class="ht-sidebar-body">
                        <p data-i18n="section_team_subtitle">The talented professionals driving our innovation and success forward with passion and expertise.</p>
                        <div class="ht-rotate-images">
                            <div class="ht-rotate-bg"></div>
                            <div class="ht-rotate-inner">
                                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="ht-decorative-svg">
                                    <defs>
                                        <linearGradient id="teamGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:var(--gold);stop-opacity:0.4"/>
                                            <stop offset="100%" style="stop-color:var(--secondary);stop-opacity:0.4"/>
                                        </linearGradient>
                                    </defs>
                                    <polygon points="100,10 180,50 180,130 100,170 20,130 20,50" fill="none" stroke="url(#teamGrad)" stroke-width="1.5" class="ht-hex-rotate"/>
                                    <polygon points="100,35 155,65 155,125 100,155 45,125 45,65" fill="rgba(212,175,55,0.03)" stroke="url(#teamGrad)" stroke-width="1"/>
                                    <text x="100" y="100" font-family="'Courier New',monospace" font-weight="700" font-size="32" fill="url(#teamGrad)" text-anchor="middle" dominant-baseline="middle" opacity="0.7">&lt;/&gt;</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Staggered team cards -->
            <div class="ht-split-right">
                <!-- Skeleton -->
                <div id="team-skeleton" class="ht-stagger-grid">
                    <div class="ht-stagger-col">
                        <div class="skeleton" style="height:300px;border-radius:10px;"></div>
                        <div class="skeleton" style="height:300px;border-radius:10px;"></div>
                    </div>
                    <div class="ht-stagger-col ht-stagger-offset">
                        <div class="skeleton" style="height:300px;border-radius:10px;"></div>
                        <div class="skeleton" style="height:300px;border-radius:10px;"></div>
                    </div>
                </div>

                <!-- Actual content -->
                <div id="team-grid" class="ht-stagger-grid" style="display:none;">
                    <div id="team-col-left" class="ht-stagger-col"></div>
                    <div id="team-col-right" class="ht-stagger-col ht-stagger-offset"></div>
                </div>

                <!-- Empty state -->
                <div id="team-empty" style="display:none;text-align:center;padding:4rem 0;">
                    <div style="width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-users" style="font-size:2rem;color:var(--gold);"></i>
                        </div>
                    <p style="font-size:1.1rem;color:var(--text-secondary);font-weight:500;">Team info coming soon!</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* ═══ SPLIT LAYOUT — Shared ═══ */
.ht-split-layout {
    display: grid;
    grid-template-columns: 5fr 7fr;
    gap: 3rem;
    align-items: start;
}
.ht-split-left { position: relative; }
.ht-sticky-sidebar { position: sticky; top: 100px; }
.ht-section-title { margin-bottom: 1.5rem; }
.ht-title {
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    font-weight: 900;
    color: var(--text-primary);
    line-height: 1.15;
    margin-top: 0.75rem;
    background: linear-gradient(135deg, var(--text-primary) 40%, var(--gold));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.ht-sidebar-body { padding-left: 0; }
.ht-sidebar-body p {
    font-size: 0.95rem; line-height: 1.8; color: var(--text-secondary);
    margin-bottom: 2rem;
}

/* Decorative rotating element */
.ht-rotate-images { position: relative; width: 200px; height: 200px; }
.ht-rotate-bg {
    position: absolute; inset: 0;
    background: radial-gradient(circle, rgba(212,175,55,0.06) 0%, transparent 70%);
    border-radius: 50%;
}
.ht-rotate-inner { position: relative; z-index: 1; }
.ht-decorative-svg { width: 200px; height: 200px; }
.ht-hex-rotate { animation: rotate-slow 30s linear infinite; transform-origin: center; }

/* ═══ STAGGER GRID ═══ */
.ht-stagger-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}
.ht-stagger-col {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.ht-stagger-offset { margin-top: 60px; }

/* ═══ TEAM CARD — Compact ═══ */
.ht-team-card {
    position: relative;
    z-index: 1;
    border-radius: 10px;
    overflow: hidden;
    background: var(--card-bg-solid);
    border: 1px solid var(--card-border);
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.ht-team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.18);
    border-color: rgba(212,175,55,0.18);
}

/* Image area */
.ht-team-img {
    position: relative;
    overflow: hidden;
    border-radius: 10px 10px 0 0;
    aspect-ratio: 5/6;
}
.ht-team-img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.ht-team-card:hover .ht-team-img img {
    transform: scale(1.05);
}

/* Gradient overlay */
.ht-team-img .ht-image-overlay {
    position: absolute;
    left: 0; right: 0; bottom: 0; top: 40%;
    z-index: 1;
    opacity: 0;
    transition: opacity 0.3s ease;
    background: linear-gradient(180deg, transparent 10%, rgba(7,10,24,0.8) 90%);
}
.ht-team-card:hover .ht-image-overlay { opacity: 1; }

/* Social share floating button */
.ht-team-social {
    z-index: 99;
    position: absolute;
    bottom: -28px;
    right: 16px;
    transition: all 0.4s ease-in-out;
}
.ht-share-icon {
    height: 56px; width: 56px;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(212,175,55,0.3);
    transition: all 0.4s ease;
    position: relative;
}
.ht-share-icon svg {
    width: 20px; height: 20px; fill: #070A18;
}
.ht-share-icon:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(212,175,55,0.45); }

/* Social links dropdown */
.ht-share-icon ul {
    position: absolute;
    right: 0; bottom: 100%;
    list-style: none;
    margin: 0 0 10px; padding: 0;
    visibility: hidden; opacity: 0;
    display: flex;
    flex-direction: column-reverse;
    gap: 8px;
    transition: all 0.4s ease-in-out;
}
.ht-team-social:hover { padding-top: 15px; }
.ht-team-social:hover .ht-share-icon ul {
    visibility: visible; opacity: 1;
}
.ht-share-icon ul li a {
    padding: 8px 14px;
    display: flex; align-items: center; justify-content: center; gap: 0;
    background: rgba(255,255,255,0.97);
    border-radius: 100px;
    color: #555;
    overflow: hidden;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 0.85rem;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.ht-share-icon ul li a i,
.ht-share-icon ul li a svg {
    width: 18px; height: 18px; display: inline-block;
    flex-shrink: 0;
    fill: currentColor;
}
.ht-share-icon ul li a span {
    font-size: 0; line-height: 26px; font-weight: 600;
    transition: all 0.3s ease-in-out;
}
.ht-share-icon ul li a:hover {
    gap: 8px; color: var(--gold); background: #fff;
    box-shadow: 0 4px 20px rgba(212,175,55,0.15);
}
.ht-share-icon ul li a:hover span { font-size: 0.85rem; }

/* Team info below image */
.ht-team-info {
    padding: 1rem 1rem 0.85rem;
}
.ht-team-title {
    font-size: 1.05rem; font-weight: 800;
    color: var(--text-primary);
    display: block; margin-bottom: 0.2rem;
    text-decoration: none;
    transition: color 0.3s;
}
.ht-team-title:hover { color: var(--gold); }
.ht-team-destination {
    color: var(--text-secondary); font-size: 0.85rem;
    display: block; margin-bottom: 0.4rem;
}
.ht-team-spec {
    font-size: 0.78rem; color: var(--gold); opacity: 0.8;
    display: flex; align-items: center; gap: 0.3rem;
}

/* Initials fallback */
.ht-team-initials {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(74,143,231,0.1));
    display: flex; align-items: center; justify-content: center;
    font-size: 4rem; font-weight: 800;
    color: var(--gold); opacity: 0.6;
}

/* ═══ LIGHT THEME ═══ */
[data-theme="light"] .ht-team-card { background: #FDFCF9; border-color: rgba(0,0,0,0.06); }
[data-theme="light"] .ht-team-card:hover {
    box-shadow: 0 20px 50px rgba(0,0,0,0.08), 0 0 0 1px rgba(184,148,31,0.1);
    border-color: rgba(184,148,31,0.15);
}
[data-theme="light"] .ht-image-overlay {
    background: linear-gradient(180deg, rgba(255,255,255,0) 10%, rgba(0,0,0,0.7) 90%);
}
[data-theme="light"] .ht-share-icon { box-shadow: 0 4px 16px rgba(184,148,31,0.2); }
[data-theme="light"] .ht-rotate-bg { background: radial-gradient(circle, rgba(184,148,31,0.04) 0%, transparent 70%); }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1024px) {
    .ht-split-layout { grid-template-columns: 1fr; gap: 2rem; }
    .ht-sticky-sidebar { position: static; text-align: center; }
    .ht-rotate-images { display: none; }
    .ht-sidebar-body { padding-left: 0; }
    .ht-sidebar-body p { max-width: 600px; margin: 0 auto 1.5rem; }
    .ht-stagger-offset { margin-top: 0; }
    .ht-title { font-size: clamp(1.5rem, 5vw, 2rem); }
}
@media (max-width: 640px) {
    .ht-stagger-grid { grid-template-columns: 1fr; gap: 14px; }
    .ht-team-img { aspect-ratio: 4/3; }
    .ht-team-info { padding: 0.85rem; }
    .ht-team-title { font-size: 0.95rem; }
    .ht-team-destination { font-size: 0.8rem; }
    .ht-team-spec { font-size: 0.72rem; }
    .ht-share-icon { height: 48px; width: 48px; }
    .ht-team-social { bottom: -22px; right: 12px; }
}
@media (max-width: 400px) {
    .ht-team-img { aspect-ratio: 1/1; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    axios.get(API_BASE + '/teams/index')
        .then(function(response) {
            var teams = response.data.data || [];
            var skeleton = document.getElementById('team-skeleton');
            var grid = document.getElementById('team-grid');
            var empty = document.getElementById('team-empty');
            var leftCol = document.getElementById('team-col-left');
            var rightCol = document.getElementById('team-col-right');

            skeleton.style.display = 'none';
            if (typeof _updateHeroStat === 'function') _updateHeroStat('team', teams.length);
            if (teams.length === 0) { empty.style.display = 'block'; return; }
            grid.style.display = 'grid';

            teams.forEach(function(member, index) {
                var photoUrl = member.photo ? escUrl(getImageUrl(member.photo)) : '';
                var fullName = esc(((member.first_name || '') + ' ' + (member.last_name || '')).trim());
                var initials = esc((member.first_name || 'U').charAt(0).toUpperCase());
                var memberId = encodeURIComponent(member.id);

                // Build social links
                var socialLinks = '';
                if (member.github_url) {
                    socialLinks += '<li><a href="' + escUrl(member.github_url) + '" target="_blank" rel="noopener" onclick="event.stopPropagation();">' +
                        '<span>GitHub</span>' +
                        '<svg viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>' +
                        '</a></li>';
                }
                if (member.linkedin_url) {
                    socialLinks += '<li><a href="' + escUrl(member.linkedin_url) + '" target="_blank" rel="noopener" onclick="event.stopPropagation();">' +
                        '<span>LinkedIn</span>' +
                        '<svg viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>' +
                        '</a></li>';
                }
                socialLinks += '<li><a href="/team/' + memberId + '" onclick="event.stopPropagation();">' +
                    '<span>' + t('view_profile') + '</span>' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>' +
                    '</a></li>';
                if (member.cv_file) {
                    socialLinks += '<li><a href="' + escUrl(getImageUrl(member.cv_file)) + '" target="_blank" download onclick="event.stopPropagation();">' +
                        '<span>CV</span>' +
                        '<i class="fas fa-file-pdf" style="font-size:16px;"></i>' +
                        '</a></li>';
                }

                var card = document.createElement('div');
                card.className = 'ht-team-card';
                card.style.cursor = 'pointer';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', (index * 100).toString());
                card.onclick = function() { window.location.href = '/team/' + memberId; };

                var photoHtml;
                if (photoUrl) {
                    photoHtml = '<img src="' + photoUrl + '" alt="' + fullName + '" loading="lazy" ' +
                        'onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';" referrerpolicy="no-referrer">' +
                        '<div class="ht-team-initials" style="display:none;">' + initials + '</div>';
                } else {
                    photoHtml = '<div class="ht-team-initials">' + initials + '</div>';
                }

                card.innerHTML =
                    '<div class="ht-team-img">' +
                        photoHtml +
                        '<div class="ht-image-overlay"></div>' +
                        '<div class="ht-team-social">' +
                            '<div class="ht-share-icon">' +
                                '<svg viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>' +
                                '<ul>' + socialLinks + '</ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="ht-team-info">' +
                        '<a href="/team/' + memberId + '" class="ht-team-title" onclick="event.stopPropagation();">' + (fullName || 'Team Member') + '</a>' +
                        '<span class="ht-team-destination">' + esc(member.position || 'Team Member') + '</span>' +
                        (member.specialization ? '<span class="ht-team-spec"><i class="fas fa-star" style="font-size:0.65rem;"></i> ' + esc(member.specialization) + '</span>' : '') +
                    '</div>';

                // Alternate between columns
                if (index % 2 === 0) {
                    leftCol.appendChild(card);
                } else {
                    rightCol.appendChild(card);
                }
            });

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load team:', error);
            if (typeof _updateHeroStat === 'function') _updateHeroStat('team', 0);
            document.getElementById('team-skeleton').style.display = 'none';
            document.getElementById('team-empty').style.display = 'block';
        });
})();
</script>
@endpush
