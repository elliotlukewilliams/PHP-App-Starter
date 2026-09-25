<?php
    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    try {
        // Get/check new password in request
        $submit_data = json_decode(file_get_contents("php://input"), true);
        if (empty($submit_data) || !is_array($submit_data)) {
            echo(json_encode([
                "status" => 400,
                "message" => "Form data missing in request body",
                "data" => [
                    "error_message" => "New password is a required field"
                ]
            ]));
            http_response_code(400);
            die();
        }

        // Merge expected keys
        $submit_data = array_merge([
            "unique_token" => "",
            "new_password" => "",
            "new_password_confirm" => ""
        ], $submit_data);
        $token = $submit_data["unique_token"];
        $password = $submit_data["password"];
        $password_confirm = $submit_data["password_confirm"];

        // Check we have a token
        if (empty($token)) {
            echo(json_encode([
                "status" => 400,
                "message" => "Unique token missing in request",
                "data" => [
                    "error_message" => "Unique token missing in request"
                ]
            ]));
            http_response_code(400);
            die();
        }

        // Check passwords are the same
        if ($password !== $password_confirm) {
            echo(json_encode([
                "status" => 400,
                "message" => "Passwords do not match",
                "data" => [
                    "error_field" => "new_password_confirm",
                    "error_message" => "Passwords do not match"
                ]
            ]));
            http_response_code(400);
            die();
        }

        $user_obj = new User();
        $password_reset_result = $user_obj->reset_password($token, $password);
        if ($password_reset_result instanceof Error) {
            echo(json_encode([
                "status" => 500,
                "message" => "Unable to update user password",
                "data" => [
                    "error_message" => $password_reset_result->getMessage()
                ]
            ]));
            http_response_code(500);
            die();
        }
        
        // Return success
        echo(json_encode([
            "status" => 200,
            "message" => "Password reset successful. <a href=\"/login\">Log in</a>."
        ]));
        http_response_code(200);
        die();
    } catch (Error $error) {
        echo(json_encode([
            "status" => 500,
            "message" => $error->getMessage(),
            "data" => [
                "error_message" => "Sorry, something went wrong. Please try again later"
            ]
        ]));
        http_response_code(500);
        die();
    }
?>