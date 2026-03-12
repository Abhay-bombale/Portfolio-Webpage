# 🛡️ Abhay Bombale — Personal Portfolio

A fully dynamic, database-driven portfolio website built from scratch with PHP, MySQL, and vanilla JavaScript. Features a full admin panel, dark mode, and security-hardened backend.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Features

**Frontend**
- Responsive design with mobile hamburger navigation
- Dark mode toggle with `localStorage` persistence and `prefers-color-scheme` detection
- 3D tilt hero image with animated status badge
- Back-to-top button with smooth scroll
- Scroll reveal animations via `IntersectionObserver`
- Character counter on the contact form textarea
- Lazy-loaded images for faster page loads

**Admin Panel** (`/admin.php`)
- Session-based authentication with bcrypt password hashing
- CRUD management for Skills, Projects, and Social Embeds
- Editable site settings (badge text, badge visibility, tilt toggle)
- View and delete contact form submissions

**Backend & Security**
- Shared `config.php` bootstrap (DRY — no duplicated DB/env code)
- CSRF token protection on every form
- Prepared statements for all database queries (SQL injection safe)
- HTML output escaping via `htmlspecialchars` helper
- Honeypot field and session-based rate limiting on the contact form
- Security headers (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, etc.)
- `.htaccess` rules blocking direct access to `.env` and `config.php`
- Session timeout (30 min inactivity) and secure cookie flags
- URL validation on project links in admin

**SEO & Accessibility**
- Open Graph and Twitter Card meta tags
- JSON-LD `Person` structured data
- Canonical URL
- Skip-to-content link and `<main>` landmark
- `:focus-visible` outlines on all interactive elements
- `prefers-reduced-motion` media query (disables animations)

---

## 🔧 Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Frontend   | HTML5, CSS3 (custom properties), vanilla JS |
| Backend    | PHP 7.4+                            |
| Database   | MySQL / MariaDB                     |
| Server     | Apache (XAMPP locally, InfinityFree live) |

---

## 🚀 Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP + MySQL stack)
- PHP 7.4 or higher

### Installation

1. **Clone the repo**
   ```bash
   git clone https://github.com/your-username/Portfolio-Webpage.git
   cd Portfolio-Webpage
   ```

2. **Create your environment file**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` with your database credentials and admin login. To generate a bcrypt hash for your password:
   ```bash
   php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
   ```

3. **Set up the database**
   - Create a database (e.g. `portfolio`)
   - Import the schema:
     ```bash
     mysql -u root -p portfolio < setup.sql
     ```

4. **Start the server**
   - Launch Apache and MySQL from the XAMPP control panel
   - Visit `http://localhost/Portfolio-Webpage`

5. **Log in to admin**
   - Navigate to `http://localhost/Portfolio-Webpage/admin.php`
   - Use the credentials from your `.env` file

---

## 📁 Project Structure

```
Portfolio-Webpage/
├── .env.example      # Environment variable template
├── .gitignore         # Git ignore rules
├── .htaccess          # Apache security & compression rules
├── config.php         # Shared bootstrap (DB, CSRF, sessions, helpers)
├── index.php          # Public portfolio page
├── admin.php          # Admin panel (auth + CRUD)
├── contact.php        # Contact form AJAX handler
├── main.js            # Client-side interactivity
├── style.css          # All styles (incl. dark mode & a11y)
├── setup.sql          # Database schema + indexes
├── package.json       # Project metadata
└── README.md
```

---

## 🗄️ Database Schema

| Table          | Purpose                              |
|----------------|--------------------------------------|
| `contacts`     | Contact form submissions             |
| `skills`       | Skills displayed on the portfolio    |
| `projects`     | Projects displayed on the portfolio  |
| `social_embeds`| Embedded social media widgets        |
| `site_settings`| Key-value store for site config      |

---

## 📝 Environment Variables

See [.env.example](.env.example) for the full list. Key variables:

| Variable        | Description                    |
|-----------------|--------------------------------|
| `LOCAL_DB_HOST`  | Local MySQL host (e.g. `localhost`) |
| `LOCAL_DB_NAME`  | Local database name            |
| `LOCAL_DB_USER`  | Local database username        |
| `LOCAL_DB_PASS`  | Local database password        |
| `LIVE_DB_*`      | Production database credentials |
| `ADMIN_USER`     | Admin panel username           |
| `ADMIN_PASS`     | Admin panel password (bcrypt hash) |

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).
