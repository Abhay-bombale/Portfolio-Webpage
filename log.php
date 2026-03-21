<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

require_once __DIR__ . '/config.php';
sendSecurityHeaders();

function habitTargetForDate($logDate) {
    $dayOfWeek = (int)date('N', strtotime($logDate));
    return ($dayOfWeek >= 6) ? 3 : 1;
}

function applyStreakRules($state, $logDate, $completedCount) {
    $currentStreak = isset($state['current_streak']) ? (int)$state['current_streak'] : 0;
    $bestStreak = isset($state['best_streak']) ? (int)$state['best_streak'] : 0;
    $freezeBalance = isset($state['freeze_balance']) ? (int)$state['freeze_balance'] : 0;
    $lastActiveDate = isset($state['last_active_date']) ? (string)$state['last_active_date'] : '';
    $feedbackType = 'success';
    $feedbackText = 'Target met. Streak increased.';

    $target = habitTargetForDate($logDate);
    $targetMet = ((int)$completedCount >= (int)$target);

    if ($targetMet) {
        $currentStreak += 1;
        if ($currentStreak > $bestStreak) {
            $bestStreak = $currentStreak;
        }
        if ((int)$completedCount >= 3) {
            $freezeBalance += 1;
        }
        $lastActiveDate = $logDate;
    } else {
        if ($freezeBalance > 0) {
            $freezeBalance -= 1;
            $feedbackType = 'info';
            $feedbackText = '🧊 Freeze used. Streak protected.';
        } else {
            $currentStreak = 0;
            $feedbackType = 'danger';
            $feedbackText = '💔 Streak reset. Target not met.';
        }
    }

    return array(
        'target' => $target,
        'target_met' => $targetMet ? 1 : 0,
        'feedback_type' => $feedbackType,
        'feedback_text' => $feedbackText,
        'state' => array(
            'current_streak' => (int)$currentStreak,
            'best_streak' => (int)$bestStreak,
            'freeze_balance' => (int)$freezeBalance,
            'last_active_date' => $lastActiveDate,
        ),
    );
}

function loadStreakStateFromDb() {
    $state = array(
        'current_streak' => 0,
        'best_streak' => 0,
        'freeze_balance' => 0,
        'last_active_date' => '',
    );
    $r = db()->query('SELECT setting_key, setting_value FROM streak_state');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            if ($row['setting_key'] === 'current_streak') {
                $state['current_streak'] = (int)$row['setting_value'];
            }
            if ($row['setting_key'] === 'best_streak') {
                $state['best_streak'] = (int)$row['setting_value'];
            }
            if ($row['setting_key'] === 'freeze_balance') {
                $state['freeze_balance'] = (int)$row['setting_value'];
            }
            if ($row['setting_key'] === 'last_active_date') {
                $state['last_active_date'] = (string)$row['setting_value'];
            }
        }
        $r->close();
    }
    return $state;
}

$today = date('Y-m-d');
$todayLong = date('l, M j, Y', strtotime($today));

$habits = array();
$todayLogMap = array();
$todayNote = array('note' => '', 'created_at' => '', 'updated_at' => '');
$streakState = array(
    'current_streak' => 0,
    'best_streak' => 0,
    'freeze_balance' => 0,
    'last_active_date' => '',
);
$tableError = '';

$hasHabitsTable = false;
$hasHabitLogsTable = false;
$hasDailyNotesTable = false;
$hasStreakStateTable = false;

$chk = db()->query("SHOW TABLES LIKE 'habits'");
if ($chk) { $hasHabitsTable = ($chk->num_rows > 0); $chk->close(); }
$chk = db()->query("SHOW TABLES LIKE 'habit_logs'");
if ($chk) { $hasHabitLogsTable = ($chk->num_rows > 0); $chk->close(); }
$chk = db()->query("SHOW TABLES LIKE 'daily_notes'");
if ($chk) { $hasDailyNotesTable = ($chk->num_rows > 0); $chk->close(); }
$chk = db()->query("SHOW TABLES LIKE 'streak_state'");
if ($chk) { $hasStreakStateTable = ($chk->num_rows > 0); $chk->close(); }

if (!$hasHabitsTable || !$hasHabitLogsTable || !$hasDailyNotesTable || !$hasStreakStateTable) {
    $tableError = 'Habit tracker tables are missing. Run setup.sql first.';
}

