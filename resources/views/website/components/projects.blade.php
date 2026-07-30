<!-- ═══ PROJECTS SECTION — HexaTerminal Style ═══ -->
<section id="projects" class="section" style="background: var(--dark-bg-2); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:300px;height:300px;background:var(--gold);top:-80px;right:-80px;"></div>

    <div class="container">
        <div class="ht-split-layout">
            <!-- Left: Sticky sidebar -->
            <div class="ht-split-left" data-aos="fade-right">
                <div class="ht-sticky-sidebar">
                    <div class="ht-section-title">
                        <span class="section-badge"><i class="fas fa-trophy"></i> <span data-i18n="section_projects_badge">Our Work</span></span>
                        <h2 class="ht-title" data-i18n="section_projects_title">Case Studies</h2>
                    </div>
                    <div class="ht-sidebar-body">
                        <p data-i18n="section_projects_subtitle">Explore our innovative projects that deliver exceptional solutions across various industries.</p>
                        <div class="ht-rotate-images">
                            <div class="ht-rotate-bg"></div>
                            <div class="ht-rotate-inner">
                                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="ht-decorative-svg">
                                    <defs>
                                        <linearGradient id="projGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:var(--gold);stop-opacity:0.4"/>
                                            <stop offset="100%" style="stop-color:var(--secondary);stop-opacity:0.4"/>
                                        </linearGradient>
                                    </defs>
                                    <polygon points="100,10 180,50 180,130 100,170 20,130 20,50" fill="none" stroke="url(#projGrad)" stroke-width="1.5" class="ht-hex-rotate"/>
                                    <polygon points="100,35 155,65 155,125 100,155 45,125 45,65" fill="rgba(212,175,55,0.03)" stroke="url(#projGrad)" stroke-width="1"/>
                                    <text x="100" y="96" font-family="'Courier New',monospace" font-weight="700" font-size="28" fill="url(#projGrad)" text-anchor="middle" dominant-baseline="middle" opacity="0.7">{ }</text>
                                </svg>
                            </div>
                        </div>

                        <a href="{{ route('projects') }}" class="ht-view-all-link" id="projects-view-all" style="display:none;">
                            <span data-i18n="view_all_projects">View All Projects</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Staggered project cards -->
            <div class="ht-split-right">
                <!-- Skeleton -->
                <div id="projects-skeleton" class="ht-stagger-grid">
                    <div class="ht-stagger-col">
                        <div class="skeleton" style="height:260px;border-radius:10px;"></div>
                        <div class="skeleton" style="height:260px;border-radius:10px;"></div>
                    </div>
                    <div class="ht-stagger-col ht-stagger-offset-reverse">
                        <div class="skeleton" style="height:260px;border-radius:10px;"></div>
                        <div class="skeleton" style="height:260px;border-radius:10px;"></div>
                    </div>
                </div>

                <!-- Actual content -->
                <div id="projects-grid" class="ht-stagger-grid" style="display:none;">
                    <div id="proj-col-left" class="ht-stagger-col"></div>
                    <div id="proj-col-right" class="ht-stagger-col ht-stagger-offset-reverse"></div>
                </div>

                <!-- Empty state -->
                <div id="projects-empty" style="display:none;text-align:center;padding:4rem 0;">
                    <div style="width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-briefcase" style="font-size:2rem;color:var(--gold);"></i>
                    </div>
                    <p style="font-size:1.1rem;color:var(--text-secondary);font-weight:500;">Projects coming soon!</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* ═══ PROJECT CARD — Compact ═══ */
.ht-project-card {
    position: relative;
    z-index: 1;
    border-radius: 10px;
    overflow: hidden;
    background: var(--card-bg-solid);
    border: 1px solid var(--card-border);
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.ht-project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.18);
    border-color: rgba(212,175,55,0.18);
}

/* Project image */
.ht-project-img {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/10;
}
.ht-project-img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.ht-project-card:hover .ht-project-img img {
    transform: scale(1.05);
}

