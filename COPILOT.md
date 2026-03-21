# Habit Tracker — Copilot Build Guide
**Project:** Abhay Bombale Portfolio — Activity / Habit Tracker Feature  
**Stack:** PHP 5.6-compatible, MySQL, Vanilla JS, existing CSS variables  
**No new libraries. No Composer. No npm.**

---

## 0. Read This First — Ground Rules

- Every PHP function must have **zero typed hints** — no `string $x`, no `int $x`, no `: void`, no `: string`. InfinityFree runs PHP 5.6.
- Use `array()` not `[]` for array literals in PHP.
- Use `isset()` checks instead of `??` null coalescing.
- Never use `inset:` CSS shorthand — use `top/right/bottom/left` separately.
- All DB queries use prepared statements (`$stmt = $conn->prepare(...)`).
- CSS must include `-webkit-` prefixes for `transform`, `transition`, `animation`, `keyframes`.
- JS must be wrapped in `document.addEventListener('DOMContentLoaded', function() { ... })`.
- Asset paths in this project: CSS → `assets/css/style.css`, JS → `assets/js/main.js`, images → `assets/images/`.

---

## 1. Project File Map — What Exists Already

```
index.php              ← main portfolio page (DO NOT restructure, only insert new section)
admin.php              ← admin panel with tabs: Messages, Skills, Projects, Embeds, Settings
log.php                ← CREATE THIS (daily habit logging, mobile-friendly, session-protected)
config.php             ← DB connection + sendSecurityHeaders() already exists
assets/
  css/style.css        ← append new CSS here, never rewrite existing rules
  js/main.js           ← append new JS here, never rewrite existing functions
setup.sql              ← append new CREATE TABLE statements here
```

---

## 2. Database — 3 New Tables

**Append these to `setup.sql`. All use `IF NOT EXISTS` — safe to re-run.**

```sql
-- Habits definition table
CREATE TABLE IF NOT EXISTS `habits` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)  NOT NULL,
  `emoji`      VARCHAR(20)   NOT NULL DEFAULT '',
  `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
  `sort_order` SMALLINT      NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-habit per-day completion log
