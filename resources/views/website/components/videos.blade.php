<!-- ═══ VIDEOS SECTION ═══ -->
<section id="videos" class="section" style="background: var(--dark-bg-2); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:250px;height:250px;background:var(--gold);bottom:-80px;left:-80px;"></div>

    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge"><i class="fas fa-play-circle"></i> <span data-i18n="section_videos_badge">Media</span></span>
            <h2 class="section-title" data-i18n="section_videos_title">Our Videos</h2>
            <p class="section-subtitle" data-i18n="section_videos_subtitle">Watch our latest content, demos, and behind-the-scenes footage</p>
        </div>

        <!-- Skeleton -->
        <div id="videos-skeleton" class="grid-3">
            @for($i = 0; $i < 3; $i++)
            <div class="skeleton skeleton-card"></div>
            @endfor
        </div>

        <!-- Actual content -->
        <div id="videos-grid" class="grid-3" style="display:none;"></div>

        <!-- Empty state -->
        <div id="videos-empty" style="display:none;text-align:center;padding:4rem 0;">
            <div style="width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-play" style="font-size:2rem;color:var(--gold);"></i>
            </div>
            <p style="font-size:1.1rem;color:var(--text-secondary);font-weight:500;" data-i18n="videos_coming_soon">Videos coming soon!</p>
        </div>
    </div>
</section>

@push('styles')
<style>
.video-card-wrap {
    position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}
.video-card-wrap iframe, .video-card-wrap video {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;
}
.video-card-body { padding: 1.5rem; }
.video-card-title {
    font-size: 1.1rem; font-weight: 700; color: var(--text-primary);
    margin-bottom: 0.4rem; line-height: 1.35;
    display: flex; align-items: center; gap: 0.5rem;
}
.video-card-title i { color: var(--gold); font-size: 0.85rem; }
.video-card-desc {
    color: var(--text-secondary); font-size: 0.85rem; line-height: 1.65;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 640px) {
    .video-card-body { padding: 1rem; }
    .video-card-title { font-size: 0.95rem; }
    .video-card-desc { font-size: 0.8rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    function extractYoutubeId(url) {
        if (!url) return null;
        var match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
        return match ? match[1] : null;
    }

    axios.get(API_BASE + '/video/index?all=1')
        .then(function(response) {
            var videos = response.data.data || [];
            var skeleton = document.getElementById('videos-skeleton');
            var grid = document.getElementById('videos-grid');
            var empty = document.getElementById('videos-empty');

            skeleton.style.display = 'none';
            if (videos.length === 0) { empty.style.display = 'block'; return; }
            grid.style.display = 'grid';

            videos.forEach(function(video, index) {
                var youtubeId = extractYoutubeId(video.video_url);

                var card = document.createElement('div');
                card.className = 'card card-gold-border';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', (index * 100).toString());

                var videoHtml = '';
                if (youtubeId) {
                    videoHtml = '<div class="video-card-wrap">' +
                        '<iframe src="https://www.youtube.com/embed/' + encodeURIComponent(youtubeId) + '" ' +
                        'allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" ' +
                        'allowfullscreen loading="lazy"></iframe></div>';
                } else if (video.video_url) {
                    videoHtml = '<div class="video-card-wrap">' +
                        '<video controls preload="metadata"><source src="' + escUrl(video.video_url) + '" type="video/mp4"></video></div>';
                }

                card.innerHTML =
                    videoHtml +
                    '<div class="video-card-body">' +
                        '<h3 class="video-card-title"><i class="fas fa-play-circle"></i> ' + esc(video.title || 'Video') + '</h3>' +
                        (video.description ? '<p class="video-card-desc">' + esc(video.description) + '</p>' : '') +
                    '</div>';
                grid.appendChild(card);
            });

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load videos:', error);
            document.getElementById('videos-skeleton').style.display = 'none';
            document.getElementById('videos-empty').style.display = 'block';
        });
})();
</script>
@endpush
