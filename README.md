<p align="center">
  <img src="public/uploads/site/4953bc49bf5ebe86759f53c5bd38fb305a2c6054.webp" alt="No More Silence — Speak safely. Stand together." width="100%" />
</p>

<h1 align="center">No More Silence</h1>

<p align="center">
  <strong>নীরবতা নয়, নিরাপত্তা।</strong><br />
  A community-powered platform to report online harassment, raise awareness, and give every voice a safe place to be heard.
</p>

<p align="center">
  <a href="https://nomoresilence.rf.gd"><img src="https://img.shields.io/badge/Live%20Demo-nomoresilence.rf.gd-e5252a?style=for-the-badge&logo=googlechrome&logoColor=white" alt="Live Demo" /></a>
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777bb4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+" />
  <img src="https://img.shields.io/badge/MariaDB-10.5%2B-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB" />
  <img src="https://img.shields.io/badge/JavaScript-Vanilla-f7df1e?style=for-the-badge&logo=javascript&logoColor=black" alt="Vanilla JavaScript" />
  <a href="#-contributing"><img src="https://img.shields.io/badge/PRs-Welcome-brightgreen?style=for-the-badge" alt="PRs Welcome" /></a>
</p>

<p align="center">
  <a href="#-live-demo">Live Demo</a> ·
  <a href="#-features">Features</a> ·
  <a href="#-quick-start">Quick Start</a> ·
  <a href="#-contributing">Contributing</a> ·
  <a href="#-roadmap">Roadmap</a> ·
  <a href="#-contact">Contact</a>
</p>

---

## 🔗 Live Demo

