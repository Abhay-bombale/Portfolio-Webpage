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
