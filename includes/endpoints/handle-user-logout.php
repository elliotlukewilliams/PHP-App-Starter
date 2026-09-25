<?php
    // Get utility functions
    require_once(__DIR__ . "/../functions.php");

    init_app();
    require_same_origin_post();

    // Clear session data and expire the session cookie before destroying the session
    $_SESSION = [];
    $cookie_params = session_get_cookie_params();
    setcookie(session_name(), "", [
        "expires"  => time() - 3600,
        "path"     => $cookie_params["path"],
        "domain"   => $cookie_params["domain"],
        "secure"   => $cookie_params["secure"],
        "httponly" => $cookie_params["httponly"],
        "samesite" => $cookie_params["samesite"]
    ]);

    if (session_destroy()) {
        json_response(200, "User session destroyed");
    }
    json_response(500, "Cannot destroy session", [
        "error_message" => "Something went wrong, please try again later"
    ]);
?>
