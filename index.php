<?php
require_once __DIR__ . '/config.php';
sendSecurityHeaders();

$_skills   = array();
$_projects = array();
$_embeds   = array();
$_articles = array();
$_heroActive = array(
  'image_path' => '',
  'alt_text' => 'Photo of Abhay Bombale',
);
$_settings = array(
    'badge_text'     => 'Open to Work',
    'badge_visible'  => '1',
    'tilt_enabled'   => '1',
    'notify_email'   => '',
    'goatcounter_id' => '',
  'article_section_title' => 'Write-ups',
  'article_section_subtitle' => 'Notes, thoughts, and security learning logs.',
);

$_habitData = array(
    'current_streak' => 0,
    'best_streak' => 0,
    'freeze_balance' => 0,
    'heatmap' => array(),
    'recent_notes' => array(),
);

$_conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
if (!$_conn->connect_error) {
    $_conn->set_charset('utf8mb4');

    $projectsHasSortOrder = false;
    $projectsHasImagePath = false;
    $skillsHasImagePath = false;

    $colCheck = $_conn->query("SHOW COLUMNS FROM projects LIKE 'sort_order'");
    if ($colCheck) {
        $projectsHasSortOrder = ($colCheck->num_rows > 0);
        $colCheck->close();
    }
    $colCheck = $_conn->query("SHOW COLUMNS FROM projects LIKE 'image_path'");
    if ($colCheck) {
        $projectsHasImagePath = ($colCheck->num_rows > 0);
        $colCheck->close();
    }
    $colCheck = $_conn->query("SHOW COLUMNS FROM skills LIKE 'image_path'");
    if ($colCheck) {
        $skillsHasImagePath = ($colCheck->num_rows > 0);
        $colCheck->close();
    }

    $skillImageSelect = $skillsHasImagePath ? ', image_path' : ', "" AS image_path';
    $r = $_conn->query("SELECT icon, title, description{$skillImageSelect} FROM skills ORDER BY sort_order ASC, id ASC");
    if ($r) { while ($row = $r->fetch_assoc()) { $_skills[] = $row; } }

    $projectOrder = $projectsHasSortOrder ? 'sort_order ASC, id ASC' : 'id ASC';
    $projectImageSelect = $projectsHasImagePath ? ', image_path' : ', "" AS image_path';
    $r = $_conn->query("SELECT icon, title, description, project_url, github_url{$projectImageSelect} FROM projects ORDER BY {$projectOrder}");
    if ($r) { while ($row = $r->fetch_assoc()) { $_projects[] = $row; } }

    $r = $_conn->query('SELECT label, embed_code FROM social_embeds ORDER BY sort_order ASC, id ASC');
    if ($r) { while ($row = $r->fetch_assoc()) { $_embeds[] = $row; } }

    $r = $_conn->query('SELECT setting_key, setting_value FROM site_settings');
    if ($r) { while ($row = $r->fetch_assoc()) { $_settings[$row['setting_key']] = $row['setting_value']; } }

    $tableCheck = $_conn->query("SHOW TABLES LIKE 'hero_images'");
    $heroTableExists = $tableCheck && $tableCheck->num_rows > 0;
    if ($tableCheck) { $tableCheck->close(); }
    if ($heroTableExists) {
      $r = $_conn->query('SELECT image_path, alt_text FROM hero_images WHERE is_active = 1 ORDER BY id DESC LIMIT 1');
      if ($r && ($row = $r->fetch_assoc())) {
        $_heroActive = $row;
      }
    }

    $tableCheck = $_conn->query("SHOW TABLES LIKE 'articles'");
    $articlesTableExists = $tableCheck && $tableCheck->num_rows > 0;
    if ($tableCheck) { $tableCheck->close(); }
    if ($articlesTableExists) {
      $r = $_conn->query('SELECT title, slug, excerpt, cover_image, published_at FROM articles WHERE is_published = 1 ORDER BY sort_order ASC, COALESCE(published_at, created_at) DESC LIMIT 6');
      if ($r) {
        while ($row = $r->fetch_assoc()) {
          $_articles[] = $row;
        }
      }
    }

    $habitTablesExist = true;
    $requiredHabitTables = array('streak_state', 'habit_logs', 'daily_notes');
    foreach ($requiredHabitTables as $tblName) {
      $tableCheck = $_conn->query("SHOW TABLES LIKE '" . $_conn->real_escape_string($tblName) . "'");
      $exists = ($tableCheck && $tableCheck->num_rows > 0);
      if ($tableCheck) { $tableCheck->close(); }
      if (!$exists) {
        $habitTablesExist = false;
        break;
      }
    }

    if ($habitTablesExist) {
      $r = $_conn->query('SELECT setting_key, setting_value FROM streak_state');
      if ($r) {
        while ($row = $r->fetch_assoc()) {
          if ($row['setting_key'] === 'current_streak') { $_habitData['current_streak'] = (int)$row['setting_value']; }
          if ($row['setting_key'] === 'best_streak') { $_habitData['best_streak'] = (int)$row['setting_value']; }
          if ($row['setting_key'] === 'freeze_balance') { $_habitData['freeze_balance'] = (int)$row['setting_value']; }
        }
        $r->close();
      }

      $r = $_conn->query(
        'SELECT hl.log_date, COUNT(*) AS completed_count
         FROM habit_logs hl
         WHERE hl.completed = 1
           AND hl.log_date >= DATE_FORMAT(CURDATE(), "%Y-01-01")
           AND hl.log_date <= CURDATE()
         GROUP BY hl.log_date'
      );
      if ($r) {
        while ($row = $r->fetch_assoc()) {
          $_habitData['heatmap'][$row['log_date']] = (int)$row['completed_count'];
        }
        $r->close();
      }

      $r = $_conn->query(
        'SELECT log_date, note, created_at, updated_at
         FROM daily_notes
         WHERE log_date >= DATE_FORMAT(CURDATE(), "%Y-01-01")
           AND log_date <= CURDATE()
         ORDER BY log_date DESC
         LIMIT 366'
      );
      if ($r) {
        while ($row = $r->fetch_assoc()) {
          $_habitData['recent_notes'][] = $row;
        }
        $r->close();
      }
    }

    $_conn->close();
}

