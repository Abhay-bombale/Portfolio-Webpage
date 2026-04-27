# Portfolio Webpage - Component Interaction Diagrams

## 1. SYSTEM ARCHITECTURE (High Level)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            USER BROWSER                                     │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │ • Display: index.php (portfolio)                                     │   │
│  │ • Display: article.php (article detail)                             │   │
│  │ • Display: 404.php (error page)                                     │   │
│  │ • Interact: main.js (menu toggle, theme switch, scroll effects)    │   │
│  │ • Style: style.css (Tailwind compiled)                             │   │
│  └────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │ ADMIN AREA:                                                           │   │
│  │ • Display: admin.php (login → dashboard)                            │   │
│  │ • Features: CRUD for skills, projects, articles, hero images       │   │
│  │ • Features: Settings, habits, message management                    │   │
│  └────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │ CONTACT FORM:                                                         │   │
│  │ • POST to contact.php → JSON response                               │   │
│  │ • Validation: spam check, rate limiting, email format              │   │
│  │ • Actions: Save to DB, send email notification                     │   │
│  └────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                  ↕ HTTP/AJAX
         ┌────────────────────────┴────────────────────────┐
         │                                                  │
    ┌────▼────────────────────────────┐    ┌──────────────────────┐
    │   PHP APPLICATION LAYER         │    │  STATIC ASSETS       │
    ├─────────────────────────────────┤    ├──────────────────────┤
    │ • config.php (bootstrap)        │    │ • assets/css/        │
    │ • index.php (homepage)          │    │ • assets/js/         │
    │ • admin.php (dashboard)         │    │ • assets/images/     │
    │ • article.php (detail)          │    │ • uploads/ (user)    │
    │ • contact.php (API)             │    └──────────────────────┘
    │ • log.php (habits)              │
    │ • 404.php (error)               │
    │                                 │
    │ Utilities:                      │
    │ • .env (config)                 │
    │ • tailwind.config.js            │
    │ • postcss.config.js             │
    └────┬────────────────────────────┘
         │
         │ Database Queries (mysqli)
         │
    ┌────▼──────────────────────────────┐
    │     MYSQL DATABASE (13 tables)     │
    ├────────────────────────────────────┤
    │ ✓ Content:                         │
    │   • skills                         │
    │   • projects                       │
    │   • articles                       │
    │   • certifications                 │
    │                                    │
    │ ✓ User Interaction:                │
    │   • contacts (form submissions)   │
    │                                    │
    │ ✓ Media:                           │
    │   • hero_images                    │
    │   • admin_storage_files            │
    │                                    │
    │ ✓ Configuration:                   │
    │   • site_settings (key-value)     │
    │   • social_embeds                  │
    │                                    │
    │ ✓ Habits/Streaks:                  │
    │   • habits                         │
    │   • habit_logs                     │
    │   • daily_notes                    │
    │   • streak_state                   │
    │                                    │
    └────────────────────────────────────┘
```

---

## 2. REQUEST LIFECYCLE (For Each Page Type)

### Flow A: User Visits Homepage (index.php)

```
1. Browser Request
   GET / (or GET /index.php)
                ↓
2. PHP Bootstrap (config.php)
   ├─ Load .env
   ├─ Detect local vs live
   ├─ Create DB connection
   ├─ Define security headers
   └─ Define helper functions
                ↓
3. Data Loading (index.php)
   ├─ Query: SELECT * FROM skills
   ├─ Query: SELECT * FROM projects
   ├─ Query: SELECT * FROM articles WHERE is_published=1
   ├─ Query: SELECT * FROM hero_images WHERE is_active=1
   ├─ Query: SELECT * FROM social_embeds
   └─ Query: SELECT * FROM site_settings
                ↓
4. HTML Generation
   ├─ Navbar with links
   ├─ Hero section with image
   ├─ Skills grid (populated from DB)
   ├─ Projects grid (populated from DB)
   ├─ Articles carousel (latest 6)
   ├─ Contact form
   └─ Social embeds footer
                ↓
5. Asset Injection
   ├─ Include main.js
   ├─ Include style.css
   └─ Include Google Fonts
                ↓
6. Send Response
   ↓
7. Browser Processes
   ├─ Parse HTML
   ├─ Load CSS (Tailwind)
   ├─ Execute main.js (setup menus, theme, listeners)
   └─ Render page
                ↓
8. User Interaction Ready
   ✓ Page visible
   ✓ Mobile menu functional
   ✓ Theme toggle active
   ✓ Contact form ready
