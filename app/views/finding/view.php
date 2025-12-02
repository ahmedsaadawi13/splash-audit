<div class="page-header">
    <h2><?php echo htmlspecialchars($finding['title']); ?></h2>
    <a href="<?php echo BASE_PATH; ?>/finding" class="btn">← Back to Findings</a>
</div>
<div class="content-section">
    <div class="details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div><strong>Severity:</strong> <span class="badge badge-<?php echo $finding['severity']; ?>"><?php echo ucfirst($finding['severity']); ?></span></div>
        <div><strong>Status:</strong> <span class="badge badge-<?php echo $finding['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $finding['status'])); ?></span></div>
        <div><strong>Risk Rating:</strong> <?php echo $finding['risk_rating']; ?></div>
        <div><strong>Created:</strong> <?php echo date('Y-m-d H:i', strtotime($finding['created_at'])); ?></div>
    </div>
    <div style="margin-bottom: 20px;"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($finding['description'])); ?></div>
    <?php if (!empty($finding['cause'])): ?>
        <div style="margin-bottom: 20px;"><strong>Cause:</strong><br><?php echo nl2br(htmlspecialchars($finding['cause'])); ?></div>
    <?php endif; ?>
    <?php if (!empty($finding['effect'])): ?>
        <div style="margin-bottom: 20px;"><strong>Effect:</strong><br><?php echo nl2br(htmlspecialchars($finding['effect'])); ?></div>
    <?php endif; ?>
    <h3>Corrective Actions (<?php echo count($correctiveActions ?? []); ?>)</h3>
    <table class="data-table">
        <thead>
            <tr><th>Action</th><th>Due Date</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($correctiveActions)): foreach ($correctiveActions as $action): ?>
                <tr>
                    <td><?php echo htmlspecialchars(substr($action['action_description'], 0, 100)); ?>...</td>
                    <td><?php echo $action['due_date']; ?></td>
                    <td><span class="badge badge-<?php echo $action['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $action['status'])); ?></span></td>
                    <td><a href="<?php echo BASE_PATH; ?>/corrective-action/view/<?php echo $action['id']; ?>" class="btn btn-sm">View</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4">No corrective actions yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
