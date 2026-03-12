<?php
require_once __DIR__ . '/config.php';
sendSecurityHeaders();
initSession();

$ADMIN_USER = $_ENV['ADMIN_USER'];
$ADMIN_PASS = $_ENV['ADMIN_PASS']; // Should be a bcrypt hash

// ── HTML escape (alias, config.php already has e()) ───────────────────────────
$error   = '';
$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');

// ── Login ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verifyCsrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $u = trim(isset($_POST['username']) ? $_POST['username'] : '');
        $p = isset($_POST['password']) ? $_POST['password'] : '';
        // Support both bcrypt hash and plain-text (for migration)
        $passOk = (strpos($ADMIN_PASS, '$2y$') === 0 || strpos($ADMIN_PASS, '$2a$') === 0)
                ? password_verify($p, $ADMIN_PASS)
                : hash_equals($ADMIN_PASS, $p);
        if (hash_equals($ADMIN_USER, $u) && $passOk) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['last_activity']   = time();
            header('Location: ' . $baseUrl . '?tab=messages');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

// ── Logout ────────────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $baseUrl);
    exit;
}

$loggedIn = !empty($_SESSION['admin_logged_in']);
$tab      = isset($_GET['tab']) ? $_GET['tab'] : 'messages';

// ══════════════════════════════════════════════════════════════════════════════
// SETTINGS ACTIONS
// ══════════════════════════════════════════════════════════════════════════════
$settingsSaved = false;
if ($loggedIn && $tab === 'settings' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!verifyCsrf()) { $error = 'Invalid request token.'; }
    else {
        $badge_text    = trim(isset($_POST['badge_text'])    ? $_POST['badge_text']    : '');
        $badge_visible = isset($_POST['badge_visible'])      ? '1' : '0';
        $tilt_enabled  = isset($_POST['tilt_enabled'])       ? '1' : '0';

        $keys = array('badge_text' => $badge_text, 'badge_visible' => $badge_visible, 'tilt_enabled' => $tilt_enabled);
        foreach ($keys as $k => $v) {
            $stmt = db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            $stmt->bind_param('ss', $k, $v);
            $stmt->execute();
            $stmt->close();
        }
        $settingsSaved = true;
    }
}

// Load current settings for the settings tab
$siteSettings = array('badge_text' => 'Open to Work', 'badge_visible' => '1', 'tilt_enabled' => '1');
if ($loggedIn && $tab === 'settings') {
    $r = db()->query('SELECT setting_key, setting_value FROM site_settings');
    if ($r) { while ($row = $r->fetch_assoc()) { $siteSettings[$row['setting_key']] = $row['setting_value']; } }
}

