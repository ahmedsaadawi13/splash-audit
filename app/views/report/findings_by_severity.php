<div class="page-header">
    <h2>Findings by Severity Report</h2>
    <a href="<?php echo BASE_PATH; ?>/report" class="btn">← Back to Reports</a>
    <a href="<?php echo BASE_PATH; ?>/report/findings-by-severity?export=csv" class="btn btn-primary">📥 Export CSV</a>
</div>
<?php if (!empty($statistics)): ?>
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card"><h4>High Severity</h4><div class="stat-value" style="color: #e74c3c;"><?php echo $statistics['high_severity'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Medium Severity</h4><div class="stat-value" style="color: #f39c12;"><?php echo $statistics['medium_severity'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Low Severity</h4><div class="stat-value" style="color: #3498db;"><?php echo $statistics['low_severity'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Total Findings</h4><div class="stat-value"><?php echo $statistics['total_findings'] ?? 0; ?></div></div>
</div>
<?php endif; ?>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>ID</th><th>Title</th><th>Severity</th><th>Status</th><th>Created</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($findings)): foreach ($findings as $finding): ?>
                <tr>
                    <td><?php echo $finding['id']; ?></td>
                    <td><?php echo htmlspecialchars($finding['title']); ?></td>
                    <td><span class="badge badge-<?php echo $finding['severity']; ?>"><?php echo ucfirst($finding['severity']); ?></span></td>
                    <td><?php echo ucfirst(str_replace('_', ' ', $finding['status'])); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($finding['created_at'])); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5">No findings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
