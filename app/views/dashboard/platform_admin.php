<!-- FILE: /app/views/dashboard/platform_admin.php -->
<div class="dashboard">
    <h2>Platform Admin Dashboard</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Tenants</h3>
            <div class="stat-value"><?php echo $total_tenants; ?></div>
        </div>

        <div class="stat-card">
            <h3>Active Tenants</h3>
            <div class="stat-value"><?php echo $active_tenants; ?></div>
        </div>
    </div>

    <div class="recent-section">
        <h3>Recent Tenants</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Subdomain</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_tenants as $tenant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tenant['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['subdomain']); ?></td>
                        <td><span class="badge badge-<?php echo $tenant['status']; ?>"><?php echo $tenant['status']; ?></span></td>
                        <td><?php echo date('Y-m-d', strtotime($tenant['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
