@extends('layouts.website')

@section('title', 'Service Details - HexaTerminal')

@section('content')
<!-- ═══ PAGE HERO ═══ -->
<section style="padding:8rem 0 2.5rem;background:var(--dark-bg-2);position:relative;overflow:hidden;">
    <div class="glow-dot" style="width:500px;height:500px;background:var(--gold);top:-200px;left:-100px;opacity:0.03;"></div>
    <div class="glow-dot" style="width:400px;height:400px;background:var(--gold-light);bottom:-100px;right:-100px;opacity:0.02;"></div>
    <div class="glow-line" style="width:100%;bottom:0;left:0;background:linear-gradient(90deg,transparent,rgba(212,175,55,0.06),transparent);"></div>

    <div class="container">
        <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;" data-aos="fade-up">
            <a href="{{ url('/') }}" style="color:var(--text-muted);text-decoration:none;transition:var(--transition-fast);" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-home" style="margin-right:0.3rem;"></i> <span data-i18n="nav_home">Home</span>
            </a>
            <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:0.55rem;"></i>
            <a href="{{ url('/') }}#services" style="color:var(--text-muted);text-decoration:none;transition:var(--transition-fast);" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-muted)'">
                <span data-i18n="nav_services">Services</span>
            </a>
            <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:0.55rem;"></i>
            <span id="breadcrumb-title" style="color:var(--gold);font-weight:600;">Loading...</span>
        </nav>
    </div>
</section>

<!-- ═══ SERVICE DETAIL ═══ -->
<section class="section" style="background:var(--dark-bg);padding-top:3rem;">
    <div class="container" style="max-width:1200px;">
        <!-- Skeleton -->
        <div id="service-skeleton" data-aos="fade-up">
            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:3rem;align-items:start;">
                <div class="skeleton" style="height:320px;border-radius:var(--radius-xl);"></div>
                <div>
                    <div class="skeleton skeleton-text" style="height:42px;width:60%;margin-bottom:1rem;"></div>
                    <div class="skeleton skeleton-text" style="height:18px;margin-bottom:0.75rem;"></div>
                    <div class="skeleton skeleton-text" style="height:18px;margin-bottom:0.75rem;"></div>
                    <div class="skeleton skeleton-text" style="height:18px;width:80%;margin-bottom:2rem;"></div>
                </div>
            </div>
            <div style="margin-top:3rem;">
                <div class="skeleton skeleton-text" style="height:32px;width:40%;margin-bottom:2rem;"></div>
                <div class="grid-3">
                    @for($i = 0; $i < 3; $i++)
                    <div class="skeleton skeleton-card"></div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Actual content -->
        <div id="service-content" style="display:none;"></div>

        <!-- Not found -->
        <div id="service-not-found" style="display:none;text-align:center;padding:5rem 0;">
            <div style="width:100px;height:100px;margin:0 auto 2rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-search" style="font-size:2.5rem;color:var(--gold);opacity:0.4;"></i>
            </div>
            <h3 style="font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:0.75rem;" data-i18n="service_not_found">Service Not Found</h3>
            <p style="color:var(--text-secondary);font-size:0.95rem;max-width:400px;margin:0 auto 2rem;" data-i18n="service_not_found_desc">
                The service you're looking for doesn't exist or may have been removed.
            </p>
            <a href="{{ url('/') }}#services" class="btn btn-primary" style="padding:0.85rem 2.25rem;">
                <span data-i18n="view_all_services">View All Services</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.svc-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 3rem;
    align-items: start;
    margin-bottom: 3.5rem;
}
.svc-detail-image-wrap {
    position: relative;
    border-radius: var(--radius-xl);
    overflow: hidden;
    border: 1px solid rgba(212,175,55,0.1);
}
.svc-detail-image-wrap::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(6,9,24,0.2), transparent 40%);
    pointer-events: none;
}
.svc-detail-image {
    width: 100%;
    height: 320px;
    object-fit: cover;
    display: block;
}
.svc-detail-placeholder {
    width: 100%;
    height: 320px;
    background: linear-gradient(135deg, rgba(212,175,55,0.08), rgba(183,134,11,0.05));
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-xl);
    border: 1px solid rgba(212,175,55,0.1);
}
.svc-detail-title {
    font-size: clamp(1.8rem, 3.5vw, 2.5rem);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.02em;
    margin-bottom: 1.25rem;
}
.svc-detail-desc {
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 2;
    margin-bottom: 1.5rem;
}
.svc-detail-stats {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.svc-stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    background: var(--card-bg);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: var(--radius-md);
}
.svc-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, rgba(212,175,55,0.1), rgba(183,134,11,0.06));
    border: 1px solid rgba(212,175,55,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gold);
    font-size: 0.9rem;
    flex-shrink: 0;
}
.svc-stat-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gold);
}
.svc-stat-label {
    font-size: 0.72rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* Projects section within service */
.svc-projects-header {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 2.5rem;
    border-top: 1px solid rgba(212,175,55,0.06);
}
.svc-projects-header .icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(212,175,55,0.1), rgba(183,134,11,0.06));
    border: 1px solid rgba(212,175,55,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Project cards */
.svc-project-card { cursor: pointer; overflow: hidden; }
.svc-project-card-img-wrap { position: relative; overflow: hidden; height: 220px; }
.svc-project-card-img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.25,0.46,0.45,0.94), filter 0.4s ease;
}
.card:hover .svc-project-card-img { transform: scale(1.08); filter: brightness(0.7); }
.svc-project-card-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(7,10,24,0.95) 0%, rgba(7,10,24,0.3) 40%, transparent 70%);
    display: flex; flex-direction: column; justify-content: flex-end; padding: 1.25rem;
    opacity: 0; transition: opacity 0.4s;
}
.card:hover .svc-project-card-overlay { opacity: 1; }
.svc-project-card-overlay .view-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.45rem 0.9rem; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.3);
    border-radius: var(--radius-sm); color: var(--gold); font-size: 0.78rem; font-weight: 600;
    width: fit-content; backdrop-filter: blur(8px);
}
.svc-project-card-body { padding: 1.25rem; }
.svc-project-card-title {
    font-size: 1.1rem; font-weight: 700; color: var(--text-primary);
    margin-bottom: 0.4rem; line-height: 1.3;
}
.svc-project-card-desc {
    color: var(--text-secondary); font-size: 0.83rem; line-height: 1.65;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    margin-bottom: 0.75rem;
}
.svc-project-card-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(212,175,55,0.08), rgba(183,134,11,0.06));
    display: flex; align-items: center; justify-content: center;
}
.svc-no-projects {
    text-align: center;
    padding: 3rem 0;
}