```

### Flow B: User Submits Contact Form

```
1. User Fills Form
   ├─ Name: "John"
   ├─ Email: "john@example.com"
   ├─ Message: "Hello, I liked your work!"
   └─ Website (honeypot): "" (empty)
                ↓
2. Form Submit (AJAX POST)
   POST /contact.php
   Headers: Content-Type: application/json
   Body: {
     name: "John",
     email: "john@example.com",
     message: "...",
     website: "",
     csrf_token: "abc123..."
   }
                ↓
3. contact.php Validation Chain
   ├─ Check honeypot empty? ✓
   ├─ Check rate limit (1 per 10s)? ✓
   ├─ Check CSRF token? ✓
   ├─ Check required fields? ✓
   ├─ Validate email format? ✓
   ├─ Check max lengths? ✓
   └─ All passed → Continue
                ↓
4. Database Insert
   PREPARE: INSERT INTO contacts (name, email, message, created_at)
            VALUES (?, ?, ?, NOW())
   BIND: name, email, message
   EXECUTE: ✓
                ↓
5. Email Notification
   ├─ Query: SELECT notify_email FROM site_settings
   ├─ If email set and valid:
   │  └─ mail(to, subject, body, headers)
   └─ Email sent (if configured)
                ↓
6. Response
   JSON: {
     "success": true,
     "message": "Message saved successfully!"
   }
                ↓
7. Browser (main.js receives response)
   ├─ Parse JSON
   ├─ Show toast: "Message saved successfully!" ✓
   ├─ Reset form
   └─ Close loading state
```

### Flow C: User Reads Article (article.php?slug=my-article)

```
1. Browser Request
   GET /article.php?slug=my-article
                ↓
2. Bootstrap (config.php)
   ├─ Connect to DB
   ├─ Send security headers
   └─ Init helpers
                ↓
3. Article Lookup (article.php)
   ├─ Extract slug from URL
   ├─ Query: SELECT * FROM articles 
             WHERE slug = ? AND is_published = 1
   ├─ If found → load article data
   └─ If not found → set 404 response code
                ↓
4. Render Article
   ├─ If found:
   │  ├─ Display title
   │  ├─ Display published date
   │  ├─ Display cover image
   │  ├─ Display content (sanitized)
   │  ├─ Display excerpt/metadata
   │  └─ Include "Back to Write-ups" link
   └─ If not found:
      └─ Show "Article not found" message
                ↓
5. Analytics (if goatcounter_id configured)
   ├─ Query: SELECT goatcounter_id FROM site_settings
   └─ If set: include tracking script
                ↓
6. Response + Render
   ✓ Article page or 404 page
```

### Flow D: Admin Dashboard (admin.php)

```
1. Browser Request
   GET /admin.php
                ↓
2. Check Session
   ├─ Is $_SESSION['admin_logged_in'] set?
   ├─ If YES → Show dashboard
   └─ If NO → Show login form
                ↓
3A. LOGIN PATH (not logged in)
    ├─ Display login form
    ├─ User enters username + password
    └─ Submit POST
                    ↓
    ├─ Validate CSRF
    ├─ Get username/password from POST
    ├─ Get ADMIN_USER, ADMIN_PASS from .env
    ├─ Compare username (hash_equals)
    ├─ Verify password (password_verify or hash_equals)
    ├─ If match: $_SESSION['admin_logged_in'] = true
    ├─ Regenerate session ID
    └─ Redirect to dashboard
                    ↓
    ✓ User logged in
                ↓
3B. DASHBOARD PATH (logged in)
    ├─ Check column existence in DB
    │  ├─ projects.sort_order
    │  ├─ projects.image_path
    │  └─ skills.image_path
    ├─ Load admin settings (upload directories)
    ├─ Check for optional tables (hero_images, articles, etc.)
    └─ Render tabs: Messages, Skills, Projects, Articles, Settings, Habits
                ↓
4. Admin Can Perform CRUD
   ├─ Read: Query data from tables
   ├─ Create: INSERT with file upload
   ├─ Update: UPDATE with file upload
   ├─ Delete: DELETE by ID
   └─ All with CSRF protection
                ↓
5. File Uploads
   ├─ Validate file type
   ├─ Validate file size
   ├─ Move to appropriate uploads/ directory
   ├─ Store path in DB
   └─ Success message
                ↓
6. Logout
   GET /admin.php?logout
   ├─ session_destroy()
   └─ Redirect to login
