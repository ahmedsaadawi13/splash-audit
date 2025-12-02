<!-- FILE: /app/views/user/index.php -->
<div class="page-header">
    <h2>User Management</h2>
    <?php if (Auth::hasRole(['platform_admin', 'tenant_admin'])): ?>
        <a href="/user/create" class="btn btn-primary">+ Add User</a>
    <?php endif; ?>
</div>

<div class="content-section">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span></td>
                        <td><span class="badge badge-<?php echo $user['status']; ?>"><?php echo $user['status']; ?></span></td>
                        <td><?php echo $user['last_login_at'] ? date('Y-m-d H:i', strtotime($user['last_login_at'])) : 'Never'; ?></td>
                        <td>
                            <a href="/user/edit/<?php echo $user['id']; ?>" class="btn btn-sm">Edit</a>
                            <?php if ($user['id'] != Auth::id()): ?>
                                <form action="/user/delete/<?php echo $user['id']; ?>" method="POST" style="display: inline;">
                                    <?php echo Session::csrfField(); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
