<?php
require_once __DIR__ . '/config.php';
sendSecurityHeaders();

$slug = trim(isset($_GET['slug']) ? $_GET['slug'] : '');
$article = null;
$settings = array('goatcounter_id' => '');

if ($slug !== '') {
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if (!$conn->connect_error) {
        $conn->set_charset('utf8mb4');

        $tableCheck = $conn->query("SHOW TABLES LIKE 'articles'");
        $articlesTableExists = $tableCheck && $tableCheck->num_rows > 0;
        if ($tableCheck) { $tableCheck->close(); }

        if ($articlesTableExists) {
            $stmt = $conn->prepare('SELECT title, excerpt, content, cover_image, published_at FROM articles WHERE slug = ? AND is_published = 1 LIMIT 1');
            $stmt->bind_param('s', $slug);
            $stmt->execute();
            $article = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        $r = $conn->query('SELECT setting_key, setting_value FROM site_settings');
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        $conn->close();
    }
}

if (!$article) {
    http_response_code(404);
}

function eh($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="color-scheme" content="light dark" />
  <title><?php echo $article ? (eh($article['title']) . ' | Abhay Bombale') : 'Article Not Found | Abhay Bombale'; ?></title>
  <meta name="description" content="<?php echo $article ? eh($article['excerpt']) : 'Article not found'; ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="icon" type="image/png" href="assets/images/favicon.png" />
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="container">
      <div class="nav-content">
        <a href="index.php" class="logo">Abhay</a>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
        <ul class="nav-links" id="navLinks">
          <li><a href="index.php#home" class="nav-link">Home</a></li>
          <li><a href="index.php#articles" class="nav-link">Write-ups</a></li>
          <li><a href="index.php#contact" class="nav-link">Contact</a></li>
          <li>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle dark mode" title="Toggle dark mode">
              <span class="theme-icon-light">☀️</span>
              <span class="theme-icon-dark">🌙</span>
            </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <main id="main-content" class="article-page-main">
    <section class="article-page">
      <div class="container article-container">
        <a href="index.php#articles" class="certs-back-link">← Back to Write-ups</a>

        <?php if (!$article): ?>
          <div class="certs-empty" style="margin-top:2rem;">
            <span class="certs-empty-icon">🧭</span>
            <p>We could not find this article.</p>
          </div>
        <?php else: ?>
          <header class="article-header">
            <h1 class="article-title"><?php echo eh($article['title']); ?></h1>
            <?php if (!empty($article['published_at'])): ?>
              <p class="article-meta">Published on <?php echo eh(date('F d, Y', strtotime($article['published_at']))); ?></p>
            <?php endif; ?>
            <?php if (!empty($article['excerpt'])): ?>
              <p class="article-excerpt"><?php echo eh($article['excerpt']); ?></p>
            <?php endif; ?>
          </header>

          <?php if (!empty($article['cover_image'])): ?>
            <div class="article-cover-wrap">
              <img src="uploads/articles/<?php echo eh($article['cover_image']); ?>" alt="<?php echo eh($article['title']); ?>" class="article-detail-cover" loading="lazy" decoding="async" width="1200" height="630" sizes="(max-width: 920px) 100vw, 880px" />
            </div>
          <?php endif; ?>

          <article class="article-content">
            <?php echo nl2br(eh($article['content'])); ?>
          </article>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
      <p>&copy; 2026 Abhay Bombale. All rights reserved.</p>
      <a href="admin.php" title="Admin Panel" style="font-size:0.78rem;color:#475569;text-decoration:none;opacity:0.45;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.45'">🔐 Admin</a>
    </div>
  </footer>

  <script src="assets/js/main.js" defer></script>
  <?php if (!empty($settings['goatcounter_id'])): ?>
  <script data-goatcounter="https://<?php echo eh($settings['goatcounter_id']); ?>.goatcounter.com/count" async src="//gc.zgo.at/count.js"></script>
  <?php endif; ?>
</body>
</html>
