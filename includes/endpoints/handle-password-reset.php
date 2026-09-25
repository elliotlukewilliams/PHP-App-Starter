<?php
    // Include User class
    require_once(__DIR__ . "/../classes/class-user.php");

    init_app();
    require_same_origin_post();

    try {
        // Get/check new password in request
        $submit_data = json_decode(file_get_contents("php://input"), true);
        if (empty($submit_data) || !is_array($submit_data)) {
            json_response(400, "Form data missing in request body", [
                "error_message" => "New password is a required field"
            ]);
        }

        // Merge expected keys
        $submit_data = array_merge([
            "unique_token" => "",
            "password" => "",
            "password_confirm" => ""
        ], $submit_data);
        $token = $submit_data["unique_token"];
        $password = (string) $submit_data["password"];
        $password_confirm = (string) $submit_data["password_confirm"];

        // Check we have a token
        if (empty($token) || !is_string($token)) {
            json_response(400, "Unique token missing in request", [
                "error_message" => "Unique token missing in request"
            ]);
        }

        // Check passwords are the same
        if ($password !== $password_confirm) {
            json_response(400, "Passwords do not match", [
                "error_field" => "new_password_confirm",
                "error_message" => "Passwords do not match"
            ]);
        }

        $user_obj = new User();
        $password_reset_result = $user_obj->reset_password($token, $password);
        if ($password_reset_result !== true) {
            json_response(400, "Unable to update user password", [
                "error_message" => $password_reset_result instanceof Error
                    ? $password_reset_result->getMessage()
                    : "Sorry, something went wrong. Please try again later"
            ]);
        }

        // Return success
        json_response(200, "Password reset successful. <a href=\"/login\">Log in</a>.");
    } catch (Throwable $error) {
        json_server_error($error);
    }
?>
