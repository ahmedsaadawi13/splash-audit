<!-- FILE: /app/views/layouts/main.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo APP_NAME; ?></h2>
                <p class="text-sm"><?php echo htmlspecialchars(Auth::userName()); ?></p>
                <p class="text-xs"><?php echo ucfirst(str_replace('_', ' ', Auth::role())); ?></p>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li><a href="/dashboard">Dashboard</a></li>

                    <?php if (Auth::hasRole(['platform_admin', 'tenant_admin', 'audit_manager'])): ?>
                        <li><a href="/user">Users</a></li>
                    <?php endif; ?>

                    <?php if (!Auth::isPlatformAdmin()): ?>
                        <li><a href="/audit/universe">Audit Universe</a></li>
                        <li><a href="/audit/programs">Audit Programs</a></li>
                        <li><a href="/audit/plans">Audit Plans</a></li>
                        <li><a href="/risk">Risk Register</a></li>
                        <li><a href="/risk/heatmap">Risk Heatmap</a></li>
                        <li><a href="/finding">Findings</a></li>
                        <li><a href="/corrective-action">Corrective Actions</a></li>
                        <li><a href="/report">Reports</a></li>
                    <?php endif; ?>

                    <li><a href="/auth/logout">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="content-header">
                <h1><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></h1>
            </header>

            <div class="content-body">
                <!-- Flash Messages -->
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

                <!-- Page Content -->
                <?php echo $content; ?>
            </div>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
