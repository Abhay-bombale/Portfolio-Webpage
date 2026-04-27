# Portfolio Webpage - Quick Architecture Reference

## VISUAL: Request → Response Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ENTRY POINTS (4)                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│   index.php ──────────→ Portfolio Page (Skills, Projects, Articles)    │
│   admin.php ──────────→ Admin Dashboard (CRUD, Auth)                   │
│   article.php ────────→ Single Article (by slug parameter)            │
│   404.php ────────────→ Error Page                                     │
│                                                                          │
└──────────────────────────────┬──────────────────────────────────────────┘
                               │
                   ┌───────────▼────────────┐
                   │   All pages include:   │
                   │   1. config.php ◄─────── (Bootstrap & DB setup)
                   │   2. Security headers   │
                   │   3. Session handling   │
                   └───────────┬────────────┘
                               │
                ┌──────────────┼──────────────┐
                │              │              │
                ▼              ▼              ▼
         ┌────────────┐ ┌────────────┐ ┌───────────────┐
         │  HTML Page │ │ contact.php│ │  API Endpoint │
         │  Response  │ │  (JSON API)│ │ (JSON/Form)   │
         └────────────┘ └────────────┘ └───────────────┘
                │              │              │
                └──────────────┼──────────────┘
                               │
                        ┌──────▼──────┐
                        │  DATABASE   │
                        │  (MySQL)    │
                        └─────────────┘
```

---

## TABLE: Component Dependencies

| Component | Depends On | Used By | Purpose |
|-----------|-----------|---------|---------|
| **config.php** | .env | ALL pages | DB connect, security, helpers |
| **index.php** | config.php, main.js, style.css | Browser | Homepage, portfolio display |
| **admin.php** | config.php, main.js | Browser | Admin dashboard, content management |
| **article.php** | config.php, main.js | Browser | Article detail page |
| **contact.php** | config.php | Browser (AJAX) | Form submission handler |
| **main.js** | HTML DOM | Browser | Interactivity (menu, theme, scroll) |
| **style.css** | tailwind.config.js | HTML pages | Visual styling |
| **.env** | File system | config.php | DB credentials (local/live) |
| **Database** | MySQL driver | All PHP files | Data persistence |
| **setup.sql** | Database | DBA | DB initialization |

---

## REFERENCE: Which Function/File Does What?

### Core Functions (from config.php)
```php
db()                  // Get DB singleton connection
e($string)            // HTML escape for security
sendSecurityHeaders() // Add HTTP security headers
csrfToken()          // Get/generate CSRF token
csrfField()          // HTML hidden input with token
verifyCsrf()         // Validate CSRF in POST
isValidUrl($url)     // Validate URL format
```

### Main Entry Points & Behaviors
```
GET  / or index.php        → Fetch data → Render portfolio HTML
GET  /admin.php            → Check session → Show login or dashboard
POST /admin.php            → Authenticate or perform admin action
GET  /article.php?slug=... → Fetch article → Render or 404
POST /contact.php          → Validate form → Save to DB → Send email → JSON response
GET  /log.php              → Calculate habit streaks → Return feedback
GET  /404.php              → Return 404 HTTP response + error HTML
```

### Database Tables (13 total)
```
Core Content:
  • skills          (title, icon, description, sort_order)
  • projects        (title, icon, description, URLs, sort_order)
  • articles        (title, slug, content, excerpt, published_at, sort_order)
  • certifications  (title, issuer, image, issued_date, sort_order)

User Interaction:
  • contacts        (name, email, message from form submissions)

Configuration:
  • site_settings   (key-value store for global config)

Media:
  • hero_images     (image_path, alt_text, is_active)
  • admin_storage_files (file management for admin area)

Social:
  • social_embeds   (label, embed_code, sort_order)

Habits:
  • habits          (name, emoji, is_active, sort_order)
  • habit_logs      (habit_id, log_date, completed)
  • daily_notes     (log_date, note)

Admin:
  • streak_state    (current_streak, best_streak, freeze_balance)
```

---

## DIAGRAM: Data Flow for Common Actions

### Action 1: User Views Homepage
```
Browser
  ↓ GET /
index.php
  ├─ require config.php ──→ DB connect
  ├─ Query skills table
  ├─ Query projects table
  ├─ Query articles table (is_published = 1, LIMIT 6)
  ├─ Query hero_images table (is_active = 1)
  ├─ Query site_settings table
  ├─ Query social_embeds table
  └─ Render HTML + include main.js + style.css
Browser
  ↓
Executes main.js
  ├─ Mobile menu setup
  ├─ Theme toggle setup
  ├─ Navbar scroll listener
  └─ Ready for interaction
User sees portfolio ✓
```

### Action 2: User Submits Contact Form
```
Browser (form with name, email, message, honeypot)
  ↓ POST /contact.php (JSON content-type)
contact.php
  ├─ Check honeypot (spam detection)
  ├─ Rate limit check (1 per 10s per IP)
  ├─ Validate CSRF token
  ├─ Validate inputs (email format, length, required fields)
  ├─ Insert into contacts table
  ├─ Query site_settings for notify_email
  ├─ Send email via mail() function
  └─ Return JSON {success: true/false, message: "..."}
Browser (main.js)
  ├─ Receive JSON response
  ├─ Show toast/alert to user
  └─ Reset form if success ✓
```

### Action 3: Admin Updates a Skill
```
Browser (admin logged in)
  ↓ POST /admin.php?action=update_skill
