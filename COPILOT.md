# 📚 COPILOT.md — Codebase Overview & Architecture

## 🎯 Project Summary

**Abhay Bombale — Personal Portfolio Website**

A fully dynamic, database-driven personal portfolio website built with **PHP**, **MySQL**, and **vanilla JavaScript**. No frameworks, no build tools—just clean, performant code that runs on any standard PHP host. The portfolio features an admin panel for content management, SEO optimization, accessibility compliance, and modern UI with dark mode support.

---

## 🏗️ Architecture Overview

### Technology Stack
- **Backend**: PHP 7.4+ (with 8.x recommended)
- **Database**: MySQL / MariaDB
- **Frontend**: Vanilla ES6+ JavaScript (no frameworks)
- **Styling**: CSS Custom Properties with responsive design
- **Build Tools**: None (fully static + server-side rendering)
- **Package Manager**: npm (only for dependency metadata)

### Core Design Principles
1. **No Dependencies**: Pure PHP/JS/CSS—no composer, npm packages, or build tools
2. **Security First**: Prepared statements, CSRF tokens, session management, bcrypt passwords
3. **Accessibility**: Skip links, ARIA labels, keyboard navigation, reduced motion support
4. **SEO Ready**: OpenGraph tags, Twitter Cards, JSON-LD structured data, canonical URLs
5. **Database-Driven**: All content (skills, projects, certifications, articles) stored in MySQL
6. **Admin Panel**: Session-based control panel to manage all site content
7. **Environment-Aware**: Auto-detects local vs. production and switches database credentials

---

## 📁 File Structure & Responsibilities

```
portfolio/
├── index.php                 # Main portfolio page (landing, all sections)
├── admin.php                 # Admin control panel (login-protected)
├── contact.php               # JSON API endpoint for contact form
├── certifications.php        # Standalone certifications gallery page
├── article.php               # Single article detail page (?slug=...)
├── config.php                # Shared bootstrap (DB, env, helpers, security)
├── setup.sql                 # Database schema + seed data (idempotent)
├── 404.php                   # Custom 404 error page
├── .env                      # 🔒 Secrets (DB creds, admin user/pass)
├── .env.example              # Template for .env
├── .htaccess                 # Apache routing + .env protection
├── package.json              # Project metadata only (no actual dependencies)
├── package-lock.json         # Lock file
├── assets/
│   ├── css/
│   │   └── style.css        # All styles (dark mode, responsive, animations)
│   ├── js/
│   │   └── main.js          # Client-side JS (3D tilt, nav, animations, etc.)
│   └── images/
│       ├── Profile.png      # Hero profile image
│       ├── heroimage.jpg    # Default hero background
│       └── favicon.png      # Site icon
└── uploads/                  # User-uploaded files (writable)
    ├── certs/               # Certification badge images
    ├── hero/                # Hero image gallery
    ├── articles/            # Article cover images
    ├── skills/              # Skill icon/image uploads
    ├── projects/            # Project logo/image uploads
    ├── storage/             # Admin mini-storage (admin-only files)
    └── Abhay_Resume.pdf     # Optional downloadable CV
```

---

## 🗄️ Database Schema (9 Tables)

All tables created by `setup.sql` with `IF NOT EXISTS` for safe re-runs.

### 1. **contacts** — Contact Form Submissions
```sql
id (PK), name, email, message, created_at
```
- Stores all contact form submissions
- Indexed by creation timestamp
- Used to display messages in admin panel

### 2. **skills** — Skills & Competencies
```sql
id (PK), icon (emoji/SVG), image_path, title, description, sort_order, created_at
```
- Displayed in the "Skills" section of homepage
- `icon` field can hold emoji, SVG, or HTML icon markup
- `image_path` supports custom uploaded skill images (optional)
- `sort_order` controls display order in UI

### 3. **projects** — Portfolio Projects
```sql
id (PK), icon, image_path, title, description, project_url, github_url, sort_order, created_at
```
- Displayed in the "Projects" section
- `project_url` = link to live demo
- `github_url` = link to source code
- Supports project logo/thumbnail images

