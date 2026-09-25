<?php
    // Get utility functions
    require_once(__DIR__ . "/../functions.php");

    init_app();
    require_same_origin_post();

    if (end_session()) {
        json_response(200, "User session destroyed");
    }
    json_response(500, "Cannot destroy session", [
        "error_message" => "Something went wrong, please try again later"
    ]);
?>
