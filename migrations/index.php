<?php
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../includes/functions.php");

global $db_connection;

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

init_db();

// Ensure migrations table exists
$db_connection->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration_name VARCHAR(255) NOT NULL,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Get all migration files recursively
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__)
);

// Get our migration sript names for reference
$migrations = [];
foreach ($files as $file) {
    if ($file->getExtension() === "php" && $file->getFilename() !== "index.php") {
        $migrations[] = $file->getRealPath();
    }
}

// Sort migrations (important!)
sort($migrations);

foreach ($migrations as $migration) {
    $migrationName = basename($migration);

    $stmt = $db_connection->prepare("SELECT COUNT(*) FROM migrations WHERE migration_name = ?");
    $stmt->execute([$migrationName]);

    // First check migration ha snot already ran to prevent creating same tables more than once
    if ($stmt->fetchColumn() == 0) {
        echo("Running: {$migrationName}\n");

        require $migration;

        // Log that the script ran
        $stmt = $db_connection->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
        $stmt->execute([$migrationName]);

        echo("Completed: {$migrationName}\n");
    }
}

?>