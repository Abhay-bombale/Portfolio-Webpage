<?php
require_once __DIR__ . '/config.php';
sendSecurityHeaders();

$_skills   = array();
$_projects = array();
$_embeds   = array();
$_settings = array(
    'badge_text'     => 'Open to Work',
    'badge_visible'  => '1',
    'tilt_enabled'   => '1',
    'notify_email'   => '',
    'goatcounter_id' => '',
);

$_conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
if (!$_conn->connect_error) {
    $_conn->set_charset('utf8mb4');

    $r = $_conn->query('SELECT icon, title, description FROM skills ORDER BY sort_order ASC, id ASC');
    if ($r) { while ($row = $r->fetch_assoc()) { $_skills[] = $row; } }

    $r = $_conn->query('SELECT icon, title, description, project_url, github_url FROM projects ORDER BY id ASC');
    if ($r) { while ($row = $r->fetch_assoc()) { $_projects[] = $row; } }

    $r = $_conn->query('SELECT label, embed_code FROM social_embeds ORDER BY sort_order ASC, id ASC');
    if ($r) { while ($row = $r->fetch_assoc()) { $_embeds[] = $row; } }

    $r = $_conn->query('SELECT setting_key, setting_value FROM site_settings');
    if ($r) { while ($row = $r->fetch_assoc()) { $_settings[$row['setting_key']] = $row['setting_value']; } }

    $_conn->close();
}

function eh($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
$_cvExists = file_exists(__DIR__ . '/uploads/resume.pdf');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Abhay | Student & Aspiring Cybersecurity Analyst</title>
  <meta name="description" content="Abhay Bombale's personal portfolio showcasing cybersecurity skills and projects." />
  <link rel="canonical" href="https://yourwebsite.com/" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Abhay Bombale | Portfolio" />
  <meta property="og:description" content="Student & Aspiring Cybersecurity Analyst" />
  <meta property="og:url" content="https://yourwebsite.com" />
  <meta property="og:image" content="https://yourwebsite.com/Profile.png" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Abhay Bombale | Portfolio" />
  <meta name="twitter:description" content="Student & Aspiring Cybersecurity Analyst" />
  <meta name="twitter:image" content="https://yourwebsite.com/Profile.png" />
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" type="image/png" href="favicon.png" />
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
    }
    .embed-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.75rem;
      max-width: 520px;
      width: 100%;
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
      max-width: 100%;
      box-shadow: var(--shadow-md);
      display: block;
    }
    /* ── LinkedIn profile card (replaces broken SDK badge) ──────────────── */
    .li-profile-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.25rem;
      background: linear-gradient(135deg, #f0f7ff 0%, #e8f0fe 100%);
      border: 1px solid #bfdbfe;
      border-radius: 0.75rem;
      text-decoration: none;
      transition: all 0.3s ease;
      margin-bottom: 1.5rem;
    }
    .li-profile-card:hover {
      border-color: #0a66c2;
      box-shadow: 0 6px 20px rgba(10,102,194,0.15);
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
    .li-profile-card .li-name { font-weight: 700; font-size: 1rem; color: #1f2937; }
    .li-profile-card .li-title { font-size: 0.8rem; color: #64748b; }
    .li-profile-card .li-cta {
      margin-left: auto;
      font-size: 0.78rem;
      font-weight: 600;
      color: #0a66c2;
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
          <?php if (!empty($_embeds)): ?>
          <li><a href="#social"   class="nav-link">Posts</a></li>
          <?php endif; ?>
          <li><a href="#contact"  class="nav-link">Contact</a></li>
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
              <a href="uploads/resume.pdf" class="btn btn-secondary" download>📄 Download CV</a>
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
                <img src="Profile.png" alt="Photo of Abhay Bombale" loading="lazy" />
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
      <h2>About Me</h2>
      <div class="about-content">
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
    </div>
  </section>

  <!-- Skills Section -->
  <section id="skills" class="skills">
    <div class="container">
      <h2>Skills</h2>
      <div class="skills-grid">
        <?php if (empty($_skills)): ?>
          <p style="color:#6b7280;text-align:center;grid-column:1/-1;">No skills listed yet.</p>
        <?php else: ?>
          <?php foreach ($_skills as $sk): ?>
            <div class="skill-card">
              <div class="skill-icon"><?= eh($sk['icon']) ?></div>
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
      <h2>Projects</h2>
      <div class="projects-grid">
        <?php if (empty($_projects)): ?>
          <p style="color:#6b7280;text-align:center;grid-column:1/-1;">No projects listed yet.</p>
        <?php else: ?>
          <?php foreach ($_projects as $proj): ?>
            <div class="project-card">
              <div class="project-image" role="img" aria-label="<?= eh($proj['title']) ?> preview"
                   style="background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;font-size:2rem;">
                <?= eh($proj['icon']) ?>
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

  <script src="main.js" defer></script>
  <?php if (!empty($_settings['goatcounter_id'])): ?>
  <script data-goatcounter="https://<?= eh($_settings['goatcounter_id']) ?>.goatcounter.com/count"
          async src="//gc.zgo.at/count.js"></script>
  <?php endif; ?>
</body>
</html>