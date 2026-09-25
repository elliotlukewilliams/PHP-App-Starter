<?php 
global $db_connection;

// Depends on the users table (foreign key), so must run after it
$db_connection->exec("
    CREATE TABLE password_reset_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token CHAR(64) NOT NULL UNIQUE, -- SHA-256 hash of the emailed token
        created_at INT,
        INDEX (email),
        FOREIGN KEY (email) REFERENCES users(email) ON UPDATE CASCADE ON DELETE CASCADE
    )
");
