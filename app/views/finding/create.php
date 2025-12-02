<div class="page-header">
    <h2>Create Finding</h2>
    <a href="<?php echo BASE_PATH; ?>/finding" class="btn">← Back</a>
</div>
<div class="content-section">
    <form action="<?php echo BASE_PATH; ?>/finding/store" method="POST" class="form">
        <?php echo Session::csrfField(); ?>
        <div class="form-group">
            <label for="audit_plan_id">Audit Plan *</label>
            <select id="audit_plan_id" name="audit_plan_id" required>
                <option value="">Select Audit Plan</option>
                <?php if (!empty($auditPlans)): foreach ($auditPlans as $plan): ?>
                    <option value="<?php echo $plan['id']; ?>"><?php echo htmlspecialchars($plan['title']); ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Finding Title *</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="severity">Severity *</label>
                <select id="severity" name="severity" required>
                    <option value="high">High</option>
                    <option value="medium" selected>Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="form-group">
                <label for="risk_rating">Risk Rating (0-25)</label>
                <input type="number" id="risk_rating" name="risk_rating" min="0" max="25" value="0">
            </div>
        </div>
        <div class="form-group">
            <label for="cause">Cause</label>
            <textarea id="cause" name="cause" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label for="effect">Effect</label>
            <textarea id="effect" name="effect" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label for="criteria">Criteria (Standard/Policy Violated)</label>
            <textarea id="criteria" name="criteria" rows="2"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Finding</button>
            <a href="<?php echo BASE_PATH; ?>/finding" class="btn">Cancel</a>
        </div>
    </form>
</div>
