<!-- FILE: /app/views/user/edit.php -->
<div class="page-header">
    <h2>Edit User</h2>
    <a href="/user" class="btn">← Back to Users</a>
</div>

<div class="content-section">
    <form action="/user/update/<?php echo $user['id']; ?>" method="POST" class="form">
        <?php echo Session::csrfField(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="tenant_admin" <?php echo $user['role'] === 'tenant_admin' ? 'selected' : ''; ?>>Tenant Admin</option>
                    <option value="audit_manager" <?php echo $user['role'] === 'audit_manager' ? 'selected' : ''; ?>>Audit Manager</option>
                    <option value="internal_auditor" <?php echo $user['role'] === 'internal_auditor' ? 'selected' : ''; ?>>Internal Auditor</option>
                    <option value="process_owner" <?php echo $user['role'] === 'process_owner' ? 'selected' : ''; ?>>Process Owner</option>
                    <option value="reviewer" <?php echo $user['role'] === 'reviewer' ? 'selected' : ''; ?>>Reviewer</option>
                    <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="/user" class="btn">Cancel</a>
        </div>
    </form>
</div>
