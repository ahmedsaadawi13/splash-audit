<div class="page-header">
    <h2>Audit Summary Report</h2>
    <a href="/report" class="btn">← Back to Reports</a>
    <a href="/report/audit-summary?export=csv" class="btn btn-primary">📥 Export CSV</a>
</div>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>ID</th><th>Title</th><th>Type</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($plans)): foreach ($plans as $plan): ?>
                <tr>
                    <td><?php echo $plan['id']; ?></td>
                    <td><?php echo htmlspecialchars($plan['title']); ?></td>
                    <td><?php echo ucfirst($plan['audit_type']); ?></td>
                    <td><?php echo $plan['planned_start']; ?></td>
                    <td><?php echo $plan['planned_end']; ?></td>
                    <td><span class="badge badge-<?php echo $plan['status']; ?>"><?php echo ucfirst($plan['status']); ?></span></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6">No audit plans found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
