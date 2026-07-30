@extends('layouts.admin')
@section('title', 'About Us')
@section('page-title', 'About Us')

@section('content')
<div class="table-card">
    <div class="table-header">
        <h2><i class="fas fa-info-circle" style="color:var(--primary);margin-right:0.5rem;font-size:0.85rem;"></i> About Us</h2>
        <button class="btn btn-primary" id="action-btn" onclick="openModal()"><i class="fas fa-plus"></i> Create</button>
    </div>
    <div id="content-area" style="padding:1.5rem;">
        <div style="text-align:center;padding:2rem;color:var(--text-muted);"><span class="spinner"></span> Loading...</div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal-overlay">
    <div class="modal" style="max-width:650px;">
        <div class="modal-header">
            <h3 id="modal-title">About Us</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="modal-form" onsubmit="saveItem(event)">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Company Name *</label>
                        <input type="text" class="form-input" id="f-company_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-input" id="f-contact_email">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea class="form-textarea" id="f-company_description" required style="min-height:120px;"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Website URL</label>
                        <input type="url" class="form-input" id="f-website_url">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Foundation Date</label>
                        <input type="date" class="form-input" id="f-foundation_date">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Facebook URL</label>
                        <input type="url" class="form-input" id="f-facebook_url">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Instagram URL</label>
                        <input type="url" class="form-input" id="f-instagram_url">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">LinkedIn URL</label>
                        <input type="url" class="form-input" id="f-linkedin_url">
                    </div>
                    <div class="form-group">
                        <label class="form-label">GitHub URL</label>
                        <input type="url" class="form-input" id="f-github_url">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Logo</label>
                    <input type="file" class="form-input" id="f-company_logo" accept="image/*">
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
var aboutData = null;

function loadData() {
    var area = document.getElementById('content-area');
    area.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><span class="spinner"></span> Loading...</div>';

    axios.get(API_BASE + '/about_us/show')
        .then(function(r) {
            aboutData = r.data.data;
            document.getElementById('action-btn').innerHTML = '<i class="fas fa-edit"></i> Edit';
            area.innerHTML =
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">' +
                    infoCard('Company Name', aboutData.company_name) +
                    infoCard('Contact Email', aboutData.contact_email) +
                    '<div style="grid-column:1/-1;">' + infoCard('Description', aboutData.company_description) + '</div>' +
                    infoCard('Website', aboutData.website_url, true) +
                    infoCard('Foundation Date', aboutData.foundation_date ? aboutData.foundation_date.substring(0,10) : null) +
                    infoCard('Facebook', aboutData.facebook_url, true) +
                    infoCard('Instagram', aboutData.instagram_url, true) +
                    infoCard('LinkedIn', aboutData.linkedin_url, true) +
                    infoCard('GitHub', aboutData.github_url, true) +
                '</div>';
        })
        .catch(function(err) {
            if (err.response && err.response.status === 404) {
                aboutData = null;
                document.getElementById('action-btn').innerHTML = '<i class="fas fa-plus"></i> Create';
                area.innerHTML = '<div class="empty-state"><i class="fas fa-info-circle" style="display:block;"></i><p>No About Us record yet. Click "Create" to add one.</p></div>';
            } else {
                area.innerHTML = '<div class="empty-state"><p>Failed to load</p></div>';
            }
        });
}

function infoCard(label, value, isUrl) {
    var display = value || '<span style="color:var(--text-muted);">Not set</span>';
    if (isUrl && value) display = '<a href="' + value + '" target="_blank" style="color:var(--primary);text-decoration:none;">' + value + '</a>';
    return '<div style="padding:1rem;background:rgba(6,9,24,0.4);border:1px solid var(--border-color);border-radius:var(--radius-sm);">' +
        '<div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);font-weight:600;margin-bottom:0.35rem;">' + label + '</div>' +
        '<div style="font-size:0.9rem;color:var(--text-primary);word-break:break-word;">' + display + '</div></div>';
}

function openModal() {
    var d = aboutData;
    document.getElementById('modal-title').textContent = d ? 'Edit About Us' : 'Create About Us';
    document.getElementById('f-company_name').value = d ? d.company_name||'' : '';
    document.getElementById('f-company_description').value = d ? d.company_description||'' : '';
    document.getElementById('f-contact_email').value = d ? d.contact_email||'' : '';
    document.getElementById('f-website_url').value = d ? d.website_url||'' : '';
    document.getElementById('f-foundation_date').value = d ? (d.foundation_date||'').substring(0,10) : '';
    document.getElementById('f-facebook_url').value = d ? d.facebook_url||'' : '';
    document.getElementById('f-instagram_url').value = d ? d.instagram_url||'' : '';
    document.getElementById('f-linkedin_url').value = d ? d.linkedin_url||'' : '';
    document.getElementById('f-github_url').value = d ? d.github_url||'' : '';
    document.getElementById('f-company_logo').value = '';
    document.getElementById('modal-overlay').classList.add('open');
}

function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

function saveItem(e) {
    e.preventDefault();
    var btn = document.getElementById('save-btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Saving...';

    var fd = new FormData();
    // Always send required fields
    fd.append('company_name', document.getElementById('f-company_name').value);
    fd.append('company_description', document.getElementById('f-company_description').value);
    // Optional fields: only append if not empty
    var optionalFields = ['contact_email','website_url','foundation_date','facebook_url','instagram_url','linkedin_url','github_url'];
    optionalFields.forEach(function(f) {
        var val = document.getElementById('f-' + f).value;
        if (val) fd.append(f, val);
    });
    var logo = document.getElementById('f-company_logo').files[0];
    if (logo) fd.append('company_logo', logo);

    var url = aboutData ? API_BASE + '/about_us/update' : API_BASE + '/about_us/store';
    axios.post(url, fd, { headers:{'Content-Type':'multipart/form-data'} })
        .then(function() { showToast('About Us saved!'); closeModal(); loadData(); })
        .catch(function(err) {
            var msg = 'Save failed';
            if (err.response && err.response.data) { msg = err.response.data.errors ? Object.values(err.response.data.errors).flat().join(', ') : (err.response.data.message || msg); }
            showToast(msg, 'error');
        })
        .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save'; });
}

loadData();
</script>
@endpush

