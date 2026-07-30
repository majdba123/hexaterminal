@extends('layouts.admin')
@section('title', 'Messages')
@section('page-title', 'Contact Messages')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-envelope" style="color:var(--warning);margin-right:0.5rem;font-size:0.85rem;"></i> Messages</h2>
        <div class="table-actions">
            <select class="search-input" id="status-filter" onchange="loadData(1)" style="width:150px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
            <input type="text" class="search-input" id="search" placeholder="Search messages..." oninput="filterTable()">
        </div>
    </div>
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody id="table-body"><tr class="loading-row"><td colspan="6"><span class="spinner"></span> Loading...</td></tr></tbody>
    </table>
    <div class="pagination-bar" id="pagination-bar" style="display:none;">
        <div class="pagination-info" id="pagination-info"></div>
        <div class="pagination-btns" id="pagination-btns"></div>
    </div>
</div>

<!-- View Modal -->
<div class="modal-overlay" id="view-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Message Details</h3>
            <button class="modal-close" onclick="document.getElementById('view-modal').classList.remove('open')">&times;</button>
        </div>
        <div class="modal-body" id="view-body"></div>
        <div class="modal-footer" id="view-footer"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var allItems = [], currentPage = 1, perPage = 10;

function loadData(page) {
    currentPage = page || 1;
    var tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr class="loading-row"><td colspan="6"><span class="spinner"></span> Loading...</td></tr>';
    axios.get(API_BASE + '/contact_us/index?page=' + currentPage + '&per_page=' + perPage)
        .then(function(r) { allItems = r.data.data || []; renderTable(allItems); if (r.data.pagination) renderPagination(r.data.pagination); })
        .catch(function() { tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><p>Failed to load</p></td></tr>'; });
}

function renderTable(items) {
    var tbody = document.getElementById('table-body');
    var statusFilter = document.getElementById('status-filter').value;
    if (statusFilter) items = items.filter(function(c) { return c.status === statusFilter; });
    if (!items.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><i class="fas fa-envelope" style="display:block;"></i><p>No messages found</p></td></tr>'; return; }
    tbody.innerHTML = '';
    items.forEach(function(c) {
        var statusClass = 'status-' + (c.status||'pending');
        var date = c.created_at ? new Date(c.created_at).toLocaleDateString() : '—';
        tbody.innerHTML += '<tr><td style="color:var(--text-primary);font-weight:500;">' + esc(c.name||'—') + '</td><td>' + esc(c.email||'—') + '</td><td>' + esc(c.subject||'—') + '</td><td><span class="status-badge ' + esc(statusClass) + '">' + esc(c.status||'pending') + '</span></td><td>' + esc(date) + '</td><td style="text-align:right;">' +
            '<button class="btn btn-outline btn-sm" onclick="viewItem(' + c.id + ')" style="margin-right:0.25rem;" title="View"><i class="fas fa-eye"></i></button>' +
            '<button class="btn btn-danger btn-sm" onclick="deleteItem(' + c.id + ')" title="Delete"><i class="fas fa-trash"></i></button>' +
            '</td></tr>';
    });
}

function renderPagination(pag) {
    document.getElementById('pagination-bar').style.display = 'flex';
    document.getElementById('pagination-info').textContent = 'Showing ' + (pag.from||0) + '-' + (pag.to||0) + ' of ' + pag.total;
    var btns = document.getElementById('pagination-btns');
    btns.innerHTML = '<button ' + (pag.current_page<=1?'disabled':'') + ' onclick="loadData(' + (pag.current_page-1) + ')"><i class="fas fa-chevron-left"></i></button>';
    for (var i=1;i<=pag.last_page;i++) btns.innerHTML += '<button class="' + (i===pag.current_page?'active':'') + '" onclick="loadData(' + i + ')">' + i + '</button>';
    btns.innerHTML += '<button ' + (pag.current_page>=pag.last_page?'disabled':'') + ' onclick="loadData(' + (pag.current_page+1) + ')"><i class="fas fa-chevron-right"></i></button>';
}

function filterTable() { var q = document.getElementById('search').value.toLowerCase(); renderTable(allItems.filter(function(c) { return ((c.name||'')+' '+(c.email||'')+' '+(c.subject||'')).toLowerCase().indexOf(q)>-1; })); }

function viewItem(id) {
    axios.get(API_BASE + '/contact_us/show/' + id)
        .then(function(r) {
            var c = r.data.data;
            var body = document.getElementById('view-body');
            body.innerHTML =
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">' +
                    '<div><div style="font-size:0.72rem;text-transform:uppercase;color:var(--text-muted);font-weight:600;margin-bottom:0.2rem;">Name</div><div style="color:var(--text-primary);font-weight:500;">' + esc(c.name||'—') + '</div></div>' +
                    '<div><div style="font-size:0.72rem;text-transform:uppercase;color:var(--text-muted);font-weight:600;margin-bottom:0.2rem;">Email</div><div><a href="mailto:' + escUrl(c.email||'') + '" style="color:var(--primary);text-decoration:none;">' + esc(c.email||'—') + '</a></div></div>' +
                    '<div><div style="font-size:0.72rem;text-transform:uppercase;color:var(--text-muted);font-weight:600;margin-bottom:0.2rem;">Phone</div><div>' + esc(c.phone||'—') + '</div></div>' +
                    '<div><div style="font-size:0.72rem;text-transform:uppercase;color:var(--text-muted);font-weight:600;margin-bottom:0.2rem;">Status</div><div><span class="status-badge status-' + esc(c.status||'pending') + '">' + esc(c.status||'pending') + '</span></div></div>' +
                '</div>' +
                '<div style="margin-bottom:1rem;"><div style="font-size:0.72rem;text-transform:uppercase;color:var(--text-muted);font-weight:600;margin-bottom:0.2rem;">Subject</div><div style="color:var(--text-primary);font-weight:600;">' + esc(c.subject||'—') + '</div></div>' +
                '<div><div style="font-size:0.72rem;text-transform:uppercase;color:var(--text-muted);font-weight:600;margin-bottom:0.2rem;">Message</div><div style="padding:1rem;background:rgba(6,9,24,0.4);border:1px solid var(--border-color);border-radius:var(--radius-sm);line-height:1.8;white-space:pre-wrap;">' + esc(c.message||'—') + '</div></div>';

            var footer = document.getElementById('view-footer');
            footer.innerHTML = '';
            if (c.status !== 'in_progress') footer.innerHTML += '<button class="btn btn-warning btn-sm" onclick="updateStatus(' + c.id + ',\'in_progress\')"><i class="fas fa-clock"></i> Mark In Progress</button>';
            if (c.status !== 'completed') footer.innerHTML += '<button class="btn btn-success btn-sm" onclick="updateStatus(' + c.id + ',\'completed\')"><i class="fas fa-check"></i> Mark Completed</button>';
            footer.innerHTML += '<button class="btn btn-outline btn-sm" onclick="document.getElementById(\'view-modal\').classList.remove(\'open\')">Close</button>';

            document.getElementById('view-modal').classList.add('open');
        })
        .catch(function() { showToast('Failed to load message', 'error'); });
}

function updateStatus(id, status) {
    axios.put(API_BASE + '/contact_us/update/' + id, { status: status })
        .then(function() { showToast('Status updated!'); document.getElementById('view-modal').classList.remove('open'); loadData(currentPage); })
        .catch(function() { showToast('Update failed', 'error'); });
}

function deleteItem(id) {
    if (!confirm('Delete this message?')) return;
    axios.delete(API_BASE + '/contact_us/delete/' + id).then(function() { showToast('Message deleted!'); loadData(currentPage); }).catch(function() { showToast('Delete failed', 'error'); });
}

loadData(1);
</script>
@endpush