```

---

## 3. COMPONENT DEPENDENCY GRAPH

```
                        ┌─────────────┐
                        │   .env      │
                        │ (DB creds)  │
                        └──────┬──────┘
                               │
                    ┌──────────▼──────────┐
                    │   config.php        │
                    │  (Bootstrap)        │
                    │  • DB singleton     │
                    │  • Security funcs   │
                    │  • CSRF helpers     │
                    └──────┬──────────────┘
                           │
          ┌────────────────┼────────────────┐
          │                │                │
    ┌─────▼─────┐    ┌────▼────┐    ┌──────▼──────┐
    │ index.php │    │admin.php │    │article.php  │
    │(Homepage) │    │(Dashboard)    │(Article)    │
    └─────┬─────┘    └────┬────┘    └──────┬──────┘
          │                │                │
          └────────────────┼────────────────┘
                           │
                    ┌──────▼────────┐
                    │  contact.php  │
                    │  (JSON API)   │
                    └──────┬────────┘
                           │
                    ┌──────▼──────┐
                    │   log.php   │
                    │(Habits API) │
                    └──────┬──────┘
                           │
          ┌────────────────┼────────────────┐
          │                │                │
    ┌─────▼─────┐  ┌──────▼──────┐  ┌──────▼──────┐
    │ main.js   │  │ style.css   │  │   assets/   │
    │(Frontend) │  │ (Tailwind)  │  │  (images)   │
    └───────────┘  └─────────────┘  └─────────────┘
          │
          └────────────────┬────────────────┐
                           │                │
                    ┌──────▼────────────┐   │
                    │    DATABASE       │   │
                    │     (MySQL)       │   │
                    │                   │   │
                    │  13 Tables:       │◄──┘
                    │  • skills         │
                    │  • projects       │
                    │  • articles       │
                    │  • hero_images    │
                    │  • contacts       │
                    │  • site_settings  │
                    │  • ... (8 more)   │
                    └───────────────────┘
```

---

## 4. DATABASE RELATIONSHIP DIAGRAM

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                        CONTENT LAYER (Public)                               │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────┐        ┌──────────────┐        ┌──────────────┐          │
│  │   SKILLS     │        │  PROJECTS    │        │  ARTICLES    │          │
│  ├──────────────┤        ├──────────────┤        ├──────────────┤          │
│  │ id           │        │ id           │        │ id           │          │
│  │ icon         │        │ icon         │        │ slug (unique)│          │
│  │ image_path   │        │ image_path   │        │ title        │          │
│  │ title        │        │ title        │        │ excerpt      │          │
│  │ description  │        │ description  │        │ content      │          │
│  │ sort_order   │        │ project_url  │        │ cover_image  │          │
│  │ created_at   │        │ github_url   │        │ is_published │          │
│  └──────────────┘        │ sort_order   │        │ sort_order   │          │
│                           │ created_at   │        │ published_at │          │
│  ┌──────────────┐        └──────────────┘        │ created_at   │          │
│  │CERTIFICATIONS│                                 │ updated_at   │          │
│  ├──────────────┤        ┌──────────────┐        └──────────────┘          │
│  │ id           │        │ HERO_IMAGES  │                                   │
│  │ title        │        ├──────────────┤        ┌──────────────┐          │
│  │ issuer       │        │ id           │        │SOCIAL_EMBEDS │          │
│  │ image_path   │        │ image_path   │        ├──────────────┤          │
│  │ issued_date  │        │ alt_text     │        │ id           │          │
│  │ sort_order   │        │ is_active    │        │ label        │          │
│  │ created_at   │        │ created_at   │        │ embed_code   │          │
│  └──────────────┘        └──────────────┘        │ sort_order   │          │
│                                                   │ created_at   │          │
│                                                   └──────────────┘          │
└──────────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────────┐
│                    CONFIGURATION LAYER                                       │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────────────────┐        ┌──────────────────────────┐          │
│  │   SITE_SETTINGS          │        │ ADMIN_STORAGE_FILES      │          │
│  │  (Key-Value Store)       │        │ (File Management)        │          │
│  ├──────────────────────────┤        ├──────────────────────────┤          │
│  │ setting_key (primary)    │        │ id                       │          │
│  │ setting_value            │        │ stored_name              │          │
│  │                          │        │ original_name            │          │
│  │ Examples:                │        │ mime_type                │          │
│  │ • badge_text             │        │ file_size                │          │
│  │ • badge_visible          │        │ file_path                │          │
│  │ • notify_email           │        │ created_at               │          │
│  │ • goatcounter_id         │        └──────────────────────────┘          │
│  │ • article_section_title  │                                              │
│  └──────────────────────────┘                                              │
└──────────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────────┐
│                    USER INTERACTION LAYER                                    │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────────────────┐        ┌──────────────────────────┐          │
│  │   CONTACTS               │        │   HABITS                 │          │
│  │ (Form Submissions)       │        │ (Habit Definitions)      │          │
│  ├──────────────────────────┤        ├──────────────────────────┤          │
│  │ id                       │        │ id                       │          │
│  │ name                     │        │ name                     │          │
│  │ email                    │        │ emoji                    │          │
│  │ message                  │        │ is_active                │          │
│  │ created_at               │        │ sort_order               │          │
│  │                          │        │ created_at               │          │
│  │ (Raw submissions stored) │        └──────────────────────────┘          │
│  └──────────────────────────┘                    ↓                         │
│                                      ┌──────────────────────────┐          │
│                                      │   HABIT_LOGS             │          │
│                                      │ (Daily Completion)       │          │
│                                      ├──────────────────────────┤          │
│                                      │ id                       │          │
│                                      │ habit_id (FK → habits)   │          │
│                                      │ log_date (DATE)          │          │
│                                      │ completed (TINYINT)      │          │
│                                      │ UNIQUE(habit_id, date)   │          │
│                                      └──────────────────────────┘          │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────┐          │
│  │   DAILY_NOTES                    STREAK_STATE                │          │
│  │ (User Notes Per Day)            (Habit Streaks)              │          │
│  ├──────────────────────────────────────────────────────────────┤          │
│  │ id                       │ current_streak  (INT)             │          │
│  │ log_date (DATE, unique)  │ best_streak     (INT)             │          │
│  │ note (TEXT)              │ freeze_balance  (INT)             │          │
│  │ created_at               │ last_active_date (DATE)           │          │
│  │ updated_at               │ created_at, updated_at            │          │
│  └──────────────────────────────────────────────────────────────┘          │
└──────────────────────────────────────────────────────────────────────────────┘

Legend:
  →  means "can reference"
  FK means "Foreign Key"
```

