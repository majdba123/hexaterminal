@extends('layouts.admin')
@section('title', 'Teams')
@section('page-title', 'Team Members')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-users" style="color:var(--primary);margin-right:0.5rem;font-size:0.85rem;"></i> Team Members</h2>
        <div class="table-actions">
            <input type="text" class="search-input" id="search" placeholder="Search members..." oninput="filterTable()">
            <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Member</button>
        </div>
    </div>
    <table class="admin-table">
        <thead><tr><th>Photo</th><th>Name</th><th>Position</th><th>Email</th><th>Specialization</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody id="table-body"><tr class="loading-row"><td colspan="6"><span class="spinner"></span> Loading...</td></tr></tbody>
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
            <h3 id="modal-title">Add Team Member</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="modal-form" onsubmit="saveItem(event)">
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-input" id="f-first_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-input" id="f-last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-input" id="f-email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-input" id="f-phone" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Position *</label>
                        <input type="text" class="form-input" id="f-position" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Specialization *</label>
                        <input type="text" class="form-input" id="f-specialization" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">GitHub URL</label>
                    <input type="url" class="form-input" id="f-github_url" placeholder="https://github.com/...">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Photo</label>
                        <input type="file" class="form-input" id="f-photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CV File</label>
                        <input type="file" class="form-input" id="f-cv_file" accept=".pdf,.doc,.docx">
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
var allItems = [];
var currentPage = 1;
var perPage = 10;

function loadData(page) {
    currentPage = page || 1;
    var tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr class="loading-row"><td colspan="6"><span class="spinner"></span> Loading...</td></tr>';

    axios.get(API_BASE + '/teams/index?page=' + currentPage + '&per_page=' + perPage)
        .then(function(r) {
            var items = r.data.data || [];
            allItems = items;
            var pag = r.data.pagination;
            renderTable(items);
            if (pag) renderPagination(pag);
        })
        .catch(function() { tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><p>Failed to load data</p></td></tr>'; });
}

function renderTable(items) {
    var tbody = document.getElementById('table-body');
    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><i class="fas fa-users" style="display:block;"></i><p>No team members found</p></td></tr>';
        return;
    }
    tbody.innerHTML = '';
    items.forEach(function(m) {
        var photo = m.photo ? '<img src="' + getImageUrl(m.photo) + '" class="thumb" alt="">' : '<div style="width:40px;height:40px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;">' + (m.first_name||'?').charAt(0) + '</div>';
        tbody.innerHTML += '<tr>' +
            '<td>' + photo + '</td>' +
            '<td style="color:var(--text-primary);font-weight:600;">' + ((m.first_name||'') + ' ' + (m.last_name||'')).trim() + '</td>' +
            '<td>' + (m.position||'—') + '</td>' +
            '<td>' + (m.email||'—') + '</td>' +
            '<td>' + (m.specialization||'—') + '</td>' +
            '<td style="text-align:right;">' +
                '<button class="btn btn-outline btn-sm" onclick="editItem(' + m.id + ')" style="margin-right:0.25rem;"><i class="fas fa-edit"></i></button>' +
                '<button class="btn btn-danger btn-sm" onclick="deleteItem(' + m.id + ')"><i class="fas fa-trash"></i></button>' +
            '</td></tr>';
    });
}

function renderPagination(pag) {
    var bar = document.getElementById('pagination-bar');
    bar.style.display = 'flex';
    document.getElementById('pagination-info').textContent = 'Showing ' + (pag.from||0) + '-' + (pag.to||0) + ' of ' + pag.total;
    var btns = document.getElementById('pagination-btns');
    btns.innerHTML = '';
    btns.innerHTML += '<button ' + (pag.current_page <= 1 ? 'disabled' : '') + ' onclick="loadData(' + (pag.current_page-1) + ')"><i class="fas fa-chevron-left"></i></button>';
    for (var i = 1; i <= pag.last_page; i++) {
        btns.innerHTML += '<button class="' + (i === pag.current_page ? 'active' : '') + '" onclick="loadData(' + i + ')">' + i + '</button>';
    }
    btns.innerHTML += '<button ' + (pag.current_page >= pag.last_page ? 'disabled' : '') + ' onclick="loadData(' + (pag.current_page+1) + ')"><i class="fas fa-chevron-right"></i></button>';
}

function filterTable() {
    var q = document.getElementById('search').value.toLowerCase();
    var filtered = allItems.filter(function(m) {
        return ((m.first_name||'') + ' ' + (m.last_name||'') + ' ' + (m.email||'') + ' ' + (m.position||'')).toLowerCase().indexOf(q) > -1;
    });
    renderTable(filtered);
}

function openModal(item) {
    document.getElementById('modal-title').textContent = item ? 'Edit Team Member' : 'Add Team Member';
    document.getElementById('edit-id').value = item ? item.id : '';
    document.getElementById('f-first_name').value = item ? item.first_name || '' : '';
    document.getElementById('f-last_name').value = item ? item.last_name || '' : '';
    document.getElementById('f-email').value = item ? item.email || '' : '';
    document.getElementById('f-phone').value = item ? item.phone || '' : '';
    document.getElementById('f-position').value = item ? item.position || '' : '';
    document.getElementById('f-specialization').value = item ? item.specialization || '' : '';
    document.getElementById('f-github_url').value = item ? item.github_url || '' : '';
    document.getElementById('f-photo').value = '';
    document.getElementById('f-cv_file').value = '';
    document.getElementById('modal-overlay').classList.add('open');
}

function closeModal() {
    document.getElementById('modal-overlay').classList.remove('open');
    document.getElementById('modal-form').reset();
}

function editItem(id) {
    axios.get(API_BASE + '/teams/show/' + id)
        .then(function(r) { openModal(r.data.data); })
        .catch(function() { showToast('Failed to load member', 'error'); });
}

function saveItem(e) {
    e.preventDefault();
    var id = document.getElementById('edit-id').value;
    var btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Saving...';

    var fd = new FormData();
    fd.append('first_name', document.getElementById('f-first_name').value);
    fd.append('last_name', document.getElementById('f-last_name').value);
    fd.append('email', document.getElementById('f-email').value);
    fd.append('phone', document.getElementById('f-phone').value);
    fd.append('position', document.getElementById('f-position').value);
    fd.append('specialization', document.getElementById('f-specialization').value);
    var githubUrl = document.getElementById('f-github_url').value;
    if (githubUrl) fd.append('github_url', githubUrl);
    var photo = document.getElementById('f-photo').files[0];
    var cv = document.getElementById('f-cv_file').files[0];
    if (photo) fd.append('photo', photo);
    if (cv) fd.append('cv_file', cv);

    var url = id ? API_BASE + '/teams/update/' + id : API_BASE + '/teams/store';
    axios.post(url, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(function() {
            showToast(id ? 'Member updated!' : 'Member created!');
            closeModal();
            loadData(currentPage);
        })
        .catch(function(err) {
            var msg = 'Save failed';
            if (err.response && err.response.data) {
                if (err.response.data.errors) {
                    msg = Object.values(err.response.data.errors).flat().join(', ');
                } else if (err.response.data.message) {
                    msg = err.response.data.message;
                }
            }
            showToast(msg, 'error');
        })
        .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save'; });
}

function deleteItem(id) {
    if (!confirm('Are you sure you want to delete this member?')) return;
    axios.delete(API_BASE + '/teams/delete/' + id)
        .then(function() { showToast('Member deleted!'); loadData(currentPage); })
        .catch(function() { showToast('Delete failed', 'error'); });
}

loadData(1);
</script>
@endpush

