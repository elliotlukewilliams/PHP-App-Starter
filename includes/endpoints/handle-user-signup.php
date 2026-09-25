<?php
    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    init_app();
    require_same_origin_post();

    try {
        // Get/check signup data
        $submit_data = json_decode(file_get_contents("php://input"), true);
        if (empty($submit_data) || !is_array($submit_data)) {
            json_response(400, "Signup data missing in request body", [
                "error_field" => null,
                "error_message" => "Sorry, something went wrong. Please try again later"
            ]);
        }

        // Merge expected keys
        $submit_data = array_merge([
            "email" => "",
            "password" => "",
            "password_confirm" => ""
        ], $submit_data);
        $email = (string) $submit_data["email"];
        $password = (string) $submit_data["password"];
        $password_confirm = (string) $submit_data["password_confirm"];

        // Check passwords are the same
        if ($password !== $password_confirm) {
            json_response(400, "Passwords do not match", [
                "error_field" => "password_confirm",
                "error_message" => "Passwords do not match"
            ]);
        }

        // Create new user
        $user_obj = new User();
        $user = $user_obj->create($email, $password);
        if ($user instanceof Error) {
            json_response(400, $user->getMessage(), [
                "error_field" => match($user->getCode()) {
                    1 => "email",
                    2 => "password",
                    default => ""
                },
                "error_message" => $user->getMessage()
            ]);
        }

        // Return success
        json_response(200, "User account created. <a href=\"/login\">Log in</a>", [
            "user" => $user
        ]);
    } catch (Throwable $error) {
        json_server_error($error);
    }
?>