### 4. **certifications** — Credentials & Badges
```sql
id (PK), title, issuer, image_path, issued_date, sort_order, created_at
```
- Certification cards with image uploads
- Displayed in dedicated `/certifications.php` page
- `image_path` = uploaded certification badge image

### 5. **social_embeds** — Social Media Widgets
```sql
id (PK), label, embed_code, sort_order, created_at
```
- Stores embedded social media posts (LinkedIn, X/Twitter, etc.)
- `embed_code` can hold iframe/script markup
- Display order controlled by `sort_order`

### 6. **site_settings** — Global Configuration (Key-Value Store)
```sql
setting_key (PK), setting_value
```
- `badge_text` — "Open to Work" badge text
- `badge_visible` — Show/hide "Open to Work" badge (0/1)
- `tilt_enabled` — Enable/disable 3D hero card tilt (0/1)
- `notify_email` — Email address for contact form notifications
- `goatcounter_id` — GoatCounter analytics ID (privacy-friendly analytics)
- All settings configurable from admin panel

### 7. **hero_images** — Hero Section Gallery
```sql
id (PK), image_path, alt_text, is_active, created_at
```
- Multiple hero background images with one active at a time
- Admin can upload new images and switch active hero

### 8. **articles** — Blog / Write-ups
```sql
id (PK), slug, title, excerpt, content, cover_image, is_published, sort_order, published_at, created_at, updated_at
```
- Full-featured article/blog system
- `slug` unique URL identifier (e.g., `/article.php?slug=my-article`)
- `is_published` controls visibility (draft vs. live)
- `content` can hold HTML/markdown
- Cover image support for article headers

### 9. **admin_storage_files** — Admin Mini-Storage
```sql
id (PK), filename, file_path, uploaded_by, created_at
```
- Private admin-only file storage (not public links by default)
- Metadata tracking for uploaded files
- Can generate shareable admin links

---

## 🔌 Entry Points & Page Flow

### **index.php** — Main Portfolio Page
**Renders**: Single-page portfolio with multiple sections
- **Hero Section**: Profile image + "Open to Work" badge + 3D tilt effect
- **About Section**: About/bio text
- **Skills Section**: All skills from `skills` table (with icons/images)
- **Projects Section**: All projects from `projects` table with links
- **Contact Section**: AJAX contact form (spam protection + rate limiting)
- **Footer**: Social links

**Features**:
- Dark mode toggle (localStorage persistence)
- Scroll animations (Intersection Observer)
- Mobile-responsive hamburger menu
- GoatCounter analytics integration
- SEO meta tags + JSON-LD structured data

---

### **admin.php** — Admin Control Panel
**Access**: Session-protected login page

**Auth Flow**:
1. User enters username + password
2. CSRF token verified
3. Password compared (bcrypt hash OR plain-text fallback for migration)
4. Session regenerated on successful login
5. 30-minute inactivity timeout enforced

**Tabs & Features**:
| Tab | Functionality |
|-----|---------------|
| **Messages** | View/delete contact form submissions |
| **Skills** | Create/edit/delete skills (title, description, icon, image) |
| **Projects** | Create/edit/delete projects (title, description, icon, image, URLs) |
| **Certifications** | Upload cert images, title, issuer, date |
| **Articles** | Write/publish/manage blog posts with cover images |
| **Hero Images** | Upload multiple hero images, select active |
| **Settings** | Toggle badge, tilt, notification email, GoatCounter ID |
| **Storage** | Admin-only file upload/management |

**Security**:
- All form submissions include CSRF tokens (via `csrfField()` helper)
- Prepared statements for all DB queries
- Session regeneration on login
- Inactivity timeout (30 minutes)
- Admin credentials sourced from `.env` (never hardcoded)

---

### **contact.php** — Contact Form API Endpoint
**Method**: POST only (JSON response)

**Validation & Security**:
1. **Honeypot Check**: Hidden `website` field—if filled, spam detected
2. **Rate Limiting**: Max 1 submission per 10s per session
3. **Input Validation**:
   - All fields required
   - Email format validation
   - Max lengths enforced (name: 100, email: 150, message: 2000)
