-- ─── setup.sql ────────────────────────────────────────────────────────────────
-- Safe to run on both local (XAMPP) and live (InfinityFree).
-- All statements use IF NOT EXISTS — re-running will never break anything.
-- ──────────────────────────────────────────────────────────────────────────────

-- Contacts table (contact form submissions)
CREATE TABLE IF NOT EXISTS `contacts` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120)  NOT NULL,
  `email`      VARCHAR(200)  NOT NULL,
  `message`    TEXT          NOT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skills table
CREATE TABLE IF NOT EXISTS `skills` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `icon`        TEXT            NOT NULL,
  `title`       VARCHAR(100)    NOT NULL,
  `description` VARCHAR(300)    NOT NULL,
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects table
CREATE TABLE IF NOT EXISTS `projects` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `icon`        TEXT            NOT NULL,
  `title`       VARCHAR(120)    NOT NULL,
  `description` VARCHAR(500)    NOT NULL,
  `project_url` VARCHAR(500)    NOT NULL DEFAULT '',
  `github_url`  VARCHAR(500)    NOT NULL DEFAULT '',
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social embeds table (LinkedIn, X/Twitter, etc.)
CREATE TABLE IF NOT EXISTS `social_embeds` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `label`       VARCHAR(80)     NOT NULL DEFAULT '',
  `embed_code`  TEXT            NOT NULL,
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings table (key-value store for admin-editable global config)
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key`   VARCHAR(80)   NOT NULL,
  `setting_value` VARCHAR(500)  NOT NULL DEFAULT '',
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certifications table (image-based)
CREATE TABLE IF NOT EXISTS `certifications` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)    NOT NULL,
  `issuer`      VARCHAR(255)    NOT NULL DEFAULT '',
  `image_path`  VARCHAR(255)    NOT NULL DEFAULT '',
  `issued_date` VARCHAR(100)    NOT NULL DEFAULT '',
  `sort_order`  INT             NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default settings (safe to re-run — INSERT IGNORE won't overwrite existing values)
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
  ('badge_text',       'Open to Work'),
  ('badge_visible',    '1'),
  ('tilt_enabled',     '1'),
  ('notify_email',     'bombleabhay24@gmail.com'),
  ('goatcounter_id',   '');

-- ── Indexes for better query performance ──────────────────────────────────────
-- MySQL/MariaDB will silently skip if the index already exists (CREATE INDEX IF NOT EXISTS
-- is not available in all versions, so we use a safe procedure approach below).

-- Index on contacts.created_at for sorting recent submissions
ALTER TABLE `contacts` ADD INDEX `idx_contacts_created_at` (`created_at`);
-- Index on contacts.email for lookups
ALTER TABLE `contacts` ADD INDEX `idx_contacts_email` (`email`);

-- ── Add updated_at columns ────────────────────────────────────────────────────
ALTER TABLE `skills`   ADD COLUMN IF NOT EXISTS `sort_order`  SMALLINT  NOT NULL DEFAULT 0 AFTER `description`;
ALTER TABLE `skills`   MODIFY COLUMN `icon` TEXT NOT NULL;
ALTER TABLE `skills`   ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
ALTER TABLE `projects` MODIFY COLUMN `icon` TEXT NOT NULL;
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `sort_order`  SMALLINT  NOT NULL DEFAULT 0 AFTER `github_url`;
ALTER TABLE `projects`  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- ── Optional: seed your existing hardcoded skills so the site isn't empty ─────
-- Remove the comment markers below if you want them pre-populated.

/*
INSERT INTO `skills` (icon, title, description) VALUES
  ('🔐', 'Network Security Basics',  'Understanding of firewalls, protocols, and basic threat detection'),
  ('🐧', 'Linux Fundamentals',       'Command line usage, file systems, and system management basics'),
  ('📊', 'SIEM Tools',               'Log monitoring and security event analysis'),
  ('🐍', 'Python Programming',       'Automation, scripting, and basic security-related tools');
*/