function eh($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function renderEmojiOrImage($emoji, $imagePath, $altText, $imgSizePx = 42) {
    $imagePath = trim((string)$imagePath);
    if ($imagePath !== '') {
        $safeSrc = eh('uploads/' . ltrim($imagePath, '/'));
        $safeAlt = eh($altText . ' image');
        $size = (int)$imgSizePx;
        return '<img src="' . $safeSrc . '" alt="' . $safeAlt . '" width="' . $size . '" height="' . $size . '" style="width:' . $size . 'px;height:' . $size . 'px;object-fit:contain;display:block;" loading="lazy" decoding="async" />';
    }

    $emoji = trim((string)$emoji);
    if ($emoji !== '') {
        return eh($emoji);
    }
    return '<span style="font-size:.9rem;font-weight:600;opacity:.85;">Project</span>';
}
$_cvRelPath = null;
if (file_exists(__DIR__ . '/uploads/Abhay_Resume.pdf')) {
  $_cvRelPath = 'uploads/Abhay_Resume.pdf';
} elseif (file_exists(__DIR__ . '/uploads/resume.pdf')) {
  $_cvRelPath = 'uploads/resume.pdf';
}
$_cvExists = ($_cvRelPath !== null);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="color-scheme" content="dark" />
  <title>Abhay | Student & Aspiring Cybersecurity Analyst</title>
  <meta name="description" content="Abhay Bombale's personal portfolio showcasing cybersecurity skills and projects." />
  <link rel="canonical" href="https://yourwebsite.com/" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Abhay Bombale | Portfolio" />
  <meta property="og:description" content="Student & Aspiring Cybersecurity Analyst" />
  <meta property="og:url" content="https://yourwebsite.com" />
  <meta property="og:image" content="https://yourwebsite.com/assets/images/Profile.png" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Abhay Bombale | Portfolio" />
  <meta name="twitter:description" content="Student & Aspiring Cybersecurity Analyst" />
  <meta name="twitter:image" content="https://yourwebsite.com/assets/images/Profile.png" />
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "Abhay Bombale",
    "url": "https://yourwebsite.com",
    "jobTitle": "Student & Aspiring Cybersecurity Analyst",
    "sameAs": [
      "https://www.linkedin.com/in/abhaybombale/",
      "https://github.com/Abhay-bombale",
      "https://x.com/AbhayBombale"
    ]
  }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="icon" type="image/png" href="assets/images/favicon.png" />
  <style>
    /* ── Social Embeds Section ─────────────────────────────────────────────── */
    .social-feed {
      padding: 5rem 0;
      background-color: var(--bg-gray);
    }
    .embeds-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
      align-items: stretch;
    }
    .embed-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.75rem;
      max-width: 520px;
      width: 100%;
      min-width: 0;
    }
    .embed-label {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-light);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .embed-item iframe {
      border-radius: 0.75rem;
      width: 100% !important;
      max-width: 504px;
      box-shadow: var(--shadow-md);
      display: block;
    }
    .embed-item iframe,
    .embed-item blockquote,
    .embed-item .twitter-tweet {
      max-width: 100% !important;
    }
    .embed-item > * {
      max-width: 100%;
    }
    @media (max-width: 768px) {
      .social-feed .container {
        padding: 0 1rem;
      }
      .embeds-grid {
        gap: 1.25rem;
      }
      .embed-item {
        max-width: 100%;
      }
    }
    /* ── LinkedIn profile card (dark mode) ──────────────────────────────── */
    .li-profile-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.25rem;
      background: linear-gradient(135deg, #0d1b2a 0%, #0a1628 100%);
      border: 1px solid rgba(10,102,194,0.35);
      border-radius: 0.75rem;
      text-decoration: none;
      transition: all 0.3s ease;
      margin-bottom: 1.5rem;
    }
    .li-profile-card:hover {
      border-color: #0a66c2;
      box-shadow: 0 6px 20px rgba(10,102,194,0.25);
      transform: translateY(-2px);
    }
    .li-profile-card .li-logo {
      width: 44px;
      height: 44px;
      background: #0a66c2;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .li-profile-card .li-logo svg {
      width: 26px;
      height: 26px;
      fill: #fff;
    }
    .li-profile-card .li-info { display: flex; flex-direction: column; gap: 0.15rem; }
    .li-profile-card .li-name { font-weight: 700; font-size: 1rem; color: #f0f0f5; }
    .li-profile-card .li-title { font-size: 0.8rem; color: #a0a0b8; }
    .li-profile-card .li-cta {
      margin-left: auto;
      font-size: 0.78rem;
      font-weight: 600;
      color: #3b9ede;
      white-space: nowrap;
    }
  </style>
</head>
<body>

  <!-- Skeleton Loading Screen -->
  <div id="skeletonLoader" class="skeleton-loader">
    <div class="skeleton-nav"></div>
    <div class="skeleton-hero">
      <div class="skeleton-hero-text">
        <div class="skeleton-line skeleton-line-lg"></div>
        <div class="skeleton-line skeleton-line-lg" style="width:70%"></div>
        <div class="skeleton-line skeleton-line-md"></div>
        <div class="skeleton-line skeleton-line-sm"></div>
        <div class="skeleton-line skeleton-line-sm" style="width:50%"></div>
        <div class="skeleton-btn"></div>
      </div>
      <div class="skeleton-avatar"></div>
    </div>
  </div>

  <!-- Skip to main content (accessibility) -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <!-- Navigation Bar -->
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
          <li><a href="#home"     class="nav-link">Home</a></li>
          <li><a href="#about"    class="nav-link">About</a></li>
          <li><a href="#skills"   class="nav-link">Skills</a></li>
          <li><a href="certifications.php" class="nav-link">Certs</a></li>
          <li><a href="#projects" class="nav-link">Projects</a></li>
          <?php if (!empty($_articles)): ?>
          <li><a href="#articles" class="nav-link">Write-ups</a></li>
          <?php endif; ?>
          <?php if (!empty($_embeds)): ?>
          <li><a href="#social"   class="nav-link">Posts</a></li>
          <?php endif; ?>
          <li><a href="#contact"  class="nav-link">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main id="main-content">

  <!-- Hero Section -->
  <section id="home" class="hero">
    <div class="container">
      <div class="hero-grid">
        <div class="hero-text">
            <h1 class="hero-title">Hello I'm</h1>
          <h1 class="hero-title">Abhay Bombale</h1>
          <p class="hero-subtitle" id="heroSubtitle" data-text="Student | Aspiring Cybersecurity Analyst"></p>
          <p class="hero-description">
            I am an aspiring cybersecurity professional focused on vulnerability research
            and defensive security.
          </p>
          <div class="hero-buttons">
            <a href="#contact" class="btn btn-primary">Contact Me</a>
            <?php if ($_cvExists): ?>
              <a href="<?= eh($_cvRelPath) ?>" class="btn btn-secondary" download>📄 Download CV</a>
            <?php endif; ?>
          </div>
        </div>
        <div class="hero-image">
          <div class="hero-card-wrap"
               id="heroCardWrap"
               data-tilt="<?php echo ($_settings['tilt_enabled'] === '1') ? '1' : '0'; ?>">
            <div class="hero-card" id="heroCard">
              <!-- Glow ring (decorative, behind card) -->
              <div class="hero-card-glow"></div>
              <!-- The card itself -->
              <div class="hero-card-inner">
                <img src="<?php echo !empty($_heroActive['image_path']) ? ('uploads/hero/' . eh($_heroActive['image_path'])) : 'assets/images/Profile.png'; ?>" alt="<?php echo !empty($_heroActive['alt_text']) ? eh($_heroActive['alt_text']) : 'Photo of Abhay Bombale'; ?>" loading="eager" decoding="async" fetchpriority="high" width="420" height="420" />
              </div>
              <!-- Badge — only rendered if visible -->
              <?php if ($_settings['badge_visible'] === '1' && $_settings['badge_text'] !== ''): ?>
              <div class="hero-badge" id="heroBadge">
                <span class="hero-badge-dot"></span>
                <?php echo eh($_settings['badge_text']); ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="about">
    <div class="container">
      <div class="about-grid">

        <div class="about-left">
          <span class="section-label">// about</span>
          <h2>About Me</h2>
          <div class="about-text">
            <p>
              I am an aspiring cybersecurity professional focused on vulnerability research and defensive
              security. I am driven by understanding how systems are compromised and applying that knowledge
              to design stronger security defences that protect organizations and their users.
            </p>
            <p>
              I bring strong problem-solving ability, disciplined time management, and Python programming
              skills to security challenges. I approach cybersecurity with an adversarial mindset while
              maintaining professional responsibility and adherence to legal and ethical standards.
            </p>
            <p>
              My goal is to help organizations reduce risk, prevent data breaches, and maintain secure
              operations by continuously learning emerging threats and implementing practical, defense-focused
              security solutions.
            </p>
            <a href="certifications.php" class="btn btn-secondary" style="margin-top:1rem;display:inline-block;">View My Certifications →</a>
          </div>
        </div>

        <div class="about-right" id="activity">
          <span class="section-label">// activity</span>
          <h2>Daily Log</h2>
          <p class="activity-subtitle">Logged daily. Click any cell to read what I worked on.</p>

          <div class="activity-stats">
            <div class="activity-stat-card">
              <span class="activity-stat-icon">🔥</span>
              <span class="activity-stat-value"><?php echo (int)$_habitData['current_streak']; ?></span>
              <span class="activity-stat-label">Day Streak</span>
            </div>
            <div class="activity-stat-card">
              <span class="activity-stat-icon">🏆</span>
              <span class="activity-stat-value"><?php echo (int)$_habitData['best_streak']; ?></span>
              <span class="activity-stat-label">Best Streak</span>
            </div>
            <div class="activity-stat-card">
              <span class="activity-stat-icon">🧊</span>
              <span class="activity-stat-value"><?php echo (int)$_habitData['freeze_balance']; ?></span>
              <span class="activity-stat-label">Freezes Saved</span>
            </div>
          </div>

          <div class="heatmap-wrap">
            <div class="heatmap-month-labels" id="heatmapMonthLabels"></div>
            <div class="heatmap-body">
              <div class="heatmap-day-labels">
                <span>Mon</span>
                <span></span>
                <span>Wed</span>
                <span></span>
                <span>Fri</span>
                <span></span>
                <span></span>
              </div>
              <div class="heatmap-grid" id="heatmapGrid">
                <?php
                $today = date('Y-m-d');
                $year = (int)date('Y');
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
                $startDow = (int)date('N', strtotime($startDate)) - 1;
                $totalDays = (int)floor((strtotime($endDate) - strtotime($startDate)) / 86400);

                for ($s = 0; $s < $startDow; $s++) {
                  echo '<div class="heatmap-cell heatmap-spacer"></div>';
                }

                for ($i = 0; $i <= $totalDays; $i++) {
                  $date = date('Y-m-d', strtotime($startDate . ' +' . $i . ' days'));
                  $count = isset($_habitData['heatmap'][$date]) ? (int)$_habitData['heatmap'][$date] : 0;
                  $level = 0;
                  if ($count === 1) { $level = 1; }
                  elseif ($count === 2) { $level = 2; }
                  elseif ($count === 3) { $level = 3; }
                  elseif ($count >= 4) { $level = 4; }
                  $isToday = ($date === $today) ? ' heatmap-today' : '';
                  $tip = $count > 0
                    ? date('M j, Y', strtotime($date)) . ': ' . $count . ' habit' . ($count > 1 ? 's' : '')
                    : date('M j, Y', strtotime($date)) . ': no activity';
                  echo '<div class="heatmap-cell level-' . $level . $isToday . '"'
                    . ' data-date="' . eh($date) . '"'
                    . ' data-count="' . $count . '"'
                    . ' data-future="' . (($date > $today) ? '1' : '0') . '"'
                    . ' title="' . eh($tip) . '"'
                    . '></div>';
                }
                ?>
              </div>
            </div>
            <div class="heatmap-legend">
              <span class="heatmap-legend-left">Learn how we count contributions</span>
              <div class="heatmap-legend-right">
                <span>Less</span>
                <div class="heatmap-cell level-0"></div>
                <div class="heatmap-cell level-1"></div>
                <div class="heatmap-cell level-2"></div>
                <div class="heatmap-cell level-3"></div>
                <div class="heatmap-cell level-4"></div>
                <span>More</span>
              </div>
            </div>
          </div>

          <div class="activity-note-panel" id="activityNotePanel" style="display:none;">
            <div class="activity-note-date" id="activityNoteDate"></div>
            <div class="activity-note-text" id="activityNoteText"></div>
            <div class="activity-note-meta" id="activityNoteMeta"></div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <script>
  window.habitNotes = <?php
    $map = array();
    foreach ($_habitData['recent_notes'] as $n) {
      $map[$n['log_date']] = array(
        'note'       => $n['note'],
        'created_at' => date('M j, Y g:i A', strtotime($n['created_at'])),
        'updated_at' => date('M j, Y g:i A', strtotime($n['updated_at'])),
      );
    }
    echo json_encode($map);
  ?>;
  </script>

  <!-- Skills Section -->
  <section id="skills" class="skills">
    <div class="container">
      <span class="section-label">// skills</span>
      <h2>Skills</h2>
      <div class="skills-grid">
        <?php if (empty($_skills)): ?>
          <p style="color:#6b7280;text-align:center;grid-column:1/-1;">No skills listed yet.</p>
        <?php else: ?>
          <?php foreach ($_skills as $sk): ?>
            <div class="skill-card">
              <div class="skill-icon"><?= renderEmojiOrImage($sk['icon'], !empty($sk['image_path']) ? ('skills/' . $sk['image_path']) : '', $sk['title'], 40) ?></div>
              <h3><?= eh($sk['title']) ?></h3>
              <p><?= eh($sk['description']) ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Projects Section -->
  <section id="projects" class="projects">
    <div class="container">
      <span class="section-label">// projects</span>
      <h2>Projects</h2>
      <div class="projects-grid">
        <?php if (empty($_projects)): ?>
          <p style="color:#6b7280;text-align:center;grid-column:1/-1;">No projects listed yet.</p>
        <?php else: ?>
          <?php foreach ($_projects as $proj): ?>
            <div class="project-card">
              <div class="project-image" role="img" aria-label="<?= eh($proj['title']) ?> preview"
                   style="background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;font-size:2rem;">
                <?= renderEmojiOrImage($proj['icon'], !empty($proj['image_path']) ? ('projects/' . $proj['image_path']) : '', $proj['title'], 92) ?>
              </div>
              <div class="project-content">
                <h3><?= eh($proj['title']) ?></h3>
                <p><?= eh($proj['description']) ?></p>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                  <?php if (!empty($proj['project_url'])): ?>
                    <a href="<?= eh($proj['project_url']) ?>" class="btn btn-secondary" target="_blank" rel="noopener">View Project</a>
                  <?php endif; ?>
                  <?php if (!empty($proj['github_url'])): ?>
                    <a href="<?= eh($proj['github_url']) ?>" class="btn btn-secondary" target="_blank" rel="noopener">GitHub</a>
                  <?php endif; ?>
                  <?php if (empty($proj['project_url']) && empty($proj['github_url'])): ?>
                    <a href="#" class="btn btn-secondary">View Project</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($_articles)): ?>
  <section id="articles" class="articles">
    <div class="container">
      <h2><?= eh($_settings['article_section_title']) ?></h2>
      <p class="articles-intro"><?= eh($_settings['article_section_subtitle']) ?></p>
      <div class="articles-grid">
        <?php foreach ($_articles as $article): ?>
          <article class="article-card">
            <?php if (!empty($article['cover_image'])): ?>
              <a href="article.php?slug=<?= eh($article['slug']) ?>" class="article-cover-link">
                <img class="article-cover" src="uploads/articles/<?= eh($article['cover_image']) ?>" alt="<?= eh($article['title']) ?>" loading="lazy" decoding="async" width="590" height="300" sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw" />
              </a>
            <?php endif; ?>
            <div class="article-card-body">
              <h3><a href="article.php?slug=<?= eh($article['slug']) ?>"><?= eh($article['title']) ?></a></h3>
              <?php if (!empty($article['published_at'])): ?>
                <p class="article-meta"><?= eh(date('M d, Y', strtotime($article['published_at']))) ?></p>
              <?php endif; ?>
              <?php if (!empty($article['excerpt'])): ?>
                <p><?= eh($article['excerpt']) ?></p>
              <?php endif; ?>
              <a class="btn btn-secondary" href="article.php?slug=<?= eh($article['slug']) ?>">Read More</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Social Feed Section (only shown when embeds exist) -->
  <?php if (!empty($_embeds)): ?>
  <section id="social" class="social-feed">
    <div class="container">
      <h2>Latest Posts</h2>
      <div class="embeds-grid">
        <?php foreach ($_embeds as $embed): ?>
          <div class="embed-item">
            <?php if (!empty($embed['label'])): ?>
              <span class="embed-label"><?= eh($embed['label']) ?></span>
            <?php endif; ?>
            <?= $embed['embed_code'] /* raw HTML — admin-controlled, trusted */ ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Contact Section -->
  <section id="contact" class="contact">
    <div class="container">
      <span class="section-label">// contact</span>
      <h2>Get In Touch</h2>
      <div class="contact-content">
        <form class="contact-form" id="contactForm" novalidate>
          <!-- Honeypot field — hidden from real users, catches bots -->
          <div style="position:absolute;left:-9999px;" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off" />
          </div>
          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required autocomplete="name" placeholder="Your name" maxlength="100" />
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autocomplete="email" placeholder="your@email.com" maxlength="150" />
          </div>
          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5" required placeholder="Your message..." maxlength="2000"></textarea>
            <span id="charCount" class="char-counter">0 / 2000</span>
          </div>
          <button type="submit" class="btn btn-primary" id="submitBtn">Send Message</button>
          <p id="formStatus" role="status" aria-live="polite" style="margin-top:1rem;font-weight:500;display:none;"></p>
        </form>

        <div class="social-links">
          <h3>Connect With Me</h3>

          <!-- LinkedIn profile card (reliable, no external SDK needed) -->
          <a href="https://www.linkedin.com/in/abhaybombale/"
             class="li-profile-card"
             target="_blank" rel="noopener noreferrer"
             title="View LinkedIn Profile">
            <div class="li-logo">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </div>
            <div class="li-info">
              <span class="li-name">Abhay Bombale</span>
              <span class="li-title">Student &amp; Aspiring Cybersecurity Analyst</span>
            </div>
            <span class="li-cta">View Profile →</span>
          </a>

          <div class="social-icons">
            <a href="https://github.com/Abhay-bombale" class="social-icon" title="GitHub" target="_blank" rel="noopener noreferrer">
              <span>GitHub</span>
            </a>
<!--
            <a href="https://www.linkedin.com/in/abhaybombale/" class="social-icon" title="LinkedIn" target="_blank" rel="noopener noreferrer">
              <span>LinkedIn</span>
            </a>
-->
            <a href="https://x.com/AbhayBombale" class="social-icon" title="X (Twitter)" target="_blank" rel="noopener noreferrer">
              <span>X</span>
            </a>
            <a href="mailto:bombleabhay24@gmail.com" class="social-icon" title="Email">
              <span>Email</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  </main>

  <!-- Back to Top Button -->
  <button id="backToTop" class="back-to-top" aria-label="Back to top" title="Back to top">↑</button>

  <!-- Footer -->
  <footer class="footer">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
      <p>&copy; 2026 Abhay Bombale. All rights reserved.</p>
      <a href="admin.php" title="Admin Panel"
         style="font-size:0.78rem;color:#475569;text-decoration:none;opacity:0.45;transition:opacity 0.2s;"
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