<div class="page-header">
    <h2>Audit Plans</h2>
    <a href="/audit/plan/create" class="btn btn-primary">+ Create Plan</a>
</div>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Type</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($plans)): foreach ($plans as $plan): ?>
                <tr>
                    <td><?php echo htmlspecialchars($plan['title']); ?></td>
                    <td><span class="badge"><?php echo ucfirst($plan['audit_type']); ?></span></td>
                    <td><?php echo $plan['planned_start'] ?? 'TBD'; ?></td>
                    <td><?php echo $plan['planned_end'] ?? 'TBD'; ?></td>
                    <td><span class="badge badge-<?php echo $plan['status']; ?>"><?php echo ucfirst($plan['status']); ?></span></td>
                    <td><a href="/audit/plan/view/<?php echo $plan['id']; ?>" class="btn btn-sm">View</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6">No audit plans found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
