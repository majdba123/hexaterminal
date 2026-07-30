@extends('layouts.admin')
@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-cogs" style="color:var(--accent);margin-right:0.5rem;font-size:0.85rem;"></i> Services</h2>
        <div class="table-actions">
            <input type="text" class="search-input" id="search" placeholder="Search services..." oninput="filterTable()">
            <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Service</button>
        </div>
    </div>
    <table class="admin-table">
        <thead><tr><th>Image</th><th>Title</th><th>Description</th><th>Projects</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody id="table-body"><tr class="loading-row"><td colspan="5"><span class="spinner"></span> Loading...</td></tr></tbody>
    </table>
    <div class="pagination-bar" id="pagination-bar" style="display:none;">
        <div class="pagination-info" id="pagination-info"></div>
        <div class="pagination-btns" id="pagination-btns"></div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-title">Add Service</h3>
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
                    <label class="form-label">Description *</label>
                    <textarea class="form-textarea" id="f-description" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Image <span id="img-required">*</span></label>
                    <input type="file" class="form-input" id="f-image" accept="image/*">
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
    axios.get(API_BASE + '/services/index?page=' + currentPage + '&per_page=' + perPage)
        .then(function(r) {
            allItems = r.data.data || [];
            renderTable(allItems);
            if (r.data.pagination) renderPagination(r.data.pagination);
        })
        .catch(function() { tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><p>Failed to load</p></td></tr>'; });
}

function renderTable(items) {
    var tbody = document.getElementById('table-body');
    if (!items.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><i class="fas fa-cogs" style="display:block;"></i><p>No services found</p></td></tr>'; return; }
    tbody.innerHTML = '';
    items.forEach(function(s) {
        var img = s.image_path ? '<img src="' + getImageUrl(s.image_path) + '" class="thumb">' : '<div style="width:40px;height:40px;border-radius:var(--radius-sm);background:rgba(168,85,247,0.15);display:flex;align-items:center;justify-content:center;color:var(--accent);"><i class="fas fa-image"></i></div>';
        var desc = (s.description||'').substring(0, 60) + ((s.description||'').length > 60 ? '...' : '');
        var projCount = s.projects ? s.projects.length : 0;
        tbody.innerHTML += '<tr><td>' + img + '</td><td style="color:var(--text-primary);font-weight:600;">' + (s.title||'—') + '</td><td>' + desc + '</td><td>' + projCount + '</td><td style="text-align:right;"><button class="btn btn-outline btn-sm" onclick="editItem(' + s.id + ')" style="margin-right:0.25rem;"><i class="fas fa-edit"></i></button><button class="btn btn-danger btn-sm" onclick="deleteItem(' + s.id + ')"><i class="fas fa-trash"></i></button></td></tr>';
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

function filterTable() {
    var q = document.getElementById('search').value.toLowerCase();
    renderTable(allItems.filter(function(s) { return ((s.title||'') + ' ' + (s.description||'')).toLowerCase().indexOf(q) > -1; }));
}

function openModal(item) {
    document.getElementById('modal-title').textContent = item ? 'Edit Service' : 'Add Service';
    document.getElementById('edit-id').value = item ? item.id : '';
    document.getElementById('f-title').value = item ? item.title||'' : '';
    document.getElementById('f-description').value = item ? item.description||'' : '';
    document.getElementById('f-image').value = '';
    document.getElementById('img-required').style.display = item ? 'none' : 'inline';
    document.getElementById('modal-overlay').classList.add('open');
}

function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); document.getElementById('modal-form').reset(); }

function editItem(id) {
    axios.get(API_BASE + '/services/show/' + id)
        .then(function(r) { openModal(r.data.data); })
        .catch(function() { showToast('Failed to load', 'error'); });
}

function saveItem(e) {
    e.preventDefault();
    var id = document.getElementById('edit-id').value;
    var btn = document.getElementById('save-btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Saving...';

    var fd = new FormData();
    fd.append('title', document.getElementById('f-title').value);
    fd.append('description', document.getElementById('f-description').value);
    var img = document.getElementById('f-image').files[0];
    if (img) fd.append('image', img);

    var url = id ? API_BASE + '/services/update/' + id : API_BASE + '/services/store';
    axios.post(url, fd, { headers:{'Content-Type':'multipart/form-data'} })
        .then(function() { showToast(id ? 'Service updated!' : 'Service created!'); closeModal(); loadData(currentPage); })
        .catch(function(err) {
            var msg = 'Save failed';
            if (err.response && err.response.data) { msg = err.response.data.errors ? Object.values(err.response.data.errors).flat().join(', ') : (err.response.data.message || msg); }
            showToast(msg, 'error');
        })
        .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save'; });
}

function deleteItem(id) {
    if (!confirm('Delete this service?')) return;
    axios.delete(API_BASE + '/services/delete/' + id)
        .then(function() { showToast('Service deleted!'); loadData(currentPage); })
        .catch(function() { showToast('Delete failed', 'error'); });
}

loadData(1);
</script>
@endpush

