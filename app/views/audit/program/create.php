<div class="page-header">
    <h2>Create Audit Program</h2>
    <a href="<?php echo BASE_PATH; ?>/audit/programs" class="btn">← Back</a>
</div>
<div class="content-section">
    <form action="<?php echo BASE_PATH; ?>/audit/storeProgram" method="POST" class="form">
        <?php echo Session::csrfField(); ?>
        <div class="form-group">
            <label for="title">Program Title *</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="year">Year *</label>
                <input type="number" id="year" name="year" value="<?php echo date('Y'); ?>" required>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date">
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date">
            </div>
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
            <button type="submit" class="btn btn-primary">Create Program</button>
            <a href="<?php echo BASE_PATH; ?>/audit/programs" class="btn">Cancel</a>
        </div>
    </form>
</div>
