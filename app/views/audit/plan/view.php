<div class="page-header">
    <h2><?php echo htmlspecialchars($plan['title']); ?></h2>
    <a href="<?php echo BASE_PATH; ?>/audit/plans" class="btn">← Back to Plans</a>
</div>
<div class="content-section">
    <div class="details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div><strong>Type:</strong> <?php echo ucfirst($plan['audit_type']); ?></div>
        <div><strong>Status:</strong> <span class="badge badge-<?php echo $plan['status']; ?>"><?php echo ucfirst($plan['status']); ?></span></div>
        <div><strong>Planned Start:</strong> <?php echo $plan['planned_start']; ?></div>
        <div><strong>Planned End:</strong> <?php echo $plan['planned_end']; ?></div>
        <div><strong>Actual Start:</strong> <?php echo $plan['actual_start'] ?? 'Not started'; ?></div>
        <div><strong>Actual End:</strong> <?php echo $plan['actual_end'] ?? 'Not completed'; ?></div>
    </div>
    <?php if (!empty($plan['description'])): ?>
        <div style="margin-bottom: 20px;"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($plan['description'])); ?></div>
    <?php endif; ?>
    <h3>Findings (<?php echo count($findings ?? []); ?>)</h3>
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
                <tr><td colspan="5">No findings yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
