@extends('layouts.admin')
@section('title', 'Projects')
@section('page-title', 'Projects')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-briefcase" style="color:var(--green);margin-right:0.5rem;font-size:0.85rem;"></i> Projects</h2>
        <div class="table-actions">
            <input type="text" class="search-input" id="search" placeholder="Search projects..." oninput="filterTable()">
            <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Project</button>
        </div>
    </div>
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Service</th><th>Status</th><th>Images</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody id="table-body"><tr class="loading-row"><td colspan="5"><span class="spinner"></span> Loading...</td></tr></tbody>
    </table>
    <div class="pagination-bar" id="pagination-bar" style="display:none;">
        <div class="pagination-info" id="pagination-info"></div>
        <div class="pagination-btns" id="pagination-btns"></div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal-overlay">
    <div class="modal" style="max-width:700px;">
        <div class="modal-header">
            <h3 id="modal-title">Add Project</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="modal-form" onsubmit="saveItem(event)">
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-input" id="f-title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Service *</label>
                        <select class="form-select" id="f-service_id" required>
                            <option value="">Select service...</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea class="form-textarea" id="f-description" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project URL</label>
                        <input type="url" class="form-input" id="f-project_url" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Video URL</label>
                        <input type="url" class="form-input" id="f-video_url" placeholder="https://youtube.com/...">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Features (one per line)</label>
                    <textarea class="form-textarea" id="f-features" placeholder="Feature 1&#10;Feature 2&#10;Feature 3" style="min-height:80px;"></textarea>
                </div>
                <!-- Existing Images (shown when editing) -->
                <div class="form-group" id="existing-images-group" style="display:none;">
                    <label class="form-label">Current Images</label>
                    <div id="existing-images" style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:0.5rem;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Upload Images <span id="img-hint" style="font-weight:400;color:var(--text-muted);font-size:0.75rem;">(multiple allowed)</span></label>
                    <input type="file" class="form-input" id="f-images" accept="image/*" multiple>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="save-btn"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.img-thumb-wrap {
    position:relative; width:80px; height:80px; border-radius:var(--radius-sm);
    overflow:hidden; border:1px solid var(--border-color);
}
.img-thumb-wrap img { width:100%; height:100%; object-fit:cover; }
.img-thumb-delete {
    position:absolute; top:2px; right:2px; width:22px; height:22px;
    border-radius:50%; background:var(--danger); color:#fff; border:none;
    cursor:pointer; font-size:0.65rem; display:flex; align-items:center; justify-content:center;
    opacity:0.85; transition:var(--transition);
}
.img-thumb-delete:hover { opacity:1; transform:scale(1.1); }
</style>
@endpush
@endsection

@push('scripts')
<script>
var allItems = [], currentPage = 1, perPage = 10;
var deletedImageIds = [];

// Load services for dropdown
axios.get(API_BASE + '/services/index').then(function(r) {
    var sel = document.getElementById('f-service_id');
    (r.data.data || []).forEach(function(s) {
        sel.innerHTML += '<option value="' + s.id + '">' + s.title + '</option>';
    });
});

