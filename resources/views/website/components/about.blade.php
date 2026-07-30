<!-- ═══ ABOUT SECTION ═══ -->
<section id="about" class="section" style="background: var(--dark-bg-2); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:300px;height:300px;background:var(--gold);bottom:-100px;right:-100px;"></div>

    <div class="container">
        <!-- Skeleton -->
        <div id="about-skeleton" class="about-grid">
            <div>
                <div class="skeleton skeleton-text" style="width:130px;height:32px;margin-bottom:1.5rem;border-radius:999px;"></div>
                <div class="skeleton skeleton-text" style="width:320px;height:42px;margin-bottom:2rem;"></div>
                <div class="skeleton skeleton-text" style="height:16px;margin-bottom:0.5rem;"></div>
                <div class="skeleton skeleton-text" style="height:16px;margin-bottom:0.5rem;"></div>
                <div class="skeleton skeleton-text short" style="height:16px;"></div>
            </div>
            <div class="skeleton" style="height:420px;border-radius:var(--radius-xl);"></div>
        </div>

        <!-- Actual content -->
        <div id="about-content" style="display:none;"></div>
    </div>
</section>

@push('styles')
<style>
.about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4.5rem; align-items: center; }
.about-image-wrap {
    position: relative; border-radius: var(--radius-xl); overflow: hidden;
}
.about-image-wrap::after {
    content: ''; position: absolute; inset: 0;
    border-radius: inherit;
    border: 2px solid rgba(212,175,55,0.15);
    pointer-events: none;
}
.about-image-wrap img {
    width: 100%; display: block; border-radius: var(--radius-xl);
    transition: transform 0.6s ease;
}
.about-image-wrap:hover img { transform: scale(1.03); }
.about-image-badge {
    position: absolute; bottom: 1.5rem; left: 1.5rem;
    background: rgba(6,9,24,0.92);
    border: 1px solid rgba(212,175,55,0.2);
    padding: 0.85rem 1.25rem; border-radius: var(--radius-md);
    display: flex; align-items: center; gap: 0.75rem;
}
.about-mission {
    margin-top: 2rem; padding: 1.5rem 1.75rem;
    background: linear-gradient(135deg, rgba(212,175,55,0.06), rgba(183,134,11,0.03));
    border-left: 3px solid var(--gold); border-radius: 0 var(--radius-md) var(--radius-md) 0;
}
.about-features {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
    margin-top: 2rem;
}
.about-feature {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem; border-radius: var(--radius-sm);
    background: rgba(212,175,55,0.03);
    border: 1px solid rgba(212,175,55,0.06);
    transition: var(--transition-fast);
}
.about-feature:hover {
    border-color: rgba(212,175,55,0.18);
    background: rgba(212,175,55,0.06);
    transform: translateY(-2px);
}
.about-feature i { color: var(--gold); font-size: 0.85rem; flex-shrink: 0; }
.about-feature span { font-size: 0.85rem; color: var(--text-secondary); font-weight: 500; }

/* Light theme */
[data-theme="light"] .about-image-wrap::after { border-color: rgba(183,134,11,0.12); }
[data-theme="light"] .about-image-badge { background: rgba(255,255,255,0.92); border-color: rgba(183,134,11,0.1); backdrop-filter: blur(12px); }
[data-theme="light"] .about-mission { background: linear-gradient(135deg, rgba(183,134,11,0.05), rgba(212,175,55,0.02)); border-left-color: var(--gold); }
[data-theme="light"] .about-feature { background: rgba(183,134,11,0.02); border-color: rgba(183,134,11,0.06); }
[data-theme="light"] .about-feature:hover { background: rgba(183,134,11,0.05); border-color: rgba(183,134,11,0.14); }

@media (max-width: 768px) {
    .about-grid { grid-template-columns: 1fr !important; gap: 2rem !important; }
    .about-features { grid-template-columns: 1fr; gap: 0.75rem; }
}
@media (max-width: 640px) {
    .about-image-badge { bottom: 0.75rem; left: 0.75rem; padding: 0.65rem 0.85rem; }
    .about-mission { padding: 1.15rem 1.25rem; margin-top: 1.5rem; }
    .about-feature { padding: 0.65rem; }
    .about-feature span { font-size: 0.8rem; }
    .about-feature i { font-size: 0.75rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    axios.get(API_BASE + '/about_us/show')
        .then(function(response) {
            var aboutUs = response.data.data;
            var skeleton = document.getElementById('about-skeleton');
            var content = document.getElementById('about-content');

            skeleton.style.display = 'none';
            if (!aboutUs) return;
            content.style.display = 'block';

            var imageHtml = (aboutUs.company_logo || aboutUs.image)
                ? '<div data-aos="fade-left" data-aos-delay="200">' +
                    '<div class="about-image-wrap">' +
                        '<img src="' + escUrl(getImageUrl(aboutUs.company_logo || aboutUs.image)) + '" alt="About HexaTerminal" loading="lazy">' +
                        '<div class="about-image-badge">' +
                            '<i class="fas fa-crown" style="color:var(--gold);font-size:1.2rem;"></i>' +
                            '<div><span style="font-size:0.75rem;color:var(--text-muted);" data-i18n="trusted_by">' + t('trusted_by') + '</span><br>' +
                            '<span style="font-size:0.95rem;font-weight:700;color:var(--text-primary);">100+ ' + t('clients') + '</span></div>' +
                        '</div>' +
                    '</div>' +
                  '</div>'
                : '';

            var missionHtml = aboutUs.mission
                ? '<div class="about-mission">' +
                    '<h3 style="color:var(--gold);margin-bottom:0.5rem;font-weight:700;font-size:1.05rem;">' +
                        '<i class="fas fa-bullseye" style="margin-right:0.5rem;"></i>' + t('our_mission') + '</h3>' +
                    '<p style="color:var(--text-secondary);line-height:1.85;font-size:0.92rem;">' + esc(aboutUs.mission) + '</p>' +
                  '</div>'
                : '';

            content.innerHTML =
                '<div class="about-grid">' +
                    '<div data-aos="fade-right">' +
                        '<span class="section-badge"><i class="fas fa-gem"></i> <span data-i18n="section_about_badge">' + t('section_about_badge') + '</span></span>' +
                        '<h2 style="font-size:clamp(2rem,3.5vw,2.75rem);font-weight:800;margin-bottom:1.5rem;line-height:1.2;letter-spacing:-0.02em;">' +
                            '<span class="text-gradient-gold" data-i18n="section_about_title">' + t('section_about_title') + '</span>' +
                        '</h2>' +
                        '<div style="color:var(--text-secondary);line-height:1.9;font-size:0.92rem;">' +
                            esc(aboutUs.company_description || aboutUs.description || t('no_desc')) +
                        '</div>' +
                        missionHtml +
                        '<div class="about-features">' +
                            '<div class="about-feature"><i class="fas fa-crown"></i><span data-i18n="expert_team">' + t('expert_team') + '</span></div>' +
                            '<div class="about-feature"><i class="fas fa-microchip"></i><span data-i18n="modern_tech">' + t('modern_tech') + '</span></div>' +
                            '<div class="about-feature"><i class="fas fa-headset"></i><span data-i18n="support_247">' + t('support_247') + '</span></div>' +
                            '<div class="about-feature"><i class="fas fa-rocket"></i><span data-i18n="fast_delivery">' + t('fast_delivery') + '</span></div>' +
                        '</div>' +
                    '</div>' +
                    imageHtml +
                '</div>';

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load about us:', error);
            document.getElementById('about-skeleton').style.display = 'none';
        });
})();
</script>
@endpush
