@extends('layouts.website')

@section('title', 'Project Details - HexaTerminal')

@section('content')
<!-- ═══ PAGE HERO ═══ -->
<section style="padding:8rem 0 2.5rem;background:var(--dark-bg-2);position:relative;overflow:hidden;">
    <div class="glow-dot" style="width:500px;height:500px;background:var(--gold);top:-200px;left:-100px;opacity:0.03;"></div>
    <div class="glow-line" style="width:100%;bottom:0;left:0;background:linear-gradient(90deg,transparent,rgba(212,175,55,0.06),transparent);"></div>

    <div class="container">
        <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;" data-aos="fade-up">
            <a href="{{ url('/') }}" style="color:var(--text-muted);text-decoration:none;transition:var(--transition-fast);" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-home" style="margin-right:0.3rem;"></i> <span data-i18n="nav_home">Home</span>
            </a>
            <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:0.55rem;"></i>
            <a href="{{ route('projects') }}" style="color:var(--text-muted);text-decoration:none;transition:var(--transition-fast);" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-muted)'">
                <span data-i18n="nav_projects">Projects</span>
            </a>
            <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:0.55rem;"></i>
            <span id="breadcrumb-title" style="color:var(--gold);font-weight:600;">Loading...</span>
        </nav>
    </div>
</section>

<!-- ═══ PROJECT DETAIL ═══ -->
<section class="section" style="background:var(--dark-bg);padding-top:3rem;">
    <div class="container" style="max-width:1200px;">
        <!-- Skeleton -->
        <div id="project-skeleton" data-aos="fade-up">
            <div class="skeleton" style="height:480px;border-radius:var(--radius-xl);margin-bottom:2rem;"></div>
            <div style="display:flex;gap:0.75rem;margin-bottom:2rem;">
                @for($i = 0; $i < 4; $i++)
                <div class="skeleton" style="width:100px;height:80px;border-radius:var(--radius-sm);"></div>
                @endfor
            </div>
            <div class="skeleton skeleton-text" style="height:42px;width:60%;margin-bottom:1.5rem;"></div>
            <div class="skeleton skeleton-text" style="height:18px;margin-bottom:0.75rem;"></div>
            <div class="skeleton skeleton-text" style="height:18px;margin-bottom:0.75rem;"></div>
            <div class="skeleton skeleton-text" style="height:18px;width:80%;"></div>
        </div>

        <!-- Actual content -->
        <div id="project-content" style="display:none;"></div>

        <!-- Not found -->
        <div id="project-not-found" style="display:none;text-align:center;padding:5rem 0;">
            <div style="width:100px;height:100px;margin:0 auto 2rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-search" style="font-size:2.5rem;color:var(--gold);opacity:0.4;"></i>
            </div>
            <h3 style="font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:0.75rem;" data-i18n="project_not_found">Project Not Found</h3>
            <p style="color:var(--text-secondary);font-size:0.95rem;max-width:400px;margin:0 auto 2rem;" data-i18n="project_not_found_desc">
                The project you're looking for doesn't exist or may have been removed.
            </p>
            <a href="{{ route('projects') }}" class="btn btn-primary" style="padding:0.85rem 2.25rem;">
                <span data-i18n="browse_all_projects">Browse All Projects</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.detail-hero-image {
    width: 100%; height: 480px; object-fit: cover; display: block;
    border-radius: var(--radius-xl); cursor: pointer;
    transition: transform 0.5s ease;
}
.detail-hero-wrap {
    position: relative; border-radius: var(--radius-xl); overflow: hidden;
    border: 1px solid rgba(212,175,55,0.1); margin-bottom: 1.5rem;
}
.detail-hero-wrap::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(7,10,24,0.3), transparent 30%);
    pointer-events: none;
}
.detail-gallery {
    display: flex; gap: 0.75rem; overflow-x: auto; padding-bottom: 0.5rem;
    margin-bottom: 2.5rem; scrollbar-width: thin;
    scrollbar-color: var(--gold) transparent;
}
.detail-gallery::-webkit-scrollbar { height: 4px; }
.detail-gallery::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 2px; }
.detail-thumb {
    width: 100px; height: 75px; border-radius: var(--radius-sm); object-fit: cover;
    cursor: pointer; border: 2px solid transparent; opacity: 0.5;
    transition: all 0.3s ease; flex-shrink: 0;
}
.detail-thumb:hover, .detail-thumb.active { opacity: 1; border-color: var(--gold); }
.detail-meta {
    display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem;
}
.detail-meta-item {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.6rem 1.15rem; background: var(--card-bg);
    border: 1px solid rgba(212,175,55,0.08); border-radius: var(--radius-md);
    font-size: 0.82rem; color: var(--text-secondary);
}
.detail-meta-item i { color: var(--gold); font-size: 0.8rem; }
.detail-title {
    font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 800;
    line-height: 1.2; letter-spacing: -0.02em;
    margin-bottom: 1.5rem;
}
.detail-description {
    color: var(--text-secondary); font-size: 1rem; line-height: 2;
    margin-bottom: 2.5rem;
}
.features-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 0.85rem;
}
.feature-item {
    display: flex; align-items: center; gap: 0.85rem;
    padding: 1rem 1.25rem; background: var(--card-bg);
    border: 1px solid rgba(212,175,55,0.06); border-radius: var(--radius-md);
    transition: var(--transition-fast);
}
.feature-item:hover { border-color: rgba(212,175,55,0.18); background: rgba(212,175,55,0.04); }
.feature-check {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, rgba(212,175,55,0.12), rgba(183,134,11,0.06));
    border: 1px solid rgba(212,175,55,0.2);
    display: flex; align-items: center; justify-content: center;
    color: var(--gold); font-size: 0.7rem;
}
.feature-text { color: var(--text-secondary); font-size: 0.88rem; font-weight: 500; }
.detail-nav {
    margin-top: 3.5rem; padding-top: 2.5rem;
    border-top: 1px solid rgba(212,175,55,0.06);
    display: flex; justify-content: space-between; align-items: center;
}
.service-info-card {
    margin-top: 2.5rem;
    padding: 1.75rem;
    background: var(--card-bg);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: var(--radius-lg);
    transition: var(--transition);
}
.service-info-link:hover .service-info-card,
.service-info-card:hover {
    border-color: rgba(212,175,55,0.25);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15), 0 0 20px rgba(212,175,55,0.04);
}

