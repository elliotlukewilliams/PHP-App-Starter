<?php 
global $db_connection;

$db_connection->exec("
    CREATE TABLE password_reset_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(225) NOT NULL,
        created_at INT
    )");