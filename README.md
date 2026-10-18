# No More Silence

PHP 8.1+ and MySQL/MariaDB reporting and awareness site. The app uses PDO prepared statements, server-rendered HTML, vanilla JavaScript, and no Composer dependencies.

## Included

- Bengali public homepage, responsive card feed, search, filters, pagination, guide and policy pages
- Anonymous multi-step report submission, private reports, private tracking codes and secure evidence uploads
- Moderator login, review, approve/reject/request-more-info, card generation, appeals/takedown, activity log and roles
- Viral-card PNG download/share, QR code, SEO fields, editable notices and pages, sitemap generation, SQL export

## Requirements

- PHP 8.1 or newer with `pdo_mysql`, `fileinfo`, `gd`, `mbstring` and `exif`
- MySQL 8+ or MariaDB 10.5+
- Apache with `mod_rewrite` and `mod_headers`, or a PHP server configured to route requests through `public/index.php`
- PHP upload limits of 5 MB per file and at least 35 MB total request size
- Writable `storage/evidence`, `public/uploads/site` and `public/sitemap.xml` locations

## Run the development preview

The project preview starts a local MariaDB instance and PHP's built-in server:

```bash
bash dev.sh
```

The first run initializes a development database at `var/mysql`, imports `install.sql`, and creates the local app database user. `var/` is excluded from source control. The development database is disposable and is not a production backup.

To make the first administrator, in another terminal run:

```bash
cd no-more-silence
export DB_HOST=127.0.0.1 DB_PORT=3310 DB_NAME=no_more_silence DB_USER=nms_app DB_PASS=
php bin/create-admin.php admin@example.com
```

Open `/admin/login` in the preview and use the account you just created. The script asks for a password of at least 12 characters.

## Install on shared hosting / cPanel

1. Create a MySQL database and a dedicated database user in cPanel. Grant that user privileges on the new database.
2. Upload this folder and point the domain's document root to `no-more-silence/public`. Do not make the project root or `storage/` public.
3. Import `install.sql` in phpMyAdmin. If your host does not permit `CREATE DATABASE`, remove only its first two lines (`CREATE DATABASE` and `USE`) and select the cPanel database before import.
4. Configure environment variables in the hosting panel, or create a private `config.local.php` returning an array:

   ```php
   <?php
   return [
       'APP_URL' => 'https://your-domain.example',
       'APP_ENV' => 'production',
       'APP_TIMEZONE' => 'Asia/Dhaka',
       'DB_HOST' => 'localhost',
       'DB_PORT' => '3306',
       'DB_NAME' => 'your_cpanel_database',
       'DB_USER' => 'your_cpanel_user',
       'DB_PASS' => 'a-unique-database-password',
       'APP_HASH_SALT' => 'generate-a-unique-random-secret-of-32-characters-or-more',
   ];
   ```

   Keep `config.local.php` private and do not upload it into a publicly served directory. Never reuse the development database or credentials in production.
5. Make `storage/evidence/`, `public/uploads/site/`, and `public/sitemap.xml` writable by the PHP process. Keep `storage/` outside the document root.
6. Create the first Super Admin from the command line:

   ```bash
   php bin/create-admin.php admin@example.com
   ```

7. Generate the sitemap once after setting `APP_URL`:

   ```bash
   php bin/generate-sitemap.php
   ```

## Configuration

| Setting | Purpose |
| --- | --- |
| `APP_URL` | Canonical public URL used for Open Graph links and the sitemap |
| `APP_ENV` | Use `production` outside local development to hide server errors |
| `APP_TIMEZONE` | PHP and database display timezone |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` | MySQL connection |
| `APP_HASH_SALT` | Private salt for one-way IP rate-limit hashes; use a unique random value |

All uploaded evidence is decoded and re-encoded to WebP, stripped of EXIF metadata, and stored outside the public document root. Evidence is served only to authenticated moderators, or as a blurred preview on an approved public card. Private and pending reports are not included in public search, tracking reveals only the status to the holder of its random code, and public allegations include a disclaimer and appeal/takedown form.

Card publishing can be set to require two different moderator approvals from **Admin → Site Settings**. Initial admin access is created explicitly; no default password is shipped.

## Routes

- `/` — homepage and awareness feed
- `/report` — anonymous report wizard
- `/track/{code}` — report status by private tracking code
- `/search?q=...`, `/cards`, `/reports`, `/reports/{slug}`, `/card/{slug}` — public search, gallery, article feed/detail and card detail
- `/admin/login` — moderator login
- `/admin` — review dashboard

## Notes

- `install.sql` is idempotent for this app's tables and reference data. Back up the database before re-importing on a live site.
- The interface loads Google Fonts and html2canvas/QRCode.js from public CDNs. Card downloads need html2canvas access and network connectivity.
- Analytics accepts only a GA4 Measurement ID; Search Console accepts only its verification token. Arbitrary tracking scripts are not evaluated.
- Configure a current, jurisdiction-appropriate helpline and seek legal review of public moderation/publishing policies before production use.
