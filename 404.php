<?php
require_once __DIR__ . '/config.php';
sendSecurityHeaders();
http_response_code(404);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>404 — Page Not Found | Abhay Bombale</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="assets/images/favicon.png" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #0f172a;
      color: #e2e8f0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2rem;
    }
    .error-wrap { max-width: 520px; }
    .error-code {
      font-family: 'Poppins', sans-serif;
      font-size: 8rem;
      font-weight: 700;
      line-height: 1;
      background: linear-gradient(135deg, #3b82f6, #8b5cf6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
    }
    .error-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }
    .error-desc {
      color: #94a3b8;
      font-size: 1rem;
      margin-bottom: 2rem;
      line-height: 1.6;
    }
    .error-btn {
      display: inline-block;
      padding: 0.75rem 2rem;
      background: #3b82f6;
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95rem;
      transition: background 0.2s, transform 0.2s;
    }
    .error-btn:hover {
      background: #2563eb;
      transform: translateY(-2px);
    }
    .error-icon {
      font-size: 4rem;
      margin-bottom: 1.5rem;
      display: block;
    }
  </style>
</head>
<body>
  <div class="error-wrap">
    <span class="error-icon">🔍</span>
    <div class="error-code">404</div>
    <h1 class="error-title">Page Not Found</h1>
    <p class="error-desc">
      The page you're looking for doesn't exist or has been moved.
      Let's get you back on track.
    </p>
    <a href="index.php" class="error-btn">← Back to Portfolio</a>
  </div>
</body>
</html>
