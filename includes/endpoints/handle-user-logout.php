<?php
    session_start();
    if (session_destroy()) {
        echo(json_encode([
            "status" => 200,
            "message" => "User session destroyed"
        ]));
        http_response_code(200);
        die();
    } else {
        echo(json_encode([
            "status" => 500,
            "message" => "Cannot destroy session",
            "data" => [
                "error_message" => "Something went wrong, please try again later"
            ]
        ]));
        http_response_code(500);
        die();
    }
?>