> **Try it here:** **[https://nomoresilence.rf.gd](https://nomoresilence.rf.gd)**

The demo is a production-style deployment. Please do not submit real personal data through the public forms while testing.

---

## 📖 About the Project

**No More Silence** is a Bengali-first awareness and reporting platform built for survivors, witnesses, and communities affected by online harassment. It gives people a private way to report abuse, a moderated public feed that resists rumor and retaliation, and a clear appeal process for anyone named in a published report.

The project is designed around three ideas:

1. **Speak safely** — anonymous reporting, private tracking codes, and evidence that is never exposed through public storage.
2. **Publish responsibly** — human moderation, redaction of sensitive material, context, and a permanent right of reply.
3. **Stay accountable** — every published card carries a disclaimer and a path to object, correct, or request removal.

**এই স্ক্রিপটি বাংলাভাষী কমিউনিটির জন্য তৈরি**—যেখানে অনলাইন হয়রানির শিকার হওয়া মানুষ নিরাপদে রিপোর্ট করতে পারে এবং প্রতিটি অভিযোগ  মডারেটরের মাধ্যমে যাচাই হয়।

---

##  Features

### Public site

| Area | What's included |
| --- | --- |
| 🏠 **Editorial homepage** | Hero slider, live community statistics, recent reports, topic-based browsing, most-read reports, process guide and FAQ |
| 📝 **Anonymous reporting** | Multi-step report wizard with optional evidence, private mode, and a random tracking code |
| 🔍 **Search & filters** | Search by name or Facebook link, filter by category and resolution status, paginated results |
| 🗂️ **Card gallery** | Responsive report cards with category, status, evidence count, and share controls |
| 📰 **Report detail pages** | Full description, evidence gallery with lightbox, source links, related reports, and an appeal form |
| 🔗 **Native sharing** | Web Share API with Facebook, WhatsApp, and copy-link fallbacks |
| 🖼️ **Card downloads** | Poster export as PNG with QR code, powered by html2canvas and QRCode.js |
| 📚 **Static content pages** | Guides, About, FAQ, Terms, Privacy, Disclaimer, Contact, and a dedicated Appeal / DMCA page |
| 🌗 **Dark & light themes** | Theme preference stored locally; responsive from mobile to desktop |

### Moderation & administration

- Secure moderator login with role-based access (`super_admin`, `moderator`)
- Review queue with approve / reject / request-more-info actions
- Two-approval publishing mode (optional, configurable in settings)
- Card creation and editing, featured cards, themes, and poster sizes
- Category and card-theme management
- Appeals & takedown review, with optional automatic card archiving
- Editable static pages, ticker/popup notices, SEO fields, analytics ID and Search Console token
- Activity log, role management, and site settings

### Safety & privacy engineering

- PDO prepared statements throughout (no string-concatenated SQL)
- CSRF protection on every state-changing form
- Honeypot fields and IP-based rate limiting
- Uploaded evidence is decoded, re-encoded to WebP, stripped of EXIF data, and stored **outside the public document root**
- Evidence is only served through PHP routes; `storage/` denies all direct access
- Private reports never appear in search, the gallery, or the public feed
- Tracking reveals status only to the holder of the random code
- Strong password hashing (`password_hash`), session hardening, and security headers
- No default administrator account or password ships with the project

---

##  Tech Stack

| Layer | Choice |
| --- | --- |
| Language | PHP 8.1+ (procedural, front-controller architecture) |
| Database | MySQL 8+ / MariaDB 10.5+ with PDO |
| Frontend | Server-rendered PHP views, modern CSS, vanilla JavaScript |
| Styling | Custom CSS with design tokens, dark/light themes, Bengali typography |
| Fonts | Google Fonts — Anek Bangla, Hind Siliguri, Inter, Bebas Neue, Poppins |
| CDN helpers | html2canvas (poster export), QRCode.js (QR codes) |
| Dependencies | **None** — no Composer, no Node.js, no build step |

---

## 📂 Project Structure

```text
no-more-silence/
├── admin/
│   └── index.php              # Admin front controller
├── bin/
│   ├── create-admin.php       # Create the first Super Admin
│   ├── generate-sitemap.php   # Build public/sitemap.xml
│   ├── live-migrate.sql       # One-time SQL upgrade for hosts without CLI
│   ├── migrate.php            # Idempotent schema updates
│   └── seed-demo.php          # Seed fictional demo content
├── includes/
│   ├── auth.php               # Authentication & roles
│   ├── bootstrap.php          # Session, security headers, autoloading
│   ├── content-pages.php      # Structured content for static pages
│   ├── csrf.php               # CSRF helpers
│   ├── db.php                 # PDO connection
│   ├── helpers.php            # Shared utilities
│   ├── render.php             # View rendering
│   ├── sitemap.php            # Sitemap generation
│   └── upload.php             # Image validation & WebP pipeline
├── public/                    # Web document root
│   ├── assets/
│   │   ├── css/               # app.css, home2.css
│   │   ├── img/               # Logo, favicon, OG image
│   │   └── js/                # app.js, home2.js, admin.js
│   ├── uploads/site/          # Admin-managed public images
│   ├── index.php              # Public front controller
│   ├── router.php             # Router for PHP's built-in server
│   ├── robots.txt
│   └── sitemap.xml
├── storage/                   # Private uploads (denied by .htaccess)
│   ├── evidence/              # Report evidence (WebP)
│   ├── profiles/              # Profile images
│   └── dev/                   # Developer photo (served via PHP route)
├── views/                     # Public & admin templates
├── config.php                 # Configuration loader
├── config.local.php           # Local overrides (git-ignored)
├── install.sql                # Database schema & seed data
├── dev.sh                     # Local MariaDB + PHP preview
└── .env.example               # Environment template
```

---

## ✅ Requirements

- **PHP 8.1+** with `pdo_mysql`, `fileinfo`, `mbstring`, `gd` (WebP support), and `exif`
- **MySQL 8+** or **MariaDB 10.5+**
- **Apache 2.4+** with `mod_rewrite` and `mod_headers` (or Nginx with an equivalent front-controller rule)
- Writable directories: `storage/evidence/`, `storage/profiles/`, `public/uploads/site/`
- Upload limits: at least **5 MB per file** and **35 MB per request** for evidence uploads
- No Composer or Node.js required

---

## 🚀 Quick Start

### Option A — XAMPP on Windows (current development setup)

1. Clone or download the project into `C:\xampp\htdocs\nms3`.
2. Import `install.sql` in phpMyAdmin. It creates and uses the `no_more_silence` database by default. To use a different name (such as `nms3`), create the database first, remove the first two lines (`CREATE DATABASE` / `USE`), select your database, then import.
3. Create `config.local.php` next to `config.php` with your local values:

   ```php
   <?php
   return [
       'APP_ENV' => 'development',
       'BASE_PATH' => '/nms3',
       'APP_URL' => 'http://localhost/nms3',
       'APP_HASH_SALT' => 'replace-with-a-random-string-of-32+-characters',
       'DB_HOST' => '127.0.0.1',
       'DB_PORT' => '3306',
       'DB_NAME' => 'no_more_silence',
       'DB_USER' => 'your_database_user',
       'DB_PASS' => 'your_database_password',
   ];
   ```

   > `config.local.php` is **git-ignored** and must never be committed or uploaded into a public folder.

4. Apply pending schema updates:

   ```bash
   C:\xampp\php\php.exe bin\migrate.php
   ```

5. Start Apache and open **http://localhost/nms3/**.

### Option B — Linux / shared hosting (cPanel)

1. Upload the project **outside** the web root and point the domain's document root to `public/`.
2. Create a MySQL database and user, then import `install.sql` via phpMyAdmin. If your host disallows `CREATE DATABASE`/`USE`, remove just those two lines and select the database first.
3. Set the environment variables in your hosting panel, or add a private `config.local.php`:

   ```php
   <?php
   return [
       'APP_URL' => 'https://your-domain.example',
       'APP_ENV' => 'production',
       'APP_TIMEZONE' => 'Asia/Dhaka',
       'DB_HOST' => 'localhost',
       'DB_PORT' => '3306',
       'DB_NAME' => 'your_database',
       'DB_USER' => 'your_user',
       'DB_PASS' => 'a-long-unique-password',
       'APP_HASH_SALT' => 'at-least-32-random-characters',
   ];
   ```

4. Make `storage/evidence/`, `storage/profiles/`, and `public/uploads/site/` writable by PHP.
5. Run the migration and create the first administrator (see below).
6. Generate the sitemap after setting `APP_URL`:

   ```bash
   php bin/generate-sitemap.php
   ```

### Option C — InfinityFree / shared hosting without SSH

InfinityFree gives you a fixed document root (`htdocs`) and no SSH or CLI, so the project is uploaded **as-is** and the root `.htaccess` routes everything into `public/`:

1. **Upload** the whole project into `htdocs` using the file manager or FTP. Do not upload `config.local.php`, `var/`, or local uploads.
2. **Create the database** in the Client Area, then open phpMyAdmin and import `install.sql` with your database selected. If the host rejects `CREATE DATABASE` / `USE`, delete those two lines first.
3. **Create `.env`** in `htdocs` (File Manager → New File) with the values from your Client Area:

   ```dotenv
   APP_NAME="No More Silence"
   APP_ENV=production
   APP_URL=https://nomoresilence.rf.gd
   BASE_PATH=
   APP_TIMEZONE=Asia/Dhaka
   DB_HOST=sqlXXX.infinityfree.com
   DB_PORT=3306
   DB_NAME=if0_XXXXXXXX_nms3
   DB_USER=if0_XXXXXXXX
   DB_PASS=your-database-password
   APP_HASH_SALT=at-least-32-random-characters
   ```

   Alternatively, upload a private `config.local.php` returning the same keys as an array. Never place either file in a public repository.
4. **Upgrade an older database** (skip on a fresh import): import `bin/live-migrate.sql` in phpMyAdmin while your database is selected. It is written for MariaDB and is safe to run more than once.
5. **Create the first administrator in the browser.** Open `https://nomoresilence.rf.gd/admin/setup` and submit an email plus a 12+ character password. This form is only active while the `admins` table is empty; it disables itself after the first account exists. Sign in afterwards at `/admin/login` and add further moderators from **Roles & Access**.
6. **Make these writable** (usually `755`, or `775` if the host asks): `storage/evidence/`, `storage/profiles/`, `public/uploads/site/`. The folder `public/` itself must be writable if you want the admin “Generate Sitemap” button to refresh `public/sitemap.xml`; otherwise generate it locally and upload it.
7. **Verify**: open `https://your-domain/`, then `/admin/login`, and check that `/assets/css/app.css` loads. A 500 error usually means a missing `.env` value or a PHP version below 8.1 (set PHP 8.2 in the Client Area).

> `.htaccess` is included and blocks `config*.php`, `.env`, `install.sql`, `bin/`, `includes/`, `views/`, `storage/`, and `var/`. Confirm that `https://your-domain/config.php` returns **403**.

### Option D — Local preview with `dev.sh` (Linux/macOS)

```bash
bash dev.sh
```

This initializes a disposable MariaDB instance in `var/mysql`, imports `install.sql`, and starts PHP's built-in server through `public/router.php`. The first run may take a moment.

---

## ⚙️ Configuration

Configuration is resolved in this order: **real environment variable → `.env` → `config.local.php` → safe default**. `config.php` loads a private `.env` file automatically when one exists, which is convenient on hosts such as InfinityFree that have no environment-variable panel. Real environment variables always take precedence over `.env`.

| Variable | Purpose |
| --- | --- |
| `APP_NAME` | Site name used across the UI and metadata |
| `APP_URL` | Canonical public URL for Open Graph links, canonical tags, and the sitemap |
| `BASE_PATH` | Subdirectory base path when the app is not served from the domain root (e.g. `/nms3`) |
| `APP_ENV` | `production` hides server error details; use `development` locally |
| `APP_TIMEZONE` | PHP and database display timezone (default: `Asia/Dhaka`) |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` | Database connection |
| `APP_HASH_SALT` | Private salt for one-way rate-limit hashes; **required** (32+ chars) in production |

> `.env` is git-ignored and never served: both the root and `public/` `.htaccess` deny it. Prefer environment variables when your host provides them; otherwise upload `.env` or `config.local.php` directly through the file manager.

---

## 🗄️ Database & Migrations

- `install.sql` is idempotent for this application's tables and reference data (`INSERT IGNORE`).
- `bin/migrate.php` is safe to run repeatedly and only applies missing changes. On hosts without CLI (InfinityFree), import `bin/live-migrate.sql` in phpMyAdmin instead. It currently adds:
  - `reports.profile_image`, `cards.profile_image`, `cards.view_count`
  - `cards.idx_card_views`
  - `appeals.whatsapp_number`, `appeals.reference_text`
  - nullable `appeals.card_id` with `ON DELETE SET NULL`
- **Back up the database before importing `install.sql` on a live site.**

### Create the first administrator

No default account ships with the project. Run:

```bash
php bin/create-admin.php admin@example.com
```

The script asks for a password of at least 12 characters and creates a `super_admin`. Then sign in at `/admin/login`.

### Seed demo content (optional)

1. Put a few JPG/PNG/WebP images in `storage/profile/`.
2. Run:

   ```bash
   php bin/seed-demo.php
   ```

This creates fictional reports, cards, evidence, and appeals for local testing. Never seed demo data into a production database.

---

## 🔐 Security & Privacy

This project handles sensitive safety reports, so security is not optional:

- Keep `config.local.php` and `.env` private; they are git-ignored.
- Keep `storage/` outside the public document root. Its `.htaccess` denies all direct access.
- Evidence is served only through PHP routes with authorisation checks; approved public cards receive blurred derivatives.
- Use HTTPS and set `APP_ENV=production` on any public deployment.
- Replace all example credentials and `APP_HASH_SALT` before going live.
- Configure a jurisdiction-appropriate helpline and have the moderation/publishing policy reviewed legally before production use.
- Do not use the public forms to transmit real personal data while testing a demo deployment.

If you discover a security issue, please **do not open a public issue**. Contact the maintainer privately using the details below.

---

## 🛣️ Routes

| Route | Description |
| --- | --- |
| `/` | Homepage, statistics, recent reports and awareness content |
| `/report`, `/report/sent/{code}` | Anonymous report wizard and confirmation |
| `/track`, `/track/{code}` | Track a report with its private code |
| `/search?q=...` | Search published reports |
| `/cards`, `/reports`, `/reports/{slug}` | Gallery, article feed, and report detail |
| `/card/{slug}`, `/card/{slug}/appeal` | Card detail and per-card appeal |
| `/appeal`, `/dmca` | Site-wide appeal, right-of-reply, and DMCA request page |
| `/guides`, `/about`, `/faq`, `/terms`, `/privacy`, `/disclaimer`, `/contact` | Static content pages |
| `/evidence/{id}`, `/profile/{id}` | Authorised image delivery |
| `/developer/photo` | Developer avatar |
| `/admin/login`, `/admin` | Moderator login and dashboard |
| `/sitemap.xml`, `/robots.txt` | SEO endpoints |

---

## 🤝 Contributing

Contributions are warmly welcome — code, translations, design, documentation, accessibility fixes, and careful bug reports all help.

**এই স্ক্রিপ্ট আরও ভালো করতে অবদান রাখুন।** নতুন ফিচার, বাংলা কনটেন্ট, অ্যাক্সেসিবিলিটি, নিরাপত্তা বা ডকুমেন্টেশন—যেকোনো ধরনের সহায়তা স্বাগত।

### How to contribute

1. **Fork** the repository and create a branch:

   ```bash
   git checkout -b feat/short-description
   ```

2. **Make a focused change.** Keep pull requests small and scoped to one problem.
3. **Test locally.** At minimum:
   - Run `php -l` on every changed PHP file.
   - Exercise the affected public and admin routes.
   - Check the mobile layout around 390 px.
4. **Follow the existing style.**
   - Procedural PHP, `declare(strict_types=1);`, PDO prepared statements.
   - Escape all output with `e()`; build URLs with `url()` / `absoluteUrl()`.
   - Reuse the CSS tokens in `public/assets/css/app.css` instead of hard-coded colours.
   - Bengali UI copy; keep tone respectful and plain.
   - Never add a JS/PHP dependency for something the browser or standard library already does.
5. **Commit with a clear message**, e.g. `feat: add appeal confirmation`, `fix: mobile menu toggle`, `docs: clarify deployment`.
6. **Open a pull request** describing:
   - what changed and why,
   - how you tested it,
   - screenshots for UI changes,
   - any migration or configuration impact.

### Good first issues

- Improve Bengali copy and typography consistency
- Add keyboard and screen-reader improvements
- Add tests for helpers, validation, and route guards
- Document the moderation workflow with screenshots
- Translate the README or UI into additional languages
- Improve performance of homepage queries and image delivery

### Please avoid

- Committing credentials, `config.local.php`, `.env`, uploads, or `var/`
- Changing published-report semantics without a moderation discussion
- Adding tracking scripts, ads, or third-party analytics beyond the configured GA4 ID
- Submitting real survivor data in issues or pull requests

---

## 🗺️ Roadmap

- [ ] Automated test suite for helpers and form validation
- [ ] Contributor-friendly Docker Compose setup
- [ ] Moderation workflow documentation and screenshots
- [ ] Richer accessibility audit (WCAG 2.2 AA)
- [ ] Optional email notifications for appeal updates
- [ ] Multi-language interface support
- [ ] Structured data and richer SEO metadata for reports

Have an idea? Open an issue and describe the problem before the solution.

---

## ⚖️ Legal & Content Disclaimer

Published reports reflect user submissions reviewed by human moderators. They are **not** a court judgment, proof of a crime, or an invitation to harass any person. Every published card includes a disclaimer and an appeal path. If you are named in a report, use the [appeal form](https://nomoresilence.rf.gd/appeal) to request a correction, right of reply, or removal.

Operators deploying this software are responsible for complying with local law, platform policies, copyright rules, and data-protection obligations in their jurisdiction.

---

## 📄 License

No open-source license has been declared yet. Until a `LICENSE` file is added, all rights are reserved by the author. If you would like to reuse or redistribute this project, please get in touch first so the licensing can be made explicit.

---

## 👨‍💻 Contact

**Saieed Rahman** — Bianibazar, Sylhet, Bangladesh
Creator & maintainer of No More Silence

| Platform | Link |
| --- | --- |
| 📧 Email | [info.saidur_bd@aol.com](mailto:info.saidur_bd@aol.com) |
| 💬 WhatsApp | [+974-66489944](https://wa.me/97466489944) |
| ✈️ Telegram | [@SR_Maasum](https://t.me/SR_Maasum) |
| 📘 Facebook | [Massum Ahmed](https://www.facebook.com/SrMaassum) |
| 🐙 GitHub | [SidMan Labs](https://github.com/sidman-labs) |
| 🌐 Portfolio | [sidman.rf.gd](https://sidman.rf.gd/) |

For technical, moderation, or removal requests, please use the contact channels above or the site's [appeal page](https://nomoresilence.rf.gd/appeal).

---

<p align="center">
  <strong>নীরবতা নয়, নিরাপত্তা।</strong><br />
  <sub>Built with care for safer online communities.</sub>
</p>
