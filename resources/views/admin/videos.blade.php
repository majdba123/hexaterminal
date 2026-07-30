@extends('layouts.admin')
@section('title', 'Videos')
@section('page-title', 'Videos')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-video" style="color:var(--green);margin-right:0.5rem;font-size:0.85rem;"></i> Videos</h2>
        <div class="table-actions">
            <input type="text" class="search-input" id="search" placeholder="Search videos..." oninput="filterTable()">
            <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Video</button>
        </div>
    </div>
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Description</th><th>Location</th><th>URL</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody id="table-body"><tr class="loading-row"><td colspan="5"><span class="spinner"></span> Loading...</td></tr></tbody>
    </table>
    <div class="pagination-bar" id="pagination-bar" style="display:none;">
        <div class="pagination-info" id="pagination-info"></div>
        <div class="pagination-btns" id="pagination-btns"></div>
    </div>
</div>

<div class="modal-overlay" id="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-title">Add Video</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="modal-form" onsubmit="saveItem(event)">
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-input" id="f-title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" id="f-description"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Location *</label>
                        <input type="text" class="form-input" id="f-location" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Video URL *</label>
                        <input type="url" class="form-input" id="f-video_url" required placeholder="https://youtube.com/...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="save-btn"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var allItems = [], currentPage = 1, perPage = 10;

function loadData(page) {
    currentPage = page || 1;
    var tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr class="loading-row"><td colspan="5"><span class="spinner"></span> Loading...</td></tr>';
    axios.get(API_BASE + '/video/index?page=' + currentPage + '&per_page=' + perPage)
        .then(function(r) { allItems = r.data.data || []; renderTable(allItems); if (r.data.pagination) renderPagination(r.data.pagination); })
        .catch(function() { tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><p>Failed to load</p></td></tr>'; });
}

function renderTable(items) {
    var tbody = document.getElementById('table-body');
    if (!items.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><i class="fas fa-video" style="display:block;"></i><p>No videos found</p></td></tr>'; return; }
    tbody.innerHTML = '';
    items.forEach(function(v) {
        var desc = (v.description||'').substring(0,50) + ((v.description||'').length > 50 ? '...' : '');
        var urlShort = (v.video_url||'').substring(0,35) + ((v.video_url||'').length > 35 ? '...' : '');
        tbody.innerHTML += '<tr><td style="color:var(--text-primary);font-weight:500;">' + (v.title||'—') + '</td><td>' + (desc||'—') + '</td><td>' + (v.location||'—') + '</td><td><a href="' + (v.video_url||'#') + '" target="_blank" style="color:var(--primary);text-decoration:none;font-size:0.82rem;">' + urlShort + '</a></td><td style="text-align:right;"><button class="btn btn-outline btn-sm" onclick="editItem(' + v.id + ')" style="margin-right:0.25rem;"><i class="fas fa-edit"></i></button><button class="btn btn-danger btn-sm" onclick="deleteItem(' + v.id + ')"><i class="fas fa-trash"></i></button></td></tr>';
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

function filterTable() { var q = document.getElementById('search').value.toLowerCase(); renderTable(allItems.filter(function(v) { return ((v.title||'')+' '+(v.location||'')).toLowerCase().indexOf(q)>-1; })); }

function openModal(item) {
    document.getElementById('modal-title').textContent = item ? 'Edit Video' : 'Add Video';
    document.getElementById('edit-id').value = item ? item.id : '';
    document.getElementById('f-title').value = item ? item.title||'' : '';
    document.getElementById('f-description').value = item ? item.description||'' : '';
    document.getElementById('f-location').value = item ? item.location||'' : '';
    document.getElementById('f-video_url').value = item ? item.video_url||'' : '';
    document.getElementById('modal-overlay').classList.add('open');
}
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); document.getElementById('modal-form').reset(); }

function editItem(id) { axios.get(API_BASE + '/video/show/' + id).then(function(r) { openModal(r.data.data); }).catch(function() { showToast('Failed to load', 'error'); }); }

function saveItem(e) {
    e.preventDefault();
    var id = document.getElementById('edit-id').value;
    var btn = document.getElementById('save-btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Saving...';
    var data = { title: document.getElementById('f-title').value, description: document.getElementById('f-description').value, location: document.getElementById('f-location').value, video_url: document.getElementById('f-video_url').value };
    var req = id ? axios.put(API_BASE + '/video/update/' + id, data) : axios.post(API_BASE + '/video/store', data);
    req.then(function() { showToast(id ? 'Video updated!' : 'Video created!'); closeModal(); loadData(currentPage); })
       .catch(function(err) { var msg='Save failed'; if(err.response&&err.response.data){msg=err.response.data.errors?Object.values(err.response.data.errors).flat().join(', '):(err.response.data.message||msg);} showToast(msg,'error'); })
       .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save'; });
}

function deleteItem(id) {
    if (!confirm('Delete this video?')) return;
    axios.delete(API_BASE + '/video/delete/' + id).then(function() { showToast('Video deleted!'); loadData(currentPage); }).catch(function() { showToast('Delete failed', 'error'); });
}

loadData(1);
</script>
@endpush