4. **Database**: Prepared statement insert into `contacts` table
5. **Email Notification**: Optional email sent to admin (if `notify_email` setting configured)

**Response Format**:
```json
{ "success": true, "message": "Message saved successfully!" }
{ "success": false, "message": "Error reason..." }
```

---

### **certifications.php** — Certifications Gallery Page
**Renders**: Dedicated page showing all certifications from `certifications` table

**Features**:
- Grid layout with certification badge images
- Title, issuer, and issue date displayed per cert
- Same navbar/footer as index.php
- Responsive mobile layout

---

### **article.php** — Single Article Detail Page
**URL**: `/article.php?slug=article-title-here`

**Flow**:
1. Reads `slug` from query parameter
2. Fetches article from `articles` table (WHERE slug = ? AND is_published = 1)
3. If not found → 404 response code
4. Renders article with title, excerpt, content, cover image
5. Includes GoatCounter analytics

---

### **404.php** — Custom 404 Error Page
**Triggers**: Via `.htaccess` ErrorDocument routing

**Content**: User-friendly 404 message with link back to homepage

---

## 🔐 config.php — The Shared Bootstrap

**Every PHP page** (`require_once __DIR__ . '/config.php'`) loads this first.

### Key Functions:

#### **loadEnv($path)**
- Reads `.env` file line-by-line
- Parses `KEY=value` format
- Populates `$_ENV` superglobal
- Skips comments and empty lines

#### **Auto-Detect Local vs. Live**
```php
$isLocal = isset($_SERVER['SERVER_NAME']) && 
           in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);
// Uses LOCAL_DB_* or LIVE_DB_* from .env accordingly
```

#### **db()** — Database Singleton
```php
function db() {
    if ($GLOBALS['_db'] === null) {
        $c = new mysqli($host, $user, $pass, $name);
        $c->set_charset('utf8mb4');
        $GLOBALS['_db'] = $c;
    }
    return $GLOBALS['_db'];
}
```
- Lazy-loads MySQLi connection on first call
- Returns same connection on subsequent calls
- Sets UTF-8 charset

#### **e($string)** — HTML Escape Helper
```php
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
```
- Escapes strings for safe HTML output
- Prevents XSS attacks
- Used throughout templates

#### **CSRF Token Helpers**
```php
csrfToken()      // Generate/retrieve token in session
csrfField()      // Output hidden HTML input with token
verifyCsrf()     // Validate POST token matches session
```
- Token stored in `$_SESSION['csrf_token']`
- Generated on first call via `random_bytes(32)` → `bin2hex()`
- Used on all forms that modify data

#### **Security Headers** — sendSecurityHeaders()
```php
X-Content-Type-Options: nosniff           // Prevent MIME sniffing
X-Frame-Options: SAMEORIGIN               // Clickjacking protection
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

#### **Session Security** — initSession()
- Sets `HttpOnly` flag (prevents JS access to session cookie)
- Sets `SameSite=Strict` (CSRF protection)
- Enforces strict mode
- 30-minute inactivity timeout
- Updates `last_activity` timestamp on each request

#### **URL Validation** — isValidUrl($url)
- Validates URLs with `filter_var(FILTER_VALIDATE_URL)`
- Ensures `http://` or `https://` protocol
- Allows empty strings (optional fields)

---

## 🎨 Frontend Architecture

### **assets/css/style.css** (Single File, All Styles)

#### CSS Custom Properties (Theme Variables)
```css
--bg-base:       #0a0a0f    /* Main background (dark) */
--bg-surface:    #111118    /* Card backgrounds */
--bg-elevated:   #1a1a24    /* Elevated surfaces */
--bg-overlay:    #22222e    /* Overlay/modal backgrounds */

--accent:        #00d4ff    /* Primary cyan color */
--accent-dim:    #00a8cc    /* Dimmed accent (hover) */
--accent-glow:   rgba(0, 212, 255, 0.15)

--orange:        #ff6b2b    /* Secondary accent */

--text-primary:  #f0f0f5    /* Main text */
--text-secondary:#a0a0b8    /* Secondary text */
--text-muted:    #5a5a78    /* Muted text */

--radius-sm:     8px        /* Border radius variants */
--radius-md:     12px
--radius-lg:     16px
--radius-xl:     24px

--transition:    all 0.25s cubic-bezier(0.4, 0, 0.2, 1)
```

