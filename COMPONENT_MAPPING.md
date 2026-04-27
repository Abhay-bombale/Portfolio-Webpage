# Portfolio Webpage - Component Interaction Map

## 1. COMPONENT LIST

### Backend (Server-side)
- **config.php** - Bootstrap & shared utilities
  - `.env` loader, DB singleton, security headers, CSRF helpers, URL validation
  
- **index.php** (Entry Point) - Main portfolio page
  - Loads: skills, projects, hero image, articles, site settings, social embeds
  
- **admin.php** - Admin dashboard (Entry Point)
  - Authentication, session management, CRUD for all content
  
- **article.php** (Entry Point) - Individual article display
  - Query by slug, render article with metadata
  
- **contact.php** (API Endpoint) - Contact form handler
  - JSON POST handler, validates inputs, saves to DB, sends email
  
- **log.php** - Habit tracking logic
  - Streak calculations, habit state management
  
- **404.php** (Entry Point) - Custom 404 error page

### Database (MySQL)
- **contacts** - Form submissions
- **skills** - Portfolio skills
- **projects** - Portfolio projects
- **social_embeds** - Social media embeds
- **site_settings** - Global settings (email, theme, etc.)
- **articles** - Blog articles
- **hero_images** - Hero section images
- **streak_state** - Habit tracking state
- **habit_logs** - Daily habit completion logs
- **daily_notes** - Habit-related notes

### Frontend (Client-side)
- **main.js** - Client interactivity
  - Mobile menu toggle, theme switching, viewport sync, smooth scroll
  
