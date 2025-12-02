<div class="page-header">
    <h2>Add Auditable Entity</h2>
    <a href="<?php echo BASE_PATH; ?>/audit/universe" class="btn">← Back</a>
</div>
<div class="content-section">
    <form action="<?php echo BASE_PATH; ?>/audit/storeUniverse" method="POST" class="form">
        <?php echo Session::csrfField(); ?>
        <div class="form-group">
            <label for="name">Entity Name *</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">Select Category</option>
                    <option value="process">Process</option>
                    <option value="department">Department</option>
                    <option value="branch">Branch</option>
                    <option value="system">System</option>
                </select>
            </div>
            <div class="form-group">
                <label for="risk_score">Risk Score (0-25)</label>
                <input type="number" id="risk_score" name="risk_score" min="0" max="25" value="0">
            </div>
        </div>
        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Entity</button>
            <a href="<?php echo BASE_PATH; ?>/audit/universe" class="btn">Cancel</a>
        </div>
    </form>
</div>
