<!-- FILE: /app/views/auth/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1><?php echo APP_NAME; ?></h1>
            <h2>Login to Your Account</h2>

            <?php if (Session::has('success')): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars(Session::flash('success')); ?>
                </div>
            <?php endif; ?>

            <?php if (Session::has('error')): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars(Session::flash('error')); ?>
                </div>
            <?php endif; ?>

            <form action="/auth/authenticate" method="POST">
                <?php echo Session::csrfField(); ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="/auth/register">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