#### Key Features:
- **Dark Mode Default**: All colors optimized for dark theme
- **Light Mode Support**: `@media (prefers-color-scheme: light)` with inverted palette
- **Responsive**: Mobile-first, clamp() for fluid scaling
- **Animations**: Intersection Observer for scroll reveals, smooth transitions
- **Accessibility**: Skip link, focus-visible outlines, reduced motion support
- **Back-to-Top Button**: Fixed position, appears on scroll

---

### **assets/js/main.js** (Vanilla ES6+ JavaScript)

#### Initialization & Setup
```javascript
document.addEventListener('DOMContentLoaded', function() { ... })
// Runs after DOM is fully parsed
```

#### Key Features:
1. **Skeleton Loader** — Shows loading spinner until page is ready
2. **Motion Preferences Detection** — Respects user's "reduced motion" preference
3. **Hero Card 3D Tilt Effect** — Mouse-tracking 3D rotation on hero section
4. **Mobile Navigation Menu** — Hamburger toggle, auto-close on link click
5. **Viewport Sync** — Prevents menu from being hidden by mobile keyboards
6. **Dark Mode Toggle** — Theme switch with localStorage persistence
7. **Scroll Animations** — Lazy-loads animations as elements enter viewport (Intersection Observer)
8. **Contact Form (AJAX)** — Prevents page reload, displays success/error toast
9. **Utility Functions** — getTheme(), setTheme(), showMessage(), scrollToTop()

---

## 🔒 Security Measures

### 1. **SQL Injection Prevention**
- All queries use **MySQLi prepared statements** with parameterized queries
- Never concatenate user input into SQL strings
- Example: `$stmt->bind_param('sss', $name, $email, $message)`

### 2. **XSS Protection**
- Output escaping via `e()` helper in all templates
- HTML special characters converted to entities
- Example: `<?php echo e($user_input); ?>`

### 3. **CSRF Protection**
- All state-changing forms include hidden CSRF token
- Token validated on submission via `verifyCsrf()`
- Token regenerated on login (`session_regenerate_id(true)`)
- Uses `hash_equals()` for timing-safe comparison

### 4. **Password Security**
- Admin password supports **bcrypt hashing** (`$2y$` prefix auto-detected)
- Fallback to plain-text comparison for migration (not recommended for production)
- Example: `password_verify($input, $hash)`

### 5. **Session Security**
- HttpOnly flag prevents JavaScript access to session cookie
- SameSite=Strict prevents cross-site cookie sending
- 30-minute inactivity timeout
- Session regenerated on login

### 6. **Environment Protection**
- `.env` file blocked from public access via `.htaccess`
- Secrets never committed to Git
- `.env.example` provided as template

