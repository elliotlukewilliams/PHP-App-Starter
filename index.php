<?php
    // Set same-site cookie restrictions
    session_set_cookie_params([
        "secure"   => true,
        "httponly" => true,
        "samesite" => "Strict"
    ]);

    // Start session
    session_start();

    // Include utlity functions
    require_once("includes/functions.php");

    // Connect DB
    init_db();

    // Include User class
    require_once("includes/classes/class-user.php");
    
    // Dynamic page routing
    $uri = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
    if ($uri === "") {
        require("pages/home.php");
    } elseif (file_exists(__DIR__ . "/pages/{$uri}.php")) {
        require(__DIR__ . "/pages/{$uri}.php");
    } else {
        http_response_code(404);
        require("pages/404.php");
    }
