<!-- FILE: /app/views/user/create.php -->
<div class="page-header">
    <h2>Create User</h2>
    <a href="/user" class="btn">← Back to Users</a>
</div>

<div class="content-section">
    <form action="/user/store" method="POST" class="form">
        <?php echo Session::csrfField(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="tenant_admin">Tenant Admin</option>
                    <option value="audit_manager">Audit Manager</option>
                    <option value="internal_auditor">Internal Auditor</option>
                    <option value="process_owner">Process Owner</option>
                    <option value="reviewer">Reviewer</option>
                    <option value="viewer">Viewer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required minlength="8">
                <small>Minimum 8 characters</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="/user" class="btn">Cancel</a>
        </div>
    </form>
</div>
