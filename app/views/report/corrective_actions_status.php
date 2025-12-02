<div class="page-header">
    <h2>Corrective Actions Status Report</h2>
    <a href="/report" class="btn">← Back to Reports</a>
    <a href="/report/corrective-actions-status?export=csv" class="btn btn-primary">📥 Export CSV</a>
</div>
<?php if (!empty($statistics)): ?>
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card"><h4>Open</h4><div class="stat-value"><?php echo $statistics['open'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Pending Review</h4><div class="stat-value"><?php echo $statistics['pending_review'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Completed</h4><div class="stat-value" style="color: #27ae60;"><?php echo $statistics['completed'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Overdue</h4><div class="stat-value" style="color: #e74c3c;"><?php echo $statistics['overdue'] ?? 0; ?></div></div>
</div>
<?php endif; ?>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>ID</th><th>Action</th><th>Due Date</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($actions)): foreach ($actions as $action): ?>
                <tr style="<?php echo ($action['status'] != 'completed' && strtotime($action['due_date']) < time()) ? 'background-color: #ffebee;' : ''; ?>">
                    <td><?php echo $action['id']; ?></td>
                    <td><?php echo htmlspecialchars(substr($action['action_description'], 0, 100)); ?></td>
                    <td><?php echo $action['due_date']; ?></td>
                    <td><span class="badge badge-<?php echo $action['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $action['status'])); ?></span></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4">No corrective actions found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