---

## 5. EXECUTION SEQUENCE: "Viewing Homepage"

```
Timeline: User visits http://portfolio.example.com/

T0: HTTP Request
    └─ Browser: GET /

T1: PHP Execution Starts
    ├─ Load index.php
    ├─ Require config.php
    │  ├─ Load .env
    │  ├─ Detect environment (local vs live)
    │  ├─ Create MySQL connection
    │  └─ Define helper functions
    └─ Send security headers
       ├─ X-Content-Type-Options: nosniff
       ├─ X-Frame-Options: SAMEORIGIN
       └─ Referrer-Policy: strict-origin-when-cross-origin

T2: Data Fetching
    ├─ Query 1: SELECT FROM skills → $_skills array
    ├─ Query 2: SELECT FROM projects → $_projects array
    ├─ Query 3: SELECT FROM articles → $_articles array
    ├─ Query 4: SELECT FROM hero_images → $_heroActive
    ├─ Query 5: SELECT FROM site_settings → $_settings
    └─ Query 6: SELECT FROM social_embeds → $_embeds

T3: HTML Generation
    ├─ Output DOCTYPE, head, meta tags
    ├─ Render navbar
    ├─ Render hero section (+ hero image from DB)
    ├─ Loop through $_skills → render skill cards
    ├─ Loop through $_projects → render project cards
    ├─ Loop through $_articles → render article previews
    ├─ Render contact form (+ hidden CSRF token)
    ├─ Render footer (+ social embeds from DB)
    └─ Include <script src="assets/js/main.js"></script>

T4: HTTP Response Sent
    └─ Browser receives HTML + headers

T5: Browser Parsing
    ├─ Parse HTML
    ├─ Load CSS (assets/css/style.css + Google Fonts)
    ├─ Load JS (assets/js/main.js)
    └─ Render layout

T6: JavaScript Execution (main.js)
    ├─ Wait for DOMContentLoaded
    ├─ Hide skeleton loader
    ├─ Setup mobile menu toggle
    ├─ Setup theme toggle
    ├─ Setup scroll listeners (for navbar hide/show)
    ├─ Setup contact form AJAX handler
    └─ Initialize viewport sync

T7: DOM Ready
    └─ Page fully rendered and interactive
        ├─ User can click navbar menu
        ├─ User can toggle theme
        ├─ User can scroll
        ├─ User can submit contact form
        └─ User can click on articles/projects

Total Time: ~200-500ms (depending on network, DB, server)
```

---

## 6. SECURITY FLOW: Contact Form Submission

