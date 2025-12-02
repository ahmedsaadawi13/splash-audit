<div class="page-header">
    <h2>Create Corrective Action</h2>
    <a href="/corrective-action" class="btn">← Back</a>
</div>
<div class="content-section">
    <form action="/corrective-action/store" method="POST" enctype="multipart/form-data" class="form">
        <?php echo Session::csrfField(); ?>
        <div class="form-group">
            <label for="finding_id">Finding *</label>
            <select id="finding_id" name="finding_id" required>
                <option value="">Select Finding</option>
                <?php if (!empty($findings)): foreach ($findings as $finding): ?>
                    <option value="<?php echo $finding['id']; ?>"><?php echo htmlspecialchars($finding['title']); ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="action_description">Action Description *</label>
            <textarea id="action_description" name="action_description" rows="4" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="due_date">Due Date *</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>
            <div class="form-group">
                <label for="responsible_user_id">Responsible Person</label>
                <select id="responsible_user_id" name="responsible_user_id">
                    <option value="">Select User</option>
                    <?php if (!empty($users)): foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="evidence_file">Evidence File (Optional)</label>
            <input type="file" id="evidence_file" name="evidence_file" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png">
            <small>Allowed: PDF, DOCX, XLSX, JPG, PNG (Max: 10MB)</small>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Action</button>
            <a href="/corrective-action" class="btn">Cancel</a>
        </div>
    </form>
</div>
