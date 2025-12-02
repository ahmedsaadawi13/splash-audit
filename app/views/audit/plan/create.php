<div class="page-header">
    <h2>Create Audit Plan</h2>
    <a href="/audit/plans" class="btn">← Back</a>
</div>
<div class="content-section">
    <form action="/audit/storePlan" method="POST" class="form">
        <?php echo Session::csrfField(); ?>
        <div class="form-group">
            <label for="title">Audit Title *</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="audit_type">Audit Type *</label>
                <select id="audit_type" name="audit_type" required>
                    <option value="internal">Internal</option>
                    <option value="compliance">Compliance</option>
                    <option value="surprise">Surprise</option>
                    <option value="operational">Operational</option>
                    <option value="financial">Financial</option>
                </select>
            </div>
            <div class="form-group">
                <label for="planned_start">Start Date *</label>
                <input type="date" id="planned_start" name="planned_start" required>
            </div>
            <div class="form-group">
                <label for="planned_end">End Date *</label>
                <input type="date" id="planned_end" name="planned_end" required>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="scope">Scope</label>
            <textarea id="scope" name="scope" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="objectives">Objectives</label>
            <textarea id="objectives" name="objectives" rows="3"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Audit Plan</button>
            <a href="/audit/plans" class="btn">Cancel</a>
        </div>
    </form>
</div>
