# 🌐 Abhay Bombale — Personal Portfolio

A fully dynamic, database-driven personal portfolio website built with **PHP**, **MySQL**, and **vanilla JavaScript** — no frameworks, no build tools, just clean code that runs on any standard PHP host.

---

## ✨ Features

- **Dynamic Content** — Skills, projects, and certifications are all managed through an admin panel and stored in MySQL; no hardcoded HTML needed.
- **Admin Panel** (`admin.php`) — A secure, session-based control panel to manage:
  - Contact form messages (view & delete)
  - Hero image gallery (upload multiple images and pick active hero)
  - Skills, projects, and certifications (add, edit, delete)
  - Articles / write-ups (publish and manage content)
  - Mini storage uploads for private admin use (public links, admin-managed)
  - Site-wide settings (badge text, email notifications, analytics)
- **Contact Form** — AJAX-powered form with spam protection (honeypot), rate limiting, server-side validation, and email notifications.
- **Certifications Page** — A dedicated `/certifications.php` page with an image gallery grid for displaying credential badges.
- **Dark Mode** — Toggle between light and dark themes, with preference saved to `localStorage`.
- **3D Hero Card Tilt** — Mouse-tracking tilt effect on the hero section (can be toggled from the admin panel).
- **Scroll Animations** — Intersection Observer-based reveal animations for cards and sections.
- **GoatCounter Analytics** — Privacy-friendly analytics integration (configurable from admin settings).
- **SEO Ready** — OpenGraph tags, Twitter Card meta, JSON-LD structured data, and canonical URLs.
- **Accessibility** — Skip-to-content link, ARIA labels, keyboard-navigable mobile menu.
- **Security** — CSRF tokens on all forms, prepared statements (no SQL injection), `X-Frame-Options`, `X-Content-Type-Options`, session timeouts, and bcrypt-compatible admin password hashing.

---

## 🗂️ Project Structure

```
portfolio/
├── index.php           # Main portfolio page (Home, About, Skills, Projects, Contact)
├── certifications.php  # Standalone certifications gallery page
├── admin.php           # Admin panel (login-protected)
├── contact.php         # Contact form handler (JSON API endpoint)
├── config.php          # Shared bootstrap: DB, env loading, helpers, security
├── setup.sql           # Database schema + seed data (safe to re-run)
├── article.php         # Single article detail page (?slug=...)
├── assets/
│   ├── css/
│   │   └── style.css   # All styles (CSS custom properties, responsive)
│   ├── js/
│   │   └── main.js     # All client-side JS (vanilla, no dependencies)
│   └── images/
│       ├── Profile.png
│       ├── heroimage.jpg
│       └── favicon.png
├── 404.php             # Custom 404 error page
├── .env                # 🔒 Environment variables (not committed to Git)
└── uploads/
  ├── Abhay_Resume.pdf  # Optional: downloadable CV
  ├── certs/            # Uploaded certification images
  ├── hero/             # Uploaded hero images
  ├── articles/         # Uploaded article cover images
  └── storage/          # Admin mini-storage files
```

---

## 🗄️ Database Schema

The site uses **9 MySQL tables**, all created by `setup.sql`:

| Table | Purpose |
|---|---|
| `contacts` | Stores contact form submissions |
| `skills` | Skills shown in the Skills section |
| `projects` | Projects shown in the Projects section (supports icon/logo + sort order) |
| `certifications` | Certification cards with image uploads |
| `social_embeds` | Embeddable social media posts/widgets |
| `site_settings` | Key-value store for admin-configurable settings |
| `hero_images` | Hero image gallery with active selection |
| `articles` | Article/write-up content with publish state and slug |
| `admin_storage_files` | Admin mini-storage metadata |

---

## 🚀 Setup & Installation

### Prerequisites
- PHP 7.4+ (8.x recommended)
- MySQL / MariaDB
- A web server (Apache/Nginx) or XAMPP for local development

### 1. Clone the Repository

```bash
git clone https://github.com/Abhay-bombale/portfolio.git
cd portfolio
```

### 2. Create the `.env` File

Create a `.env` file in the project root (never commit this):

