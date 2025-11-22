<!-- FILE: /app/views/dashboard/process_owner.php -->
<div class="dashboard">
    <h2>Process Owner Dashboard</h2>

    <div class="recent-section">
        <h3>My Findings</h3>
        <?php if (!empty($assigned_findings)): ?>
            <table class="data-table">
                <thead>
                    <tr><th>Title</th><th>Severity</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($assigned_findings as $finding): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($finding['title']); ?></td>
                            <td><span class="badge badge-<?php echo $finding['severity']; ?>"><?php echo $finding['severity']; ?></span></td>
                            <td><?php echo $finding['status']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No findings assigned.</p>
        <?php endif; ?>
    </div>

    <div class="recent-section">
        <h3>My Corrective Actions</h3>
        <?php if (!empty($assigned_actions)): ?>
            <table class="data-table">
                <thead>
                    <tr><th>Action</th><th>Due Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($assigned_actions as $action): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($action['action_description'], 0, 100)); ?></td>
                            <td><?php echo $action['due_date']; ?></td>
                            <td><?php echo $action['status']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No corrective actions assigned.</p>
        <?php endif; ?>
    </div>
</div>
