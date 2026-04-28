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
    $currentStreak  = isset($state['current_streak'])   ? (int)$state['current_streak']    : 0;
    $bestStreak     = isset($state['best_streak'])      ? (int)$state['best_streak']        : 0;
    $freezeBalance  = isset($state['freeze_balance'])   ? (int)$state['freeze_balance']     : 0;
    $lastActiveDate = isset($state['last_active_date']) ? (string)$state['last_active_date'] : '';
    $feedbackType   = 'success';
    $feedbackText   = 'Target met. Streak continued.';

    $target    = habitTargetForDate($logDate);
    $targetMet = ((int)$completedCount >= (int)$target);

    // Calculate day gap between last active log and the current log date.
    // -1 means no prior log exists (first log ever).
    // Uses DateTime::diff to handle DST transitions correctly.
    $dayGap = -1;
    if ($lastActiveDate !== '') {
        $d1 = DateTime::createFromFormat('Y-m-d', $lastActiveDate);
        $d2 = DateTime::createFromFormat('Y-m-d', $logDate);
        if ($d1 !== false && $d2 !== false) {
            $diff = (int)$d1->diff($d2)->days;
            // If last_active_date is in the future, treat as no prior log.
            $dayGap = ($d2 >= $d1) ? $diff : -1;
        }
    }

    // Account for any fully missed days between the last active date and today.
    // dayGap > 1 means (dayGap - 1) days were skipped without any log.
    if ($dayGap > 1) {
        $missedDays = $dayGap - 1;
        if ($freezeBalance >= $missedDays) {
            $freezeBalance -= $missedDays;
        } else {
            // Not enough freezes to cover all missed days – streak broken.
            $freezeBalance = 0;
            $currentStreak = 0;
        }
    }

    // Apply today's target logic.
    if ($targetMet) {
        $currentStreak += 1;
        if ($currentStreak > $bestStreak) {
            $bestStreak = $currentStreak;
        }
        $feedbackType   = 'success';
        $feedbackText   = 'Target met. Streak continued.';
        if ((int)$completedCount >= 3) {
            $freezeBalance += 1;
            $feedbackType  = 'earned';
            $feedbackText  = 'Target met. Freeze earned for 3+ completions.';
        }
        $lastActiveDate = $logDate;
    } else {
        if ($freezeBalance > 0) {
            $freezeBalance -= 1;
            $feedbackType   = 'freeze';
            $feedbackText   = '🧊 Freeze used. Streak protected.';
            // Advance last_active_date so tomorrow does not see a 2-day gap.
            $lastActiveDate = $logDate;
        } else {
            $currentStreak  = 0;
            $feedbackType   = 'reset';
            $feedbackText   = '💔 Streak reset. Target not met.';
        }
    }

    return array(
        'target'        => $target,
        'target_met'    => $targetMet ? 1 : 0,
        'feedback_type' => $feedbackType,
        'feedback_text' => $feedbackText,
        'state' => array(
            'current_streak'   => (int)$currentStreak,
            'best_streak'      => (int)$bestStreak,
            'freeze_balance'   => (int)$freezeBalance,
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

// Load the streak state that was saved before the first log of today.
// Used when re-logging on the same day so the streak is recalculated
// from a clean base instead of compounding the previous save's effects.
function loadPreTodayStateFromDb() {
    $state = array(
        'current_streak' => 0,
        'best_streak' => 0,
        'freeze_balance' => 0,
        'last_active_date' => '',
        'today_log_date' => '',
    );
    $r = db()->query('SELECT setting_key, setting_value FROM streak_state');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            if ($row['setting_key'] === 'pre_today_current_streak') {
                $state['current_streak'] = (int)$row['setting_value'];
            }
            if ($row['setting_key'] === 'pre_today_best_streak') {
                $state['best_streak'] = (int)$row['setting_value'];
            }
            if ($row['setting_key'] === 'pre_today_freeze_balance') {
                $state['freeze_balance'] = (int)$row['setting_value'];
            }
            if ($row['setting_key'] === 'pre_today_last_active_date') {
                $state['last_active_date'] = (string)$row['setting_value'];
            }
            if ($row['setting_key'] === 'today_log_date') {
                $state['today_log_date'] = (string)$row['setting_value'];
            }
        }
        $r->close();
    }
    return $state;
}

