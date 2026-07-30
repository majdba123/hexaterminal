@extends('layouts.admin')
@section('title', 'FAQ')
@section('page-title', 'FAQ Management')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-question-circle" style="color:var(--primary);margin-right:0.5rem;font-size:0.85rem;"></i> FAQs</h2>
        <div class="table-actions">
            <input type="text" class="search-input" id="search" placeholder="Search FAQs..." oninput="filterTable()">
            <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add FAQ</button>
        </div>
    </div>
    <table class="admin-table">
        <thead><tr><th style="width:35%;">Question</th><th>Answer</th><th style="text-align:right;width:120px;">Actions</th></tr></thead>
        <tbody id="table-body"><tr class="loading-row"><td colspan="3"><span class="spinner"></span> Loading...</td></tr></tbody>
    </table>
    <div class="pagination-bar" id="pagination-bar" style="display:none;">
        <div class="pagination-info" id="pagination-info"></div>
        <div class="pagination-btns" id="pagination-btns"></div>
    </div>
</div>

<div class="modal-overlay" id="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-title">Add FAQ</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="modal-form" onsubmit="saveItem(event)">
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label class="form-label">Question *</label>
                    <input type="text" class="form-input" id="f-question" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Answer *</label>
                    <textarea class="form-textarea" id="f-answer" required style="min-height:120px;"></textarea>
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
    tbody.innerHTML = '<tr class="loading-row"><td colspan="3"><span class="spinner"></span> Loading...</td></tr>';
    axios.get(API_BASE + '/faq/index?page=' + currentPage + '&per_page=' + perPage)
        .then(function(r) { allItems = r.data.data || []; renderTable(allItems); if (r.data.pagination) renderPagination(r.data.pagination); })
        .catch(function() { tbody.innerHTML = '<tr><td colspan="3" class="empty-state"><p>Failed to load</p></td></tr>'; });
}

function renderTable(items) {
    var tbody = document.getElementById('table-body');
    if (!items.length) { tbody.innerHTML = '<tr><td colspan="3" class="empty-state"><i class="fas fa-question-circle" style="display:block;"></i><p>No FAQs found</p></td></tr>'; return; }
    tbody.innerHTML = '';
    items.forEach(function(f) {
        var ans = (f.answer||'').substring(0,80) + ((f.answer||'').length > 80 ? '...' : '');
        tbody.innerHTML += '<tr><td style="color:var(--text-primary);font-weight:500;">' + (f.question||'—') + '</td><td>' + ans + '</td><td style="text-align:right;"><button class="btn btn-outline btn-sm" onclick="editItem(' + f.id + ')" style="margin-right:0.25rem;"><i class="fas fa-edit"></i></button><button class="btn btn-danger btn-sm" onclick="deleteItem(' + f.id + ')"><i class="fas fa-trash"></i></button></td></tr>';
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

function filterTable() { var q = document.getElementById('search').value.toLowerCase(); renderTable(allItems.filter(function(f) { return ((f.question||'')+' '+(f.answer||'')).toLowerCase().indexOf(q)>-1; })); }

function openModal(item) {
    document.getElementById('modal-title').textContent = item ? 'Edit FAQ' : 'Add FAQ';
    document.getElementById('edit-id').value = item ? item.id : '';
    document.getElementById('f-question').value = item ? item.question||'' : '';
    document.getElementById('f-answer').value = item ? item.answer||'' : '';
    document.getElementById('modal-overlay').classList.add('open');
}
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); document.getElementById('modal-form').reset(); }

function editItem(id) { axios.get(API_BASE + '/faq/show/' + id).then(function(r) { openModal(r.data.data); }).catch(function() { showToast('Failed to load', 'error'); }); }

function saveItem(e) {
    e.preventDefault();
    var id = document.getElementById('edit-id').value;
    var btn = document.getElementById('save-btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Saving...';
    var data = { question: document.getElementById('f-question').value, answer: document.getElementById('f-answer').value };
    var req = id ? axios.put(API_BASE + '/faq/update/' + id, data) : axios.post(API_BASE + '/faq/store', data);
    req.then(function() { showToast(id ? 'FAQ updated!' : 'FAQ created!'); closeModal(); loadData(currentPage); })
       .catch(function(err) { var msg='Save failed'; if(err.response&&err.response.data){msg=err.response.data.errors?Object.values(err.response.data.errors).flat().join(', '):(err.response.data.message||msg);} showToast(msg,'error'); })
       .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save'; });
}

function deleteItem(id) {
    if (!confirm('Delete this FAQ?')) return;
    axios.delete(API_BASE + '/faq/delete/' + id).then(function() { showToast('FAQ deleted!'); loadData(currentPage); }).catch(function() { showToast('Delete failed', 'error'); });
}

loadData(1);
</script>
@endpush