admin.php
  ├─ Check session: $_SESSION['admin_logged_in']
  ├─ Verify CSRF token
  ├─ Validate form inputs
  ├─ If file upload present:
  │  ├─ Validate file type & size
  │  ├─ Move to uploads/skills/
  │  └─ Store path in DB
  ├─ UPDATE skills table
  ├─ Set flash message
  └─ Redirect with success message ✓
Browser
  ├─ Show updated dashboard
  └─ Display confirmation flash
```

### Action 4: Habit Logging (Daily Habit Check-in)
```
Browser/Admin (log.php called)
  ↓
log.php
  ├─ Load from streak_state table
  ├─ Load from habit_logs table (today's entry)
  ├─ Call applyStreakRules($state, $logDate, $completedCount)
  │  ├─ Calculate day gap since last_active_date
  │  ├─ Check if freeze balance covers missed days
  │  ├─ Determine if today's target met (1 weekday, 3 weekend)
  │  ├─ Update current_streak, best_streak, freeze_balance
  │  └─ Return feedback (success/freeze/reset)
  ├─ Update streak_state table
  └─ Return feedback
Browser
  └─ Show feedback: "Streak continued" / "🧊 Freeze used" / "💔 Streak reset" ✓
```

---

## SECURITY MECHANISMS AT WORK

```
Layer 1: Session & Authentication
  ├─ admin.php requires $_SESSION['admin_logged_in']
  ├─ Login verifies username + bcrypt password
  └─ Session regenerated after login success

Layer 2: CSRF Protection
  ├─ csrfToken() generates token in session
  ├─ csrfField() outputs hidden input for forms
  └─ verifyCsrf() validates token on POST

Layer 3: Input Validation
  ├─ Required field checks
  ├─ Email format validation
  ├─ Max length enforced (prevent DB abuse)
  ├─ File type & size validation (for uploads)
  └─ Honeypot field for contact form (bot detection)

Layer 4: Output Escaping
  ├─ e() function escapes HTML special chars
  ├─ Applied to all user input displayed in HTML
  └─ Prevents XSS attacks

Layer 5: HTTP Security Headers
  ├─ X-Content-Type-Options: nosniff
  ├─ X-Frame-Options: SAMEORIGIN
  ├─ Referrer-Policy: strict-origin-when-cross-origin
  └─ Permissions-Policy: camera=(), microphone=(), geolocation=()

Layer 6: Rate Limiting
  ├─ Contact form: 1 submission per 10s per IP (via session)
  └─ Prevents spam/abuse

Layer 7: Database
  ├─ Prepared statements with bind_param (prevent SQL injection)
  └─ Charset: utf8mb4 (safe Unicode handling)
```

---

## DEPLOYMENT ARCHITECTURE

```
┌──────────────────────────────────────────────────┐
│          Production (Live Server)               │
├──────────────────────────────────────────────────┤
│                                                  │
│  .env (LIVE credentials)                        │
│   ↓                                              │
│  PHP 7.4+ with mysqli support                   │
│   ↓                                              │
│  ├─ index.php                                    │
│  ├─ admin.php                                    │
│  ├─ article.php                                  │
│  ├─ contact.php                                  │
│  ├─ config.php                                   │
│  └─ assets/ (CSS, JS, images)                    │
│   ↓                                              │
│  MySQL 5.7+ or MariaDB (InfinityFree, AWS, etc) │
│   ↓                                              │
│  uploads/ (skills, projects, articles, hero)    │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## STARTUP CHECKLIST (Setup Sequence)

1. **Database Setup**
   - Run `setup.sql` to create tables
   - Verify MySQL/MariaDB running

2. **Configuration**
   - Create `.env` with DB credentials
   - Set `LOCAL_*` for development, `LIVE_*` for production
   - Set `ADMIN_USER` and `ADMIN_PASS` (bcrypt hash preferred)

3. **File Permissions**
   - Ensure `uploads/` directory writable (chmod 755)
   - Ensure `assets/` readable
   - Ensure `.env` readable (but restricted access)

4. **Web Server**
   - Enable PHP (7.4+)
   - Ensure `mod_rewrite` if using `.htaccess`
   - Point document root to project folder

5. **Optional Configuration**
   - Add `notify_email` in admin panel (site settings)
   - Add `goatcounter_id` for analytics
   - Create admin settings (badge, theme, etc.)

---

## COMMON TASKS & ENTRY POINTS

| Task | Entry Point | Method | Result |
|------|-------------|--------|--------|
| View homepage | index.php | GET | HTML page |
| Admin login | admin.php | GET/POST | Dashboard or login form |
| Submit contact form | contact.php | POST | JSON response |
| View article | article.php?slug=... | GET | Article HTML or 404 |
| Admin edit skill | admin.php | POST | Skill updated in DB |
| Log habit | log.php | GET/POST | Streak state updated |
| Handle 404 | 404.php | GET | Error HTML + 404 status |

---

## CODEBASE SIZE Reference

- **PHP Lines**: ~2,500+ (index, admin, article, contact, config, log, 404)
- **JavaScript Lines**: ~500+ (main.js - no frameworks, vanilla JS)
- **CSS Lines**: ~1,000+ (style.css - Tailwind compiled)
- **SQL Tables**: 13 (contacts, skills, projects, articles, hero_images, site_settings, social_embeds, certifications, admin_storage_files, habits, habit_logs, daily_notes, streak_state)
- **Entry Points**: 4 (index.php, admin.php, article.php, 404.php) + 1 API (contact.php)

