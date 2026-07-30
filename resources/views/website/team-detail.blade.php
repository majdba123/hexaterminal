@extends('layouts.website')

@section('title', 'Team Member - HexaTerminal')

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
            <a href="{{ url('/') }}#team" style="color:var(--text-muted);text-decoration:none;transition:var(--transition-fast);" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-muted)'">
                <span data-i18n="nav_team">Team</span>
            </a>
            <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:0.55rem;"></i>
            <span id="breadcrumb-name" style="color:var(--gold);font-weight:600;">Loading...</span>
        </nav>
    </div>
</section>

<!-- ═══ TEAM MEMBER DETAIL ═══ -->
<section class="section" style="background:var(--dark-bg);padding-top:3rem;">
    <div class="container" style="max-width:1000px;">
        <!-- Skeleton -->
        <div id="member-skeleton" data-aos="fade-up">
            <div style="display:grid;grid-template-columns:300px 1fr;gap:3rem;align-items:start;">
                <div>
                    <div class="skeleton" style="width:280px;height:280px;border-radius:50%;margin:0 auto;"></div>
                </div>
                <div>
                    <div class="skeleton skeleton-text" style="height:42px;width:60%;margin-bottom:1rem;"></div>
                    <div class="skeleton skeleton-text" style="height:20px;width:40%;margin-bottom:1.5rem;"></div>
                    <div class="skeleton skeleton-text" style="height:16px;margin-bottom:0.75rem;"></div>
                    <div class="skeleton skeleton-text" style="height:16px;margin-bottom:0.75rem;"></div>
                    <div class="skeleton skeleton-text" style="height:16px;width:80%;margin-bottom:2rem;"></div>
                    <div style="display:flex;gap:0.75rem;">
                        <div class="skeleton" style="width:140px;height:44px;border-radius:var(--radius-sm);"></div>
                        <div class="skeleton" style="width:140px;height:44px;border-radius:var(--radius-sm);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actual content -->
        <div id="member-content" style="display:none;"></div>

        <!-- Not found -->
        <div id="member-not-found" style="display:none;text-align:center;padding:5rem 0;">
            <div style="width:100px;height:100px;margin:0 auto 2rem;border-radius:50%;background:rgba(212,175,55,0.06);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-user-slash" style="font-size:2.5rem;color:var(--gold);opacity:0.4;"></i>
            </div>
            <h3 style="font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:0.75rem;" data-i18n="member_not_found">Member Not Found</h3>
            <p style="color:var(--text-secondary);font-size:0.95rem;max-width:400px;margin:0 auto 2rem;" data-i18n="member_not_found_desc">
                The team member you're looking for doesn't exist or may have been removed.
            </p>
            <a href="{{ url('/') }}#team" class="btn btn-primary" style="padding:0.85rem 2.25rem;">
                <span data-i18n="view_all_team">View All Team</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.member-profile-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 3.5rem;
    align-items: start;
}
.member-photo-container {
    position: relative;
    width: 280px;
    height: 280px;
    margin: 0 auto;
}
.member-photo-ring {
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    opacity: 0.35;
    animation: pulse-glow 3s ease-in-out infinite;
}
@keyframes pulse-glow {
    0%, 100% { opacity: 0.25; transform: scale(1); }
    50% { opacity: 0.45; transform: scale(1.02); }
}
.member-photo-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    position: relative;
    z-index: 1;
    border: 4px solid var(--dark-bg);
}
.member-initials-large {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 6rem;
    font-weight: 800;
    color: #0a0a1a;
    position: relative;
    z-index: 1;
    border: 4px solid var(--dark-bg);
}
.member-position-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    background: linear-gradient(135deg, rgba(212,175,55,0.12), rgba(183,134,11,0.06));
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}
.member-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin: 2rem 0;
}
.member-info-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1rem 1.25rem;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: var(--radius-md);
    transition: var(--transition-fast);
}
.member-info-item:hover {
    border-color: rgba(212,175,55,0.2);
    background: rgba(212,175,55,0.04);
}
.member-info-icon {
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
.member-info-label {
    font-size: 0.72rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
    margin-bottom: 0.1rem;
}
.member-info-value {
    font-size: 0.9rem;
    color: var(--text-primary);
    font-weight: 500;
}
.member-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 2rem;
}

