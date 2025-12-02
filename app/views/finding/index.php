<div class="page-header">
    <h2>Findings</h2>
    <a href="<?php echo BASE_PATH; ?>/finding/create" class="btn btn-primary">+ Create Finding</a>
</div>
<?php if (!empty($statistics)): ?>
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card"><h4>Total</h4><div class="stat-value"><?php echo $statistics['total_findings'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Open</h4><div class="stat-value"><?php echo $statistics['open'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>High Severity</h4><div class="stat-value" style="color: #e74c3c;"><?php echo $statistics['high_severity'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Closed</h4><div class="stat-value" style="color: #27ae60;"><?php echo $statistics['closed'] ?? 0; ?></div></div>
</div>
<?php endif; ?>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Severity</th><th>Status</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($findings)): foreach ($findings as $finding): ?>
                <tr>
                    <td><?php echo htmlspecialchars($finding['title']); ?></td>
                    <td><span class="badge badge-<?php echo $finding['severity']; ?>"><?php echo ucfirst($finding['severity']); ?></span></td>
                    <td><?php echo ucfirst(str_replace('_', ' ', $finding['status'])); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($finding['created_at'])); ?></td>
                    <td><a href="<?php echo BASE_PATH; ?>/finding/view/<?php echo $finding['id']; ?>" class="btn btn-sm">View</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5">No findings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
