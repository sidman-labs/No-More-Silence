<?php
declare(strict_types=1);

/**
 * Apply small schema updates to an existing database.
 * Usage: php bin/migrate.php
 *
 * Safe to run more than once: every change is checked before it is applied.
 */

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/includes/db.php';
require dirname(__DIR__) . '/includes/helpers.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line only.\n");
    exit(1);
}

$changes = [
    ['reports', 'profile_image', 'VARCHAR(120) NULL', 'post_url'],
    ['cards', 'profile_image', 'VARCHAR(120) NULL', 'post_url'],
    ['cards', 'view_count', 'INT UNSIGNED NOT NULL DEFAULT 0', 'is_featured'],
    ['appeals', 'whatsapp_number', 'VARCHAR(32) NULL', 'sender_email'],
    ['appeals', 'reference_text', 'VARCHAR(500) NULL', 'card_id'],
];

$pdo = db();
$database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
if ($database === '') {
    fwrite(STDERR, "The database connection does not select a database.\n");
    exit(1);
}

$exists = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
);

$applied = 0;
foreach ($changes as [$table, $column, $definition, $after]) {
    $exists->execute([$database, $table, $column]);
    if ((int) $exists->fetchColumn() > 0) {
        fwrite(STDOUT, "  skipped  {$table}.{$column} (already present)\n");
        continue;
    }
    // Table, column and position names are fixed literals from the list above.
    $afterSql = $after !== '' ? " AFTER `{$after}`" : '';
    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}{$afterSql}");
    fwrite(STDOUT, "  added    {$table}.{$column}\n");
    $applied++;
}

$indexExists = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?'
);
$indexExists->execute([$database, 'cards', 'idx_card_views']);
if ((int) $indexExists->fetchColumn() === 0) {
    $pdo->exec('ALTER TABLE `cards` ADD INDEX `idx_card_views` (`status`, `view_count`)');
    fwrite(STDOUT, "  added    cards.idx_card_views\n");
    $applied++;
}

// Site-wide appeals may not point at a card, and an appeal should survive if its card is deleted.
$appealFk = $pdo->prepare(
    "SELECT CONSTRAINT_NAME, DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = 'appeals' AND REFERENCED_TABLE_NAME = 'cards' LIMIT 1"
);
$appealFk->execute([$database]);
$foreignKey = $appealFk->fetch();
$foreignKeyName = '';
if ($foreignKey && $foreignKey['DELETE_RULE'] !== 'SET NULL') {
    $foreignKeyName = (string) $foreignKey['CONSTRAINT_NAME'];
    $quotedName = str_replace('`', '``', $foreignKeyName);
    $pdo->exec("ALTER TABLE `appeals` DROP FOREIGN KEY `{$quotedName}`");
    fwrite(STDOUT, "  changed  appeals.{$foreignKeyName} (dropped for nullable card_id)\n");
    $applied++;
}

$nullable = $pdo->prepare(
    'SELECT IS_NULLABLE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
);
$nullable->execute([$database, 'appeals', 'card_id']);
if ($nullable->fetchColumn() !== 'YES') {
    $pdo->exec('ALTER TABLE `appeals` MODIFY `card_id` BIGINT UNSIGNED NULL');
    fwrite(STDOUT, "  changed  appeals.card_id (nullable)\n");
    $applied++;
}

if ($foreignKeyName !== '') {
    $pdo->exec('ALTER TABLE `appeals` ADD CONSTRAINT `fk_appeal_card` FOREIGN KEY (`card_id`) REFERENCES `cards`(`id`) ON DELETE SET NULL');
    fwrite(STDOUT, "  changed  appeals.fk_appeal_card (ON DELETE SET NULL)\n");
    $applied++;
}

if (!is_dir(PROFILE_STORAGE) && !mkdir(PROFILE_STORAGE, 0750, true) && !is_dir(PROFILE_STORAGE)) {
    fwrite(STDERR, 'Cannot create ' . PROFILE_STORAGE . "\n");
    exit(1);
}
$gitkeep = PROFILE_STORAGE . '/.gitkeep';
if (!is_file($gitkeep) && @file_put_contents($gitkeep, '') === false) {
    fwrite(STDERR, 'Cannot write ' . $gitkeep . "\n");
    exit(1);
}
fwrite(STDOUT, "Storage ready: " . PROFILE_STORAGE . "\n");

fwrite(STDOUT, $applied === 0 ? "Database already up to date.\n" : "Database updated ({$applied} change(s)).\n");

// Extra poster themes are CSS-only for visitors, but they are seeded here so
// administrators can also pick them as a card's default theme.
$themes = [
    ['violet-pulse', 'Violet Pulse'],
    ['emerald-watch', 'Emerald Watch'],
];
try {
    $themeInsert = $pdo->prepare('INSERT IGNORE INTO card_themes (theme_key, theme_name) VALUES (?, ?)');
    foreach ($themes as [$key, $name]) {
        $themeInsert->execute([$key, $name]);
    }
    fwrite(STDOUT, "Poster themes checked: " . count($themes) . "\n");
} catch (Throwable $error) {
    fwrite(STDERR, 'Could not add poster themes: ' . $error->getMessage() . "\n");
}