// ══════════════════════════════════════════════════════════════════════════════
// SKILLS ACTIONS
// ══════════════════════════════════════════════════════════════════════════════
$editSkill = null;
if ($loggedIn && $tab === 'skills') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
        if (verifyCsrf()) {
            $icon  = trim(isset($_POST['icon'])  ? $_POST['icon']  : '');
            $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
            $desc  = trim(isset($_POST['desc'])  ? $_POST['desc']  : '');
            if ($title !== '' && $desc !== '') {
                $stmt = db()->prepare('INSERT INTO skills (icon, title, description) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $icon, $title, $desc);
                $stmt->execute();
                $stmt->close();
            }
        }
        header('Location: ' . $baseUrl . '?tab=skills'); exit;
    }

    if (isset($_GET['edit_skill'])) {
        $id   = (int)$_GET['edit_skill'];
        $stmt = db()->prepare('SELECT * FROM skills WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $editSkill = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_skill'])) {
        if (verifyCsrf()) {
            $id    = (int)$_POST['skill_id'];
            $icon  = trim(isset($_POST['icon'])  ? $_POST['icon']  : '');
            $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
            $desc  = trim(isset($_POST['desc'])  ? $_POST['desc']  : '');
            $stmt  = db()->prepare('UPDATE skills SET icon=?, title=?, description=? WHERE id=?');
            $stmt->bind_param('sssi', $icon, $title, $desc, $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: ' . $baseUrl . '?tab=skills'); exit;
    }

    if (isset($_GET['delete_skill'])) {
        $id   = (int)$_GET['delete_skill'];
        $stmt = db()->prepare('DELETE FROM skills WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . $baseUrl . '?tab=skills'); exit;
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// PROJECTS ACTIONS
// ══════════════════════════════════════════════════════════════════════════════
$editProject = null;
if ($loggedIn && $tab === 'projects') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
        if (verifyCsrf()) {
            $icon   = trim(isset($_POST['icon'])   ? $_POST['icon']   : '');
            $title  = trim(isset($_POST['title'])  ? $_POST['title']  : '');
            $desc   = trim(isset($_POST['desc'])   ? $_POST['desc']   : '');
            $url    = trim(isset($_POST['url'])    ? $_POST['url']    : '');
            $github = trim(isset($_POST['github']) ? $_POST['github'] : '');
            if (!isValidUrl($url)) $url = '';
            if (!isValidUrl($github)) $github = '';
            if ($title !== '' && $desc !== '') {
                $stmt = db()->prepare('INSERT INTO projects (icon, title, description, project_url, github_url) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('sssss', $icon, $title, $desc, $url, $github);
                $stmt->execute();
                $stmt->close();
            }
        }
        header('Location: ' . $baseUrl . '?tab=projects'); exit;
    }

    if (isset($_GET['edit_project'])) {
        $id   = (int)$_GET['edit_project'];
        $stmt = db()->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $editProject = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project'])) {
        if (verifyCsrf()) {
            $id     = (int)$_POST['project_id'];
            $icon   = trim(isset($_POST['icon'])   ? $_POST['icon']   : '');
            $title  = trim(isset($_POST['title'])  ? $_POST['title']  : '');
            $desc   = trim(isset($_POST['desc'])   ? $_POST['desc']   : '');
            $url    = trim(isset($_POST['url'])    ? $_POST['url']    : '');
            $github = trim(isset($_POST['github']) ? $_POST['github'] : '');
            if (!isValidUrl($url)) $url = '';
            if (!isValidUrl($github)) $github = '';
            $stmt   = db()->prepare('UPDATE projects SET icon=?, title=?, description=?, project_url=?, github_url=? WHERE id=?');
            $stmt->bind_param('sssssi', $icon, $title, $desc, $url, $github, $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: ' . $baseUrl . '?tab=projects'); exit;
    }

    if (isset($_GET['delete_project'])) {
        $id   = (int)$_GET['delete_project'];
        $stmt = db()->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . $baseUrl . '?tab=projects'); exit;
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// SOCIAL EMBEDS ACTIONS
// ══════════════════════════════════════════════════════════════════════════════
$editEmbed = null;
if ($loggedIn && $tab === 'embeds') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_embed'])) {
        if (verifyCsrf()) {
            $label      = trim(isset($_POST['label'])      ? $_POST['label']      : '');
            $embed_code = trim(isset($_POST['embed_code']) ? $_POST['embed_code'] : '');
            $sort_order = (int)(isset($_POST['sort_order']) ? $_POST['sort_order'] : 0);
            if ($embed_code !== '') {
                $stmt = db()->prepare('INSERT INTO social_embeds (label, embed_code, sort_order) VALUES (?, ?, ?)');
                $stmt->bind_param('ssi', $label, $embed_code, $sort_order);
                $stmt->execute();
                $stmt->close();
            }
        }
        header('Location: ' . $baseUrl . '?tab=embeds'); exit;
    }

    if (isset($_GET['edit_embed'])) {
        $id   = (int)$_GET['edit_embed'];
        $stmt = db()->prepare('SELECT * FROM social_embeds WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $editEmbed = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_embed'])) {
        if (verifyCsrf()) {
            $id         = (int)$_POST['embed_id'];
            $label      = trim(isset($_POST['label'])      ? $_POST['label']      : '');
            $embed_code = trim(isset($_POST['embed_code']) ? $_POST['embed_code'] : '');
            $sort_order = (int)(isset($_POST['sort_order']) ? $_POST['sort_order'] : 0);
            $stmt = db()->prepare('UPDATE social_embeds SET label=?, embed_code=?, sort_order=? WHERE id=?');
            $stmt->bind_param('ssii', $label, $embed_code, $sort_order, $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: ' . $baseUrl . '?tab=embeds'); exit;
    }

    if (isset($_GET['delete_embed'])) {
        $id   = (int)$_GET['delete_embed'];
        $stmt = db()->prepare('DELETE FROM social_embeds WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . $baseUrl . '?tab=embeds'); exit;
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// MESSAGES ACTIONS & DATA
// ══════════════════════════════════════════════════════════════════════════════
$messages    = array();
$totalCount  = 0;
$dbError     = '';
$search      = trim(isset($_GET['search']) ? $_GET['search'] : '');
$currentPage = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$limit       = 10;
$offset      = ($currentPage - 1) * $limit;

if ($loggedIn && isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = db()->prepare('DELETE FROM contacts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $qs = http_build_query(array_filter(array('tab' => 'messages', 'page' => isset($_GET['page']) ? $_GET['page'] : '', 'search' => $search)));
    header('Location: ' . $baseUrl . ($qs ? '?' . $qs : '?tab=messages')); exit;
}

if ($loggedIn && $tab === 'messages') {
    if ($search !== '') {
        $like      = '%' . $search . '%';
        $countStmt = db()->prepare('SELECT COUNT(*) FROM contacts WHERE name LIKE ? OR email LIKE ? OR message LIKE ?');
        $countStmt->bind_param('sss', $like, $like, $like);
        $countStmt->execute();
        $countStmt->bind_result($totalCount);
        $countStmt->fetch();
        $countStmt->close();
        $dataStmt = db()->prepare('SELECT id, name, email, message, created_at FROM contacts WHERE name LIKE ? OR email LIKE ? OR message LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
        $dataStmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
    } else {
        $countStmt = db()->prepare('SELECT COUNT(*) FROM contacts');
        $countStmt->execute();
        $countStmt->bind_result($totalCount);
        $countStmt->fetch();
        $countStmt->close();
        $dataStmt = db()->prepare('SELECT id, name, email, message, created_at FROM contacts ORDER BY id DESC LIMIT ? OFFSET ?');
        $dataStmt->bind_param('ii', $limit, $offset);
    }
    $dataStmt->execute();
    $result = $dataStmt->get_result();
    while ($row = $result->fetch_assoc()) { $messages[] = $row; }
    $dataStmt->close();
}

// ── Fetch data for active tab ─────────────────────────────────────────────────
$skills   = array();
$projects = array();
$embeds   = array();
if ($loggedIn) {
    if ($tab === 'skills') {
        $r = db()->query('SELECT * FROM skills ORDER BY id ASC');
        if ($r) { while ($row = $r->fetch_assoc()) { $skills[] = $row; } }
    }
    if ($tab === 'projects') {
        $r = db()->query('SELECT * FROM projects ORDER BY id ASC');
        if ($r) { while ($row = $r->fetch_assoc()) { $projects[] = $row; } }
    }
    if ($tab === 'embeds') {
        $r = db()->query('SELECT * FROM social_embeds ORDER BY sort_order ASC, id ASC');
        if ($r) { while ($row = $r->fetch_assoc()) { $embeds[] = $row; } }
    }
}

$totalPages = $totalCount > 0 ? (int)ceil($totalCount / $limit) : 1;
$searchSafe = e($search);

function pageUrl($page, $search, $base) {
    $qs = http_build_query(array_filter(array('tab' => 'messages', 'page' => $page, 'search' => $search)));
    return $base . ($qs ? '?' . $qs : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel | Abhay Portfolio</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg:       #0f1117;
      --surface:  #1a1d27;
      --surface2: #20243a;
      --border:   #2a2d3e;
      --accent:   #6366f1;
      --accent-h: #818cf8;
      --danger:   #ef4444;
      --success:  #22c55e;
      --warning:  #f59e0b;
      --text:     #e2e8f0;
      --muted:    #64748b;
      --radius:   10px;
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
    .login-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
    .login-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:2.5rem 2rem; width:100%; max-width:380px; }
    .login-card h1 { font-size:1.5rem; margin-bottom:.4rem; }
    .login-card > p { color:var(--muted); font-size:.875rem; margin-bottom:2rem; }
    .form-group { margin-bottom:1.2rem; }
    .form-group label { display:block; font-size:.78rem; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:var(--muted); margin-bottom:.4rem; }
    .form-group input, .form-group textarea { width:100%; padding:.65rem .9rem; background:var(--bg); border:1px solid var(--border); border-radius:6px; color:var(--text); font-size:.95rem; outline:none; transition:border-color .2s; font-family:inherit; resize:vertical; }
    .form-group input:focus, .form-group textarea:focus { border-color:var(--accent); }
    .btn { display:inline-block; padding:.6rem 1.3rem; border-radius:6px; font-size:.875rem; font-weight:600; cursor:pointer; border:none; transition:opacity .2s; text-decoration:none; white-space:nowrap; }
    .btn:hover { opacity:.85; }
    .btn-primary { background:var(--accent); color:#fff; }
    .btn-success { background:var(--success); color:#fff; }
    .btn-danger  { background:var(--danger);  color:#fff; font-size:.78rem; padding:.32rem .75rem; }
    .btn-warning { background:var(--warning); color:#000; font-size:.78rem; padding:.32rem .75rem; }
    .btn-outline { background:transparent; border:1px solid var(--border); color:var(--muted); }
    .btn-block   { width:100%; text-align:center; }
    .error-msg   { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#f87171; padding:.65rem .9rem; border-radius:6px; font-size:.875rem; margin-bottom:1.2rem; }
    .topbar { background:var(--surface); border-bottom:1px solid var(--border); padding:1rem 2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
    .topbar-brand { font-size:1.1rem; font-weight:700; color:var(--accent); }
    .topbar-brand span { color:var(--text); font-weight:400; }
    .tabs { display:flex; border-bottom:1px solid var(--border); background:var(--surface); padding:0 2rem; overflow-x:auto; }
    .tab-link { display:inline-block; padding:.85rem 1.4rem; font-size:.9rem; font-weight:600; color:var(--muted); text-decoration:none; border-bottom:3px solid transparent; transition:color .2s, border-color .2s; white-space:nowrap; }
    .tab-link:hover { color:var(--text); }
    .tab-link.active { color:var(--accent-h); border-bottom-color:var(--accent); }
    .main { padding:2rem; max-width:1100px; margin:0 auto; }
    .stats { display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
    .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:1rem 1.5rem; min-width:140px; }
    .stat-card .val { font-size:1.8rem; font-weight:700; color:var(--accent); }
    .stat-card .lbl { font-size:.8rem; color:var(--muted); margin-top:.2rem; }
    .toolbar { display:flex; gap:.8rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center; }
    .search-input { flex:1; min-width:200px; padding:.6rem 1rem; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); font-size:.9rem; outline:none; }
    .search-input:focus { border-color:var(--accent); }
    .table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; overflow-x:auto; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead { background:rgba(99,102,241,.08); }
    th { padding:.85rem 1rem; text-align:left; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); border-bottom:1px solid var(--border); white-space:nowrap; }
    td { padding:.85rem 1rem; border-bottom:1px solid var(--border); vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:rgba(255,255,255,.02); }
    .msg-text { max-width:340px; white-space:pre-wrap; word-break:break-word; color:#94a3b8; font-size:.85rem; }
    .badge-id { background:rgba(99,102,241,.15); color:var(--accent-h); padding:.2rem .5rem; border-radius:4px; font-size:.78rem; font-weight:600; }
    .email-link { color:var(--accent-h); text-decoration:none; }
    .email-link:hover { text-decoration:underline; }
    .pagination { display:flex; gap:.4rem; justify-content:center; margin-top:1.5rem; flex-wrap:wrap; }
    .page-btn { padding:.45rem .85rem; border-radius:6px; font-size:.85rem; font-weight:600; text-decoration:none; border:1px solid var(--border); color:var(--muted); background:var(--surface); transition:all .2s; }
    .page-btn:hover { border-color:var(--accent); color:var(--accent); }
    .page-btn.active { background:var(--accent); color:#fff; border-color:var(--accent); }
    .panel { background:var(--surface2); border:1px solid var(--accent); border-radius:var(--radius); padding:1.5rem; margin-bottom:1.5rem; }
    .panel h3 { font-size:1rem; margin-bottom:1.2rem; color:var(--accent-h); }
    .panel-hint { color:var(--muted); font-size:.85rem; margin-bottom:1.2rem; line-height:1.6; }
    .panel-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .span-2 { grid-column:span 2; }
    .panel-actions { display:flex; gap:.8rem; margin-top:.5rem; flex-wrap:wrap; }
    .section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
    .section-header h2 { font-size:1.25rem; }
    .cards-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(290px,1fr)); gap:1.2rem; }
    .item-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:1.2rem; display:flex; flex-direction:column; gap:.6rem; transition:border-color .2s; }
    .item-card:hover { border-color:var(--accent); }
    .item-card-header { display:flex; align-items:center; gap:.8rem; }
    .item-card-icon { font-size:2rem; line-height:1; }
    .item-card-title { font-weight:700; font-size:1rem; color:var(--text); }
    .item-card-desc { font-size:.875rem; color:var(--muted); line-height:1.6; flex:1; }
    .item-card-links { display:flex; gap:.5rem; flex-wrap:wrap; }
    .item-card-link { color:var(--accent-h); text-decoration:none; padding:.2rem .55rem; background:rgba(99,102,241,.1); border-radius:4px; font-size:.78rem; }
    .item-card-link:hover { background:rgba(99,102,241,.25); }
    .item-card-footer { display:flex; gap:.5rem; border-top:1px solid var(--border); padding-top:.8rem; margin-top:auto; flex-wrap:wrap; }
    .embed-preview { overflow:hidden; border-radius:6px; border:1px solid var(--border); background:#fff; display:flex; justify-content:center; padding:.5rem; }
    .empty-state { text-align:center; padding:3rem 1rem; color:var(--muted); }
    .empty-state .icon { font-size:2.5rem; margin-bottom:.8rem; }
    code { background:rgba(99,102,241,.15); color:var(--accent-h); padding:.1rem .4rem; border-radius:4px; font-size:.85em; }
    /* Toggle switch */
    .toggle-switch { position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; cursor:pointer; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:var(--border); border-radius:24px; transition:background .2s; }
    .toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; top:3px; background:#fff; border-radius:50%; transition:transform .2s; }
    .toggle-switch input:checked + .toggle-slider { background:var(--accent); }
    .toggle-switch input:checked + .toggle-slider::before { transform:translateX(20px); }
    @media (max-width:640px) {
      .main { padding:1rem; } .topbar { padding:.8rem 1rem; } .tabs { padding:0 1rem; }
      .panel-grid { grid-template-columns:1fr; } .span-2 { grid-column:span 1; }
    }
  </style>
</head>
<body>

<?php if (!$loggedIn): ?>
<!-- ═══════════════════════════════ LOGIN ═════════════════════════════════════ -->
<div class="login-wrap">
  <div class="login-card">
    <h1>🔐 Admin Login</h1>
    <p>Sign in to manage your portfolio</p>
    <?php if ($error): ?>
      <div class="error-msg"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="on">
      <?php echo csrfField(); ?>
      <div class="form-group">
        <label for="uname">Username</label>
        <input type="text" id="uname" name="username" autocomplete="username" required autofocus />
      </div>
      <div class="form-group">
        <label for="upass">Password</label>
        <input type="password" id="upass" name="password" autocomplete="current-password" required />
      </div>
      <button type="submit" name="login" class="btn btn-primary btn-block">Sign In</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════ DASHBOARD ════════════════════════════════════ -->
<div class="topbar">
  <div class="topbar-brand">🛡️ Admin Panel <span>/ Abhay Portfolio</span></div>
  <a href="?logout=1" class="btn btn-outline">Log Out</a>
</div>

<div class="tabs">
  <a href="?tab=messages" class="tab-link <?php echo $tab==='messages'?'active':''; ?>">📨 Messages</a>
  <a href="?tab=skills"   class="tab-link <?php echo $tab==='skills'  ?'active':''; ?>">🔧 Skills</a>
  <a href="?tab=projects" class="tab-link <?php echo $tab==='projects'?'active':''; ?>">🚀 Projects</a>
  <a href="?tab=embeds"   class="tab-link <?php echo $tab==='embeds'  ?'active':''; ?>">📌 Social Embeds</a>
  <a href="?tab=settings" class="tab-link <?php echo $tab==='settings'?'active':''; ?>">⚙️ Settings</a>
</div>

<div class="main">

<!-- ═══════════════════════ MESSAGES TAB ═════════════════════════════════════ -->
<?php if ($tab === 'messages'): ?>

  <div class="stats">
    <div class="stat-card">
      <div class="val"><?php echo $totalCount; ?></div>
      <div class="lbl"><?php echo $searchSafe ? 'Results Found' : 'Total Messages'; ?></div>
    </div>
    <div class="stat-card">
      <div class="val"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></div>
      <div class="lbl">Page</div>
    </div>
  </div>

  <div class="toolbar">
    <form method="GET" style="display:flex;gap:.8rem;flex:1;flex-wrap:wrap;">
      <input type="hidden" name="tab" value="messages" />
      <input class="search-input" type="text" name="search"
             placeholder="Search by name, email, or message…"
             value="<?php echo $searchSafe; ?>" />
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($searchSafe): ?>
        <a href="?tab=messages" class="btn btn-outline">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-wrap">
    <?php if (empty($messages)): ?>
      <div class="empty-state">
        <div class="icon">📭</div>
        <p><?php echo $searchSafe ? 'No messages match your search.' : 'No messages yet.'; ?></p>
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($messages as $msg): ?>
            <tr>
              <td><span class="badge-id"><?php echo (int)$msg['id']; ?></span></td>
              <td><?php echo e($msg['name']); ?></td>
              <td><a class="email-link" href="mailto:<?php echo e($msg['email']); ?>"><?php echo e($msg['email']); ?></a></td>
              <td class="msg-text"><?php echo e($msg['message']); ?></td>
              <td style="white-space:nowrap;color:var(--muted);font-size:.82rem;">
                <?php echo !empty($msg['created_at']) ? e($msg['created_at']) : '—'; ?>
              </td>
              <td>
                <?php
                  $dqs = http_build_query(array_filter(array(
                    'delete' => $msg['id'], 'tab' => 'messages',
                    'page'   => $currentPage > 1 ? $currentPage : '',
                    'search' => $search,
                  )));
                ?>
                <a href="<?php echo $baseUrl . '?' . $dqs; ?>"
                   class="btn btn-danger"
                   onclick="return confirm('Delete message from <?php echo e(addslashes($msg['name'])); ?>?')">
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($currentPage > 1): ?>
        <a class="page-btn" href="<?php echo pageUrl($currentPage-1, $search, $baseUrl); ?>">← Prev</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="page-btn <?php echo $i===$currentPage?'active':''; ?>"
           href="<?php echo pageUrl($i, $search, $baseUrl); ?>"><?php echo $i; ?></a>
      <?php endfor; ?>
      <?php if ($currentPage < $totalPages): ?>
        <a class="page-btn" href="<?php echo pageUrl($currentPage+1, $search, $baseUrl); ?>">Next →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<!-- ═══════════════════════ SKILLS TAB ═══════════════════════════════════════ -->
<?php elseif ($tab === 'skills'): ?>

  <?php if (!empty($editSkill)): ?>
  <div class="panel">
    <h3>✏️ Edit Skill — <?php echo e($editSkill['title']); ?></h3>
    <form method="POST">
      <?php echo csrfField(); ?>
      <input type="hidden" name="skill_id" value="<?php echo (int)$editSkill['id']; ?>" />
      <div class="panel-grid">
        <div class="form-group">
          <label>Icon / Emoji</label>
          <input type="text" name="icon" value="<?php echo e($editSkill['icon']); ?>" placeholder="e.g. 🔐" maxlength="10" />
        </div>
        <div class="form-group">
          <label>Title *</label>
          <input type="text" name="title" value="<?php echo e($editSkill['title']); ?>" required maxlength="100" />
        </div>
        <div class="form-group span-2">
          <label>Description *</label>
          <textarea name="desc" rows="3" required maxlength="300"><?php echo e($editSkill['description']); ?></textarea>
        </div>
      </div>
      <div class="panel-actions">
        <button type="submit" name="save_skill" class="btn btn-success">Save Changes</button>
        <a href="?tab=skills" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
  <?php else: ?>
  <div class="panel">
    <h3>➕ Add New Skill</h3>
    <form method="POST">
      <?php echo csrfField(); ?>
      <div class="panel-grid">
        <div class="form-group">
          <label>Icon / Emoji</label>
          <input type="text" name="icon" placeholder="e.g. 🔐" maxlength="10" />
        </div>
        <div class="form-group">
          <label>Title *</label>
          <input type="text" name="title" required maxlength="100" placeholder="e.g. Network Security" />
        </div>
        <div class="form-group span-2">
          <label>Description *</label>
          <textarea name="desc" rows="3" required maxlength="300" placeholder="Brief description…"></textarea>
        </div>
      </div>
      <div class="panel-actions">
        <button type="submit" name="add_skill" class="btn btn-primary">Add Skill</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="section-header">
    <h2>All Skills <span style="color:var(--muted);font-weight:400;font-size:.9rem;">(<?php echo count($skills); ?>)</span></h2>
  </div>

  <?php if (empty($skills)): ?>
    <div class="empty-state"><div class="icon">🔧</div><p>No skills yet. Add your first skill above.</p></div>
  <?php else: ?>
    <div class="cards-grid">
      <?php foreach ($skills as $s): ?>
        <div class="item-card">
          <div class="item-card-header">
            <div class="item-card-icon"><?php echo e($s['icon']); ?></div>
            <div class="item-card-title"><?php echo e($s['title']); ?></div>
          </div>
          <div class="item-card-desc"><?php echo e($s['description']); ?></div>
          <div class="item-card-footer">
            <a href="?tab=skills&edit_skill=<?php echo (int)$s['id']; ?>" class="btn btn-warning">✏️ Edit</a>
            <a href="?tab=skills&delete_skill=<?php echo (int)$s['id']; ?>"
               class="btn btn-danger"
               onclick="return confirm('Delete skill: <?php echo e(addslashes($s['title'])); ?>?')">🗑 Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<!-- ═══════════════════════ PROJECTS TAB ═════════════════════════════════════ -->
<?php elseif ($tab === 'projects'): ?>

  <?php if (!empty($editProject)): ?>
  <div class="panel">
    <h3>✏️ Edit Project — <?php echo e($editProject['title']); ?></h3>
    <form method="POST">
      <?php echo csrfField(); ?>
      <input type="hidden" name="project_id" value="<?php echo (int)$editProject['id']; ?>" />
      <div class="panel-grid">
        <div class="form-group">
          <label>Icon / Emoji</label>
          <input type="text" name="icon" value="<?php echo e($editProject['icon']); ?>" placeholder="e.g. 🛡️" maxlength="10" />
        </div>
        <div class="form-group">
          <label>Title *</label>
          <input type="text" name="title" value="<?php echo e($editProject['title']); ?>" required maxlength="120" />
        </div>
        <div class="form-group span-2">
          <label>Description *</label>
          <textarea name="desc" rows="3" required maxlength="500"><?php echo e($editProject['description']); ?></textarea>
        </div>
        <div class="form-group">
          <label>Live Project URL</label>
          <input type="url" name="url" value="<?php echo e($editProject['project_url']); ?>" placeholder="https://…" />
        </div>
        <div class="form-group">
          <label>GitHub URL</label>
          <input type="url" name="github" value="<?php echo e($editProject['github_url']); ?>" placeholder="https://github.com/…" />
        </div>
      </div>
      <div class="panel-actions">
        <button type="submit" name="save_project" class="btn btn-success">Save Changes</button>
        <a href="?tab=projects" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
  <?php else: ?>
  <div class="panel">
    <h3>➕ Add New Project</h3>
    <form method="POST">
      <?php echo csrfField(); ?>
      <div class="panel-grid">
        <div class="form-group">
          <label>Icon / Emoji</label>
          <input type="text" name="icon" placeholder="e.g. 🛡️" maxlength="10" />
        </div>
        <div class="form-group">
          <label>Title *</label>
          <input type="text" name="title" required maxlength="120" placeholder="Project name" />
        </div>
        <div class="form-group span-2">
          <label>Description *</label>
          <textarea name="desc" rows="3" required maxlength="500" placeholder="What does this project do?"></textarea>
        </div>
        <div class="form-group">
          <label>Live Project URL</label>
          <input type="url" name="url" placeholder="https://…" />
        </div>
        <div class="form-group">
          <label>GitHub URL</label>
          <input type="url" name="github" placeholder="https://github.com/…" />
        </div>
      </div>
      <div class="panel-actions">
        <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="section-header">
    <h2>All Projects <span style="color:var(--muted);font-weight:400;font-size:.9rem;">(<?php echo count($projects); ?>)</span></h2>
  </div>

  <?php if (empty($projects)): ?>
    <div class="empty-state"><div class="icon">🚀</div><p>No projects yet. Add your first project above.</p></div>
  <?php else: ?>
    <div class="cards-grid">
      <?php foreach ($projects as $p): ?>
        <div class="item-card">
          <div class="item-card-header">
            <div class="item-card-icon"><?php echo e($p['icon']); ?></div>
            <div class="item-card-title"><?php echo e($p['title']); ?></div>
          </div>
          <div class="item-card-desc"><?php echo e($p['description']); ?></div>
          <?php if ($p['project_url'] || $p['github_url']): ?>
            <div class="item-card-links">
              <?php if ($p['project_url']): ?>
                <a class="item-card-link" href="<?php echo e($p['project_url']); ?>" target="_blank" rel="noopener">🔗 Live</a>
              <?php endif; ?>
              <?php if ($p['github_url']): ?>
                <a class="item-card-link" href="<?php echo e($p['github_url']); ?>" target="_blank" rel="noopener">🐙 GitHub</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="item-card-footer">
            <a href="?tab=projects&edit_project=<?php echo (int)$p['id']; ?>" class="btn btn-warning">✏️ Edit</a>
            <a href="?tab=projects&delete_project=<?php echo (int)$p['id']; ?>"
               class="btn btn-danger"
               onclick="return confirm('Delete project: <?php echo e(addslashes($p['title'])); ?>?')">🗑 Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<!-- ═══════════════════════ SOCIAL EMBEDS TAB ════════════════════════════════ -->
<?php elseif ($tab === 'embeds'): ?>

  <?php if (!empty($editEmbed)): ?>
  <div class="panel">
    <h3>✏️ Edit Embed</h3>
    <form method="POST">
      <?php echo csrfField(); ?>
      <input type="hidden" name="embed_id" value="<?php echo (int)$editEmbed['id']; ?>" />
      <div class="panel-grid">
        <div class="form-group">
          <label>Label <span style="font-weight:400;text-transform:none;">(shown above embed on site)</span></label>
          <input type="text" name="label" value="<?php echo e($editEmbed['label']); ?>" placeholder="e.g. LinkedIn Post" maxlength="80" />
        </div>
        <div class="form-group">
          <label>Sort Order <span style="font-weight:400;text-transform:none;">(lower = first)</span></label>
          <input type="number" name="sort_order" value="<?php echo (int)$editEmbed['sort_order']; ?>" min="0" max="999" />
        </div>
        <div class="form-group span-2">
          <label>Embed Code * <span style="font-weight:400;text-transform:none;">(paste full &lt;iframe&gt; tag)</span></label>
          <textarea name="embed_code" rows="5" required><?php echo e($editEmbed['embed_code']); ?></textarea>
        </div>
      </div>
      <div class="panel-actions">
        <button type="submit" name="save_embed" class="btn btn-success">Save Changes</button>
        <a href="?tab=embeds" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
  <?php else: ?>
  <div class="panel">
    <h3>➕ Add Social Embed</h3>
    <p class="panel-hint">
      Paste the <code>&lt;iframe&gt;</code> embed code from LinkedIn, X/Twitter, or any platform.
      On LinkedIn: open a post → click <strong>···</strong> → <strong>Embed this post</strong> → copy the iframe code.
    </p>
    <form method="POST">
      <?php echo csrfField(); ?>
      <div class="panel-grid">
        <div class="form-group">
          <label>Label <span style="font-weight:400;text-transform:none;">(optional)</span></label>
          <input type="text" name="label" placeholder="e.g. LinkedIn Post" maxlength="80" />
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" name="sort_order" value="0" min="0" max="999" />
        </div>
        <div class="form-group span-2">
          <label>Embed Code *</label>
          <textarea name="embed_code" rows="5" required placeholder='<iframe src="https://www.linkedin.com/embed/feed/update/urn:li:ugcPost:..." height="566" width="504" frameborder="0" allowfullscreen="" title="Embedded post"></iframe>'></textarea>
        </div>
      </div>
      <div class="panel-actions">
        <button type="submit" name="add_embed" class="btn btn-primary">Add Embed</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="section-header">
    <h2>All Embeds <span style="color:var(--muted);font-weight:400;font-size:.9rem;">(<?php echo count($embeds); ?>)</span></h2>
    <span style="color:var(--muted);font-size:.82rem;">Shown as "Latest Posts" section on your portfolio.</span>
  </div>

  <?php if (empty($embeds)): ?>
    <div class="empty-state"><div class="icon">📌</div><p>No embeds yet. Paste your first social media embed above.</p></div>
  <?php else: ?>
    <div class="cards-grid">
      <?php foreach ($embeds as $em): ?>
        <div class="item-card">
          <div class="item-card-header">
            <div class="item-card-icon">📌</div>
            <div>
              <div class="item-card-title">
                <?php echo $em['label'] !== '' ? e($em['label']) : '<span style="color:var(--muted);font-weight:400;">No label</span>'; ?>
              </div>
              <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem;">Sort order: <?php echo (int)$em['sort_order']; ?></div>
            </div>
          </div>
          <div class="embed-preview"><?php echo $em['embed_code']; ?></div>
          <div class="item-card-footer">
            <a href="?tab=embeds&edit_embed=<?php echo (int)$em['id']; ?>" class="btn btn-warning">✏️ Edit</a>
            <a href="?tab=embeds&delete_embed=<?php echo (int)$em['id']; ?>"
               class="btn btn-danger"
               onclick="return confirm('Delete this embed?')">🗑 Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<!-- ═══════════════════════ SETTINGS TAB ═════════════════════════════════════ -->
<?php elseif ($tab === 'settings'): ?>

  <?php if ($settingsSaved): ?>
    <div style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#16a34a;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.5rem;font-weight:600;">
      ✅ Settings saved successfully.
    </div>
  <?php endif; ?>

  <div class="panel" style="max-width:560px;">
    <h3>🖼️ Hero Image Settings</h3>
    <p class="panel-hint">Control the 3D tilt effect and the status badge shown on your profile photo.</p>
    <form method="POST">
      <?php echo csrfField(); ?>

      <!-- 3D Tilt toggle -->
      <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem;background:var(--bg);border:1px solid var(--border);border-radius:8px;margin-bottom:1rem;">
        <div>
          <div style="font-weight:600;font-size:.95rem;">3D Tilt Effect</div>
          <div style="font-size:.8rem;color:var(--muted);margin-top:.2rem;">Mouse-tracking parallax tilt on hero photo</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" name="tilt_enabled" value="1"
                 <?php echo $siteSettings['tilt_enabled'] === '1' ? 'checked' : ''; ?> />
          <span class="toggle-slider"></span>
        </label>
      </div>

      <!-- Badge toggle -->
      <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem;background:var(--bg);border:1px solid var(--border);border-radius:8px;margin-bottom:1rem;">
        <div>
          <div style="font-weight:600;font-size:.95rem;">Status Badge</div>
          <div style="font-size:.8rem;color:var(--muted);margin-top:.2rem;">Show badge pill below your photo</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" name="badge_visible" value="1"
                 <?php echo $siteSettings['badge_visible'] === '1' ? 'checked' : ''; ?> />
          <span class="toggle-slider"></span>
        </label>
      </div>

      <!-- Badge text -->
      <div class="form-group" style="margin-bottom:1.2rem;">
        <label>Badge Text</label>
        <input type="text" name="badge_text"
               value="<?php echo e($siteSettings['badge_text']); ?>"
               maxlength="40"
               placeholder="e.g. Open to Work" />
        <div style="font-size:.75rem;color:var(--muted);margin-top:.4rem;">Max 40 characters. Shown only when badge is enabled.</div>
      </div>

      <div class="panel-actions">
        <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
      </div>
    </form>
  </div>

<?php endif; // end tab ?>
</div><!-- /.main -->
<?php endif; // end logged in ?>
</body>
</html>