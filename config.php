<?php
// ── config.php — Shared bootstrap: env, DB, helpers, security headers ────────

// Prevent double-include
if (defined('CONFIG_LOADED')) return;
define('CONFIG_LOADED', true);

// ── Load .env ─────────────────────────────────────────────────────────────────
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}
loadEnv(__DIR__ . '/.env');

// ── Auto-detect local vs live ─────────────────────────────────────────────────
$isLocal = isset($_SERVER['SERVER_NAME']) &&
           in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'));

$db_host = $isLocal ? $_ENV['LOCAL_DB_HOST'] : $_ENV['LIVE_DB_HOST'];
$db_user = $isLocal ? $_ENV['LOCAL_DB_USER'] : $_ENV['LIVE_DB_USER'];
$db_pass = $isLocal ? $_ENV['LOCAL_DB_PASS'] : $_ENV['LIVE_DB_PASS'];
$db_name = $isLocal ? $_ENV['LOCAL_DB_NAME'] : $_ENV['LIVE_DB_NAME'];

// ── DB singleton ──────────────────────────────────────────────────────────────
$GLOBALS['_db']      = null;
$GLOBALS['_db_host'] = $db_host;
$GLOBALS['_db_user'] = $db_user;
$GLOBALS['_db_pass'] = $db_pass;
$GLOBALS['_db_name'] = $db_name;

function db() {
    if ($GLOBALS['_db'] === null) {
        $c = new mysqli($GLOBALS['_db_host'], $GLOBALS['_db_user'], $GLOBALS['_db_pass'], $GLOBALS['_db_name']);
        if ($c->connect_error) {
            error_log('DB connect failed: ' . $c->connect_error);
            if (php_sapi_name() === 'cli' || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
            } else {
                http_response_code(500);
                echo '<p style="color:red;padding:2rem;font-family:sans-serif">Database connection error. Please try again later.</p>';
            }
            exit;
        }
        $c->set_charset('utf8mb4');
        $GLOBALS['_db'] = $c;
    }
    return $GLOBALS['_db'];
}

// ── HTML escape helper ────────────────────────────────────────────────────────
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function envValue($key, $default = '') {
    return isset($_ENV[$key]) && trim((string)$_ENV[$key]) !== '' ? trim((string)$_ENV[$key]) : $default;
}

function siteBaseUrl() {
    $configured = envValue('SITE_URL', envValue('CANONICAL_URL', ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $scriptDir = isset($_SERVER['SCRIPT_NAME']) ? trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') : '';

    $base = $scheme . '://' . $host;
    if ($scriptDir !== '' && $scriptDir !== '.') {
        $base .= '/' . $scriptDir;
    }

    return rtrim($base, '/');
}

function siteUrl($path = '') {
    $path = (string)$path;
    if ($path === '') {
        return siteBaseUrl();
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return siteBaseUrl() . '/' . ltrim($path, '/');
}

function seoImageUrl($path = '') {
    return $path !== '' ? siteUrl($path) : '';
}

function renderSeoHead($options = array()) {
    $defaults = array(
        'title' => 'Abhay Bombale | Portfolio',
        'description' => 'Abhay Bombale’s personal portfolio showcasing cybersecurity skills, projects, certifications, and write-ups.',
        'canonical' => '',
        'canonicalPath' => '',
        'image' => siteUrl('assets/images/Profile.png'),
        'type' => 'website',
        'siteName' => 'Abhay Bombale',
        'twitterCard' => 'summary_large_image',
        'robots' => 'index,follow',
        'locale' => 'en_US',
        'schema' => array(),
    );

    $meta = array_merge($defaults, is_array($options) ? $options : array());
    $title = trim((string)$meta['title']);
    $description = trim((string)$meta['description']);
    $canonical = trim((string)$meta['canonical']);
    $canonicalPath = trim((string)$meta['canonicalPath']);
    $image = trim((string)$meta['image']);
    $siteName = trim((string)$meta['siteName']) !== '' ? trim((string)$meta['siteName']) : 'Abhay Bombale';

    if ($canonical === '' && $canonicalPath !== '') {
        $canonical = siteUrl($canonicalPath);
    }

    if ($canonical === '') {
        $canonical = siteBaseUrl();
    }

    if ($title === '') {
        $title = $siteName;
    }

    echo '<title>' . e($title) . '</title>' . PHP_EOL;
    if ($description !== '') {
        echo '<meta name="description" content="' . e($description) . '" />' . PHP_EOL;
    }
    if ($canonical !== '') {
        echo '<link rel="canonical" href="' . e($canonical) . '" />' . PHP_EOL;
    }
    if (!empty($meta['robots'])) {
        echo '<meta name="robots" content="' . e($meta['robots']) . '" />' . PHP_EOL;
    }

    echo '<meta property="og:type" content="' . e($meta['type']) . '" />' . PHP_EOL;
    echo '<meta property="og:title" content="' . e($title) . '" />' . PHP_EOL;
    if ($description !== '') {
        echo '<meta property="og:description" content="' . e($description) . '" />' . PHP_EOL;
    }
    echo '<meta property="og:url" content="' . e($canonical) . '" />' . PHP_EOL;
    echo '<meta property="og:site_name" content="' . e($siteName) . '" />' . PHP_EOL;
    if ($image !== '') {
        echo '<meta property="og:image" content="' . e($image) . '" />' . PHP_EOL;
    }
    if (!empty($meta['locale'])) {
        echo '<meta property="og:locale" content="' . e($meta['locale']) . '" />' . PHP_EOL;
    }

    echo '<meta name="twitter:card" content="' . e($meta['twitterCard']) . '" />' . PHP_EOL;
    echo '<meta name="twitter:title" content="' . e($title) . '" />' . PHP_EOL;
    if ($description !== '') {
        echo '<meta name="twitter:description" content="' . e($description) . '" />' . PHP_EOL;
    }
    if ($image !== '') {
        echo '<meta name="twitter:image" content="' . e($image) . '" />' . PHP_EOL;
    }

    $schemaEntries = isset($meta['schema']) ? $meta['schema'] : array();
    if (!is_array($schemaEntries)) {
        $schemaEntries = array($schemaEntries);
    }
    foreach ($schemaEntries as $schema) {
        if (empty($schema)) {
            continue;
        }
        $schemaJson = is_string($schema)
            ? $schema
            : json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($schemaJson !== false && $schemaJson !== '') {
            echo '<script type="application/ld+json">' . $schemaJson . '</script>' . PHP_EOL;
        }
    }
}

// ── Security headers ──────────────────────────────────────────────────────────
function sendSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

// ── CSRF token helpers ────────────────────────────────────────────────────────
function csrfToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '" />';
}

function verifyCsrf() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// ── URL validation helper ─────────────────────────────────────────────────────
function isValidUrl($url) {
    if ($url === '') return true; // empty is allowed (optional field)
    return (bool)filter_var($url, FILTER_VALIDATE_URL) &&
           preg_match('/^https?:\/\//i', $url);
}

// ── Session security ──────────────────────────────────────────────────────────
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
    // Session timeout — 30 minutes
    $timeout = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
