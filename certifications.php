<?php
require_once __DIR__ . '/config.php';
sendSecurityHeaders();

$_certs    = array();
$_settings = array(
    'badge_text'     => 'Open to Work',
    'badge_visible'  => '1',
    'tilt_enabled'   => '1',
    'goatcounter_id' => '',
);

$_conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
if (!$_conn->connect_error) {
    $_conn->set_charset('utf8mb4');

    $r = $_conn->query('SELECT title, issuer, image_path, issued_date FROM certifications ORDER BY sort_order ASC, id ASC');
    if ($r) { while ($row = $r->fetch_assoc()) { $_certs[] = $row; } }

    $r = $_conn->query('SELECT setting_key, setting_value FROM site_settings');
    if ($r) { while ($row = $r->fetch_assoc()) { $_settings[$row['setting_key']] = $row['setting_value']; } }

    $_conn->close();
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
  <meta name="color-scheme" content="light" />
  <title>Certifications | Abhay Bombale</title>
  <meta name="description" content="Certifications and credentials earned by Abhay Bombale." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="icon" type="image/png" href="assets/images/favicon.png" />
</head>
<body>

  <a href="#main-content" class="skip-link">Skip to main content</a>

  <!-- Navigation Bar (same as index.php) -->
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
          <li><a href="index.php#home"     class="nav-link">Home</a></li>
          <li><a href="index.php#about"    class="nav-link">About</a></li>
          <li><a href="index.php#skills"   class="nav-link">Skills</a></li>
          <li><a href="certifications.php" class="nav-link" style="color:var(--primary-color);font-weight:700;">Certs</a></li>
          <li><a href="index.php#projects" class="nav-link">Projects</a></li>
          <li><a href="index.php#contact"  class="nav-link">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main id="main-content">

  <section class="certifications-page">
    <div class="container">

      <a href="index.php" class="certs-back-link">← Back to Portfolio</a>

      <h2>Certifications</h2>

      <?php if (empty($_certs)): ?>
        <div class="certs-empty">
          <span class="certs-empty-icon">🎓</span>
          <p>No certifications to show yet. Check back soon!</p>
        </div>
      <?php else: ?>
        <div class="certs-grid">
          <?php foreach ($_certs as $cert): ?>
            <div class="cert-card">
              <?php if ($cert['image_path'] !== ''): ?>
                <a href="uploads/certs/<?= eh($cert['image_path']) ?>" target="_blank" rel="noopener" class="cert-card-img-link">
                  <img src="uploads/certs/<?= eh($cert['image_path']) ?>"
                       alt="<?= eh($cert['title']) ?>"
                       class="cert-card-img" loading="lazy" decoding="async" width="1200" height="848" sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw" />
                </a>
              <?php endif; ?>
              <div class="cert-card-body">
                <h3><?= eh($cert['title']) ?></h3>
                <?php if ($cert['issuer'] !== '' || $cert['issued_date'] !== ''): ?>
                  <p class="cert-card-meta">
                    <?= eh($cert['issuer']) ?>
                    <?php if ($cert['issuer'] !== '' && $cert['issued_date'] !== ''): ?> — <?php endif; ?>
                    <?= eh($cert['issued_date']) ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  </main>

  <footer class="footer">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
      <p>&copy; 2026 Abhay Bombale. All rights reserved.</p>
      <a href="admin.php" title="Admin Panel"
         style="font-size:0.78rem;color:var(--text-secondary);text-decoration:none;opacity:0.45;transition:opacity 0.2s;"
         onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.45'">🔐 Admin</a>
    </div>
  </footer>

  <script src="assets/js/main.js" defer></script>
  <?php if (!empty($_settings['goatcounter_id'])): ?>
  <script data-goatcounter="https://<?= eh($_settings['goatcounter_id']) ?>.goatcounter.com/count"
          async src="//gc.zgo.at/count.js"></script>
  <?php endif; ?>
</body>
</html>
