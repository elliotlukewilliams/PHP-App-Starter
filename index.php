<?php
    // Include utlity functions
    require_once("includes/functions.php");

    // Error display, security headers and hardened session
    init_app();

    // Connect DB
    init_db();

    // Include User class
    require_once("includes/classes/class-user.php");

    // Dynamic page routing. Only allow simple slug paths so the URI can't traverse outside /pages
    $uri = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
    $is_valid_route = preg_match("/^[a-z0-9_-]+(\/[a-z0-9_-]+)*$/i", $uri);
    if ($uri === "") {
        require("pages/home.php");
    } elseif ($is_valid_route && file_exists(__DIR__ . "/pages/{$uri}.php")) {
        require(__DIR__ . "/pages/{$uri}.php");
    } else {
        http_response_code(404);
        require("pages/404.php");
    }