if ($tableError === '' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_habits') {
    if (!verifyCsrf()) {
        $_SESSION['log_feedback'] = array('type' => 'danger', 'text' => 'Invalid request token. Please try again.');
        header('Location: log.php');
        exit;
    }

    $r = db()->query('SELECT id, name, emoji, is_active, sort_order FROM habits WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $habits[] = $row;
        }
        $r->close();
    }

    $saveStmt = db()->prepare('INSERT INTO habit_logs (habit_id, log_date, completed) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE completed = VALUES(completed)');

    $completedCount = 0;
    foreach ($habits as $h) {
        $habitId = (int)$h['id'];
        $checkboxKey = 'habit_' . $habitId;
        $completed = isset($_POST[$checkboxKey]) ? 1 : 0;
        if ($completed === 1) {
            $completedCount++;
        }

        $saveStmt->bind_param('isi', $habitId, $today, $completed);
        $saveStmt->execute();
    }
    $saveStmt->close();

    $streakState = loadStreakStateFromDb();
    $result = applyStreakRules($streakState, $today, $completedCount);

    $stateSaveStmt = db()->prepare('INSERT INTO streak_state (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($result['state'] as $k => $v) {
        $key = (string)$k;
        $val = (string)$v;
        $stateSaveStmt->bind_param('ss', $key, $val);
        $stateSaveStmt->execute();
    }
    $stateSaveStmt->close();

    $_SESSION['log_feedback'] = array('type' => $result['feedback_type'], 'text' => $result['feedback_text']);
    header('Location: log.php?saved=1#note');
    exit;
}

if ($tableError === '' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_note') {
    if (!verifyCsrf()) {
        $_SESSION['log_feedback'] = array('type' => 'danger', 'text' => 'Invalid request token. Please try again.');
        header('Location: log.php#note');
        exit;
    }

    $rawNote = isset($_POST['note']) ? $_POST['note'] : '';
    $note = trim(strip_tags($rawNote));
    if (strlen($note) > 1000) {
        $note = substr($note, 0, 1000);
    }

    $stmt = db()->prepare('INSERT INTO daily_notes (log_date, note) VALUES (?, ?) ON DUPLICATE KEY UPDATE note = VALUES(note), updated_at = NOW()');
    $stmt->bind_param('ss', $today, $note);
    $stmt->execute();
    $stmt->close();

    $_SESSION['log_feedback'] = array('type' => 'success', 'text' => 'Today\'s note saved.');
    header('Location: log.php#note');
    exit;
}

if ($tableError === '') {
    $r = db()->query('SELECT id, name, emoji, is_active, sort_order FROM habits WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $habits[] = $row;
        }
        $r->close();
    }

    $stmt = db()->prepare('SELECT habit_id, completed FROM habit_logs WHERE log_date = ?');
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $todayLogMap[(int)$row['habit_id']] = (int)$row['completed'];
    }
    $stmt->close();

    $stmt = db()->prepare('SELECT log_date, note, created_at, updated_at FROM daily_notes WHERE log_date = ? LIMIT 1');
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $todayNote = $row;
    }
    $stmt->close();

    $streakState = loadStreakStateFromDb();
}

$completedToday = 0;
foreach ($habits as $h) {
    $hid = (int)$h['id'];
    if (isset($todayLogMap[$hid]) && (int)$todayLogMap[$hid] === 1) {
        $completedToday++;
    }
}

$target = habitTargetForDate($today);
$targetMet = ($completedToday >= $target);

