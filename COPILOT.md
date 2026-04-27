# Portfolio Copilot Build Guide
**Project:** Abhay Bombale — Cybersecurity Portfolio  
**Stack:** PHP 5.6-compatible · MySQL · Vanilla JS · CSS Variables  
**No new libraries. No Composer. No npm. No external JS frameworks.**

---

## 0. Ground Rules — Read Before Every Single File

- **PHP 5.6 compat — non-negotiable:**
  - Zero typed hints — never `string $x`, `int $x`, `: void`, `: string`, `: bool`
  - Use `array()` not `[]`
  - Use `isset($x) ? $x : $default` not `??`
  - Use `list($a, $b) = ...` only with safety checks — prefer `explode` with `count()` check
- **CSS compat:**
  - Never use `inset: Xpx` shorthand — use `top/right/bottom/left` separately
  - All `transform`, `transition`, `animation`, `@keyframes` need `-webkit-` prefixed versions
  - Never use CSS `aspect-ratio` — set explicit `width` + `height`
- **JS rules:**
  - All JS wrapped in existing `document.addEventListener('DOMContentLoaded', function() { ... })`
  - Append to existing `assets/js/main.js` — never rewrite
- **Asset paths in this project:**
  - CSS → `assets/css/style.css`
  - JS → `assets/js/main.js`
  - Images → `assets/images/`
  - Uploads → `uploads/`
- **DB:** All queries use prepared statements. No string concatenation for values.
- **Append only:** Never rewrite existing CSS rules or existing JS functions. Only append.

---

## PART A — WEB3 UI REDESIGN

---

## A1. Design Direction — "Dark Terminal / Cyber Identity"

This is a cybersecurity portfolio. The design must feel like it belongs to someone who understands systems, terminals, and networks — not a generic developer template.

**Aesthetic:** Dark-mode first. Deep near-black background. Neon cyan/electric blue accent. Monospace touches for code elements. Subtle animated noise grain. Glowing borders. This is not purple-gradient Web3 cliché — it is precision-dark, like a security dashboard.