```
┌─────────────────────────────────────────────────────────────────┐
│  SECURITY VALIDATION PIPELINE                                   │
└─────────────────────────────────────────────────────────────────┘

Step 1: Rate Limiting
┌─────────────────────────────────────┐
│ Check: $_SESSION['last_contact_submit']
│ Rule: Current time - last submit < 10 seconds?
│ Action: If true → REJECT with "Please wait 10s"
│ Purpose: Prevent spam/DoS
└─────────────────────────────────────┘
         │ PASS
         ↓

Step 2: Honeypot Detection
┌─────────────────────────────────────┐
│ Check: $_POST['website'] (hidden field)
│ Rule: Must be empty string
│ Action: If filled → REJECT "Spam detected"
│ Purpose: Bot detection
│ Reason: Real users don't see this field
└─────────────────────────────────────┘
         │ PASS
         ↓

Step 3: CSRF Token Validation
┌─────────────────────────────────────┐
│ Check: $_POST['csrf_token'] vs $_SESSION['csrf_token']
│ Rule: Must match exactly (constant-time comparison)
│ Action: If mismatch → REJECT "Invalid request"
│ Purpose: Prevent Cross-Site Request Forgery
│ Function: verifyCsrf() using hash_equals()
└─────────────────────────────────────┘
         │ PASS
         ↓

Step 4: Input Validation (Presence)
┌─────────────────────────────────────┐
│ Check: $_POST['name'], $_POST['email'], $_POST['message']
│ Rule: All required, not empty
│ Action: If missing → REJECT "All fields required"
│ Purpose: Prevent empty submissions
└─────────────────────────────────────┘
         │ PASS
         ↓

Step 5: Email Format Validation
┌─────────────────────────────────────┐
│ Check: filter_var($email, FILTER_VALIDATE_EMAIL)
│ Rule: Must be valid email format
│ Action: If invalid → REJECT "Invalid email"
│ Purpose: Prevent fake email addresses
└─────────────────────────────────────┘
         │ PASS
         ↓

Step 6: Length Limits
┌─────────────────────────────────────┐
│ Check: strlen($name) ≤ 100
│         strlen($email) ≤ 150
│         strlen($message) ≤ 2000
│ Rule: Enforce max lengths
│ Action: If exceeded → REJECT "Input exceeds max"
│ Purpose: Prevent buffer overflow, DB abuse
└─────────────────────────────────────┘
         │ PASS
         ↓

Step 7: SQL Injection Prevention
┌─────────────────────────────────────┐
│ Method: Prepared Statement + Bind Parameters
│ Code: $stmt = $conn->prepare(
│         'INSERT INTO contacts
│          (name, email, message, created_at)
│          VALUES (?, ?, ?, NOW())'
│       );
│       $stmt->bind_param('sss', $name, $email, $message);
│       $stmt->execute();
│ Purpose: Prevent SQL injection
│ How: User input never directly in SQL query
└─────────────────────────────────────┘
         │ INSERT SUCCESS
         ↓

Step 8: Rate Limit Update
┌─────────────────────────────────────┐
│ $_SESSION['last_contact_submit'] = time();
│ Purpose: Track for rate limiting next request
└─────────────────────────────────────┘
         │
         ↓

Step 9: Email Notification (Optional)
┌─────────────────────────────────────┐
│ Check: Query site_settings for notify_email
│ Rule: Email must be valid format
│ Action: If set → Send email via mail()
│ Headers: From, Reply-To, Content-Type
│ Purpose: Notify admin of new message
└─────────────────────────────────────┘
         │
         ↓

Step 10: Success Response
┌─────────────────────────────────────┐
│ Send: JSON {"success": true, "message": "..."}
│ HTTP Status: 200 OK
│ Browser: main.js receives → show toast → reset form
└─────────────────────────────────────┘

═══════════════════════════════════════════════════════════════════════════════

ATTACK SCENARIOS PREVENTED:

Scenario 1: Bot spam (1000 requests/sec)
  → Rate limit blocks after 1st request (10s cooldown)

Scenario 2: XSS in message field
  → HTML special chars escaped when displayed in email
  → Database stores raw text safely

Scenario 3: SQL injection ('; DROP TABLE contacts; --)
  → Prepared statement + bind_param prevents this
  → User input never injected into SQL string

Scenario 4: CSRF attack (form posted from attacker site)
  → CSRF token required and verified
  → Token is random, session-specific

Scenario 5: Fake email addresses
  → Email format validated with filter_var
  → Invalid emails rejected before insertion

Scenario 6: Message too long (buffer overflow)
  → Max length enforced: 2000 chars
  → Prevents database abuse

Scenario 7: Bot honeypot field filled
  → If 'website' field has value → spam detected
  → Real users never see this field
```

