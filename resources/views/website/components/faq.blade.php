<!-- ═══ FAQ SECTION ═══ -->
<section id="faq" class="section" style="background: var(--dark-bg); position:relative; overflow:hidden;">
    <div class="glow-dot" style="width:250px;height:250px;background:var(--gold);bottom:-80px;right:-80px;"></div>

    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:4rem;align-items:start;">
            <!-- Left side info -->
            <div data-aos="fade-right">
                <span class="section-badge"><i class="fas fa-question-circle"></i> <span data-i18n="section_faq_badge">FAQ</span></span>
                <h2 style="font-size:clamp(2rem,3.5vw,2.75rem);font-weight:800;margin-bottom:1rem;line-height:1.2;letter-spacing:-0.02em;">
                    <span class="text-gradient-gold" data-i18n="section_faq_title">Frequently Asked Questions</span>
                </h2>
                <p style="color:var(--text-secondary);line-height:1.85;font-size:0.92rem;margin-bottom:2rem;" data-i18n="section_faq_subtitle">
                    Find answers to the most common questions about our services, process, and how we work.
                </p>
                <a href="#contact" class="btn btn-primary" style="padding:0.85rem 2rem;">
                    <i class="fas fa-paper-plane" style="font-size:0.85rem;"></i>
                    <span data-i18n="still_have_questions">Still Have Questions?</span>
                </a>
            </div>

            <!-- Right side accordion -->
            <div>
                <!-- Skeleton -->
                <div id="faq-skeleton">
                    @for($i = 0; $i < 4; $i++)
                    <div class="skeleton" style="height:64px;margin-bottom:0.75rem;border-radius:var(--radius-md);"></div>
                    @endfor
                </div>

                <!-- Actual content -->
                <div id="faq-container" style="display:none;"></div>

                <!-- Empty state -->
                <div id="faq-empty" style="display:none;text-align:center;padding:3rem 0;">
                    <i class="fas fa-question-circle" style="font-size:2.5rem;color:var(--gold);opacity:0.3;margin-bottom:1rem;"></i>
                    <p style="font-size:1rem;color:var(--text-secondary);" data-i18n="faq_coming_soon">FAQs coming soon!</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.faq-item {
    background: var(--card-bg-solid);
    border: 1px solid var(--card-border); border-radius: var(--radius-md);
    margin-bottom: 0.6rem; overflow: hidden;
    transition: border-color 0.3s ease;
}
.faq-item:hover { border-color: rgba(212,175,55,0.15); }
.faq-item.active { border-color: rgba(212,175,55,0.3); box-shadow: 0 0 20px rgba(212,175,55,0.05); }
.faq-question {
    width: 100%; padding: 1.25rem 1.5rem; background: transparent; border: none;
    text-align: left; cursor: pointer; display: flex; justify-content: space-between;
    align-items: center; gap: 1rem; color: var(--text-primary);
    font-size: 0.95rem; font-weight: 600; font-family: inherit;
    transition: var(--transition-fast);
}
.faq-question:hover { color: var(--gold); }
.faq-toggle {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    background: rgba(212,175,55,0.06); border: 1px solid rgba(212,175,55,0.12);
    display: flex; align-items: center; justify-content: center;
    color: var(--gold); font-size: 0.85rem;
    transition: var(--transition);
}
.faq-item.active .faq-toggle {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #0a0a1a; border-color: var(--gold);
    transform: rotate(45deg);
    box-shadow: 0 0 12px rgba(212,175,55,0.2);
}
.faq-answer {
    max-height: 0; overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.faq-answer-inner {
    padding: 0 1.5rem 1.5rem;
    color: var(--text-secondary); line-height: 1.8; font-size: 0.88rem;
    border-top: 1px solid rgba(212,175,55,0.06);
    padding-top: 1rem;
}

/* Light theme */
[data-theme="light"] .faq-item { background: var(--card-bg); border-color: var(--card-border); }
[data-theme="light"] .faq-item:hover { border-color: rgba(183,134,11,0.15); }
[data-theme="light"] .faq-item.active { border-color: rgba(183,134,11,0.25); box-shadow: 0 4px 20px rgba(183,134,11,0.06); }
[data-theme="light"] .faq-toggle { background: rgba(183,134,11,0.04); border-color: rgba(183,134,11,0.1); }
[data-theme="light"] .faq-item.active .faq-toggle { background: linear-gradient(135deg, var(--gold), var(--gold-dark)); color: #fff; border-color: var(--gold); }
[data-theme="light"] .faq-answer-inner { border-top-color: rgba(183,134,11,0.06); }

@media (max-width: 768px) {
    #faq > .container > div { grid-template-columns: 1fr !important; gap: 1.5rem !important; }
}
@media (max-width: 640px) {
    .faq-question { padding: 1rem 1.15rem; font-size: 0.88rem; gap: 0.75rem; min-height: 44px; }
    .faq-toggle { width: 28px; height: 28px; }
    .faq-answer-inner { padding: 0 1.15rem 1.15rem; font-size: 0.82rem; }
    .faq-item { margin-bottom: 0.5rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    axios.get(API_BASE + '/faq/index?all=1')
        .then(function(response) {
            var faqs = response.data.data || [];
            var skeleton = document.getElementById('faq-skeleton');
            var container = document.getElementById('faq-container');
            var empty = document.getElementById('faq-empty');

            skeleton.style.display = 'none';
            if (faqs.length === 0) { empty.style.display = 'block'; return; }
            container.style.display = 'block';

            faqs.forEach(function(faq, index) {
                var item = document.createElement('div');
                item.className = 'faq-item';
                item.setAttribute('data-aos', 'fade-up');
                item.setAttribute('data-aos-delay', (index * 60).toString());

                item.innerHTML =
                    '<button class="faq-question" onclick="toggleFaqItem(this)">' +
                        '<span>' + esc(faq.question || 'Question') + '</span>' +
                        '<span class="faq-toggle"><i class="fas fa-plus" style="font-size:0.7rem;"></i></span>' +
                    '</button>' +
                    '<div class="faq-answer">' +
                        '<div class="faq-answer-inner">' + esc(faq.answer || 'No answer available.') + '</div>' +
                    '</div>';
                container.appendChild(item);
            });

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load FAQs:', error);
            document.getElementById('faq-skeleton').style.display = 'none';
            document.getElementById('faq-empty').style.display = 'block';
        });
})();

function toggleFaqItem(btn) {
    var faqItem = btn.closest('.faq-item');
    var answer = btn.nextElementSibling;
    var icon = btn.querySelector('.faq-toggle i');
    var isOpen = faqItem.classList.contains('active');

    // Close all
    document.querySelectorAll('.faq-item').forEach(function(item) {
        item.classList.remove('active');
        item.querySelector('.faq-answer').style.maxHeight = '0px';
        var itemIcon = item.querySelector('.faq-toggle i');
        if (itemIcon) { itemIcon.className = 'fas fa-plus'; itemIcon.style.fontSize = '0.7rem'; }
    });

    if (!isOpen) {
        faqItem.classList.add('active');
        answer.style.maxHeight = answer.scrollHeight + 'px';
        icon.className = 'fas fa-plus';
        icon.style.fontSize = '0.7rem';
    }
}
</script>
@endpush