$feedback = null;
if (isset($_SESSION['log_feedback'])) {
    $feedback = $_SESSION['log_feedback'];
    unset($_SESSION['log_feedback']);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Log - <?php echo e($todayLong); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    body { background: var(--bg-gray); }
    .log-wrap { max-width: 760px; margin: 0 auto; padding: 1rem 1rem 3rem 1rem; }
    .log-topbar { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:1rem; }
    .log-card { background: var(--bg-light); border:1px solid var(--border-color); border-radius: 12px; padding: 1rem; box-shadow: var(--shadow-sm); margin-bottom: 1rem; }
    .log-title { margin: 0; font-size: 1.35rem; color: var(--text-dark); }
    .streak-bar { display:flex; gap:.75rem; flex-wrap:wrap; margin-top:.75rem; }
    .streak-pill { background: var(--bg-gray); border:1px solid var(--border-color); border-radius: 999px; padding:.45rem .8rem; font-size:.88rem; color: var(--text-dark); }
    .feedback { border-radius:999px; padding:.55rem .9rem; font-size:.85rem; font-weight:600; display:inline-block; margin-top:.75rem; }
    .feedback.success { background: rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.3); color:#047857; }
    .feedback.danger { background: rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#b91c1c; }
    .feedback.info { background: rgba(59,130,246,.12); border:1px solid rgba(59,130,246,.3); color:#1d4ed8; }
    .log-habit-row { display:flex; align-items:center; gap:1rem; padding:.9rem 1rem; border:1px solid var(--border-color); border-radius:8px; margin-bottom:.5rem; cursor:pointer; transition:background-color .15s ease; min-height:44px; }
    .log-habit-row:hover { background-color: var(--bg-gray); }
    .log-habit-row input[type="checkbox"] { width:20px; height:20px; cursor:pointer; flex-shrink:0; }
    .log-progress { margin-top: .75rem; font-size: .9rem; color: var(--text-dark); }
    .log-progress .ok { color: #047857; font-weight: 600; }
    .log-progress .no { color: #b91c1c; font-weight: 600; }
    .log-divider { border-top: 1px dashed var(--border-color); margin: 1rem 0; }
    .log-textarea { width:100%; min-height:130px; border:1px solid var(--border-color); border-radius:8px; padding:.75rem; font-family:inherit; font-size:.95rem; color:var(--text-dark); background:var(--bg-light); resize:vertical; }
    .log-note-meta { margin-top:.55rem; color:var(--text-light); font-size:.78rem; }
    .log-actions { margin-top:.8rem; display:flex; gap:.6rem; flex-wrap:wrap; }
    .log-error { background: rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#b91c1c; border-radius:8px; padding:.8rem .95rem; }
    @media (max-width: 640px) {
      .log-wrap { padding: .85rem .75rem 2rem .75rem; }
      .log-title { font-size: 1.15rem; }
      .streak-pill { font-size:.8rem; }
    }
  </style>
</head>
<body>
  <div class="log-wrap">
    <div class="log-topbar">
      <a href="index.php" class="logo" style="font-size:1.1rem;">Abhay</a>
      <a href="admin.php?tab=habits" class="btn btn-secondary">&larr; Admin</a>
    </div>

    <div class="log-card">
      <h1 class="log-title">📅 Log - <?php echo e($todayLong); ?></h1>

      <div class="streak-bar">
        <div class="streak-pill">🔥 <?php echo (int)$streakState['current_streak']; ?> days</div>
        <div class="streak-pill">🏆 Best: <?php echo (int)$streakState['best_streak']; ?></div>
        <div class="streak-pill">🧊 Freezes: <?php echo (int)$streakState['freeze_balance']; ?></div>
      </div>

      <?php if ($feedback): ?>
        <div class="feedback <?php echo e(isset($feedback['type']) ? $feedback['type'] : 'info'); ?>">
          <?php echo e(isset($feedback['text']) ? $feedback['text'] : 'Saved.'); ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($tableError !== ''): ?>
      <div class="log-error"><?php echo e($tableError); ?></div>
    <?php else: ?>
      <div class="log-card">
        <form method="POST">
          <?php echo csrfField(); ?>
          <input type="hidden" name="action" value="save_habits" />

          <?php if (empty($habits)): ?>
            <p style="margin:0;color:var(--text-light);">No active habits found. Add habits from admin first.</p>
          <?php else: ?>
            <?php foreach ($habits as $h): ?>
              <?php $hid = (int)$h['id']; ?>
              <label class="log-habit-row" for="habit_<?php echo $hid; ?>">
                <input id="habit_<?php echo $hid; ?>" type="checkbox" name="habit_<?php echo $hid; ?>" value="1" <?php echo (isset($todayLogMap[$hid]) && (int)$todayLogMap[$hid] === 1) ? 'checked' : ''; ?> />
                <span style="font-size:1.1rem;"><?php echo e($h['emoji'] !== '' ? $h['emoji'] : '✅'); ?></span>
                <span style="font-weight:600;color:var(--text-dark);"><?php echo e($h['name']); ?></span>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>

          <div class="log-progress">
            <?php echo (int)$completedToday; ?> / <?php echo count($habits); ?> today - Target: <?php echo (int)$target; ?> (<?php echo ((int)date('N') >= 6) ? 'weekend' : 'weekday'; ?>)
            <?php if ($targetMet): ?>
              <span class="ok">✅ MET</span>
            <?php else: ?>
              <span class="no">❌ NOT YET</span>
            <?php endif; ?>
          </div>

          <div class="log-actions">
            <button type="submit" class="btn btn-primary">Save Habits</button>
          </div>
        </form>

        <div class="log-divider"></div>

        <div id="note"></div>
        <h3 style="margin-bottom:.5rem;">Today's Note</h3>
        <form method="POST">
          <?php echo csrfField(); ?>
          <input type="hidden" name="action" value="save_note" />
          <textarea class="log-textarea" name="note" maxlength="1000" placeholder="What did you commit today?"><?php echo e(isset($todayNote['note']) ? $todayNote['note'] : ''); ?></textarea>
          <?php if (!empty($todayNote['created_at'])): ?>
            <div class="log-note-meta">
              Logged at <?php echo e(date('M j, Y g:i A', strtotime($todayNote['created_at']))); ?>
              <?php if (!empty($todayNote['updated_at']) && $todayNote['updated_at'] !== $todayNote['created_at']): ?>
                · Edited at <?php echo e(date('M j, Y g:i A', strtotime($todayNote['updated_at']))); ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="log-actions">
            <button type="submit" class="btn btn-primary">Save Note</button>
          </div>
        </form>
      </div>

      <a href="admin.php?tab=habits" class="btn btn-secondary">&larr; Back to Admin Panel</a>
    <?php endif; ?>
  </div>
</body>
</html>