/* Overlay gradient */
.ht-project-img .ht-image-overlay {
    position: absolute;
    left: 0; right: 0; bottom: 0; top: 40%;
    z-index: 1; opacity: 0;
    transition: opacity 0.3s ease;
    background: linear-gradient(180deg, transparent 10%, rgba(7,10,24,0.8) 90%);
}
.ht-project-card:hover .ht-project-img .ht-image-overlay { opacity: 1; }

/* View button on hover */
.ht-project-view-btn {
    position: absolute;
    bottom: 12px; left: 50%; transform: translateX(-50%) translateY(16px);
    z-index: 2;
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.5rem 1.25rem;
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: #070A18; font-weight: 700; font-size: 0.8rem;
    border-radius: 100px;
    text-decoration: none;
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.ht-project-card:hover .ht-project-view-btn {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* Project info */
.ht-project-info {
    padding: 1rem 1rem 0.85rem;
}
.ht-project-title {
    font-size: 1rem; font-weight: 800;
    color: var(--text-primary);
    display: block; margin-bottom: 0.3rem;
    text-decoration: none;
    transition: color 0.3s;
    line-height: 1.3;
}
.ht-project-title:hover { color: var(--gold); }
.ht-project-desc {
    color: var(--text-secondary); font-size: 0.82rem;
    line-height: 1.6; margin-bottom: 0.6rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.ht-project-tags {
    display: flex; flex-wrap: wrap; gap: 0.4rem;
}
.ht-project-tag {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.25rem 0.7rem;
    background: linear-gradient(135deg, rgba(212,175,55,0.08), rgba(74,143,231,0.05));
    border: 1px solid rgba(212,175,55,0.1);
    color: var(--gold); border-radius: 999px;
    font-size: 0.72rem; font-weight: 600; letter-spacing: 0.3px;
}

/* Placeholder */
.ht-project-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(212,175,55,0.08), rgba(74,143,231,0.08));
    display: flex; align-items: center; justify-content: center;
}

/* Reverse stagger offset */
.ht-stagger-offset-reverse { margin-top: -50px; }

/* View all link in sidebar */
.ht-view-all-link {
    display: inline-flex; align-items: center; gap: 0.6rem;
    padding: 0.85rem 2rem;
    background: transparent;
    border: 1.5px solid rgba(212,175,55,0.25);
    color: var(--gold);
    border-radius: 100px;
    font-weight: 700; font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.4s ease;
    margin-top: 2rem;
}
.ht-view-all-link:hover {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #070A18;
    border-color: var(--gold);
    box-shadow: 0 4px 20px rgba(212,175,55,0.25);
}

/* ═══ LIGHT THEME ═══ */
[data-theme="light"] .ht-project-card { background: #FDFCF9; border-color: rgba(0,0,0,0.06); }
[data-theme="light"] .ht-project-card:hover {
    box-shadow: 0 20px 50px rgba(0,0,0,0.08), 0 0 0 1px rgba(184,148,31,0.1);
    border-color: rgba(184,148,31,0.15);
}
[data-theme="light"] .ht-project-img .ht-image-overlay {
    background: linear-gradient(180deg, rgba(255,255,255,0) 10%, rgba(0,0,0,0.7) 90%);
}
[data-theme="light"] .ht-view-all-link {
    border-color: rgba(184,148,31,0.2);
}
[data-theme="light"] .ht-view-all-link:hover {
    color: #fff; box-shadow: 0 4px 20px rgba(184,148,31,0.15);
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1024px) {
    .ht-stagger-offset-reverse { margin-top: 0; }
}
@media (max-width: 640px) {
    .ht-stagger-grid { grid-template-columns: 1fr; gap: 14px; }
    .ht-project-img { aspect-ratio: 16/9; }
    .ht-project-info { padding: 0.85rem; }
    .ht-project-title { font-size: 0.92rem; }
    .ht-project-desc { font-size: 0.78rem; -webkit-line-clamp: 2; }
    .ht-project-tag { font-size: 0.68rem; padding: 0.2rem 0.55rem; }
    .ht-project-view-btn { font-size: 0.75rem; padding: 0.4rem 1rem; }
    .ht-view-all-link { padding: 0.7rem 1.5rem; font-size: 0.82rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    axios.get(API_BASE + '/projects/index?all=4')
        .then(function(response) {
            var allProjects = response.data.data || [];
            var skeleton = document.getElementById('projects-skeleton');
            var grid = document.getElementById('projects-grid');
            var empty = document.getElementById('projects-empty');
            var viewAll = document.getElementById('projects-view-all');
            var leftCol = document.getElementById('proj-col-left');
            var rightCol = document.getElementById('proj-col-right');

            allProjects.sort(function(a, b) {
                return new Date(b.updated_at || b.created_at || 0) - new Date(a.updated_at || a.created_at || 0);
            });

            var projects = allProjects.slice(0, 6);
            skeleton.style.display = 'none';
            if (typeof _updateHeroStat === 'function') _updateHeroStat('projects', allProjects.length);

            if (projects.length === 0) { empty.style.display = 'block'; return; }

            grid.style.display = 'grid';
            if (allProjects.length > 6) viewAll.style.display = 'inline-flex';

            projects.forEach(function(project, index) {
                var images = project.images || [];
                var firstImage = images.length > 0 ? (images[0].image_path || images[0]) : '';
                var imageUrl = escUrl(getImageUrl(firstImage));
                var serviceTitle = project.service ? (project.service.title || project.service.name) : '';
                var projectId = encodeURIComponent(project.id || 0);

                var card = document.createElement('div');
                card.className = 'ht-project-card';
                card.style.cursor = 'pointer';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', (index * 100).toString());
                card.onclick = function() { window.location.href = '/project/' + projectId; };

                var imageHtml;
                if (imageUrl) {
                    imageHtml = '<img src="' + imageUrl + '" alt="' + esc(project.title || 'Project') + '" loading="lazy">';
                } else {
                    imageHtml = '<div class="ht-project-placeholder"><i class="fas fa-image" style="font-size:3rem;color:rgba(212,175,55,0.2);"></i></div>';
                }

                var tagsHtml = '';
                if (serviceTitle) {
                    tagsHtml += '<span class="ht-project-tag"><i class="fas fa-cog" style="font-size:0.6rem;"></i> ' + esc(serviceTitle) + '</span>';
                }
                if (project.category) {
                    tagsHtml += '<span class="ht-project-tag"><i class="fas fa-tag" style="font-size:0.6rem;"></i> ' + esc(project.category) + '</span>';
                }

                card.innerHTML =
                    '<div class="ht-project-img">' +
                        imageHtml +
                        '<div class="ht-image-overlay"></div>' +
                        '<a href="/project/' + projectId + '" class="ht-project-view-btn" onclick="event.stopPropagation();">' +
                            '<i class="fas fa-eye"></i> ' + t('view_details') +
                        '</a>' +
                    '</div>' +
                    '<div class="ht-project-info">' +
                        '<a href="/project/' + projectId + '" class="ht-project-title" onclick="event.stopPropagation();">' + esc(project.title || 'Untitled Project') + '</a>' +
                        '<p class="ht-project-desc">' + esc(project.description || t('no_desc')) + '</p>' +
                        (tagsHtml ? '<div class="ht-project-tags">' + tagsHtml + '</div>' : '') +
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
            console.error('Failed to load projects:', error);
            if (typeof _updateHeroStat === 'function') _updateHeroStat('projects', 0);
            document.getElementById('projects-skeleton').style.display = 'none';
            document.getElementById('projects-empty').style.display = 'block';
        });
})();
</script>
@endpush
