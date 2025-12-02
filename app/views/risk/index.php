<div class="page-header">
    <h2>Risk Register</h2>
    <a href="/risk/create" class="btn btn-primary">+ Add Risk</a>
    <a href="/risk/heatmap" class="btn">View Heatmap</a>
</div>
<?php if (!empty($statistics)): ?>
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card"><h4>Total Risks</h4><div class="stat-value"><?php echo $statistics['total_risks'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>High Risks</h4><div class="stat-value" style="color: #e74c3c;"><?php echo $statistics['high_risks'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Medium Risks</h4><div class="stat-value" style="color: #f39c12;"><?php echo $statistics['medium_risks'] ?? 0; ?></div></div>
    <div class="stat-card"><h4>Low Risks</h4><div class="stat-value" style="color: #3498db;"><?php echo $statistics['low_risks'] ?? 0; ?></div></div>
</div>
<?php endif; ?>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Category</th><th>Likelihood</th><th>Impact</th><th>Inherent Risk</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($risks)): foreach ($risks as $risk): ?>
                <tr>
                    <td><?php echo htmlspecialchars($risk['title']); ?></td>
                    <td><?php echo htmlspecialchars($risk['category'] ?? 'N/A'); ?></td>
                    <td><?php echo $risk['likelihood']; ?>/5</td>
                    <td><?php echo $risk['impact']; ?>/5</td>
                    <td><span class="badge <?php echo $risk['inherent_risk'] >= 15 ? 'badge-high' : ($risk['inherent_risk'] >= 8 ? 'badge-medium' : 'badge-low'); ?>"><?php echo $risk['inherent_risk']; ?></span></td>
                    <td><span class="badge badge-<?php echo $risk['status']; ?>"><?php echo ucfirst($risk['status']); ?></span></td>
                    <td><a href="/risk/edit/<?php echo $risk['id']; ?>" class="btn btn-sm">Edit</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7">No risks found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
