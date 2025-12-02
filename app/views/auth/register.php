<!-- FILE: /app/views/auth/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1><?php echo APP_NAME; ?></h1>
            <h2>Create Your Account</h2>

            <?php if (Session::has('error')): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars(Session::flash('error')); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_PATH; ?>/auth/process-register" method="POST">
                <?php echo Session::csrfField(); ?>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" required>
                </div>

                <div class="form-group">
                    <label for="subdomain">Subdomain</label>
                    <input type="text" id="subdomain" name="subdomain" required placeholder="yourcompany">
                    <small>Your workspace URL will be: yourcompany.splashaudit.com</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small>Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?php echo BASE_PATH; ?>/auth/login">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
