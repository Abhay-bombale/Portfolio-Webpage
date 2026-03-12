<?php
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

// ── Helper: wipe buffer, send JSON, exit ─────────────────────────────────────
function respond($success, $message) {
    ob_clean();
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// ── Only allow POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// ── Collect & sanitize inputs ─────────────────────────────────────────────────
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

// Validate: required fields
if ($name === '' || $email === '' || $message === '') {
    respond(false, 'All fields are required.');
}

// Validate: email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

// Validate: max lengths (prevent abuse)
if (strlen($name) > 100 || strlen($email) > 150 || strlen($message) > 2000) {
    respond(false, 'Input exceeds maximum allowed length.');
}

// ── Connect ───────────────────────────────────────────────────────────────────
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    error_log('DB connect failed: ' . $conn->connect_error);
    respond(false, 'Server error. Please try again later.');
}

// Use utf8mb4 so emojis and special chars store correctly
$conn->set_charset('utf8mb4');

// ── Insert ────────────────────────────────────────────────────────────────────
$stmt = $conn->prepare(
    'INSERT INTO contacts (name, email, message, created_at) VALUES (?, ?, ?, NOW())'
);

if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    respond(false, 'Server error. Please try again later.');
}

$stmt->bind_param('sss', $name, $email, $message);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    respond(true, 'Message saved successfully!');
} else {
    error_log('Execute failed: ' . $stmt->error);
    $stmt->close();
    $conn->close();
    respond(false, 'Could not save your message. Please try again.');
}