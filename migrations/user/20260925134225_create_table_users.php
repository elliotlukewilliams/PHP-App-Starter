<?php 
global $db_connection;

$db_connection->exec("
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255),
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        bio LONGTEXT,
        image_url VARCHAR(255),
        created_at INT
    )
");