/* Light theme */
[data-theme="light"] .detail-hero-wrap { border-color: rgba(183,134,11,0.08); box-shadow: 0 2px 16px rgba(0,0,0,0.06); }
[data-theme="light"] .detail-hero-wrap::after { background: linear-gradient(to top, rgba(248,250,252,0.3), transparent 30%); }
[data-theme="light"] .detail-thumb { border-color: transparent; }
[data-theme="light"] .detail-thumb:hover, [data-theme="light"] .detail-thumb.active { border-color: var(--gold); }
[data-theme="light"] .detail-meta-item { background: var(--card-bg); border-color: rgba(183,134,11,0.06); }
[data-theme="light"] .feature-item { background: var(--card-bg); border-color: rgba(183,134,11,0.06); }
[data-theme="light"] .feature-item:hover { border-color: rgba(183,134,11,0.15); background: rgba(183,134,11,0.02); }
[data-theme="light"] .feature-check { background: linear-gradient(135deg, rgba(183,134,11,0.08), rgba(212,175,55,0.04)); border-color: rgba(183,134,11,0.15); }
[data-theme="light"] .service-info-card { background: var(--card-bg); border-color: rgba(183,134,11,0.08); }

@media (max-width: 768px) {
    .detail-hero-image { height: 240px; }
    .detail-nav { flex-direction: column; gap: 1rem; align-items: stretch; }
    .detail-nav .btn { width: 100%; justify-content: center; }
}
@media (max-width: 480px) {
    .detail-hero-image { height: 180px; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var projectId = {{ $id }};

    axios.get(API_BASE + '/projects/show/' + projectId)
        .then(function(response) {
            var project = response.data.data;
            var skeleton = document.getElementById('project-skeleton');
            var content = document.getElementById('project-content');
            var notFound = document.getElementById('project-not-found');

            skeleton.style.display = 'none';

            if (!project) {
                notFound.style.display = 'block';
                return;
            }

            document.title = (project.title || 'Project') + ' - HexaTerminal';
            document.getElementById('breadcrumb-title').textContent = project.title || 'Project';

            content.style.display = 'block';

            var images = project.images || [];
            var mainImageUrl = images.length > 0 ? escUrl(getImageUrl(images[0].image_path || images[0])) : '';

            // Gallery
            var galleryHtml = '';
            if (images.length > 1) {
                galleryHtml = '<div class="detail-gallery" data-aos="fade-up" data-aos-delay="100">';
                for (var i = 0; i < images.length; i++) {
                    var thumbUrl = escUrl(getImageUrl(images[i].image_path || images[i]));
                    galleryHtml += '<img src="' + thumbUrl + '" alt="Image ' + (i + 1) + '" ' +
                        'class="detail-thumb' + (i === 0 ? ' active' : '') + '" loading="lazy" ' +
                        'onclick="switchProjectImage(this, \'' + thumbUrl.replace(/'/g, '') + '\')">';
                }
                galleryHtml += '</div>';
            }

            // Meta info
            var metaHtml = '<div class="detail-meta" data-aos="fade-up" data-aos-delay="150">';
            if (project.service) {
                metaHtml += '<a href="/service/' + encodeURIComponent(project.service.id) + '" class="detail-meta-item" style="text-decoration:none;cursor:pointer;transition:var(--transition-fast);" onmouseover="this.style.borderColor=\'var(--gold)\'" onmouseout="this.style.borderColor=\'rgba(212,175,55,0.08)\'">' +
                    '<i class="fas fa-cogs"></i> ' + esc(project.service.title || project.service.name || 'Service') + '</a>';
            }
            if (project.category) {
                metaHtml += '<div class="detail-meta-item"><i class="fas fa-tag"></i> ' + esc(project.category) + '</div>';
            }
            if (project.created_at) {
                var date = new Date(project.created_at);
                metaHtml += '<div class="detail-meta-item"><i class="fas fa-calendar"></i> ' +
                    date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) + '</div>';
            }
            if (images.length > 0) {
                metaHtml += '<div class="detail-meta-item"><i class="fas fa-images"></i> ' + images.length + ' ' + t('images') + '</div>';
            }
            if (project.features && project.features.length > 0) {
                metaHtml += '<div class="detail-meta-item"><i class="fas fa-star"></i> ' + project.features.length + ' ' + t('features') + '</div>';
            }
            metaHtml += '</div>';

            // Service info card
            var serviceInfoHtml = '';
            if (project.service) {
                var svc = project.service;
                var svcImageUrl = svc.image_path ? escUrl(getImageUrl(svc.image_path)) : '';
                serviceInfoHtml =
                    '<div class="service-info-card" data-aos="fade-up" data-aos-delay="280">' +
                        '<h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;display:flex;align-items:center;gap:0.75rem;">' +
                            '<span style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,rgba(212,175,55,0.1),rgba(183,134,11,0.06));border:1px solid rgba(212,175,55,0.15);display:flex;align-items:center;justify-content:center;">' +
                                '<i class="fas fa-cogs" style="color:var(--gold);font-size:0.85rem;"></i>' +
                            '</span> <span data-i18n="related_service">' + t('related_service') + '</span>' +
                        '</h2>' +
                        '<a href="/service/' + encodeURIComponent(svc.id) + '" class="service-info-link" style="text-decoration:none;">' +
                            '<div style="display:flex;gap:1.25rem;align-items:center;">' +
                                (svcImageUrl
                                    ? '<img src="' + svcImageUrl + '" alt="' + esc(svc.title || 'Service') + '" style="width:80px;height:80px;border-radius:var(--radius-md);object-fit:cover;border:1px solid rgba(212,175,55,0.1);flex-shrink:0;" referrerpolicy="no-referrer" onerror="this.style.display=\'none\'">'
                                    : '<div style="width:80px;height:80px;border-radius:var(--radius-md);background:linear-gradient(135deg,rgba(212,175,55,0.08),rgba(183,134,11,0.05));border:1px solid rgba(212,175,55,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-cogs" style="font-size:1.5rem;color:var(--gold);opacity:0.4;"></i></div>') +
                                '<div>' +
                                    '<h4 style="font-size:1.05rem;font-weight:700;color:var(--text-primary);margin-bottom:0.3rem;">' + esc(svc.title || svc.name || 'Service') + '</h4>' +
                                    '<p style="color:var(--text-secondary);font-size:0.85rem;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' + esc(svc.description || '') + '</p>' +
                                    '<span style="display:inline-flex;align-items:center;gap:0.4rem;margin-top:0.5rem;color:var(--gold);font-size:0.82rem;font-weight:600;">' + t('view_service') + ' <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i></span>' +
                                '</div>' +
                            '</div>' +
                        '</a>' +
                    '</div>';
            }

            // Features
            var featuresHtml = '';
            if (project.features && project.features.length > 0) {
                featuresHtml =
                    '<div data-aos="fade-up" data-aos-delay="250">' +
                        '<h2 style="font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;display:flex;align-items:center;gap:0.75rem;">' +
                            '<span style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,rgba(212,175,55,0.1),rgba(183,134,11,0.06));border:1px solid rgba(212,175,55,0.15);display:flex;align-items:center;justify-content:center;">' +
                                '<i class="fas fa-list-check" style="color:var(--gold);font-size:0.85rem;"></i>' +
                            '</span> <span data-i18n="project_features">' + t('project_features') + '</span>' +
                        '</h2>' +
                        '<div class="features-grid">';
                project.features.forEach(function(feature) {
                    var featureName = feature.feature_text || feature.name || feature;
                    featuresHtml +=
                        '<div class="feature-item">' +
                            '<div class="feature-check"><i class="fas fa-check"></i></div>' +
                            '<span class="feature-text">' + esc(featureName) + '</span>' +
                        '</div>';
                });
                featuresHtml += '</div></div>';
            }

            content.innerHTML =
                (mainImageUrl
                    ? '<div class="detail-hero-wrap" data-aos="fade-up">' +
                        '<img src="' + mainImageUrl + '" alt="' + esc(project.title || 'Project') + '" class="detail-hero-image" id="main-project-image">' +
                      '</div>'
                    : '') +
                galleryHtml +
                metaHtml +
                '<h1 class="detail-title" data-aos="fade-up" data-aos-delay="200"><span class="text-gradient-gold">' + esc(project.title || 'Untitled Project') + '</span></h1>' +
                '<div class="detail-description" data-aos="fade-up" data-aos-delay="200">' +
                    esc(project.description || t('no_desc')).replace(/\n/g, '<br>') +
                '</div>' +
                featuresHtml +
                serviceInfoHtml +
                '<div class="detail-nav" data-aos="fade-up">' +
                    '<a href="' + '{{ route("projects") }}' + '" class="btn btn-outline" style="padding:0.8rem 1.75rem;">' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' +
                        '<span data-i18n="all_projects">' + t('all_projects') + '</span>' +
                    '</a>' +
                    '<a href="#contact" class="btn btn-primary" style="padding:0.8rem 1.75rem;">' +
                        '<span data-i18n="start_project_like">' + t('start_project_like') + '</span>' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>' +
                    '</a>' +
                '</div>';

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load project:', error);
            document.getElementById('project-skeleton').style.display = 'none';
            document.getElementById('project-not-found').style.display = 'block';
        });
})();

function switchProjectImage(thumb, imageUrl) {
    document.getElementById('main-project-image').src = imageUrl;
    document.querySelectorAll('.detail-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}
</script>
@endpush
