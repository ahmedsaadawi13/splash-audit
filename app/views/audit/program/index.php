<div class="page-header">
    <h2>Audit Programs</h2>
    <a href="<?php echo BASE_PATH; ?>/audit/program/create" class="btn btn-primary">+ Create Program</a>
</div>
<div class="content-section">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Year</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($programs)): foreach ($programs as $program): ?>
                <tr>
                    <td><?php echo htmlspecialchars($program['title']); ?></td>
                    <td><?php echo $program['year']; ?></td>
                    <td><?php echo $program['start_date'] ?? 'N/A'; ?></td>
                    <td><?php echo $program['end_date'] ?? 'N/A'; ?></td>
                    <td><span class="badge badge-<?php echo $program['status']; ?>"><?php echo $program['status']; ?></span></td>
                    <td><a href="<?php echo BASE_PATH; ?>/audit/program/view/<?php echo $program['id']; ?>" class="btn btn-sm">View</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6">No programs found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
