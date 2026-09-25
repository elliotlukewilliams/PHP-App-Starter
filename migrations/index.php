<?php
/**
 * Migration runner. Run with: docker-compose exec web php migrations/index.php
 *
 * Migrations live in sub folders grouped by table (e.g. migrations/user/) and must be named
 * YYYYMMDDHHMMSS_description.php. They run in timestamp order across all folders, so a migration
 * that depends on another table (e.g. a foreign key) just needs a later timestamp.
 * Each migration is recorded by its path relative to this folder and only ever runs once.
 */
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../includes/functions.php");

global $db_connection;

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->safeLoad(); // Env may already be provided by the container

init_db();

// Ensure migrations table exists
$db_connection->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration_name VARCHAR(255) NOT NULL UNIQUE,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Get all migration files recursively
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS)
);

// Get our migration script names for reference, keyed by path relative to this folder
$migrations = [];
foreach ($files as $file) {
    if ($file->getExtension() !== "php" || $file->getRealPath() === __FILE__) {
        continue;
    }
    $migration_name = substr($file->getRealPath(), strlen(__DIR__) + 1);
    if (!preg_match("/^\d{14}_[a-z0-9_]+\.php$/", $file->getFilename())) {
        fwrite(STDERR, "Invalid migration file name: {$migration_name}. Expected YYYYMMDDHHMMSS_description.php\n");
        exit(1);
    }
    $migrations[$migration_name] = $file->getRealPath();
}

// Sort by file name (timestamp first) so migrations run in the order they were written, regardless of folder
uksort($migrations, fn($a, $b) => strcmp(basename($a), basename($b)) ?: strcmp($a, $b));

foreach ($migrations as $migration_name => $migration_path) {
    $stmt = $db_connection->prepare("SELECT COUNT(*) FROM migrations WHERE migration_name = ?");
    $stmt->execute([$migration_name]);

    // First check migration has not already run to prevent creating same tables more than once
    if ($stmt->fetchColumn() == 0) {
        echo("Running: {$migration_name}\n");

        require $migration_path;

        // Log that the script ran
        $stmt = $db_connection->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
        $stmt->execute([$migration_name]);

        echo("Completed: {$migration_name}\n");
    }
}

echo("Migrations up to date\n");
