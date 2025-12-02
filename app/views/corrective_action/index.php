<div class="page-header">
    <h2>Corrective Actions</h2>
    <a href="<?php echo BASE_PATH; ?>/corrective-action/create" class="btn btn-primary">+ Create Action</a>
</div>
<?php if (!empty($statistics)): ?>
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card"><h4>Total</h4><div class="stat-value"><?php echo $statistics['total_actions'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Open</h4><div class="stat-value"><?php echo $statistics['open'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Overdue</h4><div class="stat-value" style="color: #e74c3c;"><?php echo $statistics['overdue'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Completed</h4><div class="stat-value" style="color: #27ae60;"><?php echo $statistics['completed'] ?? 0; ?></div></div>
</div>
<?php endif; ?>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>Action</th><th>Due Date</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($actions)): foreach ($actions as $action): ?>
                <tr style="<?php echo ($action['status'] != 'completed' && strtotime($action['due_date']) < time()) ? 'background-color: #ffebee;' : ''; ?>">
                    <td><?php echo htmlspecialchars(substr($action['action_description'], 0, 100)); ?>...</td>
                    <td><?php echo $action['due_date']; ?></td>
                    <td><span class="badge badge-<?php echo $action['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $action['status'])); ?></span></td>
                    <td>
                        <form action="<?php echo BASE_PATH; ?>/corrective-action/updateStatus/<?php echo $action['id']; ?>" method="POST" style="display: inline;">
                            <?php echo Session::csrfField(); ?>
                            <select name="status" onchange="this.form.submit()" style="padding: 5px;">
                                <option value="open" <?php echo $action['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="pending_review" <?php echo $action['status'] === 'pending_review' ? 'selected' : ''; ?>>Pending Review</option>
                                <option value="completed" <?php echo $action['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4">No corrective actions found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