function loadData(page) {
    currentPage = page || 1;
    var tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr class="loading-row"><td colspan="5"><span class="spinner"></span> Loading...</td></tr>';
    axios.get(API_BASE + '/projects/index?page=' + currentPage + '&per_page=' + perPage)
        .then(function(r) {
            allItems = r.data.data || [];
            renderTable(allItems);
            if (r.data.pagination) renderPagination(r.data.pagination);
        })
        .catch(function() { tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><p>Failed to load</p></td></tr>'; });
}

function renderTable(items) {
    var tbody = document.getElementById('table-body');
    if (!items.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><i class="fas fa-briefcase" style="display:block;"></i><p>No projects found</p></td></tr>'; return; }
    tbody.innerHTML = '';
    items.forEach(function(p) {
        var svcName = p.service ? p.service.title : '—';
        var imgCount = p.images ? p.images.length : 0;
        var statusBadge = '<span class="status-badge status-completed">Active</span>';
        tbody.innerHTML += '<tr>' +
            '<td style="color:var(--text-primary);font-weight:600;">' + (p.title||'—') + '</td>' +
            '<td>' + svcName + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + imgCount + '</td>' +
            '<td style="text-align:right;">' +
                '<button class="btn btn-outline btn-sm" onclick="editItem(' + p.id + ')" style="margin-right:0.25rem;"><i class="fas fa-edit"></i></button>' +
                '<button class="btn btn-danger btn-sm" onclick="deleteItem(' + p.id + ')"><i class="fas fa-trash"></i></button>' +
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

function filterTable() {
    var q = document.getElementById('search').value.toLowerCase();
    renderTable(allItems.filter(function(p) { return ((p.title||'') + ' ' + (p.description||'')).toLowerCase().indexOf(q) > -1; }));
}

function openModal(item) {
    document.getElementById('modal-title').textContent = item ? 'Edit Project' : 'Add Project';
    document.getElementById('edit-id').value = item ? item.id : '';
    document.getElementById('f-title').value = item ? item.title||'' : '';
    document.getElementById('f-service_id').value = item ? item.service_id||'' : '';
    document.getElementById('f-description').value = item ? item.description||'' : '';
    document.getElementById('f-project_url').value = item ? item.project_url||'' : '';
    document.getElementById('f-video_url').value = item ? item.video_url||'' : '';

    // Features - join as lines
    var featuresText = '';
    if (item && item.features && item.features.length) {
        featuresText = item.features.map(function(f) { return f.feature_text || f; }).join('\n');
    }
    document.getElementById('f-features').value = featuresText;

    document.getElementById('f-images').value = '';
    deletedImageIds = [];

    // Show existing images when editing
    var existingGroup = document.getElementById('existing-images-group');
    var existingContainer = document.getElementById('existing-images');
    existingContainer.innerHTML = '';

    if (item && item.images && item.images.length > 0) {
        existingGroup.style.display = 'block';
        item.images.forEach(function(img) {
            var wrap = document.createElement('div');
            wrap.className = 'img-thumb-wrap';
            wrap.id = 'img-wrap-' + img.id;
            wrap.innerHTML =
                '<img src="' + getImageUrl(img.image_path) + '" alt="Project image">' +
                '<button type="button" class="img-thumb-delete" onclick="markImageDelete(' + img.id + ')" title="Remove"><i class="fas fa-times"></i></button>';
            existingContainer.appendChild(wrap);
        });
    } else {
        existingGroup.style.display = 'none';
    }

    document.getElementById('modal-overlay').classList.add('open');
}

function markImageDelete(imgId) {
    deletedImageIds.push(imgId);
    var wrap = document.getElementById('img-wrap-' + imgId);
    if (wrap) {
        wrap.style.opacity = '0.3';
        wrap.style.pointerEvents = 'none';
        wrap.querySelector('.img-thumb-delete').style.display = 'none';
    }
}

function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); document.getElementById('modal-form').reset(); deletedImageIds = []; }

function editItem(id) {
    axios.get(API_BASE + '/projects/show/' + id)
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
    fd.append('service_id', document.getElementById('f-service_id').value);
    fd.append('description', document.getElementById('f-description').value);

    var projectUrl = document.getElementById('f-project_url').value;
    if (projectUrl) fd.append('project_url', projectUrl);

    var videoUrl = document.getElementById('f-video_url').value;
    if (videoUrl) fd.append('video_url', videoUrl);

    // Features as array
    var featuresText = document.getElementById('f-features').value.trim();
    if (featuresText) {
        var features = featuresText.split('\n').filter(function(f) { return f.trim(); });
        features.forEach(function(f) { fd.append('features[]', f.trim()); });
    }

    // Images
    var files = document.getElementById('f-images').files;
    for (var i = 0; i < files.length; i++) fd.append('images[]', files[i]);

    // Deleted images (for update)
    if (id && deletedImageIds.length > 0) {
        deletedImageIds.forEach(function(imgId) { fd.append('deleted_images[]', imgId); });
    }

    var url = id ? API_BASE + '/projects/update/' + id : API_BASE + '/projects/store';
    axios.post(url, fd, { headers:{'Content-Type':'multipart/form-data'} })
        .then(function() { showToast(id ? 'Project updated!' : 'Project created!'); closeModal(); loadData(currentPage); })
        .catch(function(err) {
            var msg = 'Save failed';
            if (err.response && err.response.data) { msg = err.response.data.errors ? Object.values(err.response.data.errors).flat().join(', ') : (err.response.data.message || msg); }
            showToast(msg, 'error');
        })
        .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save'; });
}

function deleteItem(id) {
    if (!confirm('Delete this project?')) return;
    axios.delete(API_BASE + '/projects/delete/' + id)
        .then(function() { showToast('Project deleted!'); loadData(currentPage); })
        .catch(function() { showToast('Delete failed', 'error'); });
}

loadData(1);
</script>
@endpush
