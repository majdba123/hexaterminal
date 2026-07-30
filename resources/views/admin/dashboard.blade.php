@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid" id="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(43,155,255,0.1);color:var(--primary);"><i class="fas fa-users"></i></div>
        <div><div class="stat-value" id="stat-teams">—</div><div class="stat-label">Team Members</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168,85,247,0.1);color:var(--accent);"><i class="fas fa-cogs"></i></div>
        <div><div class="stat-value" id="stat-services">—</div><div class="stat-label">Services</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(0,208,132,0.1);color:var(--green);"><i class="fas fa-briefcase"></i></div>
        <div><div class="stat-value" id="stat-projects">—</div><div class="stat-label">Projects</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:var(--warning);"><i class="fas fa-envelope"></i></div>
        <div><div class="stat-value" id="stat-contacts">—</div><div class="stat-label">Messages</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,0.1);color:var(--danger);"><i class="fas fa-star"></i></div>
        <div><div class="stat-value" id="stat-reviews">—</div><div class="stat-label">Reviews</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(43,155,255,0.1);color:var(--primary);"><i class="fas fa-question-circle"></i></div>
        <div><div class="stat-value" id="stat-faq">—</div><div class="stat-label">FAQs</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(0,208,132,0.1);color:var(--green);"><i class="fas fa-video"></i></div>
        <div><div class="stat-value" id="stat-videos">—</div><div class="stat-label">Videos</div></div>
    </div>
</div>

<!-- Recent Messages -->
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-envelope" style="color:var(--primary);margin-right:0.5rem;font-size:0.85rem;"></i> Recent Messages</h2>
        <a href="{{ route('admin.contacts') }}" class="btn btn-outline btn-sm">View All</a>
    </div>
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
        <tbody id="recent-contacts"><tr class="loading-row"><td colspan="5"><span class="spinner"></span> Loading...</td></tr></tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // Fetch counts for all resources
    var endpoints = [
        { key:'teams', url:'/teams/index?page=1&per_page=1' },
        { key:'services', url:'/services/index?page=1&per_page=1' },
        { key:'projects', url:'/projects/index?page=1&per_page=1' },
        { key:'contacts', url:'/contact_us/index?page=1&per_page=1' },
        { key:'reviews', url:'/review/index?page=1&per_page=1' },
        { key:'faq', url:'/faq/index?page=1&per_page=1' },
        { key:'videos', url:'/video/index?page=1&per_page=1' }
    ];

    endpoints.forEach(function(ep) {
        axios.get(API_BASE + ep.url)
            .then(function(r) {
                var total = r.data.pagination ? r.data.pagination.total : (r.data.data ? r.data.data.length : 0);
                var el = document.getElementById('stat-' + ep.key);
                if (el) el.textContent = total;
                if (ep.key === 'contacts') {
                    var badge = document.getElementById('contacts-badge');
                    if (badge && total > 0) { badge.textContent = total; badge.style.display = 'inline'; }
                }
            })
            .catch(function() {
                var el = document.getElementById('stat-' + ep.key);
                if (el) el.textContent = '0';
            });
    });

    // Recent contacts
    axios.get(API_BASE + '/contact_us/index?page=1&per_page=5')
        .then(function(r) {
            var items = r.data.data || [];
            var tbody = document.getElementById('recent-contacts');
            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><p>No messages yet</p></td></tr>';
                return;
            }
            tbody.innerHTML = '';
            items.forEach(function(c) {
                var statusClass = 'status-' + (c.status || 'pending');
                var date = c.created_at ? new Date(c.created_at).toLocaleDateString() : '—';
                tbody.innerHTML += '<tr>' +
                    '<td style="color:var(--text-primary);font-weight:500;">' + (c.name || '—') + '</td>' +
                    '<td>' + (c.email || '—') + '</td>' +
                    '<td>' + (c.subject || '—') + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + (c.status || 'pending') + '</span></td>' +
                    '<td>' + date + '</td>' +
                    '</tr>';
            });
        })
        .catch(function() {
            document.getElementById('recent-contacts').innerHTML = '<tr><td colspan="5" style="text-align:center;padding:1.5rem;color:var(--text-muted);">Failed to load</td></tr>';
        });
})();
</script>
@endpush

