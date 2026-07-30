@extends('layouts.admin')
@section('title', 'Reviews')
@section('page-title', 'Reviews')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-star" style="color:var(--warning);margin-right:0.5rem;font-size:0.85rem;"></i> Reviews</h2>
        <div class="table-actions">
            <input type="text" class="search-input" id="search" placeholder="Search reviews..." oninput="filterTable()">
        </div>
    </div>
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Content</th><th>Rating</th><th>Approved</th><th>Date</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody id="table-body"><tr class="loading-row"><td colspan="6"><span class="spinner"></span> Loading...</td></tr></tbody>
    </table>
    <div class="pagination-bar" id="pagination-bar" style="display:none;">
        <div class="pagination-info" id="pagination-info"></div>
        <div class="pagination-btns" id="pagination-btns"></div>
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
    axios.get(API_BASE + '/review/index?page=' + currentPage + '&per_page=' + perPage)
        .then(function(r) { allItems = r.data.data || []; renderTable(allItems); if (r.data.pagination) renderPagination(r.data.pagination); })
        .catch(function() { tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><p>Failed to load</p></td></tr>'; });
}

function renderTable(items) {
    var tbody = document.getElementById('table-body');
    if (!items.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><i class="fas fa-star" style="display:block;"></i><p>No reviews found</p></td></tr>'; return; }
    tbody.innerHTML = '';
    items.forEach(function(r) {
        var stars = '';
        for (var i = 1; i <= 5; i++) stars += '<i class="fas fa-star" style="color:' + (i <= (r.rating||0) ? 'var(--warning)' : 'var(--border-color)') + ';font-size:0.7rem;"></i>';
        var content = esc((r.content||'').substring(0,60) + ((r.content||'').length > 60 ? '...' : ''));
        var approved = r.is_approved ? '<span class="status-badge status-completed">Approved</span>' : '<span class="status-badge status-pending">Pending</span>';
        var date = r.review_date ? new Date(r.review_date).toLocaleDateString() : '—';
        tbody.innerHTML += '<tr><td style="color:var(--text-primary);font-weight:500;">' + esc(r.name||'—') + '</td><td>' + content + '</td><td>' + stars + '</td><td>' + approved + '</td><td>' + esc(date) + '</td><td style="text-align:right;">' +
            (!r.is_approved ? '<button class="btn btn-success btn-sm" onclick="approveItem(' + r.id + ')" title="Approve" style="margin-right:0.25rem;"><i class="fas fa-check"></i></button>' : '') +
            '<button class="btn btn-danger btn-sm" onclick="deleteItem(' + r.id + ')"><i class="fas fa-trash"></i></button></td></tr>';
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

function filterTable() { var q = document.getElementById('search').value.toLowerCase(); renderTable(allItems.filter(function(r) { return ((r.name||'')+' '+(r.content||'')).toLowerCase().indexOf(q)>-1; })); }

function approveItem(id) {
    axios.put(API_BASE + '/review/update/' + id, { is_approved: true })
        .then(function() { showToast('Review approved!'); loadData(currentPage); })
        .catch(function() { showToast('Failed to approve', 'error'); });
}

function deleteItem(id) {
    if (!confirm('Delete this review?')) return;
    axios.delete(API_BASE + '/review/delete/' + id).then(function() { showToast('Review deleted!'); loadData(currentPage); }).catch(function() { showToast('Delete failed', 'error'); });
}

loadData(1);
</script>
@endpush