### 7. **Security Headers**
- `X-Content-Type-Options: nosniff` — MIME sniffing prevention
- `X-Frame-Options: SAMEORIGIN` — Clickjacking protection
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`

### 8. **Input Validation**
- Server-side validation on all forms
- Max length checks to prevent abuse
- Email format validation
- Honeypot field in contact form (spam prevention)
- Rate limiting on contact submissions (1 per 10 seconds)

### 9. **File Upload Protection**
- No executable uploads allowed
- Uploads stored outside web root (in `/uploads/` with specific subdirs)
- Filenames sanitized
- MIME type validation (via PHP move_uploaded_file + checks)

---

## 🌍 SEO & Metadata

### OpenGraph Tags (index.php)
```html
<meta property="og:title" content="Abhay Bombale">
<meta property="og:description" content="Portfolio description">
<meta property="og:image" content="...">
<meta property="og:type" content="website">
<meta property="og:url" content="https://...">
```

### Twitter Card Tags
```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="...">
<meta name="twitter:description" content="...">
<meta name="twitter:image" content="...">
<meta name="twitter:creator" content="@AbhayBombale">
```

### JSON-LD Structured Data
```json
{
  "@context": "https://schema.org",
  "@type": "Person",
  "name": "Abhay Bombale",
  "url": "https://...",
  "image": "...",
  "description": "...",
  "sameAs": ["https://linkedin.com/in/...", "https://twitter.com/..."]
}
```

### Canonical URLs
```html
<link rel="canonical" href="https://example.com/page">
```

---

## ♿ Accessibility Features

### 1. **Skip Link**
```html
<a href="#main-content" class="skip-link">Skip to main content</a>
```
- Focus-visible outline on keyboard navigation
- Positioned off-screen by default, visible on focus

### 2. **ARIA Labels & Roles**
```html
<nav role="navigation" aria-label="Main navigation">
<button aria-label="Toggle navigation menu" aria-expanded="false">
<main id="main-content" role="main">
```

### 3. **Keyboard Navigation**
- Mobile hamburger menu navigable via keyboard
- All buttons focusable with `:focus-visible` outline
- Tab order preserved through semantic HTML

### 4. **Reduced Motion Support**
```javascript
var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
if (!prefersReducedMotion) { // Only enable animations if not preferred }
```
- Animations disabled for users with accessibility preference
- No tilt effect, no scroll animations, no transitions

### 5. **Color Contrast**
- Cyan accent (#00d4ff) on dark background meets WCAG AA standards
- Text colors have sufficient contrast ratios

### 6. **Semantic HTML**
- Proper heading hierarchy (h1, h2, h3, etc.)
- `<nav>`, `<section>`, `<article>`, `<footer>` elements
- Forms have proper `<label>` associations

---

## 🚀 Deployment & Configuration

### Local Development (XAMPP)
```env
LOCAL_DB_HOST=localhost
LOCAL_DB_USER=root
LOCAL_DB_PASS=
LOCAL_DB_NAME=portfolio
```
- Run `mysql -u root portfolio < setup.sql`
- Visit `http://localhost/portfolio/`

### Production Deployment (InfinityFree, cPanel, etc.)
```env
LIVE_DB_HOST=your.host.com
LIVE_DB_USER=db_user
LIVE_DB_PASS=db_password
LIVE_DB_NAME=portfolio_db
```
- Upload files via FTP
- Create database in hosting control panel
- Import `setup.sql`
- Ensure `/uploads/` directory is writable (chmod 755)
- Set admin credentials in `.env`

### Environment Auto-Detection
```php
$isLocal = isset($_SERVER['SERVER_NAME']) && 
           in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);
// Uses appropriate DB config based on detection
```

---

## 📊 Analytics Integration

### GoatCounter (Privacy-Friendly Analytics)
- Configurable from admin panel (`site_settings` table)
- Script injected into footer if ID configured
- No personal data tracking (privacy by default)
- GDPR compliant

### Contact Form Notifications
- Optional email sent to admin on contact form submission
- Configured via `notify_email` setting
- Uses PHP `mail()` function

---

## 🛠️ Common Tasks & Extension Points

### Add New Admin Tab
1. Create new form in `admin.php` (around line 500+)
2. Add case in switch statement for tab handling
3. Add database queries/inserts with prepared statements
4. Include CSRF token validation

### Add New Database Table
1. Add CREATE TABLE statement to `setup.sql`
2. Use `IF NOT EXISTS` for safety
3. Add admin form in `admin.php` to manage entries
4. Display data on appropriate front-end page

### Add New Portfolio Section
1. Add new database table if needed
2. Query data in `index.php`
3. Add HTML markup and styling in `assets/css/style.css`
4. Add animations/interactions in `assets/js/main.js` (Intersection Observer)

### Enable/Disable Features
- All feature toggles in `site_settings` table
- Accessible from admin "Settings" tab
- Checked conditionally in code (e.g., tilt, badge visibility)

---

## 🔄 Data Flow Examples

### Contact Form Submission Flow
```
User fills form (index.php)
    ↓
AJAX POST to contact.php
    ↓
Honeypot check + rate limiting
    ↓
Input validation (required, email format, lengths)
    ↓
Prepared statement INSERT into contacts table
    ↓
(Optional) Send email notification to admin
    ↓
Return JSON response { success: true/false, message: ... }
    ↓
JavaScript displays toast notification
```

### Admin Login Flow
```
User submits login form (admin.php)
    ↓
CSRF token verification
    ↓
Username & password validation (bcrypt or plain-text)
    ↓
Session regenerate + set admin_logged_in flag
    ↓
Redirect to admin dashboard (messages tab)
    ↓
On logout: session_destroy() + redirect to login
```

### Article Retrieval Flow
```
User visits /article.php?slug=my-article
    ↓
article.php reads slug from $_GET
    ↓
Prepared statement: SELECT ... FROM articles WHERE slug = ? AND is_published = 1
    ↓
If found: render article template
    ↓
If not found: http_response_code(404)
```

---

## 📝 Key Files & LOC Estimate

| File | Type | Purpose | Size |
|------|------|---------|------|
| config.php | Backend | Bootstrap, DB, helpers, security | ~120 lines |
| index.php | Backend+Frontend | Main portfolio page | 800-1000 lines |
| admin.php | Backend+Frontend | Admin control panel | 1000+ lines |
| contact.php | Backend | Contact form API | 90 lines |
| certifications.php | Backend+Frontend | Certs gallery page | 300 lines |
| article.php | Backend+Frontend | Single article page | 200 lines |
| 404.php | Frontend | Error page | 50 lines |
| setup.sql | Database | Schema + seed data | 200 lines |
| assets/css/style.css | Frontend | All styles | 1500+ lines |
| assets/js/main.js | Frontend | All interactions | 500+ lines |
| **Total** | - | - | **5000-6000 lines** |

---

## 🎓 Technologies Used

| Category | Technology | Notes |
|----------|-----------|-------|
| Language | PHP 7.4+ | Server-side templating & logic |
| Database | MySQL / MariaDB | Data persistence |
| Frontend | Vanilla JavaScript ES6+ | No jQuery, no frameworks |
| Styling | CSS3 with Custom Properties | Dark mode, responsive, animations |
| APIs | REST-style JSON endpoints | contact.php as example |
| Analytics | GoatCounter | Optional, privacy-friendly |
| Hosting | Any PHP 7.4+ host | XAMPP locally, cPanel/InfinityFree live |

---

## 💡 Design Patterns & Best Practices

### 1. **Singleton Pattern (Database)**
- Single shared MySQLi connection via `db()` function
- Lazy-loaded on first call, reused thereafter

### 2. **Configuration Management**
- Environment-based (local vs. production)
- Centralized in `.env` file
- Loaded into `$_ENV` superglobal

### 3. **Template Rendering**
- PHP files as templates (mixed HTML + PHP)
- No separate template engine (no Twig, Blade, etc.)
- Output escaping via `e()` helper throughout

### 4. **CSRF Protection Pattern**
- Generate token in session
- Output hidden field in form
- Validate token on form submission
- Use `hash_equals()` for timing-safe comparison

### 5. **Lazy Loading & Observers**
- Intersection Observer for scroll animations
- Only load/animate when visible
- Respects motion preferences

### 6. **Progressive Enhancement**
- Core functionality works without JavaScript (forms are POST-based)
- JavaScript enhances with AJAX, animations, dark mode
- Graceful degradation on older browsers

---

## 🎉 Summary

This is a **modern, secure, production-ready personal portfolio** built with:
- ✅ Zero external dependencies
- ✅ Full-stack database-driven architecture
- ✅ Admin panel for content management
- ✅ Industry-standard security practices
- ✅ SEO optimization & accessibility compliance
- ✅ Beautiful dark mode UI with animations
- ✅ Mobile-responsive design
- ✅ Privacy-friendly analytics

Perfect for hosting on affordable shared hosting while maintaining professional quality and functionality.

---

*Last Updated: 2026-04-25*  
*Codebase Size: ~5000-6000 lines of code*  
*Status: Production-Ready*
