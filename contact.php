<?php
require_once __DIR__ . '/config.php';
sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
ob_start();

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

// ── Honeypot check — bots fill hidden fields ──────────────────────────────────
$honeypot = trim(isset($_POST['website']) ? $_POST['website'] : '');
if ($honeypot !== '') {
    respond(false, 'Spam detected.');
}

// ── Simple rate limiting (1 submission per 10s per IP via session) ─────────────
initSession();
$now = time();
$lastSubmit = isset($_SESSION['last_contact_submit']) ? $_SESSION['last_contact_submit'] : 0;
if ($now - $lastSubmit < 10) {
    respond(false, 'Please wait a few seconds before submitting again.');
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
$conn = db();

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
    $_SESSION['last_contact_submit'] = time();

    // ── Send email notification ───────────────────────────────────────────────
    $notifyEmail = '';
    $r = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'notify_email'");
    if ($r && $row = $r->fetch_assoc()) { $notifyEmail = trim($row['setting_value']); }
    if ($notifyEmail !== '' && filter_var($notifyEmail, FILTER_VALIDATE_EMAIL)) {
        $subject = 'New Contact Form Message from ' . $name;
        $body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: noreply@" . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost') . "\r\n"
                 . "Reply-To: $email\r\n"
                 . "Content-Type: text/plain; charset=UTF-8";
        @mail($notifyEmail, $subject, $body, $headers);
    }

    respond(true, 'Message saved successfully!');
} else {
    error_log('Execute failed: ' . $stmt->error);
    $stmt->close();
    respond(false, 'Could not save your message. Please try again.');
}