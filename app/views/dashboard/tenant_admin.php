<!-- FILE: /app/views/dashboard/tenant_admin.php -->
<div class="dashboard">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Audit Plans</h3>
            <div class="stat-value"><?php echo $audit_stats['total_audits'] ?? 0; ?></div>
            <div class="stat-details">
                <span>Scheduled: <?php echo $audit_stats['scheduled'] ?? 0; ?></span>
                <span>In Progress: <?php echo $audit_stats['in_progress'] ?? 0; ?></span>
                <span>Completed: <?php echo $audit_stats['completed'] ?? 0; ?></span>
            </div>
        </div>

        <div class="stat-card">
            <h3>Findings</h3>
            <div class="stat-value"><?php echo $finding_stats['total_findings'] ?? 0; ?></div>
            <div class="stat-details">
                <span class="severity-high">High: <?php echo $finding_stats['high_severity'] ?? 0; ?></span>
                <span class="severity-medium">Medium: <?php echo $finding_stats['medium_severity'] ?? 0; ?></span>
                <span class="severity-low">Low: <?php echo $finding_stats['low_severity'] ?? 0; ?></span>
            </div>
        </div>

        <div class="stat-card">
            <h3>Corrective Actions</h3>
            <div class="stat-value"><?php echo $action_stats['total_actions'] ?? 0; ?></div>
            <div class="stat-details">
                <span>Open: <?php echo $action_stats['open'] ?? 0; ?></span>
                <span class="text-danger">Overdue: <?php echo $action_stats['overdue'] ?? 0; ?></span>
                <span>Completed: <?php echo $action_stats['completed'] ?? 0; ?></span>
            </div>
        </div>

        <div class="stat-card">
            <h3>Quota Usage</h3>
            <div class="quota-details">
                <div class="quota-item">
                    <span>Audit Plans:</span>
                    <span><?php echo $quota_stats['audit_plans']['used']; ?> / <?php echo $quota_stats['audit_plans']['limit'] == -1 ? '∞' : $quota_stats['audit_plans']['limit']; ?></span>
                </div>
                <div class="quota-item">
                    <span>Users:</span>
                    <span><?php echo $quota_stats['users']['used']; ?> / <?php echo $quota_stats['users']['limit'] == -1 ? '∞' : $quota_stats['users']['limit']; ?></span>
                </div>
                <div class="quota-item">
                    <span>Storage:</span>
                    <span><?php echo $quota_stats['storage']['used']; ?> MB / <?php echo $quota_stats['storage']['limit'] == -1 ? '∞' : $quota_stats['storage']['limit']; ?> MB</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    <div class="alerts-section">
        <?php if (!empty($overdue_audits)): ?>
            <div class="alert alert-warning">
                <h4>⚠️ Overdue Audits (<?php echo count($overdue_audits); ?>)</h4>
                <ul>
                    <?php foreach (array_slice($overdue_audits, 0, 3) as $audit): ?>
                        <li>
                            <a href="/audit/plan/view/<?php echo $audit['id']; ?>">
                                <?php echo htmlspecialchars($audit['title']); ?>
                            </a>
                            - Due: <?php echo $audit['planned_end']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($overdue_actions)): ?>
            <div class="alert alert-danger">
                <h4>🚨 Overdue Corrective Actions (<?php echo count($overdue_actions); ?>)</h4>
                <ul>
                    <?php foreach (array_slice($overdue_actions, 0, 3) as $action): ?>
                        <li>
                            <?php echo htmlspecialchars(substr($action['action_description'], 0, 100)); ?>...
                            - Due: <?php echo $action['due_date']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <h3>Quick Actions</h3>
        <div class="link-grid">
            <a href="/audit/plan/create" class="quick-link-card">
                <h4>+ Create Audit Plan</h4>
            </a>
            <a href="/finding/create" class="quick-link-card">
                <h4>+ Create Finding</h4>
            </a>
            <a href="/risk/create" class="quick-link-card">
                <h4>+ Add Risk</h4>
            </a>
            <a href="/report" class="quick-link-card">
                <h4>📊 View Reports</h4>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-section">
        <h3>Upcoming Audits</h3>
        <?php if (!empty($upcoming_audits)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($upcoming_audits, 0, 5) as $audit): ?>
                        <tr>
                            <td>
                                <a href="/audit/plan/view/<?php echo $audit['id']; ?>">
                                    <?php echo htmlspecialchars($audit['title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($audit['audit_type']); ?></td>
                            <td><?php echo $audit['planned_start']; ?></td>
                            <td><span class="badge badge-<?php echo $audit['status']; ?>"><?php echo $audit['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No upcoming audits scheduled.</p>
        <?php endif; ?>
    </div>

    <!-- High Severity Findings -->
    <?php if (!empty($high_severity_findings)): ?>
        <div class="recent-section">
            <h3>High Severity Findings</h3>
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
                    <?php foreach (array_slice($high_severity_findings, 0, 5) as $finding): ?>
                        <tr>
                            <td>
                                <a href="/finding/view/<?php echo $finding['id']; ?>">
                                    <?php echo htmlspecialchars($finding['title']); ?>
                                </a>
                            </td>
                            <td><span class="badge badge-high"><?php echo $finding['severity']; ?></span></td>
                            <td><span class="badge badge-<?php echo $finding['status']; ?>"><?php echo $finding['status']; ?></span></td>
                            <td><?php echo date('Y-m-d', strtotime($finding['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