**Reference image (activity section):** Dark charcoal cards (#1c1c1e), rounded corners (12–16px), orange accent for streaks, muted grid cells, clean label typography, very tight spacing. Match this exactly for the Activity section. The rest of the site uses cyan/blue instead of orange as primary accent.

**Personality:** One person who knows what they're doing. Not a startup. Not a freelancer marketplace. A security-minded builder.

---

## A2. New Color System — Replace CSS Variables

**Replace the entire `:root` block in `assets/css/style.css`:**

```css
:root {
  /* ── Core palette ──────────────────────────────── */
  --bg-base:          #0a0a0f;      /* near-black page background */
  --bg-surface:       #111118;      /* card / section background */
  --bg-elevated:      #1a1a24;      /* elevated card, input bg */
  --bg-overlay:       #22222e;      /* hover states, tooltips */

  /* ── Accent — electric cyan (cybersecurity feel) ── */
  --accent:           #00d4ff;      /* primary accent */
  --accent-dim:       #00a8cc;      /* hover state */
  --accent-glow:      rgba(0, 212, 255, 0.15);
  --accent-glow-lg:   rgba(0, 212, 255, 0.08);

  /* ── Secondary accent — kept for streak/activity ── */
  --orange:           #ff6b2b;      /* streak fire, activity accent */
  --orange-glow:      rgba(255, 107, 43, 0.15);

  /* ── Text ──────────────────────────────────────── */
  --text-primary:     #f0f0f5;      /* headings, important text */
  --text-secondary:   #a0a0b8;      /* body, descriptions */
  --text-muted:       #5a5a78;      /* labels, placeholders */

  /* ── Borders ───────────────────────────────────── */
  --border:           rgba(255, 255, 255, 0.07);
  --border-accent:    rgba(0, 212, 255, 0.25);
  --border-hover:     rgba(0, 212, 255, 0.5);

  /* ── Shadows ───────────────────────────────────── */
  --shadow-sm:        0 1px 3px rgba(0, 0, 0, 0.4);
  --shadow-md:        0 4px 16px rgba(0, 0, 0, 0.5);
  --shadow-lg:        0 12px 40px rgba(0, 0, 0, 0.6);
  --shadow-accent:    0 0 30px rgba(0, 212, 255, 0.12);
  --shadow-glow:      0 0 60px rgba(0, 212, 255, 0.08);

  /* ── Typography ────────────────────────────────── */
  --font-display:     'Space Grotesk', 'Poppins', sans-serif;
  --font-body:        'DM Sans', 'Inter', system-ui, sans-serif;
  --font-mono:        'JetBrains Mono', 'Fira Code', monospace;

  /* ── Transitions ───────────────────────────────── */
  --transition:       all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow:  all 0.5s cubic-bezier(0.4, 0, 0.2, 1);

  /* ── Radius ────────────────────────────────────── */
  --radius-sm:        8px;
  --radius-md:        12px;
  --radius-lg:        16px;
  --radius-xl:        24px;
}
```

**Why these choices:**
- `#0a0a0f` — slightly purple-black, feels like a monitor in a dark room. Not pure black (too harsh).
- `#00d4ff` cyan — instantly reads "cybersecurity / network traffic / scanner". Not purple (overused in web3). Not green (too hacker-movie cliché).
- Orange kept ONLY for the Activity/Habits section to match the reference image exactly.
- `Space Grotesk` + `DM Sans` — geometric display paired with humanist body. Distinctive without being unreadable.

**Add to `<head>` in index.php (replace existing Google Fonts link):**
```html
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
```

---

## A3. Global Base Styles — Replace

```css
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html {
  scroll-behavior: smooth;
}

body {
  font-family: var(--font-body);
  font-size: 16px;
  line-height: 1.7;
  color: var(--text-secondary);
  background-color: var(--bg-base);
  font-synthesis: none;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  /* Animated noise grain overlay */
  position: relative;
}

/* Noise grain texture — pure CSS, no image needed */
body::before {
  content: '';
  position: fixed;
  top: 0; right: 0; bottom: 0; left: 0;
  pointer-events: none;
  z-index: 9999;
  opacity: 0.025;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
  background-size: 128px 128px;
}

/* Ambient background glow — subtle, not distracting */
body::after {
  content: '';
  position: fixed;
  top: -20%;
  left: 50%;
  -webkit-transform: translateX(-50%);
  transform: translateX(-50%);
  width: 800px;
  height: 600px;
  background: radial-gradient(ellipse, rgba(0, 212, 255, 0.04) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
}

h1, h2, h3, h4 {
  font-family: var(--font-display);
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}

h1 { font-size: 3.5rem; letter-spacing: -0.02em; }
h2 { font-size: 2.25rem; letter-spacing: -0.01em; margin-bottom: 2.5rem; }
h3 { font-size: 1.25rem; }

p  { color: var(--text-secondary); }
a  { color: var(--accent); text-decoration: none; -webkit-transition: var(--transition); transition: var(--transition); }
a:hover { color: var(--accent-dim); }

/* Section label — small uppercase text above h2 */
.section-label {
  font-family: var(--font-mono);
  font-size: 0.7rem;
  font-weight: 500;
  color: var(--accent);
  text-transform: uppercase;
  letter-spacing: 0.15em;
  margin-bottom: 0.5rem;
  display: block;
}
```

---

## A4. Component Styles — Section by Section

### Navbar
```css
.navbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  background-color: rgba(10, 10, 15, 0.7);
  border-bottom: 1px solid var(--border);
  z-index: 1000;
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-transition: var(--transition);
  transition: var(--transition);
}

.navbar.scrolled {
  background-color: rgba(10, 10, 15, 0.92);
  border-bottom-color: var(--border-accent);
  box-shadow: 0 1px 0 rgba(0, 212, 255, 0.1), var(--shadow-md);
}

.nav-content .logo {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.4rem;
  color: var(--text-primary);
  letter-spacing: -0.02em;
  -webkit-transition: var(--transition);
  transition: var(--transition);
}

.nav-content .logo:hover { color: var(--accent); }

.nav-link {
  font-family: var(--font-body);
  font-weight: 500;
  font-size: 0.875rem;
  color: var(--text-secondary);
  -webkit-transition: var(--transition);
  transition: var(--transition);
  position: relative;
  padding-bottom: 3px;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  right: 50%;
  height: 1px;
  background: var(--accent);
  -webkit-transition: left 0.25s ease, right 0.25s ease;
  transition: left 0.25s ease, right 0.25s ease;
  box-shadow: 0 0 8px var(--accent);
}

.nav-link:hover,
.nav-link.active { color: var(--accent); }

.nav-link:hover::after,
.nav-link.active::after { left: 0; right: 0; }

.menu-toggle span { background-color: var(--text-secondary); }
```

### Buttons
```css
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.5rem;
  border-radius: var(--radius-sm);
  font-family: var(--font-body);
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  border: none;
  -webkit-transition: var(--transition);
  transition: var(--transition);
  text-decoration: none;
  white-space: nowrap;
  position: relative;
  overflow: hidden;
}

.btn-primary {
  background: var(--accent);
  color: #0a0a0f;
  box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
}

.btn-primary:hover {
  background: var(--accent-dim);
  color: #0a0a0f;
  box-shadow: 0 0 35px rgba(0, 212, 255, 0.5);
  -webkit-transform: translateY(-1px);
  transform: translateY(-1px);
}

.btn-secondary {
  background: transparent;
  color: var(--accent);
  border: 1px solid var(--border-accent);
}

.btn-secondary:hover {
  background: var(--accent-glow);
  border-color: var(--border-hover);
  color: var(--accent);
  -webkit-transform: translateY(-1px);
  transform: translateY(-1px);
}
```

### Hero Section
```css
.hero {
  margin-top: 70px;
  min-height: calc(100vh - 70px);
  display: -webkit-flex;
  display: flex;
  -webkit-align-items: center;
  align-items: center;
  -webkit-justify-content: center;
  justify-content: center;
  background: var(--bg-base);
  padding: 2rem 0;
  position: relative;
  overflow: hidden;
}

/* Animated grid background — cyber terminal feel */
.hero::before {
  content: '';
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  background-image:
    linear-gradient(rgba(0, 212, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 212, 255, 0.03) 1px, transparent 1px);
  background-size: 60px 60px;
  pointer-events: none;
}

/* Radial fade so grid fades out at edges */
.hero::after {
  content: '';
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  background: radial-gradient(ellipse 80% 80% at 50% 50%, transparent 40%, var(--bg-base) 100%);
  pointer-events: none;
}

.hero-title {
  font-family: var(--font-display);
  color: var(--text-primary);
  font-size: 3.5rem;
  letter-spacing: -0.03em;
  margin-bottom: 0.5rem;
}

/* "Hello I'm" line gets monospace treatment */
.hero-title:first-child {
  font-family: var(--font-mono);
  font-size: 1rem;
  font-weight: 400;
  color: var(--accent);
  letter-spacing: 0.1em;
  margin-bottom: 0.25rem;
}

.hero-subtitle {
  font-family: var(--font-mono);
  font-size: 1.1rem;
  color: var(--accent);
  font-weight: 400;
  margin-bottom: 1.5rem;
  letter-spacing: 0.02em;
}

/* Cursor blink for typing animation */
.hero-subtitle::after {
  content: '|';
  -webkit-animation: blink 1s step-end infinite;
  animation: blink 1s step-end infinite;
  color: var(--accent);
  margin-left: 2px;
}

@-webkit-keyframes blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0; }
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0; }
}

.hero-description {
  font-size: 1.05rem;
  color: var(--text-secondary);
  margin-bottom: 2rem;
  max-width: 440px;
}
```

### Section Backgrounds — Alternating
```css
/* Every section gets bg-base, alternating ones get bg-surface for rhythm */
.about    { padding: 5rem 0; background-color: var(--bg-surface); }
.skills   { padding: 5rem 0; background-color: var(--bg-base); }
.projects { padding: 5rem 0; background-color: var(--bg-surface); }
.contact  { padding: 5rem 0; background-color: var(--bg-base); }
```

### Cards — Skills and Projects
```css
.skill-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 1.75rem;
  -webkit-transition: var(--transition);
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

/* Accent glow line at top on hover */
.skill-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--accent), transparent);
  opacity: 0;
  -webkit-transition: opacity 0.3s ease;
  transition: opacity 0.3s ease;
}

.skill-card:hover {
  border-color: var(--border-accent);
  -webkit-transform: translateY(-4px);
  transform: translateY(-4px);
  box-shadow: var(--shadow-accent);
}

.skill-card:hover::before { opacity: 1; }

.skill-card h3 { color: var(--text-primary); margin-bottom: 0.5rem; }
.skill-card p  { color: var(--text-secondary); font-size: 0.9rem; }

.skill-icon {
  font-size: 2rem;
  margin-bottom: 1rem;
  display: block;
}

.project-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  -webkit-transition: var(--transition);
  transition: var(--transition);
}

.project-card:hover {
  border-color: var(--border-accent);
  -webkit-transform: translateY(-4px);
  transform: translateY(-4px);
  box-shadow: var(--shadow-accent);
}

.project-content { padding: 1.5rem; }
.project-content h3 { color: var(--text-primary); margin-bottom: 0.5rem; }
.project-content p  { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.25rem; }
```

### Contact Form
```css
.contact-form {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 2rem;
}

.form-group label {
  color: var(--text-secondary);
  font-size: 0.85rem;
  font-weight: 500;
  font-family: var(--font-mono);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.5rem;
  display: block;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: var(--font-body);
  font-size: 0.95rem;
  color: var(--text-primary);
  -webkit-transition: var(--transition);
  transition: var(--transition);
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--border-accent);
  box-shadow: 0 0 0 3px var(--accent-glow);
  background: var(--bg-elevated);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: var(--text-muted);
}
```

### Footer
```css
.footer {
  padding: 2rem 0;
  background-color: var(--bg-surface);
  border-top: 1px solid var(--border);
  color: var(--text-muted);
  text-align: center;
  font-family: var(--font-mono);
  font-size: 0.8rem;
}

.footer p { color: var(--text-muted); }
```

### Section headings — add glowing accent dot
Add this HTML pattern before each `<h2>` across all sections:
```html
<span class="section-label">// about</span>
<h2>About Me</h2>
```
Labels: `// about`, `// skills`, `// projects`, `// activity`, `// contact`

---

## A5. Activity Section UI — Match Reference Image

The reference image shows:
- Near-black background (`#1c1c1e`)
- Rounded dark cards with no visible border in default state
- Orange (`#ff6b2b`) as streak accent
- Small grid cells for heatmap — dark grey empty, orange-scale filled
- Clean sans-serif labels in light grey
- Compact, tight spacing

**Activity section specific CSS:**
```css
.activity-section {
  padding: 5rem 0;
  background-color: var(--bg-base);
}

/* Stat cards — single row, all three side by side, never wrap */
.activity-stats {
  display: -webkit-flex;
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  -webkit-flex-wrap: nowrap;
  flex-wrap: nowrap;
}

.activity-stat-card {
  background: #1c1c24;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  -webkit-flex: 1;
  flex: 1;
  min-width: 0;           /* allows flex children to shrink below content size */
  display: -webkit-flex;
  display: flex;
  -webkit-flex-direction: row;
  flex-direction: row;
  -webkit-align-items: center;
  align-items: center;
  gap: 0.5rem;
  -webkit-transition: var(--transition);
  transition: var(--transition);
}

.activity-stat-card:hover {
  border-color: rgba(255, 107, 43, 0.3);
  box-shadow: 0 0 20px rgba(255, 107, 43, 0.1);
}

.activity-stat-icon {
  font-size: 1.1rem;
  flex-shrink: 0;
  line-height: 1;
}

.activity-stat-value {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1;
  flex-shrink: 0;
}

.activity-stat-label {
  font-family: var(--font-mono);
  font-size: 0.65rem;
  color: var(--text-muted);
  letter-spacing: 0.05em;
  text-transform: uppercase;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Heatmap cells — match reference image */
.heatmap-cell.level-0 { background-color: #2a2a35; }
.heatmap-cell.level-1 { background-color: #7c3912; }
.heatmap-cell.level-2 { background-color: #b85420; }
.heatmap-cell.level-3 { background-color: #e8652a; }
.heatmap-cell.level-4 { background-color: #ff6b2b; box-shadow: 0 0 6px rgba(255,107,43,0.5); }

/* Heatmap container card */
.heatmap-wrap {
  background: #1c1c24;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  margin-bottom: 1rem;
}

/* Note panel — dark, left-orange border */
.activity-note-panel {
  background: #1c1c24;
  border: 1px solid rgba(255,255,255,0.06);
  border-left: 3px solid var(--orange);
  border-radius: var(--radius-md);
  padding: 1.25rem 1.5rem;
}

.activity-note-date { color: var(--orange); font-family: var(--font-mono); font-size: 0.75rem; }
.activity-note-text { color: var(--text-primary); font-size: 0.95rem; line-height: 1.65; }
.activity-note-meta { color: var(--text-muted); font-size: 0.7rem; font-family: var(--font-mono); }
```

---

## A6. `log.php` Visual Style

`log.php` is admin-only and mobile-first. Style it to match the reference image exactly:

- Background: `#0a0a0f`
- Cards/forms: `#1c1c24` with `border: 1px solid rgba(255,255,255,0.06)`
- Habit checkboxes: large rows (min-height 52px), rounded, with orange checkmark
- Progress bar: dark track, orange fill
- Save button: orange background (`var(--orange)`), not cyan — keeps habits section visually consistent

```css
/* log.php specific — add in a <style> block inside log.php, NOT in style.css */
body {
  background: #0a0a0f;
  font-family: 'DM Sans', system-ui, sans-serif;
  color: #a0a0b8;
  padding: 0;
  margin: 0;
}

.log-wrap {
  max-width: 480px;
  margin: 0 auto;
  padding: 1.5rem 1rem 4rem;
}

.log-card {
  background: #1c1c24;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.log-streak-bar {
  display: flex;
  gap: 1.5rem;
  justify-content: space-around;
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
  color: #f0f0f5;
  line-height: 1;
}

.log-streak-label {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #5a5a78;
}

/* Progress bar */
.log-progress-track {
  height: 4px;
  background: #2a2a35;
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

/* Habit rows */
.log-habit-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #22222e;
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 10px;
  margin-bottom: 0.5rem;
  cursor: pointer;
  -webkit-transition: border-color 0.2s ease, background 0.2s ease;
  transition: border-color 0.2s ease, background 0.2s ease;
}

.log-habit-row.checked {
  border-color: rgba(255, 107, 43, 0.4);
  background: rgba(255, 107, 43, 0.06);
}

.log-habit-row input[type="checkbox"] {
  width: 20px;
  height: 20px;
  accent-color: #ff6b2b;
  cursor: pointer;
  flex-shrink: 0;
}

.log-habit-emoji { font-size: 1.4rem; }
.log-habit-name  { font-size: 1rem; color: #f0f0f5; font-weight: 500; }

/* Save button */
.log-btn-save {
  width: 100%;
  padding: 0.9rem;
  background: #ff6b2b;
  color: #0a0a0f;
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
  box-shadow: 0 0 24px rgba(255, 107, 43, 0.4);
}

/* Note textarea */
.log-note-textarea {
  width: 100%;
  background: #22222e;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 10px;
  padding: 0.9rem;
  color: #f0f0f5;
  font-size: 0.95rem;
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
  -webkit-transition: border-color 0.2s ease;
  transition: border-color 0.2s ease;
}

.log-note-textarea:focus {
  outline: none;
  border-color: rgba(255, 107, 43, 0.4);
}

/* Feedback pill */
.log-feedback {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 1rem;
  text-align: center;
}

.log-feedback.success { background: rgba(34,197,94,0.12); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
.log-feedback.freeze  { background: rgba(0,212,255,0.08); color: #00d4ff;  border: 1px solid rgba(0,212,255,0.15); }
.log-feedback.reset   { background: rgba(239,68,68,0.1);  color: #ef4444;  border: 1px solid rgba(239,68,68,0.15); }
.log-feedback.earned  { background: rgba(255,107,43,0.1); color: #ff6b2b;  border: 1px solid rgba(255,107,43,0.2); }
```

---

## A7. Responsive Dark Mode Mobile

```css
@media (max-width: 768px) {
  h1 { font-size: 2.25rem; }
  h2 { font-size: 1.75rem; }

  /* Mobile nav menu */
  .nav-links {
    background-color: rgba(10, 10, 15, 0.98);
    -webkit-backdrop-filter: blur(20px);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border-accent);
  }

  .nav-link.active {
    color: var(--accent);
    background-color: var(--accent-glow);
    border-left: 2px solid var(--accent);
    padding-left: calc(1.5rem - 2px);
  }

  /* Activity section mobile */
  .activity-stats { gap: 0.5rem; }
  .activity-stat-card { padding: 0.6rem 0.75rem; }
  .activity-stat-label { display: none; } /* hide label on small screens, icon+number is enough */
}
```

---

## PART B — HABIT TRACKER FEATURE

*(All content from original COPILOT.md — unchanged. Appended below for single-file reference.)*

---

## B1. Ground Rules — Same as Part A Section 0

Already covered above. Apply to ALL new files.

---

## B2. Database — 3 New Tables

**Append to `setup.sql`:**

```sql
CREATE TABLE IF NOT EXISTS `habits` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)  NOT NULL,
  `emoji`      VARCHAR(20)   NOT NULL DEFAULT '',
  `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
  `sort_order` SMALLINT      NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `habit_logs` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `habit_id`  INT UNSIGNED NOT NULL,
  `log_date`  DATE         NOT NULL,
  `completed` TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_habit_day` (`habit_id`, `log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `daily_notes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_date`   DATE         NOT NULL,
  `note`       TEXT         NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `streak_state` (
  `setting_key`   VARCHAR(80)  NOT NULL,
  `setting_value` VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `streak_state` (`setting_key`, `setting_value`) VALUES
  ('current_streak',   '0'),
  ('best_streak',      '0'),
  ('freeze_balance',   '0'),
  ('last_active_date', '');

INSERT IGNORE INTO `habits` (`id`, `name`, `emoji`, `sort_order`) VALUES
  (1, 'LeetCode',  '💻', 1),
  (2, 'TryHackMe', '🛡️', 2),
  (3, 'Coursera',  '🎓', 3),
  (4, 'Coding',    '⌨️', 4);
```

---

## B3. Streak Rules Engine

```
DAILY TARGET:
  Mon–Fri  → completed_count >= 1
  Sat–Sun  → completed_count >= 3

ON SAVE:
  $dow    = date('N', strtotime($log_date))   // 1=Mon, 7=Sun
  $target = ($dow >= 6) ? 3 : 1
  $met    = ($completed_count >= $target)

  IF $met:
    $current_streak += 1
    IF $current_streak > $best_streak: $best_streak = $current_streak
    IF $completed_count >= 3: $freeze_balance += 1
    $last_active_date = $log_date
  ELSE:
    IF $freeze_balance > 0:
      $freeze_balance -= 1
      // streak unchanged — set flash: "freeze"
    ELSE:
      $current_streak = 0
      // set flash: "reset"

  Save all four streak_state rows using:
  INSERT INTO streak_state (setting_key, setting_value) VALUES (?,?)
  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)

IMPORTANT: Run engine ONLY on admin save in log.php. Never on public page load.
```

---

## B4. `log.php` — Full Structure

```
require config.php
session_start()
IF empty($_SESSION['admin_logged_in']): redirect to admin.php

Load:
  - Active habits ORDER BY sort_order ASC
  - Today's habit_logs WHERE log_date = date('Y-m-d')
  - Today's daily_notes WHERE log_date = date('Y-m-d')
  - All streak_state rows

POST action=save_habits:
  For each active habit:
    INSERT INTO habit_logs (habit_id, log_date, completed)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE completed = VALUES(completed)
  Count completed habits
  Run streak engine
  Set $_SESSION['log_flash'] = array('type' => $type, 'msg' => $msg)
  header('Location: log.php?saved=1#note'); exit

POST action=save_note:
  $note = substr(strip_tags(trim($_POST['note'])), 0, 1000)
  INSERT INTO daily_notes (log_date, note) VALUES (?, ?)
  ON DUPLICATE KEY UPDATE note = VALUES(note), updated_at = NOW()
  header('Location: log.php?noted=1'); exit

HTML:
  <head> — include assets/css/style.css + inline log.php CSS (Section A6)
  <body>
    .log-wrap
      ← Back to Admin (link to admin.php)
      "📅 {Day}, {Date}" heading
      Feedback pill (from session flash — clear after showing)
      .log-card → streak bar + progress bar
      .log-card → habit checkboxes form (action=save_habits)
      <div id="note">
      .log-card → note textarea form (action=save_note)
        Show created_at if note exists, updated_at if different
```

**JS for log.php (inline `<script>` at bottom of log.php):**
```javascript
// Highlight checked rows dynamically
document.querySelectorAll('.log-habit-row').forEach(function(row) {
  var cb = row.querySelector('input[type="checkbox"]')
  if (cb && cb.checked) row.classList.add('checked')
  row.addEventListener('click', function() {
    if (cb) {
      cb.checked = !cb.checked
      row.classList.toggle('checked', cb.checked)
    }
  })
  if (cb) {
    cb.addEventListener('click', function(e) { e.stopPropagation() })
  }
})
```

---

## B5. `admin.php` — Add Habits Tab

**Tab link** (add to existing tabs row):
```php
<a href="?tab=habits" class="tab-link <?php echo $tab==='habits'?'active':''; ?>">📅 Habits</a>
```

**PHP actions** (add before existing SKILLS ACTIONS block):
```
POST add_habit:
  INSERT INTO habits (name, emoji, sort_order) VALUES (?, ?, ?)
  redirect ?tab=habits

POST toggle_habit:
  UPDATE habits SET is_active = (1 - is_active) WHERE id = ?
  redirect ?tab=habits

POST delete_habit:
  Check: SELECT COUNT(*) FROM habit_logs WHERE habit_id = ?
  IF count > 0: redirect with error "Cannot delete — logs exist"
  ELSE: DELETE FROM habits WHERE id = ?
  redirect ?tab=habits
```

**Tab HTML content:**
```
Streak Status card:
  🔥 Current: {current_streak}  🏆 Best: {best_streak}  🧊 Freezes: {freeze_balance}
  Last logged: {last_active_date}
  [📅 Log Today →] button → href="log.php"

Manage Habits card:
  Add form: name input + emoji input + sort_order number input + [Add] button
  List of habits:
    Each row: emoji + name + sort_order
    [Active/Inactive] toggle button
    [Delete] button (disabled if logs exist)
```

---

## B6. `index.php` — Activity Section

**Position: AFTER Projects section, BEFORE Articles/Social/Contact**

Find this comment in index.php:
```php
  <?php if (!empty($_articles)): ?>
```
Insert the entire Activity section HTML immediately above this line.

**PHP data fetch** — add inside existing `if (!$_conn->connect_error)` block after existing queries:

```php
$_habitData = array(
    'current_streak' => 0,
    'best_streak'    => 0,
    'freeze_balance' => 0,
    'heatmap'        => array(),
    'recent_notes'   => array(),
);

$tableCheck = $_conn->query("SHOW TABLES LIKE 'streak_state'");
$habitTablesExist = ($tableCheck && $tableCheck->num_rows > 0);
if ($tableCheck) { $tableCheck->close(); }

if ($habitTablesExist) {
    $r = $_conn->query('SELECT setting_key, setting_value FROM streak_state');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            if ($row['setting_key'] === 'current_streak') $_habitData['current_streak'] = (int)$row['setting_value'];
            if ($row['setting_key'] === 'best_streak')    $_habitData['best_streak']    = (int)$row['setting_value'];
            if ($row['setting_key'] === 'freeze_balance') $_habitData['freeze_balance'] = (int)$row['setting_value'];
        }
    }

    $r = $_conn->query(
        'SELECT hl.log_date, COUNT(*) AS completed_count
         FROM habit_logs hl
         WHERE hl.completed = 1
           AND hl.log_date >= DATE_SUB(CURDATE(), INTERVAL 76 DAY)
         GROUP BY hl.log_date'
    );
    if ($r) { while ($row = $r->fetch_assoc()) { $_habitData['heatmap'][$row['log_date']] = (int)$row['completed_count']; } }

    $r = $_conn->query('SELECT log_date, note, created_at, updated_at FROM daily_notes ORDER BY log_date DESC LIMIT 77');
    if ($r) { while ($row = $r->fetch_assoc()) { $_habitData['recent_notes'][] = $row; } }
}
```

**Section HTML:**
```html
<section id="activity" class="activity-section">
  <div class="container">
    <span class="section-label">// activity</span>
    <h2>Daily Log</h2>
    <p class="activity-subtitle">Logged daily. Click any cell to read what I worked on.</p>

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
        <span class="activity-stat-label">Freezes</span>
      </div>
    </div>

    <div class="heatmap-wrap">
      <div class="heatmap-grid" id="heatmapGrid">
        <?php
        for ($i = 76; $i >= 0; $i--) {
            $date  = date('Y-m-d', strtotime("-{$i} days"));
            $count = isset($_habitData['heatmap'][$date]) ? $_habitData['heatmap'][$date] : 0;
            $level = 0;
            if ($count === 1) $level = 1;
            elseif ($count === 2) $level = 2;
            elseif ($count === 3) $level = 3;
            elseif ($count >= 4) $level = 4;
            $tip = $count > 0 ? date('M j', strtotime($date)).': '.$count.' habit'.($count>1?'s':'') : date('M j', strtotime($date)).': no activity';
            echo '<div class="heatmap-cell level-'.$level.'" data-date="'.$date.'" data-count="'.$count.'" title="'.htmlspecialchars($tip, ENT_QUOTES, 'UTF-8').'"></div>';
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

    <div class="activity-note-panel" id="activityNotePanel" style="display:none;">
      <div class="activity-note-date" id="activityNoteDate"></div>
      <div class="activity-note-text" id="activityNoteText"></div>
      <div class="activity-note-meta" id="activityNoteMeta"></div>
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
```

**Nav link — add after Projects:**
```html
<li><a href="#activity" class="nav-link">Activity</a></li>
```

---

## B7. JS — Append to `assets/js/main.js`

Append inside the existing `DOMContentLoaded` wrapper:

```javascript
// ─── Heatmap cell click → note panel ─────────────────────────────────────────
var heatmapGrid = document.getElementById('heatmapGrid')
var notePanel   = document.getElementById('activityNotePanel')
var noteDate    = document.getElementById('activityNoteDate')
var noteText    = document.getElementById('activityNoteText')
var noteMeta    = document.getElementById('activityNoteMeta')
var activeCell  = null

if (heatmapGrid && notePanel) {
  heatmapGrid.addEventListener('click', function(e) {
    var cell = e.target
    if (!cell.classList.contains('heatmap-cell')) return
    var date  = cell.getAttribute('data-date')
    var count = parseInt(cell.getAttribute('data-count'), 10) || 0

    if (activeCell === cell) {
      notePanel.style.display = 'none'
      activeCell = null
      return
    }
    activeCell = cell

    var parts   = date.split('-')
    var d       = new Date(parseInt(parts[0],10), parseInt(parts[1],10)-1, parseInt(parts[2],10))
    var dateStr = d.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
    noteDate.textContent = dateStr

    var notes = (typeof window.habitNotes !== 'undefined') ? window.habitNotes : {}
    if (notes[date] && notes[date].note) {
      noteText.textContent = notes[date].note
      noteText.classList.remove('empty')
      noteMeta.textContent = 'Logged: ' + notes[date].created_at +
        (notes[date].updated_at !== notes[date].created_at ? ' · Edited: ' + notes[date].updated_at : '')
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

---

## B8. Build Order

```
Step 1 → setup.sql           Run new tables in phpMyAdmin
Step 2 → assets/css/style.css  Replace :root vars (Part A2), replace base styles (A3),
                               append all component styles (A4), append activity CSS (A5)
Step 3 → log.php             Create with dark UI (A6 CSS inline)
Step 4 → admin.php           Add Habits tab (B5)
Step 5 → index.php           Add data fetch + Activity section (B6) + update Google Fonts link (A2)
Step 6 → assets/js/main.js   Append heatmap JS (B7)
Step 7 → Test full flow
```

---

## B9. Testing Checklist

```
□ setup.sql runs cleanly — 4 tables + streak seed + habits seed
□ index.php loads without PHP errors
□ Dark theme renders — background is near-black, not white
□ Navbar has blur/glass effect, accent underline on nav links
□ Hero has grid background, cyan subtitle, typing cursor
□ Cards have dark background, glow on hover
□ log.php requires login
□ Habit checkboxes tick and highlight orange
□ Save habits → habit_logs updated in DB
□ Streak increments correctly (weekday: 1+, weekend: 3+)
□ Freeze earns on 3+ habits done
□ Freeze auto-used when target missed and balance > 0
□ Streak resets when target missed and balance = 0
□ Note saves with correct created_at
□ Activity section visible on index.php after Projects
□ Heatmap 77 cells render, empty = dark grey, filled = orange scale
□ Clicking cell opens note panel
□ Clicking same cell closes it
□ Mobile: heatmap 35 cells, no overflow
□ admin.php Habits tab shows streak state + manage habits
□ section-label "// about" etc. visible above each h2
```

---

## B10. Common Mistakes

| Wrong | Correct |
|---|---|
| `??` operator | `isset($x) ? $x : $default` |
| Typed function hints | `function foo($a)` only |
| `inset: Xpx` | `top/right/bottom/left` separately |
| JS before DOMContentLoaded | Append inside existing wrapper |
| Running streak on page load | Only on admin save in log.php |
| `[]` array syntax | `array()` |
| New CSS file | Append to `assets/css/style.css` only |
| New JS file | Append to `assets/js/main.js` only |
| Hardcoded DB credentials | `require_once __DIR__ . '/config.php'` |
| Rewriting existing CSS rules | Append only — never overwrite |

---

---

## PART C — NEW CHANGES (Apply after Parts A and B)

---

## C1. Remove Theme Toggle — Permanently Dark

**Why:** The dark CSS from Part A is the only theme. A toggle that switches to a non-existent light theme is broken and confusing. Removing it also simplifies the navbar on mobile.

### `index.php` — 3 removals

**1. Remove the meta tag — find and replace:**
```html
<!-- REMOVE this line: -->
<meta name="color-scheme" content="light dark" />

<!-- REPLACE with: -->
<meta name="color-scheme" content="dark" />
```

**2. Remove the themeToggle button — delete this entire block:**
```html
<li>
  <button id="themeToggle" class="theme-toggle" aria-label="Toggle dark mode" title="Toggle dark mode">
    <span class="theme-icon-light">☀️</span>
    <span class="theme-icon-dark">🌙</span>
  </button>
</li>
```

### `assets/js/main.js` — remove theme toggle JS

Find and delete any block that references `themeToggle`, `data-theme`, `localStorage.getItem('theme')`, or `classList.toggle('dark')`. The entire block, not just individual lines.

### `assets/css/style.css` — remove theme CSS

Find and delete:
- `.theme-toggle { ... }` block
- `.theme-icon-light { ... }` and `.theme-icon-dark { ... }` blocks  
- Any `[data-theme="light"]` or `[data-theme="dark"]` blocks
- Any `@media (prefers-color-scheme: light)` blocks
- Any `:root.light` or `body.light` overrides

**Do NOT delete any other CSS. Only theme-switching related rules.**

---

## C2. About + Activity Two-Column Layout

**Why:** Stacking every section vertically wastes horizontal space and forces unnecessary scrolling. Placing Activity beside About creates information density — HR reads your bio and immediately sees your consistency data without scrolling. It's the strongest possible pairing.

### The layout

```
Desktop (>768px):
┌──────────────────────┬───────────────────────┐
│  About Me            │  Activity              │
│  // about            │                        │
│                      │  🔥 32  🏆 45  🧊 2   │
│  Bio paragraph 1     │                        │
│  Bio paragraph 2     │  [Calendar heatmap]    │
│  Bio paragraph 3     │                        │
│                      │  [Note panel]          │
│  [View Certs →]      │                        │
└──────────────────────┴───────────────────────┘

Mobile (<768px):
Bio text stacks on top, Activity widget below.
```

### `index.php` — restructure About section

**Find the current About section:**
```html
<section id="about" class="about">
  <div class="container">
    <h2>About Me</h2>
    <div class="about-content">
      <div class="about-text">
        ...bio paragraphs...
        <a href="certifications.php" ...>View My Certifications →</a>
      </div>
    </div>
  </div>
</section>
```

**Replace with this structure:**
```html
<section id="about" class="about">
  <div class="container">
    <div class="about-grid">

      <!-- Left column: bio -->
      <div class="about-left">
        <span class="section-label">// about</span>
        <h2>About Me</h2>
        <div class="about-text">
          ...existing bio paragraphs unchanged...
          <a href="certifications.php" class="btn btn-secondary" style="margin-top:1rem;display:inline-block;">View My Certifications →</a>
        </div>
      </div>

      <!-- Right column: activity widget -->
      <div class="about-right" id="activity">
        <span class="section-label">// activity</span>
        <h2>Daily Log</h2>
        <p class="activity-subtitle">Logged daily. Click any cell to read what I worked on.</p>

        <!-- Stat cards -->
        <div class="activity-stats">
          ...existing stat cards PHP — unchanged...
        </div>

        <!-- Calendar heatmap (see C3 for new structure) -->
        <div class="heatmap-wrap">
          <div class="heatmap-body">
            <div class="heatmap-day-labels">
              <span>M</span>
              <span>T</span>
              <span>W</span>
              <span>T</span>
              <span>F</span>
              <span>S</span>
              <span>S</span>
            </div>
            <div class="heatmap-grid-wrap">
              <div class="heatmap-month-labels" id="heatmapMonthLabels"></div>
              <div class="heatmap-grid" id="heatmapGrid">
                ...PHP cell generation — see C3 for new PHP...
              </div>
            </div>
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

        <!-- Note panel -->
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
```

**Also remove the standalone Activity section entirely** — find and delete:
```html
<section id="activity" class="activity-section">
  ...everything inside...
</section>
<script>window.habitNotes = ...;</script>
```
It is now inside the About section. Do not duplicate it.

**Remove Activity nav link** — find and delete:
```html
<li><a href="#activity" class="nav-link">Activity</a></li>
```
The `id="activity"` remains on `.about-right` so deep-linking still works if needed.

### `assets/css/style.css` — update About layout

**Find and replace `.about-content` and related rules:**

```css
/* About Section */
.about {
  padding: 5rem 0;
  background-color: var(--bg-surface);
}

/* Two-column grid: bio left, activity right */
.about-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: start;   /* top-align both columns */
}

.about-left h2,
.about-right h2 {
  margin-bottom: 1.25rem;
}

.about-text p {
  font-size: 1rem;
  margin-bottom: 1.25rem;
  color: var(--text-secondary);
  line-height: 1.75;
}

.about-text p:last-of-type {
  margin-bottom: 1.25rem;
}

/* Activity widget inside about — inherits .about padding, no extra needed */
.about-right .activity-stats {
  margin-bottom: 1.25rem;
}

/* Mobile: stack bio above activity */
@media (max-width: 900px) {
  .about-grid {
    grid-template-columns: 1fr;
    gap: 3rem;
  }
}
```

**Also remove the now-redundant `.activity-section` rule** since Activity is no longer a standalone section:
```css
/* DELETE this rule: */
.activity-section {
  padding: 5rem 0;
  background-color: var(--bg-base);
}
```

---

## C3. Calendar-Format Heatmap

**Why the current approach is wrong:** `grid-template-columns: repeat(77, 12px)` produces a single horizontal line — all 77 days left to right. A calendar grid is 7 rows (Mon–Sun) × N columns (weeks), flowing top-to-bottom then left-to-right. This requires both a CSS change AND a PHP change.

### How it works

```
Example: today = Friday March 21
77 days ago = Saturday Jan 4
Saturday = weekday index 5 (0=Mon, 6=Sun)

So the first column needs 5 empty cells above day 1:
Col 1: [empty Mon] [empty Tue] [empty Wed] [empty Thu] [empty Fri] [Jan 4 Sat] [Jan 5 Sun]
Col 2: [Jan 6 Mon] [Jan 7 Tue] ... [Jan 12 Sun]
...and so on

CSS grid with grid-auto-flow: column fills this automatically.
PHP just needs to emit the offset empty cells first.
```

### PHP — new cell generation (replaces the old loop in B6)

```php
<?php
// Calendar heatmap — 7 rows (Mon=0 … Sun=6) × N weeks
$today      = date('Y-m-d');
$startDate  = date('Y-m-d', strtotime('-76 days'));

// Weekday of start date: 0=Mon, 6=Sun (ISO: 1=Mon, 7=Sun → subtract 1)
$startDow   = (int)date('N', strtotime($startDate)) - 1;  // 0–6

// Emit empty spacer cells for alignment
for ($s = 0; $s < $startDow; $s++) {
    echo '<div class="heatmap-cell heatmap-spacer"></div>';
}

// Emit the 77 real day cells
for ($i = 0; $i <= 76; $i++) {
    $date  = date('Y-m-d', strtotime($startDate . ' +' . $i . ' days'));
    $count = isset($_habitData['heatmap'][$date]) ? $_habitData['heatmap'][$date] : 0;
    $level = 0;
    if     ($count === 1) $level = 1;
    elseif ($count === 2) $level = 2;
    elseif ($count === 3) $level = 3;
    elseif ($count >= 4)  $level = 4;
    $isToday = ($date === $today) ? ' heatmap-today' : '';
    $tip = $count > 0
        ? date('M j, Y', strtotime($date)) . ': ' . $count . ' habit' . ($count > 1 ? 's' : '')
        : date('M j, Y', strtotime($date)) . ': no activity';
    echo '<div class="heatmap-cell level-' . $level . $isToday . '"'
       . ' data-date="' . $date . '"'
       . ' data-count="' . $count . '"'
       . ' title="' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') . '"'
       . '></div>';
}
?>
```

### CSS — calendar grid (replaces old `.heatmap-grid` and `.heatmap-wrap` rules)

```css
/* ── Calendar Heatmap ────────────────────────────────── */
.heatmap-wrap {
  background: #1c1c24;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: var(--radius-lg);
  padding: 1.25rem 1.5rem;
  margin-bottom: 1rem;
  width: 100%;
  box-sizing: border-box;
}

/* Day labels + grid side by side, fills full width */
.heatmap-body {
  display: -webkit-flex;
  display: flex;
  gap: 6px;
  -webkit-align-items: flex-start;
  align-items: flex-start;
  width: 100%;
}

/* Day labels column — 7 rows of M T W T F S S */
.heatmap-day-labels {
  display: -webkit-flex;
  display: flex;
  -webkit-flex-direction: column;
  flex-direction: column;
  gap: 3px;
  padding-top: 22px;
  -webkit-flex-shrink: 0;
  flex-shrink: 0;
}

.heatmap-day-labels span {
  font-family: var(--font-mono);
  font-size: 0.6rem;
  color: var(--text-muted);
  text-align: right;
  width: 10px;
  height: 16px;           /* matches cell height */
  line-height: 16px;
  display: block;
}

/* Grid wrapper — takes ALL remaining width inside heatmap-body */
.heatmap-grid-wrap {
  -webkit-flex: 1;
  flex: 1;
  min-width: 0;
  display: -webkit-flex;
  display: flex;
  -webkit-flex-direction: column;
  flex-direction: column;
  gap: 4px;
  width: 100%;
}

/* Month labels row — positioned above grid */
.heatmap-month-labels {
  height: 18px;
  position: relative;
  width: 100%;
}

.heatmap-month-label {
  font-family: var(--font-mono);
  font-size: 0.62rem;
  color: var(--text-muted);
  position: absolute;
  white-space: nowrap;
  top: 0;
}

/* The calendar grid — 7 rows, columns fill full width evenly */
.heatmap-grid {
  display: grid;
  grid-template-rows: repeat(7, 16px);   /* 7 day rows, 16px tall each */
  grid-auto-flow: column;                /* fills top→bottom, then next col */
  grid-auto-columns: 1fr;               /* each week column takes equal share */
  gap: 3px;
  width: 100%;                           /* stretches to fill .heatmap-grid-wrap */
}

/* Cells — fluid width (set by grid), fixed height */
.heatmap-cell {
  width: 100%;
  height: 16px;
  border-radius: 3px;
  cursor: pointer;
  -webkit-transition: opacity 0.15s ease, -webkit-transform 0.15s ease;
  transition: opacity 0.15s ease, transform 0.15s ease;
  display: block;
}

.heatmap-cell:hover {
  opacity: 0.75;
  -webkit-transform: scale(1.25);
  transform: scale(1.25);
  z-index: 1;
  position: relative;
}

.heatmap-spacer {
  cursor: default;
  background: transparent !important;
  pointer-events: none;
  visibility: hidden;
}

/* Today highlight — cyan ring */
.heatmap-today {
  outline: 2px solid rgba(0, 212, 255, 0.85);
  outline-offset: 1px;
}

/* Color levels — orange scale */
.heatmap-cell.level-0 { background-color: #2a2a35; }
.heatmap-cell.level-1 { background-color: #7c3912; }
.heatmap-cell.level-2 { background-color: #b85420; }
.heatmap-cell.level-3 { background-color: #e8652a; }
.heatmap-cell.level-4 { background-color: #ff6b2b; box-shadow: 0 0 6px rgba(255,107,43,0.4); }

/* Legend */
.heatmap-legend {
  display: -webkit-flex;
  display: flex;
  -webkit-align-items: center;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  color: var(--text-muted);
  margin-top: 0.75rem;
  font-family: var(--font-mono);
}

.heatmap-legend .heatmap-cell {
  width: 12px;
  height: 12px;
  cursor: default;
  -webkit-transform: none !important;
  transform: none !important;
  flex-shrink: 0;
}

/* Mobile — smaller cells but same fluid layout */
@media (max-width: 900px) {
  .heatmap-grid {
    grid-template-rows: repeat(7, 12px);
    gap: 2px;
  }
  .heatmap-cell {
    height: 12px;
  }
  .heatmap-day-labels span {
    height: 12px;
    line-height: 12px;
  }
}
```

### JS — month label generation (append inside DOMContentLoaded in `assets/js/main.js`)

The month labels ("Jan", "Feb" etc.) need to be positioned above the correct week column. This is done in JS after the grid renders, because column positions are only known at runtime.

```javascript
// ─── Heatmap month labels — positioned above correct week columns ─────────────
var heatmapGrid2   = document.getElementById('heatmapGrid')
var monthLabelsEl  = document.getElementById('heatmapMonthLabels')

if (heatmapGrid2 && monthLabelsEl) {
  setTimeout(function() {
    var cells      = heatmapGrid2.querySelectorAll('.heatmap-cell:not(.heatmap-spacer)')
    var lastMonth  = -1
    // Use heatmapGrid2 (the grid itself) as reference — not monthLabelsEl
    // because both are children of .heatmap-grid-wrap so they share the same left offset
    var wrapLeft   = heatmapGrid2.getBoundingClientRect().left

    cells.forEach(function(cell) {
      var date = cell.getAttribute('data-date')
      if (!date) return
      var parts = date.split('-')
      var month = parseInt(parts[1], 10)
      if (month !== lastMonth) {
        lastMonth = month
        var cellLeft = cell.getBoundingClientRect().left - wrapLeft
        var label    = document.createElement('span')
        label.className   = 'heatmap-month-label'
        label.textContent = ['Jan','Feb','Mar','Apr','May','Jun',
                             'Jul','Aug','Sep','Oct','Nov','Dec'][month - 1]
        label.style.left  = cellLeft + 'px'
        monthLabelsEl.appendChild(label)
      }
    })
  }, 150)  // slightly longer delay ensures grid is fully painted and sized
}
```

---

## C4. Build Order for These Changes

**Run AFTER completing Part A and Part B builds.**

```
Step C1 → index.php        Remove themeToggle <li><button> block
Step C2 → index.php        Change color-scheme meta to "dark"
Step C3 → main.js          Remove themeToggle JS event listener block
Step C4 → style.css        Remove .theme-toggle and theme-switching CSS blocks
Step C5 → index.php        Restructure About section into .about-grid two-column layout
Step C6 → index.php        Move Activity widget into .about-right column
Step C7 → index.php        Delete standalone <section id="activity"> and its <script>
Step C8 → index.php        Delete Activity nav link <li>
Step C9 → style.css        Replace .about-content with .about-grid rules
Step C10 → style.css       Delete .activity-section { padding: 5rem 0 } rule
Step C11 → index.php       Replace old heatmap PHP loop with new calendar PHP (C3)
Step C12 → style.css       Replace .heatmap-grid and .heatmap-wrap with calendar CSS (C3)
Step C13 → main.js         Append month label JS (C3)
```

---

## C5. Testing Checklist for Part C

```
□ No theme toggle button visible in navbar
□ Page is permanently dark — no flash of white on load
□ color-scheme meta = "dark"
□ About section is two-column on desktop (bio left, activity right)
□ About section stacks to one column on mobile (<900px)
□ Activity stat cards visible in right column, not as standalone section
□ No duplicate Activity section on page
□ Heatmap renders as 7-row calendar grid, NOT a single row
□ First column starts on correct weekday (Mon top, Sun bottom)
□ Empty spacer cells are invisible (transparent background)
□ Today's cell has cyan outline ring
□ Month labels appear above correct week columns
□ Clicking a cell opens note panel below heatmap
□ Legend renders below heatmap inside the dark card
□ Mobile: cells shrink to 10px, grid still readable
□ No horizontal overflow on mobile
□ Deep link #activity scrolls to the about-right column
```

---

*Single source of truth. Part A = UI redesign. Part B = Habit tracker. Part C = Theme removal + layout restructure + calendar heatmap. Follow each part's build order strictly.*