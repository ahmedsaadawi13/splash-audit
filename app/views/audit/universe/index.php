<div class="page-header">
    <h2>Audit Universe</h2>
    <a href="<?php echo BASE_PATH; ?>/audit/universe/create" class="btn btn-primary">+ Add Entity</a>
</div>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>Name</th><th>Category</th><th>Risk Score</th><th>Last Audit</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($entities)): foreach ($entities as $entity): ?>
                <tr>
                    <td><?php echo htmlspecialchars($entity['name']); ?></td>
                    <td><?php echo htmlspecialchars($entity['category'] ?? 'N/A'); ?></td>
                    <td><span class="badge <?php echo $entity['risk_score'] >= 15 ? 'badge-high' : ($entity['risk_score'] >= 8 ? 'badge-medium' : 'badge-low'); ?>"><?php echo $entity['risk_score']; ?></span></td>
                    <td><?php echo $entity['last_audit_date'] ?? 'Never'; ?></td>
                    <td><span class="badge badge-<?php echo $entity['status']; ?>"><?php echo $entity['status']; ?></span></td>
                    <td><a href="<?php echo BASE_PATH; ?>/audit/universe/edit/<?php echo $entity['id']; ?>" class="btn btn-sm">Edit</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6">No entities found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