// Persist the pre-today snapshot and mark which calendar date it was taken for.
function savePreTodayStateToDb($state, $todayDate) {
    $stmt = db()->prepare(
        'INSERT INTO streak_state (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $pairs = array(
        'pre_today_current_streak'    => (string)(int)$state['current_streak'],
        'pre_today_best_streak'       => (string)(int)$state['best_streak'],
        'pre_today_freeze_balance'    => (string)(int)$state['freeze_balance'],
        'pre_today_last_active_date'  => (string)$state['last_active_date'],
        'today_log_date'              => (string)$todayDate,
    );
    foreach ($pairs as $k => $v) {
        $stmt->bind_param('ss', $k, $v);
        $stmt->execute();
    }
    $stmt->close();
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
    $_SESSION['log_flash'] = array('type' => 'reset', 'text' => 'Invalid request token. Please try again.');
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

    $selectedCount = 0;
    foreach ($habits as $h) {
        $habitId = (int)$h['id'];
        if (isset($_POST['habit_' . $habitId])) {
            $selectedCount++;
        }
    }
    if ($selectedCount === 0) {
        $_SESSION['log_flash'] = array('type' => 'reset', 'text' => 'Select at least one habit before saving.');
        header('Location: log.php');
        exit;
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

    // Load current streak state and the pre-today snapshot.
    // On the first save of the day, snapshot the current state so any
    // re-save today always recalculates from that clean baseline – this
    // ensures unchecking habits later in the day correctly reverts streaks
    // and freeze changes instead of stacking them.
    $streakState   = loadStreakStateFromDb();
    $preTodayState = loadPreTodayStateFromDb();

    if ($preTodayState['today_log_date'] !== $today) {
        // First log of this calendar day: save a snapshot of the state
        // as it stood before any changes today.
        savePreTodayStateToDb($streakState, $today);
        $baseState = $streakState;
    } else {
        // Re-logging the same day: recalculate from today's opening state.
        $baseState = $preTodayState;
    }

    $result = applyStreakRules($baseState, $today, $completedCount);

    $stateSaveStmt = db()->prepare('INSERT INTO streak_state (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($result['state'] as $k => $v) {
        $key = (string)$k;
        $val = (string)$v;
        $stateSaveStmt->bind_param('ss', $key, $val);
        $stateSaveStmt->execute();
    }
    $stateSaveStmt->close();

    $_SESSION['log_flash'] = array('type' => $result['feedback_type'], 'text' => $result['feedback_text']);
    header('Location: log.php?saved=1#note');
    exit;
}

if ($tableError === '' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_note') {
    if (!verifyCsrf()) {
    $_SESSION['log_flash'] = array('type' => 'reset', 'text' => 'Invalid request token. Please try again.');
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

    $_SESSION['log_flash'] = array('type' => 'success', 'text' => 'Today\'s note saved.');
    header('Location: log.php?noted=1');
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
if (isset($_SESSION['log_flash'])) {
  $feedback = $_SESSION['log_flash'];
  unset($_SESSION['log_flash']);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Log - <?php echo e($todayLong); ?></title>
  <meta name="robots" content="noindex,nofollow" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    body {
      background: #ffffff;
      font-family: 'DM Sans', system-ui, sans-serif;
      color: #4a5568;
      padding: 0;
      margin: 0;
    }
    .log-wrap {
      max-width: 480px;
      margin: 0 auto;
      padding: 1.5rem 1rem 4rem;
    }
    .log-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }
    .log-back {
      color: #0066cc;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
    }
    .log-card {
      background: #f8f9fa;
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 1rem;
    }
    .log-title {
      margin: 0;
      font-size: 1.2rem;
      color: #1a1a2a;
    }
    .log-streak-bar {
      display: flex;
      gap: 1.5rem;
      justify-content: space-around;
      margin-top: 1.25rem;
      margin-bottom: 1.25rem;
    }
    .log-streak-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.2rem;
    }
    .log-streak-val {
      font-size: 2rem;
      font-weight: 700;
      color: #1a1a2a;
      line-height: 1;
    }
    .log-streak-label {
      font-size: 0.65rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #a0aec0;
    }
    .log-progress-track {
      height: 4px;
      background: #e2e8f0;
      border-radius: 2px;
      overflow: hidden;
      margin-top: 1rem;
    }
    .log-progress-fill {
      height: 100%;
      background: #ff6b2b;
      border-radius: 2px;
      -webkit-transition: width 0.4s ease;
      transition: width 0.4s ease;
    }
    .log-progress {
      margin-top: 0.75rem;
      font-size: 0.85rem;
      color: #4a5568;
    }
    .log-progress .ok { color: #22c55e; font-weight: 600; }
    .log-progress .no { color: #ef4444; font-weight: 600; }
    .log-habit-row {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem;
      background: #f8f9fa;
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 10px;
      margin-bottom: 0.5rem;
      cursor: pointer;
      min-height: 52px;
      -webkit-transition: border-color 0.2s ease, background 0.2s ease;
      transition: border-color 0.2s ease, background 0.2s ease;
    }
    .log-habit-row.checked {
      border-color: rgba(255, 107, 43, 0.4);
      background: rgba(255, 107, 43, 0.08);
    }
    .log-habit-row input[type="checkbox"] {
      width: 20px;
      height: 20px;
      accent-color: #ff6b2b;
      cursor: pointer;
      flex-shrink: 0;
    }
    .log-habit-emoji { font-size: 1.4rem; }
    .log-habit-name  { font-size: 1rem; color: #1a1a2a; font-weight: 500; }
    .log-divider {
      border-top: 1px dashed rgba(0,0,0,0.12);
      margin: 1rem 0;
    }
    .log-note-textarea {
      width: 100%;
      background: #f8f9fa;
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 10px;
      padding: 0.9rem;
      color: #1a1a2a;
      font-size: 0.95rem;
      resize: vertical;
      min-height: 100px;
      font-family: inherit;
      -webkit-transition: border-color 0.2s ease;
      transition: border-color 0.2s ease;
    }
    .log-note-textarea:focus {
      outline: none;
      border-color: rgba(0, 102, 204, 0.4);
    }
    .log-note-meta {
      margin-top: 0.55rem;
      color: #a0aec0;
      font-size: 0.78rem;
    }
    .log-actions { margin-top: 0.8rem; display: flex; gap: 0.6rem; flex-wrap: wrap; }
    .log-btn-save {
      width: 100%;
      padding: 0.9rem;
      background: #ff6b2b;
      color: #ffffff;
      border: none;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      -webkit-transition: background 0.2s ease, box-shadow 0.2s ease;
      transition: background 0.2s ease, box-shadow 0.2s ease;
      margin-top: 0.75rem;
    }
    .log-btn-save:hover {
      background: #e8652a;
      box-shadow: 0 0 24px rgba(255, 107, 43, 0.3);
    }
    .log-feedback {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      font-size: 0.875rem;
      font-weight: 600;
      margin-bottom: 1rem;
      text-align: center;
    }
    .log-feedback.success { background: rgba(34,197,94,0.12); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
    .log-feedback.freeze  { background: rgba(0,212,255,0.08); color: #00d4ff; border: 1px solid rgba(0,212,255,0.15); }
    .log-feedback.reset   { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.15); }
    .log-feedback.earned  { background: rgba(255,107,43,0.1); color: #ff6b2b; border: 1px solid rgba(255,107,43,0.2); }
    .log-error {
      background: rgba(239,68,68,.12);
      border: 1px solid rgba(239,68,68,.3);
      color: #ef4444;
      border-radius: 8px;
      padding: .8rem .95rem;
    }
    @media (max-width: 640px) {
      .log-wrap { padding: .85rem .75rem 2rem .75rem; }
      .log-title { font-size: 1.15rem; }
      .log-streak-bar { gap: 0.75rem; }
      .log-streak-val { font-size: 1.6rem; }
    }
  </style>
</head>
<body>
  <div class="log-wrap">
    <div class="log-topbar">
      <a href="admin.php" class="log-back">&larr; Back to Admin</a>
    </div>

    <div class="log-card">
      <h1 class="log-title">📅 <?php echo e($todayLong); ?></h1>

      <?php if ($feedback): ?>
        <div class="log-feedback <?php echo e(isset($feedback['type']) ? $feedback['type'] : 'success'); ?>">
          <?php echo e(isset($feedback['text']) ? $feedback['text'] : 'Saved.'); ?>
        </div>
      <?php endif; ?>

      <div class="log-streak-bar">
        <div class="log-streak-item">
          <span class="log-streak-val"><?php echo (int)$streakState['current_streak']; ?></span>
          <span class="log-streak-label">Current</span>
        </div>
        <div class="log-streak-item">
          <span class="log-streak-val"><?php echo (int)$streakState['best_streak']; ?></span>
          <span class="log-streak-label">Best</span>
        </div>
        <div class="log-streak-item">
          <span class="log-streak-val"><?php echo (int)$streakState['freeze_balance']; ?></span>
          <span class="log-streak-label">Freezes</span>
        </div>
      </div>

      <?php $progressPercent = ($target > 0) ? min(100, max(0, (int)round(($completedToday / $target) * 100))) : 0; ?>
      <div class="log-progress-track"><div class="log-progress-fill" style="width:<?php echo (int)$progressPercent; ?>%;"></div></div>
    </div>

    <?php if ($tableError !== ''): ?>
      <div class="log-error"><?php echo e($tableError); ?></div>
    <?php else: ?>
      <div class="log-card">
        <form method="POST" id="habitsForm">
          <?php echo csrfField(); ?>
          <input type="hidden" name="action" value="save_habits" />

          <?php if (empty($habits)): ?>
            <p style="margin:0;color:var(--text-light);">No active habits found. Add habits from admin first.</p>
          <?php else: ?>
            <?php foreach ($habits as $h): ?>
              <?php $hid = (int)$h['id']; ?>
              <label class="log-habit-row" for="habit_<?php echo $hid; ?>">
                <input id="habit_<?php echo $hid; ?>" type="checkbox" name="habit_<?php echo $hid; ?>" value="1" <?php echo (isset($todayLogMap[$hid]) && (int)$todayLogMap[$hid] === 1) ? 'checked' : ''; ?> />
                <span class="log-habit-emoji"><?php echo e($h['emoji'] !== '' ? $h['emoji'] : '✅'); ?></span>
                <span class="log-habit-name"><?php echo e($h['name']); ?></span>
              </label>
            <?php endforeach; ?>

            <div class="log-progress">
              <?php echo (int)$completedToday; ?> / <?php echo count($habits); ?> today - Target: <?php echo (int)$target; ?> (<?php echo ((int)date('N') >= 6) ? 'weekend' : 'weekday'; ?>)
              <?php if ($targetMet): ?>
                <span class="ok">✅ MET</span>
              <?php else: ?>
                <span class="no">❌ NOT YET</span>
              <?php endif; ?>
            </div>

            <div class="log-actions">
              <button type="submit" class="log-btn-save">Save Habits</button>
            </div>
          <?php endif; ?>
        </form>

        <div class="log-divider"></div>

        <div id="note"></div>
        <h3 style="margin-bottom:.5rem;">Today's Note</h3>
        <form method="POST">
          <?php echo csrfField(); ?>
          <input type="hidden" name="action" value="save_note" />
          <textarea class="log-note-textarea" name="note" maxlength="1000" placeholder="What did you commit today?"><?php echo e(isset($todayNote['note']) ? $todayNote['note'] : ''); ?></textarea>
          <?php if (!empty($todayNote['created_at'])): ?>
            <div class="log-note-meta">
              Logged at <?php echo e(date('M j, Y g:i A', strtotime($todayNote['created_at']))); ?>
              <?php if (!empty($todayNote['updated_at']) && $todayNote['updated_at'] !== $todayNote['created_at']): ?>
                · Edited at <?php echo e(date('M j, Y g:i A', strtotime($todayNote['updated_at']))); ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="log-actions">
            <button type="submit" class="log-btn-save">Save Note</button>
          </div>
        </form>
      </div>

      <a href="admin.php?tab=habits" class="log-back">&larr; Back to Admin Panel</a>
    <?php endif; ?>
  </div>
  <script>
  document.querySelectorAll('.log-habit-row').forEach(function(row) {
    var cb = row.querySelector('input[type="checkbox"]')
    if (cb && cb.checked) row.classList.add('checked')
    row.addEventListener('click', function(e) {
      e.preventDefault()
      if (cb) {
        cb.checked = !cb.checked
        row.classList.toggle('checked', cb.checked)
      }
    })
    if (cb) {
      cb.addEventListener('click', function(e) { e.stopPropagation() })
    }
  })

  var habitsForm = document.getElementById('habitsForm')
  if (habitsForm) {
    habitsForm.addEventListener('submit', function(e) {
      var checkedCount = habitsForm.querySelectorAll('input[type="checkbox"]:checked').length
      if (checkedCount === 0) {
        e.preventDefault()
        alert('Select at least one habit before saving.')
      }
    })
  }
  </script>
</body>
</html>