CREATE TABLE IF NOT EXISTS `habit_logs` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `habit_id`   INT UNSIGNED  NOT NULL,
  `log_date`   DATE          NOT NULL,
  `completed`  TINYINT(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_habit_day` (`habit_id`, `log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One note per day (the "what did you commit" popup result)
CREATE TABLE IF NOT EXISTS `daily_notes` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `log_date`   DATE          NOT NULL,
  `note`       TEXT          NOT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Streak state (key-value, same pattern as site_settings)
-- Keys: current_streak, best_streak, freeze_balance, last_active_date
CREATE TABLE IF NOT EXISTS `streak_state` (
  `setting_key`   VARCHAR(80)  NOT NULL,
  `setting_value` VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed streak state defaults (INSERT IGNORE = safe to re-run)
INSERT IGNORE INTO `streak_state` (`setting_key`, `setting_value`) VALUES
  ('current_streak',  '0'),
  ('best_streak',     '0'),
  ('freeze_balance',  '0'),
  ('last_active_date','');

-- Seed the 4 default habits
INSERT IGNORE INTO `habits` (`id`, `name`, `emoji`, `sort_order`) VALUES
  (1, 'LeetCode',   '💻', 1),
  (2, 'TryHackMe',  '🛡️', 2),
  (3, 'Coursera',   '🎓', 3),
  (4, 'Coding',     '⌨️', 4);
```

> **Note:** The `INSERT IGNORE` on habits uses explicit IDs (1–4). If you already ran setup.sql and habits exist, these lines are safely skipped.

---

## 3. Streak Rules Engine — PHP Logic

**This logic must be implemented identically in both `log.php` (on save) and `admin.php` (on display). Write it as a standalone function.**

```
DAILY TARGET:
  Monday–Friday  → need completed_count >= 1
  Saturday–Sunday → need completed_count >= 3

ON SAVE (after admin ticks habits and saves):
  1. Count how many habits completed today = $completed_count
  2. Determine $day_of_week = date('N', strtotime($log_date))  // 1=Mon … 7=Sun
  3. Determine $target = ($day_of_week >= 6) ? 3 : 1
  4. $target_met = ($completed_count >= $target)
  5. IF $target_met:
       - $current_streak += 1
       - IF $current_streak > $best_streak: $best_streak = $current_streak
       - IF $completed_count >= 3: $freeze_balance += 1   // freeze EARNED
       - $last_active_date = $log_date
  6. ELSE (target NOT met):
       - IF $freeze_balance > 0:
           $freeze_balance -= 1
           // streak unchanged, freeze consumed
           // store a flag to show "🧊 Freeze used" feedback to admin
       - ELSE:
           $current_streak = 0
           // show "💔 Streak reset" feedback to admin

SAVE streak_state:
  UPDATE streak_state SET setting_value = $current_streak WHERE setting_key = 'current_streak'
  (repeat for best_streak, freeze_balance, last_active_date)
  Use INSERT ... ON DUPLICATE KEY UPDATE pattern.
```

> **Important:** Streak recalculation runs ONLY when the admin saves a day's log via `log.php`. It does NOT run on every public page load. The `streak_state` table is the cached result shown publicly.

---

## 4. `log.php` — Daily Logging Page

**This is a NEW file. Session-protected. Mobile-first. Lightweight.**

### Authentication
Copy the session check from `admin.php` exactly:
```php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}
```

### DB Connection
Use `config.php` — it already exists:
```php
require_once __DIR__ . '/config.php';
```

### Page Logic (PHP at top, before any HTML)

```
1. Load all active habits from `habits` table (ORDER BY sort_order ASC)
2. Load today's existing logs from `habit_logs` WHERE log_date = TODAY
3. Load today's existing note from `daily_notes` WHERE log_date = TODAY
4. Load streak_state values
5. IF POST request AND action = 'save_habits':
     a. For each active habit: INSERT or UPDATE habit_logs (completed = 1 if in POST, else 0)
        Use: INSERT INTO habit_logs (habit_id, log_date, completed) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE completed = VALUES(completed)
     b. Count completed habits
     c. Run Streak Rules Engine (Section 3)
     d. Store $feedback message in session, redirect back (PRG pattern)
6. IF POST request AND action = 'save_note':
     a. Sanitize note text (strip_tags + trim, max 1000 chars)
     b. INSERT INTO daily_notes ... ON DUPLICATE KEY UPDATE note=VALUES(note), updated_at=NOW()
     c. Redirect back
```

### HTML Structure

```
[Page title: "📅 Log — {Day}, {Date}"]

[Streak bar: 🔥 {current_streak} days  |  🏆 Best: {best_streak}  |  🧊 Freezes: {freeze_balance}]

[Feedback message if exists in session — green/red/blue pill]

[Habit checkboxes — large tap targets, each habit on its own row]
  ☐ 💻 LeetCode
  ☐ 🛡️ TryHackMe  
  ☑ 🎓 Coursera    ← pre-checked if already logged today
  ☐ ⌨️  Coding

[Progress mini-bar: "2 / 4 today — Target: 1 (weekday) ✅ MET" or "❌ NOT YET"]

[Button: "Save Habits"]

--- separator ---

[Today's Note]
[textarea — pre-filled if note exists today]
[small text: "Logged at {created_at}" if exists, "Edited at {updated_at}" if different]
[Button: "Save Note"]

--- separator ---

[← Back to Admin Panel]
```

### Popup behavior
The "what did you commit" popup is **NOT a JS modal** — it is the note textarea below the habits form. After saving habits, the page reloads and scrolls to the note section automatically via `#note` anchor. This is simpler, works without JS, and is more reliable.

Add `header('Location: log.php?saved=1#note')` after saving habits.

---

## 5. `admin.php` — Add 📅 Habits Tab

### Tab link (add to existing tabs row)
```php
<a href="?tab=habits" class="tab-link <?php echo $tab==='habits'?'active':''; ?>">📅 Habits</a>
```

### Tab content — two sub-sections

**Sub-section A: Streak Status**
```
🔥 Current Streak: {current_streak} days
🏆 Best Streak: {best_streak} days
🧊 Freeze Balance: {freeze_balance}
📅 Last Logged: {last_active_date}

[Button: "📅 Log Today →" href="log.php"]
```

**Sub-section B: Manage Habits**
- Add form: `name` (text input) + `emoji` (text input, max 4 chars) + `sort_order` (number)
- List of all habits (active + inactive) as rows:
  - Shows: emoji + name + sort_order
  - Toggle active/inactive button
  - Delete button (only if no logs exist for this habit — check `habit_logs` table first)
- Use same card/panel styling as existing Skills tab in admin.php

### PHP actions needed
```
POST add_habit    → INSERT INTO habits (name, emoji, sort_order)
POST toggle_habit → UPDATE habits SET is_active = (1 - is_active) WHERE id = ?
POST delete_habit → DELETE FROM habits WHERE id = ? (only if no logs)
```
All redirect with `header('Location: '.$baseUrl.'?tab=habits'); exit;` after action.

---

## 6. `index.php` — Insert Activity Section

### WHERE TO INSERT — HR perspective decision

**Insert AFTER the Projects section, BEFORE the Write-ups/Social/Contact sections.**

Exact insertion point in the current `index.php`:
```
... Projects section ends (</section>) ...
↓ INSERT ACTIVITY SECTION HERE ↓
... Articles section (<?php if (!empty($_articles)):?>) ...
```

**Why this position:**
- HR reads: Hero → About → Skills → Projects → **[Activity — sees your discipline]** → Contact
- By the time HR reaches Activity, they already know what you can build (Projects). The Activity section then proves you build consistently, not just for portfolios.
- Placing it after Projects but before Contact means it's the last strong impression before they reach out.

### PHP data to fetch (add to the existing DB block at top of index.php)

Add these queries inside the existing `if (!$_conn->connect_error)` block, after existing queries:

```php
// Habit tracker data — only load if tables exist
$_habitData = array(
    'current_streak' => 0,
    'best_streak'    => 0,
    'freeze_balance' => 0,
    'heatmap'        => array(),  // 77 days, keyed by 'Y-m-d'
    'recent_notes'   => array(),  // last 5 days with notes
);

$tableCheck = $_conn->query("SHOW TABLES LIKE 'streak_state'");
$habitTablesExist = ($tableCheck && $tableCheck->num_rows > 0);
if ($tableCheck) { $tableCheck->close(); }

if ($habitTablesExist) {
    // Streak state
    $r = $_conn->query('SELECT setting_key, setting_value FROM streak_state');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            if ($row['setting_key'] === 'current_streak') $_habitData['current_streak'] = (int)$row['setting_value'];
            if ($row['setting_key'] === 'best_streak')    $_habitData['best_streak']    = (int)$row['setting_value'];
            if ($row['setting_key'] === 'freeze_balance') $_habitData['freeze_balance'] = (int)$row['setting_value'];
        }
    }

    // Heatmap: last 77 days — count completed habits per day
    $r = $_conn->query(
        'SELECT hl.log_date, COUNT(*) AS completed_count
         FROM habit_logs hl
         WHERE hl.completed = 1
           AND hl.log_date >= DATE_SUB(CURDATE(), INTERVAL 76 DAY)
         GROUP BY hl.log_date'
    );
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $_habitData['heatmap'][$row['log_date']] = (int)$row['completed_count'];
        }
    }

    // Recent notes: last 5 days that have a note
    $r = $_conn->query(
        'SELECT log_date, note, created_at, updated_at
         FROM daily_notes
         ORDER BY log_date DESC
         LIMIT 5'
    );
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $_habitData['recent_notes'][] = $row;
        }
    }
}
```

### HTML Section Structure

```html
<!-- Activity Section -->
<section id="activity" class="activity-section">
  <div class="container">
    <h2>Activity</h2>
    <p class="activity-subtitle">Daily learning log — updated by me, every day.</p>

    <!-- Stat cards row -->
    <div class="activity-stats">
      <div class="activity-stat-card">
        <span class="activity-stat-icon">🔥</span>
        <span class="activity-stat-value"><?= (int)$_habitData['current_streak'] ?></span>
        <span class="activity-stat-label">Day Streak</span>
      </div>
      <div class="activity-stat-card">
        <span class="activity-stat-icon">🏆</span>
        <span class="activity-stat-value"><?= (int)$_habitData['best_streak'] ?></span>
        <span class="activity-stat-label">Best Streak</span>
      </div>
      <div class="activity-stat-card">
        <span class="activity-stat-icon">🧊</span>
        <span class="activity-stat-value"><?= (int)$_habitData['freeze_balance'] ?></span>
        <span class="activity-stat-label">Freezes Saved</span>
      </div>
    </div>

    <!-- Heatmap -->
    <div class="heatmap-wrap">
      <div class="heatmap-grid" id="heatmapGrid">
        <?php
        // Generate 77 days from oldest to newest (left to right)
        for ($i = 76; $i >= 0; $i--) {
            $date    = date('Y-m-d', strtotime("-{$i} days"));
            $count   = isset($_habitData['heatmap'][$date]) ? $_habitData['heatmap'][$date] : 0;
            $level   = 0;
            if ($count === 1) $level = 1;
            elseif ($count === 2) $level = 2;
            elseif ($count === 3) $level = 3;
            elseif ($count >= 4) $level = 4;
            $label   = date('M j, Y', strtotime($date));
            $tooltip = $count > 0 ? "{$label}: {$count} habit" . ($count > 1 ? 's' : '') : $label . ': No activity';
            echo '<div class="heatmap-cell level-' . $level . '" data-date="' . $date . '" data-count="' . $count . '" title="' . htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') . '"></div>';
        }
        ?>
      </div>
      <div class="heatmap-legend">
        <span>Less</span>
        <div class="heatmap-cell level-0"></div>
        <div class="heatmap-cell level-1"></div>
        <div class="heatmap-cell level-2"></div>
        <div class="heatmap-cell level-3"></div>
        <div class="heatmap-cell level-4"></div>
        <span>More</span>
      </div>
    </div>

    <!-- Note panel — shown when a cell is clicked via JS -->
    <div class="activity-note-panel" id="activityNotePanel" style="display:none;">
      <div class="activity-note-date" id="activityNoteDate"></div>
      <div class="activity-note-text" id="activityNoteText"></div>
      <div class="activity-note-meta" id="activityNoteMeta"></div>
    </div>

  </div>
</section>
```

### Add nav link (insert into existing `<ul class="nav-links">` in index.php)
```html
<li><a href="#activity" class="nav-link">Activity</a></li>
```
Insert after the Projects nav link, before Write-ups/Posts.

---

## 7. CSS — Append to `assets/css/style.css`

**Append at the end of the file. Do not edit existing rules.**

```css
/* ═══════════════════════════════════════════════════════
   ACTIVITY / HABIT TRACKER SECTION
   ═══════════════════════════════════════════════════════ */

.activity-section {
  padding: 5rem 0;
  background-color: var(--bg-light);
}

.activity-subtitle {
  color: var(--text-light);
  font-size: 0.95rem;
  margin-top: -1rem;
  margin-bottom: 2rem;
}

/* ── Stat cards ──────────────────────────────────────── */
.activity-stats {
  display: -webkit-flex;
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  -webkit-flex-wrap: wrap;
  flex-wrap: wrap;
}

.activity-stat-card {
  display: -webkit-flex;
  display: flex;
  -webkit-flex-direction: column;
  flex-direction: column;
  -webkit-align-items: center;
  align-items: center;
  gap: 0.25rem;
  padding: 1.25rem 1.75rem;
  background: var(--bg-gray);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  min-width: 110px;
  -webkit-flex: 1;
  flex: 1;
  -webkit-transition: box-shadow 0.2s ease, -webkit-transform 0.2s ease;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.activity-stat-card:hover {
  -webkit-transform: translateY(-2px);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.1);
}

.activity-stat-icon  { font-size: 1.5rem; }
.activity-stat-value { font-size: 2rem; font-weight: 700; color: var(--text-dark); line-height: 1; }
.activity-stat-label { font-size: 0.75rem; color: var(--text-light); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; }

/* ── Heatmap ─────────────────────────────────────────── */
.heatmap-wrap {
  margin-bottom: 1.5rem;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(77, 12px);
  grid-template-rows: 12px;
  gap: 3px;
  padding: 0.5rem 0;
  min-width: max-content;
}

.heatmap-cell {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  cursor: pointer;
  -webkit-transition: opacity 0.15s ease, -webkit-transform 0.15s ease;
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.heatmap-cell:hover {
  opacity: 0.8;
  -webkit-transform: scale(1.3);
  transform: scale(1.3);
}

/* Color levels — 0 habits to 4+ habits */
.heatmap-cell.level-0 { background-color: #ebedf0; }
.heatmap-cell.level-1 { background-color: #93c5fd; }
.heatmap-cell.level-2 { background-color: #3b82f6; }
.heatmap-cell.level-3 { background-color: #1d4ed8; }
.heatmap-cell.level-4 { background-color: #1e3a8a; }

.heatmap-legend {
  display: -webkit-flex;
  display: flex;
  -webkit-align-items: center;
  align-items: center;
  gap: 4px;
  font-size: 0.72rem;
  color: var(--text-light);
  margin-top: 0.5rem;
}

.heatmap-legend .heatmap-cell {
  cursor: default;
  -webkit-transform: none !important;
  transform: none !important;
}

/* ── Note panel ──────────────────────────────────────── */
.activity-note-panel {
  margin-top: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--bg-gray);
  border: 1px solid var(--border-color);
  border-left: 4px solid var(--primary-color);
  border-radius: 8px;
  -webkit-animation: noteFadeIn 0.2s ease;
  animation: noteFadeIn 0.2s ease;
}

@-webkit-keyframes noteFadeIn {
  from { opacity: 0; -webkit-transform: translateY(6px); }
  to   { opacity: 1; -webkit-transform: translateY(0); }
}
@keyframes noteFadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}

.activity-note-date {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--primary-color);
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.activity-note-text {
  font-size: 0.95rem;
  color: var(--text-dark);
  line-height: 1.6;
}

.activity-note-text.empty {
  color: var(--text-light);
  font-style: italic;
}

.activity-note-meta {
  font-size: 0.72rem;
  color: var(--text-light);
  margin-top: 0.5rem;
}

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 768px) {
  .activity-stats {
    gap: 0.75rem;
  }
  .activity-stat-card {
    padding: 1rem 1.25rem;
    min-width: 90px;
  }
  .activity-stat-value {
    font-size: 1.6rem;
  }
  /* Mobile heatmap: show 35 cells, hide older ones */
  .heatmap-grid {
    grid-template-columns: repeat(35, 12px);
  }
  .heatmap-cell:nth-child(-n+42) {
    display: none;
  }
}
```

---

## 8. JS — Append to `assets/js/main.js`

**Append inside the existing `DOMContentLoaded` wrapper, before the closing `})`.**

```javascript
// ─── Heatmap cell click → show note panel ────────────────────────────────────
var heatmapGrid   = document.getElementById('heatmapGrid')
var notePanel     = document.getElementById('activityNotePanel')
var noteDate      = document.getElementById('activityNoteDate')
var noteText      = document.getElementById('activityNoteText')
var noteMeta      = document.getElementById('activityNoteMeta')
var activeCell    = null

// Notes are embedded as a JSON object from PHP — generated inline
// Format: window.habitNotes = { "2026-03-21": { note: "...", created_at: "...", updated_at: "..." }, ... }

if (heatmapGrid && notePanel) {
  heatmapGrid.addEventListener('click', function(e) {
    var cell = e.target
    if (!cell.classList.contains('heatmap-cell')) return
    var date  = cell.getAttribute('data-date')
    var count = cell.getAttribute('data-count')

    // Toggle off if clicking same cell
    if (activeCell === cell) {
      notePanel.style.display = 'none'
      activeCell = null
      return
    }

    activeCell = cell

    // Format date nicely
    var parts    = date.split('-')
    var dateObj  = new Date(parts[0], parts[1] - 1, parts[2])
    var options  = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    var dateStr  = dateObj.toLocaleDateString('en-US', options)

    noteDate.textContent = dateStr

    // Look up note from embedded data
    var notes = (typeof window.habitNotes !== 'undefined') ? window.habitNotes : {}
    if (notes[date] && notes[date].note) {
      noteText.textContent = notes[date].note
      noteText.classList.remove('empty')
      var loggedAt = notes[date].created_at ? 'Logged: ' + notes[date].created_at : ''
      var editedAt = (notes[date].updated_at && notes[date].updated_at !== notes[date].created_at)
                     ? ' · Edited: ' + notes[date].updated_at
                     : ''
      noteMeta.textContent = loggedAt + editedAt
    } else if (count > 0) {
      noteText.textContent = 'No note logged for this day.'
      noteText.classList.add('empty')
      noteMeta.textContent = count + ' habit' + (count > 1 ? 's' : '') + ' completed'
    } else {
      noteText.textContent = 'No activity on this day.'
      noteText.classList.add('empty')
      noteMeta.textContent = ''
    }

    notePanel.style.display = 'block'
    notePanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  })
}
```

### PHP inline data for JS notes (add inside the Activity section HTML, just before `</section>`)

```php
<script>
window.habitNotes = <?php
    $notesMap = array();
    // $_habitData['recent_notes'] only has 5 — for full heatmap clicks, fetch all 77 days
    // Re-query here or pass from the top — use the already-fetched recent_notes
    // For cells older than 5 days: note panel shows "No note logged" which is acceptable
    foreach ($_habitData['recent_notes'] as $n) {
        $notesMap[$n['log_date']] = array(
            'note'       => $n['note'],
            'created_at' => date('M j, Y g:i A', strtotime($n['created_at'])),
            'updated_at' => date('M j, Y g:i A', strtotime($n['updated_at'])),
        );
    }
    echo json_encode($notesMap);
?>;
</script>
```

> **Note on notes data:** The current plan fetches only the last 5 notes to keep the page payload small. If you want all 77 days of notes available on click, change the SQL LIMIT from 5 to 77 in the index.php DB query. Both are valid — your choice.

---

## 9. `log.php` — Complete Page Layout Reference

```
log.php
├── require config.php
├── session_start() + auth check
├── Load habits, today's logs, today's note, streak_state
├── Handle POST: action=save_habits → run streak engine → redirect
├── Handle POST: action=save_note  → save note → redirect
└── HTML:
    ├── Same <head> as index.php (same fonts, same style.css)
    ├── Minimal navbar: just "Abhay" logo + "← Admin" link
    ├── Streak bar
    ├── Feedback message (from session flash)
    ├── Form: checkboxes + "Save Habits" button
    ├── Progress indicator (X/Y today, target met or not)
    ├── Anchor: <div id="note">
    ├── Note textarea + "Save Note" button
    └── "← Back to Admin" link
```

**Important styling note for log.php:**
The habit checkboxes need large tap targets for mobile (minimum 44px height). Use:
```css
.log-habit-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  margin-bottom: 0.5rem;
  cursor: pointer;
  transition: background-color 0.15s ease;
}
.log-habit-row input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  flex-shrink: 0;
}
```

---

## 10. Build Order — Do This Exactly

```
Step 1 → setup.sql       Add 4 new tables + seed data. Run in phpMyAdmin first.
Step 2 → log.php         Create full logging page. Test locally: log habits, save note.
Step 3 → admin.php       Add Habits tab (streak status + manage habits).
Step 4 → index.php       Add data fetch at top + Activity section HTML in correct position.
Step 5 → style.css       Append activity CSS. Check heatmap renders correctly.
Step 6 → main.js         Append heatmap JS. Test cell click → note panel.
Step 7 → Test full flow  Log → see streak update → open index.php → see heatmap update.
```

---

## 11. Testing Checklist

```
□ Run setup.sql — all 4 tables created, streak_state seeded, 4 habits seeded
□ log.php opens without errors on localhost
□ log.php requires login — unauthenticated access redirects to admin.php
□ Ticking habits and saving updates habit_logs table (check in phpMyAdmin)
□ Streak increments correctly on weekday (1+ habit done)
□ Streak increments correctly on weekend (3+ habits done)
□ Freeze earned when 3+ habits done (freeze_balance increases by 1)
□ Freeze auto-used when target not met and balance > 0 (balance decreases, streak unchanged)
□ Streak resets to 0 when target not met and balance = 0
□ Note saves to daily_notes table with correct created_at timestamp
□ index.php shows Activity section with correct streak numbers
□ Heatmap renders 77 cells — empty days are level-0 (grey)
□ Days with habits show correct color level (1→light, 4→dark)
□ Clicking a cell opens note panel
□ Clicking same cell again closes note panel
□ Note panel shows "No note logged" for days without a note
□ Mobile: heatmap shows 35 cells, no horizontal overflow
□ admin.php Habits tab shows correct streak state
□ Adding a new habit from admin appears in log.php checkbox list
□ Deleting a habit fails gracefully if logs exist for it
```

---

## 12. Common Mistakes to Avoid

| Mistake | Correct approach |
|---|---|
| Using `??` null coalescing | Use `isset($x) ? $x : $default` |
| Typed function hints | No types anywhere: `function foo($a, $b)` not `function foo(string $a)` |
| `inset: Xpx` CSS | Use `top: Xpx; right: Xpx; bottom: Xpx; left: Xpx` |
| JS outside DOMContentLoaded | All JS inside existing `document.addEventListener('DOMContentLoaded', ...)` |
| Running streak engine on public page load | Only run on admin save in log.php |
| Calculating streak from scratch each time | Use `streak_state` table as cache |
| `[]` array syntax | Use `array()` for PHP 5.6 compat |
| `INSERT INTO ... ON DUPLICATE KEY` with habits seed | Use `INSERT IGNORE` for seeds |
| Rewriting existing CSS rules | Only APPEND to end of style.css |
| Hardcoded DB credentials | Use `config.php` via `require_once` |

---

## 13. File Structure After Build

```
index.php              ← modified (new data fetch + new section)
admin.php              ← modified (new Habits tab)
log.php                ← NEW
config.php             ← unchanged
setup.sql              ← modified (4 new tables appended)
assets/
  css/style.css        ← modified (activity CSS appended)
  js/main.js           ← modified (heatmap JS appended)
```

**No other files should be created or modified.**

---

*This document is the single source of truth for the Habit Tracker feature build. Follow Section 10 (Build Order) strictly. When in doubt, re-read the relevant section rather than guessing.*
