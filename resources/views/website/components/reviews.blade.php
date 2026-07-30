<!-- ═══ REVIEWS SECTION ═══ -->
<section id="reviews" class="section" style="background: var(--dark-bg); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:300px;height:300px;background:var(--gold);top:-80px;left:-80px;"></div>

    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge"><i class="fas fa-crown"></i> <span data-i18n="section_reviews_badge">Testimonials</span></span>
            <h2 class="section-title" data-i18n="section_reviews_title">Client Reviews</h2>
            <p class="section-subtitle" data-i18n="section_reviews_subtitle">What our valued clients say about their experience working with us</p>
        </div>

        <!-- Skeleton -->
        <div id="reviews-skeleton" class="grid-3">
            @for($i = 0; $i < 3; $i++)
            <div class="skeleton skeleton-card" style="height:300px;"></div>
            @endfor
        </div>

        <!-- Actual content -->
        <div id="reviews-grid" class="grid-3" style="display:none;"></div>

        <!-- Empty state -->
        <div id="reviews-empty" style="display:none;text-align:center;padding:4rem 0;">
            <div style="width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-star" style="font-size:2rem;color:var(--gold);"></i>
            </div>
            <p style="font-size:1.1rem;color:var(--text-secondary);font-weight:500;" data-i18n="reviews_coming_soon">Reviews coming soon!</p>
        </div>
    </div>
</section>

@push('styles')
<style>
.review-card {
    padding: 1.5rem; display: flex; flex-direction: column; height: 100%;
    position: relative;
}
.review-quote {
    position: absolute; top: 1.2rem; right: 1.5rem;
    font-size: 3.5rem; line-height: 1; color: rgba(212,175,55,0.08);
    font-family: Georgia, serif;
}
.review-stars { display: flex; gap: 3px; margin-bottom: 1.25rem; }
.review-star { font-size: 0.95rem; }
.review-star.filled { color: var(--gold); text-shadow: 0 0 8px rgba(212,175,55,0.3); }
.review-star.empty { color: rgba(255,255,255,0.06); }
.review-text {
    color: var(--text-secondary); font-size: 0.9rem; line-height: 1.8;
    margin-bottom: 1.75rem; flex: 1; font-style: italic;
    position: relative; z-index: 1;
}
.review-author {
    display: flex; align-items: center; gap: 0.85rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(212,175,55,0.06);
}
.review-avatar {
    width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    display: flex; align-items: center; justify-content: center;
    color: #0a0a1a; font-weight: 700; font-size: 1.1rem;
    border: 2px solid rgba(212,175,55,0.25);
    box-shadow: 0 0 12px rgba(212,175,55,0.12);
}
.review-author-name { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.1rem; }
.review-author-company { font-size: 0.75rem; color: var(--gold); opacity: 0.7; }

/* Light theme */
[data-theme="light"] .review-quote { color: rgba(183,134,11,0.06); }
[data-theme="light"] .review-author { border-top-color: rgba(183,134,11,0.08); }
[data-theme="light"] .review-avatar { border-color: rgba(183,134,11,0.2); color: #fff; }
[data-theme="light"] .review-star.empty { color: rgba(0,0,0,0.06); }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 640px) {
    .review-card { padding: 1.25rem; }
    .review-text { font-size: 0.85rem; margin-bottom: 1.25rem; }
    .review-quote { font-size: 2.5rem; top: 0.8rem; right: 1rem; }
    .review-avatar { width: 40px; height: 40px; font-size: 0.95rem; }
    .review-author-name { font-size: 0.88rem; }
    .review-author-company { font-size: 0.7rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    axios.get(API_BASE + '/review/index?all=1')
        .then(function(response) {
            var reviews = response.data.data || [];
            var skeleton = document.getElementById('reviews-skeleton');
            var grid = document.getElementById('reviews-grid');
            var empty = document.getElementById('reviews-empty');

            skeleton.style.display = 'none';
            if (reviews.length === 0) { empty.style.display = 'block'; return; }
            grid.style.display = 'grid';

            reviews.forEach(function(review, index) {
                var rating = review.rating || 5;
                var starsHtml = '';
                for (var i = 0; i < 5; i++) {
                    starsHtml += '<i class="fas fa-star review-star ' + (i < rating ? 'filled' : 'empty') + '"></i>';
                }

                var initial = esc((review.name || review.client_name || 'U').charAt(0).toUpperCase());

                var card = document.createElement('div');
                card.className = 'card card-gold-border';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', (index * 100).toString());

                card.innerHTML =
                    '<div class="review-card">' +
                        '<span class="review-quote">\u201C</span>' +
                        '<div class="review-stars">' + starsHtml + '</div>' +
                        '<p class="review-text">\u201C' + esc(review.content || review.comment || review.review || 'Great service!') + '\u201D</p>' +
                        '<div class="review-author">' +
                            '<div class="review-avatar">' + initial + '</div>' +
                            '<div>' +
                                '<div class="review-author-name">' + esc(review.name || review.client_name || 'Anonymous') + '</div>' +
                                (review.company ? '<div class="review-author-company"><i class="fas fa-building" style="margin-right:0.3rem;font-size:0.65rem;"></i>' + esc(review.company) + '</div>' : '') +
                            '</div>' +
                        '</div>' +
                    '</div>';
                grid.appendChild(card);
            });

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load reviews:', error);
            document.getElementById('reviews-skeleton').style.display = 'none';
            document.getElementById('reviews-empty').style.display = 'block';
        });
})();
</script>
@endpush