- **style.css** - Tailwind CSS compiled styles
- **assets/images/** - Static assets

### Configuration
- **.env** - DB credentials (local/live)
- **tailwind.config.js** - Theme & design tokens
- **postcss.config.js** - CSS processing
- **setup.sql** - DB initialization script

---

## 2. INTERACTION MAP

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          USER BROWSER                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  HTTP Request → index.php / admin.php / article.php / 404.php           │
│                            ↓                                             │
│                      ┌──────────────────────┐                           │
│                      │   config.php         │                           │
│                      │  (Bootstrap)         │                           │
│                      │  • DB connect        │                           │
│                      │  • Sec headers       │                           │
│                      │  • CSRF token        │                           │
│                      │  • Helpers (e(), db())                           │
│                      └─────────┬────────────┘                           │
│                                │                                         │
│                 ┌──────────────┼──────────────┐                         │
│                 ↓              ↓              ↓                         │
│          ┌─────────────┐ ┌──────────┐ ┌──────────────┐                │
│          │ index.php   │ │admin.php │ │ article.php  │                │
│          │ (Portfolio) │ │(Dashboard)│ │ (Articles)   │                │
│          └──────┬──────┘ └────┬─────┘ └──────┬───────┘                │
│                 │             │              │                         │
│                 └─────────────┼──────────────┘                         │
│                               │                                         │
│                        ┌──────▼──────┐                                 │
│                        │  contact.php│◄─────  POST form data           │
│                        │  (JSON API) │─────→  JSON response            │
│                        └──────┬──────┘                                 │
│                               │                                         │
│                        ┌──────▼──────────────────────┐                 │
│                        │      DATABASE (MySQL)       │                 │
│                        ├─────────────────────────────┤                 │
│                        │ • contacts                  │                 │
│                        │ • skills                    │                 │
│                        │ • projects                  │                 │
│                        │ • articles                  │                 │
│                        │ • hero_images               │                 │
│                        │ • social_embeds             │                 │
│                        │ • site_settings             │                 │
│                        │ • streak_state / habit_logs │                 │
│                        └─────────────────────────────┘                 │
│                                                                          │
│                         ┌──────────────────┐                           │
│                         │   main.js        │                           │
│                         │ (Client-side JS) │                           │
│                         │ • Menu toggle    │                           │
│                         │ • Theme switch   │                           │
│                         │ • Mobile nav sync│                           │
│                         └──────────────────┘                           │
│                                                                          │
│                         ┌──────────────────┐                           │
│                         │   style.css      │                           │
│                         │ (Tailwind)       │                           │
│                         └──────────────────┘                           │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘

                    Environment (.env)
                          ↓
                    Local/Live DB Config
```

---

## 3. DETAILED FLOW BY ENTRY POINT

### A. User Visits Portfolio (`index.php`)
```
index.php
  ↓
config.php (bootstrap) → DB connection
  ↓
Query DB tables:
  • skills → display skills grid
  • projects → display projects grid
  • articles → display latest 6 articles
  • hero_images → display hero section
  • site_settings → load global config
  • social_embeds → display social links
  ↓
Render HTML + inject main.js + style.css
  ↓
Browser: main.js initializes
  • Mobile menu logic
  • Theme toggle
  • Viewport sync
  ↓
User sees portfolio + can interact
```

### B. User Submits Contact Form
```
Browser (form) → contact.php (POST, JSON)
  ↓
contact.php:
  • Validate CSRF token
  • Check honeypot
  • Rate limit (1 per 10s per IP)
  • Sanitize inputs
  ↓
Insert into DB: contacts table
  ↓
Query site_settings for notify_email
  ↓
Send email notification (if configured)
  ↓
Return JSON: {success: true/false, message: "..."}
  ↓
main.js receives response → display toast/alert
```

### C. User Reads Article (`article.php?slug=...`)
```
article.php
  ↓
config.php (bootstrap) → DB connection
  ↓
Query by slug (articles table)
  • If found: render article with title, excerpt, content, image
  • If not found: 404 response
  ↓
Load site_settings for metadata (goatcounter_id)
  ↓
Browser renders with navbar + main.js
  ↓
User can navigate back to portfolio
```

### D. Admin Logs In (`admin.php`)
```
admin.php
  ↓
Check session: $_SESSION['admin_logged_in']
  ↓
If not logged in → show login form
  ↓
POST login:
  • Validate CSRF
  • Verify username/password (bcrypt or plain)
  • Regenerate session ID
  ↓
Access granted → show admin dashboard
  • Tabs: messages, skills, projects, articles, settings, habits
  ↓
CRUD operations (skills/projects/articles):
  • Create/Read/Update/Delete from DB
  • File uploads to uploads/ directories
  ↓
Admin can manage all site content
```

### E. Habit Tracking (`log.php`)
```
log.php (called by admin or API)
  ↓
Load habit_logs and streak_state from DB
  ↓
applyStreakRules():
  • Check daily target (1 = weekday, 3 = weekend)
  • Calculate day gap since last active date
  • Handle freeze balance for missed days
  • Update streak counters
  ↓
Save new state to streak_state table
  ↓
Return feedback (success/freeze/reset)
```

---

## 4. CORE LOGIC FLOW (Summary)

**Request → config.php bootstrap (DB + security) → PHP logic (query/process) → DB (read/write) → JSON/HTML response → main.js enhancements → User sees result**

### Key Dependencies:
- **config.php** - All pages depend on this (DB singleton, helpers, security)
- **.env** - All DB operations depend on env credentials
- **Database** - Core data source; all content flows through here
- **main.js** - Enhances UX on all pages (menu, theme, interaction)
- **setup.sql** - Initializes DB structure

### Security Mechanisms:
1. CSRF token (all forms)
2. HTML escaping with `e()` function
3. Honeypot field (contact form)
4. Rate limiting (contact form: 1 per 10s per IP)
5. Session authentication (admin area)
6. Input validation (length, format, required fields)
7. Security headers (X-Frame-Options, CSP, Referrer-Policy, etc.)

---

## 5. EXTERNAL DEPENDENCIES

- **MySQL/MariaDB** - Database backend
- **Google Fonts** - Typography (Inter, Poppins)
- **Tailwind CSS** - Styling framework
- **PostCSS** - CSS processing
- **Mail function** - Email notifications
- **Environment variables** - Configuration management (via .env)

---

## QUICK REFERENCE: "How Does X Work?"

| What? | How? |
|-------|------|
| **View portfolio** | Browser → index.php → DB query (skills, projects, articles) → HTML render |
| **Submit contact form** | Form POST → contact.php → validate → insert DB → email notify → JSON response |
| **Read an article** | Browser → article.php?slug=... → DB query → render or 404 |
| **Admin login** | admin.php → verify credentials → session → dashboard |
| **Update skills** | admin.php (logged in) → form POST → DB update → file upload (if image) |
| **Track habits** | log.php → streak calculations → streak_state update → feedback |
| **Mobile menu works** | main.js → listen for click → toggle .active class → CSS handles animation |
| **Theme switches** | main.js → toggle dark/light → save to localStorage → CSS adjusts colors |

