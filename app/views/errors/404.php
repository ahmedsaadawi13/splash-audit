<!-- FILE: /app/views/errors/404.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .error-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-content {
            text-align: center;
            color: white;
            padding: 40px;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
        }
        .error-btn {
            display: inline-block;
            padding: 12px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
        }
        .error-btn:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body class="error-page">
    <div class="error-content">
        <div class="error-code">404</div>
        <div class="error-message">Page Not Found</div>
        <p>The page you are looking for does not exist.</p>
        <br>
        <a href="/dashboard" class="error-btn">Go to Dashboard</a>
    </div>
</body>
</html>