/* Light theme */
[data-theme="light"] .svc-detail-image-wrap { border-color: rgba(183,134,11,0.08); box-shadow: 0 2px 16px rgba(0,0,0,0.06); }
[data-theme="light"] .svc-detail-image-wrap::after { background: linear-gradient(to top, rgba(248,250,252,0.2), transparent 40%); }
[data-theme="light"] .svc-detail-placeholder { background: linear-gradient(135deg, rgba(183,134,11,0.06), rgba(212,175,55,0.03)); border-color: rgba(183,134,11,0.08); }
[data-theme="light"] .svc-stat-item { background: var(--card-bg); border-color: rgba(183,134,11,0.08); }
[data-theme="light"] .svc-stat-icon { background: linear-gradient(135deg, rgba(183,134,11,0.06), rgba(212,175,55,0.04)); border-color: rgba(183,134,11,0.1); }
[data-theme="light"] .svc-projects-header { border-top-color: rgba(183,134,11,0.08); }

@media (max-width: 768px) {
    .svc-detail-grid {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }
    .svc-detail-image, .svc-detail-placeholder {
        height: 200px;
    }
}
@media (max-width: 480px) {
    .svc-detail-image, .svc-detail-placeholder {
        height: 160px;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var serviceId = {{ $id }};

    axios.get(API_BASE + '/services/show/' + serviceId)
        .then(function(response) {
            var service = response.data.data;
            var skeleton = document.getElementById('service-skeleton');
            var content = document.getElementById('service-content');
            var notFound = document.getElementById('service-not-found');

            skeleton.style.display = 'none';

            if (!service) {
                notFound.style.display = 'block';
                return;
            }

            var rawTitle = service.title || service.name || 'Service';
            document.title = rawTitle + ' - HexaTerminal';
            document.getElementById('breadcrumb-title').textContent = rawTitle;

            content.style.display = 'block';

            var title = esc(rawTitle);
            var imageUrl = service.image_path ? escUrl(getImageUrl(service.image_path)) : '';
            var projects = service.projects || [];

            // Service image HTML
            var imageHtml;
            if (imageUrl) {
                imageHtml =
                    '<div class="svc-detail-image-wrap">' +
                        '<img src="' + imageUrl + '" alt="' + title + '" class="svc-detail-image" referrerpolicy="no-referrer" ' +
                            'onerror="this.parentElement.outerHTML=\'<div class=svc-detail-placeholder><i class=\\\'fas fa-cogs\\\' style=\\\'font-size:4rem;color:rgba(212,175,55,0.3);\\\'></i></div>\';">' +
                    '</div>';
            } else {
                imageHtml = '<div class="svc-detail-placeholder"><i class="fas fa-cogs" style="font-size:4rem;color:rgba(212,175,55,0.3);"></i></div>';
            }

            // Stats HTML
            var statsHtml =
                '<div class="svc-detail-stats">' +
                    '<div class="svc-stat-item">' +
                        '<div class="svc-stat-icon"><i class="fas fa-briefcase"></i></div>' +
                        '<div><div class="svc-stat-value">' + projects.length + '</div>' +
                        '<div class="svc-stat-label">' + t('projects') + '</div></div>' +
                    '</div>' +
                '</div>';

            // Projects grid
            var projectsHtml = '';
            if (projects.length > 0) {
                projectsHtml =
                    '<h2 class="svc-projects-header" data-aos="fade-up">' +
                        '<span class="icon-wrap"><i class="fas fa-briefcase" style="color:var(--gold);font-size:0.85rem;"></i></span>' +
                        '<span class="text-gradient-gold">' + t('projects_in') + ' ' + title + '</span>' +
                    '</h2>' +
                    '<div class="grid-3">';

                projects.forEach(function(project, index) {
                    var images = project.images || [];
                    var firstImage = images.length > 0 ? (images[0].image_path || images[0]) : '';
                    var projImageUrl = escUrl(getImageUrl(firstImage));

                    projectsHtml +=
                        '<div class="card card-gold-border svc-project-card" data-aos="fade-up" data-aos-delay="' + (index % 3 * 80) + '" onclick="window.location.href=\'/project/' + encodeURIComponent(project.id || 0) + '\'">' +
                            '<div class="svc-project-card-img-wrap">' +
                                (projImageUrl
                                    ? '<img src="' + projImageUrl + '" alt="' + esc(project.title || 'Project') + '" class="svc-project-card-img" loading="lazy" referrerpolicy="no-referrer">'
                                    : '<div class="svc-project-card-placeholder"><i class="fas fa-image" style="font-size:2.5rem;color:rgba(212,175,55,0.2);"></i></div>'
                                ) +
                                '<div class="svc-project-card-overlay">' +
                                    '<span class="view-btn"><i class="fas fa-eye"></i> ' + t('view_details') + '</span>' +
                                '</div>' +
                            '</div>' +
                            '<div class="svc-project-card-body">' +
                                '<h3 class="svc-project-card-title">' + esc(project.title || 'Untitled Project') + '</h3>' +
                                '<p class="svc-project-card-desc">' + esc(project.description || t('no_desc')) + '</p>' +
                            '</div>' +
                        '</div>';
                });

                projectsHtml += '</div>';
            } else {
                projectsHtml =
                    '<div class="svc-no-projects" data-aos="fade-up">' +
                        '<div style="width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">' +
                            '<i class="fas fa-folder-open" style="font-size:2rem;color:var(--gold);opacity:0.4;"></i>' +
                        '</div>' +
                        '<p style="font-size:1.05rem;color:var(--text-secondary);font-weight:500;" data-i18n="no_projects_yet">' + t('no_projects_yet') + '</p>' +
                        '<p style="font-size:0.85rem;color:var(--text-muted);margin-top:0.5rem;">' + t('projects_appear_soon') + '</p>' +
                    '</div>';
            }

            content.innerHTML =
                '<div class="svc-detail-grid" data-aos="fade-up">' +
                    '<div>' + imageHtml + '</div>' +
                    '<div>' +
                        '<h1 class="svc-detail-title"><span class="text-gradient-gold">' + title + '</span></h1>' +
                        '<div class="svc-detail-desc">' +
                            esc(service.description || t('no_desc')).replace(/\n/g, '<br>') +
                        '</div>' +
                        statsHtml +
                    '</div>' +
                '</div>' +
                projectsHtml +
                '<div style="margin-top:3.5rem;padding-top:2.5rem;border-top:1px solid rgba(212,175,55,0.06);display:flex;justify-content:space-between;align-items:center;" data-aos="fade-up">' +
                    '<a href="' + '{{ url("/") }}#services' + '" class="btn btn-outline" style="padding:0.8rem 1.75rem;">' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' +
                        '<span data-i18n="all_services">' + t('all_services') + '</span>' +
                    '</a>' +
                    '<a href="#contact" class="btn btn-primary" style="padding:0.8rem 1.75rem;">' +
                        '<span data-i18n="start_project">' + t('start_project') + '</span>' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>' +
                    '</a>' +
                '</div>';

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load service:', error);
            document.getElementById('service-skeleton').style.display = 'none';
            document.getElementById('service-not-found').style.display = 'block';
        });
})();
</script>
@endpush

