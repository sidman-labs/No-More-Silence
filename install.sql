CREATE DATABASE IF NOT EXISTS no_more_silence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE no_more_silence;

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_key VARCHAR(32) NOT NULL UNIQUE,
  role_name VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id TINYINT UNSIGNED NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(64) NOT NULL UNIQUE,
  name_bn VARCHAR(100) NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS card_themes (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  theme_key VARCHAR(48) NOT NULL UNIQUE,
  theme_name VARCHAR(80) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tracking_code CHAR(16) NOT NULL UNIQUE,
  accused_name VARCHAR(160) NOT NULL,
  fb_url VARCHAR(500) NULL,
  normalized_fb_url VARCHAR(255) NULL,
  post_url VARCHAR(500) NULL,
  profile_image VARCHAR(120) NULL,
  category_id SMALLINT UNSIGNED NOT NULL,
  description TEXT NOT NULL,
  contact_email VARCHAR(190) NULL,
  is_private TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('pending','approved','rejected','needs_info') NOT NULL DEFAULT 'pending',
  reviewer_note VARCHAR(1000) NULL,
  ip_hash CHAR(64) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_report_status_created (status, created_at),
  INDEX idx_report_name (accused_name),
  INDEX idx_report_fb (normalized_fb_url),
  CONSTRAINT fk_report_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS report_evidence (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id BIGINT UNSIGNED NOT NULL,
  storage_name VARCHAR(120) NOT NULL,
  mime_type VARCHAR(80) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  is_blurred TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_evidence_report (report_id),
  CONSTRAINT fk_evidence_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cards (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id BIGINT UNSIGNED NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  accused_name VARCHAR(160) NOT NULL,
  category_id SMALLINT UNSIGNED NOT NULL,
  summary VARCHAR(420) NOT NULL,
  fb_url VARCHAR(500) NULL,
  normalized_fb_url VARCHAR(255) NULL,
  post_url VARCHAR(500) NULL,
  profile_image VARCHAR(120) NULL,
  status ENUM('active','resolved','archived') NOT NULL DEFAULT 'active',
  theme_id SMALLINT UNSIGNED NOT NULL,
  size_key ENUM('facebook','square','story') NOT NULL DEFAULT 'facebook',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  evidence_blurred TINYINT(1) NOT NULL DEFAULT 1,
  published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_card_public (status, published_at),
  INDEX idx_card_featured (is_featured, status, published_at),
  INDEX idx_card_views (status, view_count),
  INDEX idx_card_name (accused_name),
  INDEX idx_card_fb (fb_url),
  INDEX idx_card_normalized_fb (normalized_fb_url),
  CONSTRAINT fk_card_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE SET NULL,
  CONSTRAINT fk_card_category FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_card_theme FOREIGN KEY (theme_id) REFERENCES card_themes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appeals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  card_id BIGINT UNSIGNED NULL,
  reference_text VARCHAR(500) NULL,
  sender_name VARCHAR(160) NOT NULL,
  sender_email VARCHAR(190) NOT NULL,
  whatsapp_number VARCHAR(32) NULL,
  appeal_type ENUM('right_of_reply','takedown') NOT NULL,
  message TEXT NOT NULL,
  status ENUM('pending','reviewed','resolved') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(1000) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_appeal_status (status, created_at),
  CONSTRAINT fk_appeal_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  notice_type ENUM('ticker','popup') NOT NULL,
  title VARCHAR(180) NOT NULL DEFAULT '',
  body TEXT NOT NULL,
  image_path VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_notice_type (notice_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key VARCHAR(64) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id BIGINT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(64) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_created (created_at),
  CONSTRAINT fk_activity_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key_hash CHAR(64) NOT NULL,
  action_key VARCHAR(64) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rate_limit (key_hash, action_key, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip_hash CHAR(64) NOT NULL,
  succeeded TINYINT(1) NOT NULL DEFAULT 0,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts (ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (role_key, role_name) VALUES
('super_admin', 'সুপার অ্যাডমিন'),
('moderator', 'মডারেটর');

INSERT IGNORE INTO categories (slug, name_bn, sort_order) VALUES
('cyberbullying', 'সাইবার বুলিং', 1),
('harassment', 'অনলাইন হয়রানি', 2),
('blackmail', 'ব্ল্যাকমেইল', 3),
('sexual-harassment', 'যৌন হয়রানি', 4),
('child-sexual-abuse', 'শিশু যৌন নির্যাতন', 5),
('hate-speech', 'হেট স্পিচ', 6),
('religious-hatred', 'ধর্মীয় উস্কানি', 7),
('death-threats', 'মৃত্যু হুমকি', 8),
('personal-attacks', 'ব্যক্তিগত আক্রমণ', 9),
('monetary-fraud', 'আর্থিক প্রতারণা', 10);





INSERT IGNORE INTO card_themes (theme_key, theme_name) VALUES
('signal', 'Signal'),
('after-dark', 'After Dark'),
('amber-alert', 'Amber Alert'),
('violet-pulse', 'Violet Pulse'),
('emerald-watch', 'Emerald Watch');

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'No More Silence'),
('footer_text', 'নিরাপদ অনলাইন পরিবেশ গড়তে সচেতন হোন।'),
('helpline', 'জরুরি সহায়তার জন্য স্থানীয় কর্তৃপক্ষের সঙ্গে যোগাযোগ করুন।'),
('ticker_enabled', '1'),
('ticker_text', 'অনলাইনে হয়রানির শিকার হলে নিরাপদে রিপোর্ট করুন।'),
('popup_enabled', '0'),
('popup_title', 'আপনি একা নন'),
('popup_body', 'নিরাপদে রিপোর্ট করুন, প্রমাণ সংরক্ষণ করুন এবং প্রয়োজনে সহায়তা নিন।'),
('seo_title', 'No More Silence — অনলাইন নিরাপত্তা ও সচেতনতা'),
('seo_description', 'অনলাইন হয়রানি সম্পর্কে সচেতনতা, গোপনীয় রিপোর্ট এবং সহায়তার তথ্য।'),
('seo_keywords', 'সাইবার বুলিং, অনলাইন হয়রানি, রিপোর্ট'),
('gsc_meta', ''),
('ga_tracking', ''),
('footer_copyright', '© No More Silence'),
('require_two_approvals', '0');

INSERT IGNORE INTO notices (notice_type, title, body, is_active) VALUES
('ticker', '', 'অনলাইনে হয়রানির শিকার হলে নিরাপদে রিপোর্ট করুন।', 1),
('popup', 'আপনি একা নন', 'নিরাপদে রিপোর্ট করুন, প্রমাণ সংরক্ষণ করুন এবং প্রয়োজনে সহায়তা নিন।', 0);

INSERT IGNORE INTO pages (page_key, title, body) VALUES
('about', 'আমাদের সম্পর্কে', 'No More Silence অনলাইন নিরাপত্তা ও সচেতনতা বাড়াতে কাজ করে।'),
('faq', 'সাধারণ প্রশ্ন', 'রিপোর্ট ব্যক্তিগতভাবে জমা হয়। প্রকাশের আগে মডারেটর যাচাই করেন।'),
('terms', 'ব্যবহারের শর্ত', 'ভুল, অবৈধ বা ক্ষতিকর তথ্য জমা দেবেন না। প্রতিটি প্রকাশিত কার্ডে আপত্তি জানানোর পথ রয়েছে।'),
('privacy', 'গোপনীয়তা নীতি', 'রিপোর্টারের পরিচয় গোপন রাখা হয়। প্রমাণ ও ব্যক্তিগত যোগাযোগের তথ্য সীমিতভাবে সংরক্ষিত হয়।'),
('disclaimer', 'দায়-অস্বীকার', 'এখানে প্রকাশিত তথ্য ব্যবহারকারীদের জমা দেওয়া প্রতিবেদন ও মডারেশন প্রক্রিয়ার ভিত্তিতে। এটি আদালতের সিদ্ধান্ত বা অপরাধ প্রমাণের ঘোষণা নয়।'),
('contact', 'যোগাযোগ', 'সাইট-সংক্রান্ত প্রশ্ন বা সহায়তার জন্য প্রশাসকের সঙ্গে যোগাযোগ করুন।'),
('guides', 'নিরাপদে কী করবেন', 'স্ক্রিনশট ও লিংক সংরক্ষণ করুন। প্ল্যাটফর্মে রিপোর্ট করুন। জরুরি ঝুঁকিতে স্থানীয় সহায়তা নিন।');