/* Light theme */
[data-theme="light"] .member-photo-img { border-color: #fff; }
[data-theme="light"] .member-initials-large { border-color: #fff; color: #fff; }
[data-theme="light"] .member-info-item { background: var(--card-bg); border-color: var(--card-border); }
[data-theme="light"] .member-info-item:hover { border-color: rgba(183,134,11,0.18); background: rgba(183,134,11,0.03); }
[data-theme="light"] .member-info-icon { background: linear-gradient(135deg, rgba(183,134,11,0.08), rgba(212,175,55,0.04)); border-color: rgba(183,134,11,0.12); }
[data-theme="light"] .member-position-badge { background: linear-gradient(135deg, rgba(183,134,11,0.08), rgba(212,175,55,0.04)); border-color: rgba(183,134,11,0.15); }

@media (max-width: 768px) {
    .member-profile-grid {
        grid-template-columns: 1fr !important;
        text-align: center;
        gap: 1.5rem !important;
    }
    .member-photo-container {
        width: 180px;
        height: 180px;
        margin: 0 auto;
    }
    .member-initials-large {
        font-size: 3.5rem;
    }
    .member-info-grid {
        grid-template-columns: 1fr;
    }
    .member-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
    .member-position-badge {
        margin-left: auto;
        margin-right: auto;
    }
}
@media (max-width: 480px) {
    .member-photo-container {
        width: 150px;
        height: 150px;
    }
    .member-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var memberId = {{ $id }};

    axios.get(API_BASE + '/teams/show/' + memberId)
        .then(function(response) {
            var member = response.data.data;
            var skeleton = document.getElementById('member-skeleton');
            var content = document.getElementById('member-content');
            var notFound = document.getElementById('member-not-found');

            skeleton.style.display = 'none';

            if (!member) {
                notFound.style.display = 'block';
                return;
            }

            var rawFullName = ((member.first_name || '') + ' ' + (member.last_name || '')).trim();
            document.title = rawFullName + ' - HexaTerminal Team';
            document.getElementById('breadcrumb-name').textContent = rawFullName;

            content.style.display = 'block';

            var fullName = esc(rawFullName);
            var photoUrl = member.photo ? escUrl(getImageUrl(member.photo)) : '';
            var initial = esc((member.first_name || 'U').charAt(0).toUpperCase());

            // Photo HTML with onerror fallback
            var photoImgHtml;
            if (photoUrl) {
                photoImgHtml = '<img src="' + photoUrl + '" alt="' + fullName + '" class="member-photo-img" referrerpolicy="no-referrer" ' +
                    'onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">' +
                    '<div class="member-initials-large" style="display:none;">' + initial + '</div>';
            } else {
                photoImgHtml = '<div class="member-initials-large">' + initial + '</div>';
            }

            // Info items
            var infoItems = '';
            if (member.email) {
                infoItems += '<div class="member-info-item" data-aos="fade-up" data-aos-delay="100">' +
                    '<div class="member-info-icon"><i class="fas fa-envelope"></i></div>' +
                    '<div><div class="member-info-label">' + t('email') + '</div>' +
                    '<div class="member-info-value">' + esc(member.email) + '</div></div></div>';
            }
            if (member.phone) {
                infoItems += '<div class="member-info-item" data-aos="fade-up" data-aos-delay="150">' +
                    '<div class="member-info-icon"><i class="fas fa-phone"></i></div>' +
                    '<div><div class="member-info-label">' + t('phone') + '</div>' +
                    '<div class="member-info-value">' + esc(member.phone) + '</div></div></div>';
            }
            if (member.specialization) {
                infoItems += '<div class="member-info-item" data-aos="fade-up" data-aos-delay="200">' +
                    '<div class="member-info-icon"><i class="fas fa-code"></i></div>' +
                    '<div><div class="member-info-label">' + t('specialization') + '</div>' +
                    '<div class="member-info-value">' + esc(member.specialization) + '</div></div></div>';
            }
            if (member.position) {
                infoItems += '<div class="member-info-item" data-aos="fade-up" data-aos-delay="250">' +
                    '<div class="member-info-icon"><i class="fas fa-briefcase"></i></div>' +
                    '<div><div class="member-info-label">' + t('position') + '</div>' +
                    '<div class="member-info-value">' + esc(member.position) + '</div></div></div>';
            }

            // Actions
            var actions = '';
            if (member.github_url) {
                actions += '<a href="' + escUrl(member.github_url) + '" target="_blank" rel="noopener" class="btn btn-outline" style="padding:0.75rem 1.5rem;">' +
                    '<i class="fab fa-github"></i> <span>GitHub</span></a>';
            }
            if (member.cv_file) {
                actions += '<a href="' + escUrl(member.cv_file) + '" target="_blank" rel="noopener" class="btn btn-primary" style="padding:0.75rem 1.5rem;">' +
                    '<i class="fas fa-file-alt"></i> <span>' + t('download_cv') + '</span></a>';
            }
            if (member.email) {
                actions += '<a href="mailto:' + escUrl(member.email) + '" class="btn btn-outline" style="padding:0.75rem 1.5rem;">' +
                    '<i class="fas fa-envelope"></i> <span>' + t('send_email') + '</span></a>';
            }

            content.innerHTML =
                '<div class="member-profile-grid" data-aos="fade-up">' +
                    '<div>' +
                        '<div class="member-photo-container">' +
                            '<div class="member-photo-ring"></div>' +
                            photoImgHtml +
                        '</div>' +
                    '</div>' +
                    '<div>' +
                        '<h1 style="font-size:clamp(2rem,4vw,2.75rem);font-weight:800;line-height:1.2;letter-spacing:-0.02em;margin-bottom:0.75rem;">' +
                            '<span class="text-gradient-gold">' + fullName + '</span>' +
                        '</h1>' +
                        '<div class="member-position-badge">' +
                            '<i class="fas fa-crown" style="color:var(--gold);font-size:0.75rem;"></i>' +
                            '<span style="color:var(--gold);">' + esc(member.position || 'Team Member') + '</span>' +
                        '</div>' +
                        (member.specialization
                            ? '<p style="color:var(--text-secondary);font-size:1rem;line-height:1.85;margin-bottom:0.5rem;">' +
                                '<i class="fas fa-code" style="color:var(--gold);margin-right:0.5rem;font-size:0.85rem;"></i>' +
                                esc(member.specialization) +
                              '</p>'
                            : '') +
                        (infoItems ? '<div class="member-info-grid">' + infoItems + '</div>' : '') +
                        (actions ? '<div class="member-actions">' + actions + '</div>' : '') +
                    '</div>' +
                '</div>' +
                '<div style="margin-top:3.5rem;padding-top:2.5rem;border-top:1px solid rgba(212,175,55,0.06);display:flex;justify-content:space-between;align-items:center;" data-aos="fade-up">' +
                    '<a href="' + '{{ url("/") }}#team' + '" class="btn btn-outline" style="padding:0.8rem 1.75rem;">' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' +
                        '<span data-i18n="back_to_team">' + t('back_to_team') + '</span>' +
                    '</a>' +
                    '<a href="#contact" class="btn btn-primary" style="padding:0.8rem 1.75rem;">' +
                        '<span data-i18n="get_in_touch">' + t('get_in_touch') + '</span>' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>' +
                    '</a>' +
                '</div>';

            AOS.refresh();
        })
        .catch(function(error) {
            console.error('Failed to load team member:', error);
            document.getElementById('member-skeleton').style.display = 'none';
            document.getElementById('member-not-found').style.display = 'block';
        });
})();
</script>
@endpush