```env
# Local (XAMPP / dev)
LOCAL_DB_HOST=localhost
LOCAL_DB_USER=root
LOCAL_DB_PASS=
LOCAL_DB_NAME=portfolio

# Live (production host)
LIVE_DB_HOST=your_live_host
LIVE_DB_USER=your_live_db_user
LIVE_DB_PASS=your_live_db_password
LIVE_DB_NAME=your_live_db_name

# Admin credentials
ADMIN_USER=admin
ADMIN_PASS=your_password_or_bcrypt_hash
```

> **Tip:** For `ADMIN_PASS`, you can use a plain password during setup. For production, replace it with a bcrypt hash: `password_hash('yourpassword', PASSWORD_BCRYPT)`.

### 3. Run the SQL Setup

Import `setup.sql` into your MySQL database:

```bash
mysql -u root -p portfolio < setup.sql
```

Or paste it into phpMyAdmin's SQL tab. The script is safe to re-run — it uses `IF NOT EXISTS` throughout.

### 4. Create Upload Directories

```bash
mkdir -p uploads/certs
chmod 755 uploads/certs
```

### 5. Add a `.htaccess` (Apache)

To route 404s through `404.php` and protect the `.env` file:

```apache
ErrorDocument 404 /404.php

<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

### 6. Visit Your Site

Open `http://localhost/portfolio/` in your browser.  
Access the admin panel at `http://localhost/portfolio/admin.php`.

---

## ⚙️ Admin Panel Guide

Log in at `/admin.php` with your credentials set in `.env`.

| Tab | What you can do |
|---|---|
| **Messages** | Read and delete contact form submissions |
| **Skills** | Add/edit/delete skills with icons and descriptions |
| **Projects** | Add/edit/delete projects with logo/icon, custom order, and links to live demo & GitHub |
| **Certifications** | Upload certificate images with title, issuer, and date |
| **Settings** | Toggle the "Open to Work" badge, hero tilt effect, notification email, and GoatCounter analytics ID |

---

## 🔒 Security Notes

- All database queries use **prepared statements** (MySQLi) — protected against SQL injection.
- All forms are protected by **CSRF tokens**.
- Admin sessions have a **30-minute inactivity timeout** and are regenerated on login.
- The `.env` file must be blocked from public access via `.htaccess` (see setup above).
- Admin passwords support **bcrypt hashing** (`$2y$` prefix is auto-detected).

---

## 🌍 Deploying to a Live Host (e.g., InfinityFree, cPanel)

1. Upload all files via FTP/File Manager.
2. Create a MySQL database and user in your hosting control panel.
3. Update the `LIVE_DB_*` values in `.env`.
4. Import `setup.sql` through phpMyAdmin.
5. Make sure `uploads/certs/` is writable by the web server.

The `config.php` auto-detects whether it's running locally or on a live server based on `SERVER_NAME`, and uses the correct DB credentials automatically.

---

## 🛠️ Built With

- **PHP** — Server-side logic and templating
- **MySQL / MariaDB** — Database
- **Vanilla JavaScript** — No frameworks; plain ES6+
- **CSS Custom Properties** — Theming (light/dark mode)
- **Google Fonts** — Inter & Poppins
- **GoatCounter** — Privacy-friendly analytics (optional)

---

## 🤖 Credits & Acknowledgements

This project was designed and built with assistance from:

- **[Claude.ai](https://claude.ai)** by Anthropic — AI pair programming, code review, security hardening, and architecture guidance.
- **[Bolt.new](https://bolt.new)** — AI-powered prototyping and rapid scaffolding of the initial UI and component structure.

---

## 👤 Author

**Abhay Bombale**  
B.Tech Computer Science Engineering Student | Aspiring Cybersecurity Analyst

- 🌐 Portfolio: [abhaybombale.page.gd]
- 💼 LinkedIn: [linkedin.com/in/abhaybombale](https://www.linkedin.com/in/abhaybombale/)
- 🐙 GitHub: [github.com/Abhay-bombale](https://github.com/Abhay-bombale)
- 🐦 X (Twitter): [@AbhayBombale](https://x.com/AbhayBombale)

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).