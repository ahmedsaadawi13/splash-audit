<!-- FILE: /app/views/dashboard/auditor.php -->
<div class="dashboard">
    <h2>Auditor Dashboard</h2>

    <div class="recent-section">
        <h3>My Assigned Audits</h3>
        <?php if (!empty($assigned_audits)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assigned_audits as $audit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($audit['title']); ?></td>
                            <td><?php echo htmlspecialchars($audit['audit_type']); ?></td>
                            <td><?php echo $audit['planned_start']; ?></td>
                            <td><span class="badge badge-<?php echo $audit['status']; ?>"><?php echo $audit['status']; ?></span></td>
                            <td><a href="/audit/plan/view/<?php echo $audit['id']; ?>" class="btn btn-sm">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No audits assigned to you yet.</p>
        <?php endif; ?>
    </div>

    <div class="recent-section">
        <h3>My Findings</h3>
        <?php if (!empty($assigned_findings)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assigned_findings as $finding): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($finding['title']); ?></td>
                            <td><span class="badge badge-<?php echo $finding['severity']; ?>"><?php echo $finding['severity']; ?></span></td>
                            <td><?php echo $finding['status']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($finding['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No findings assigned to you.</p>
        <?php endif; ?>
    </div>
</div>
