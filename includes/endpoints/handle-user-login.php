<?php
    // Include User class
    require_once(__DIR__ . "/../classes/class-user.php");

    init_app();
    require_same_origin_post();

    try {
        // Get/check login data
        $submit_data = json_decode(file_get_contents("php://input"), true);
        if (empty($submit_data) || !is_array($submit_data)) {
            json_response(400, "Login data missing in request body", [
                "error_field" => null,
                "error_message" => "Sorry, something went wrong. Please try again later"
            ]);
        }

        // Merge expected keys
        $submit_data = array_merge([
            "email" => "",
            "password" => ""
        ], $submit_data);
        $email = (string) $submit_data["email"];
        $password = (string) $submit_data["password"];

        // Log user in, return errors if not successful
        $user = new User();
        $user_logged_in = $user->login($email, $password);
        if ($user_logged_in instanceof Error) {
            json_response(401, $user_logged_in->getMessage(), [
                "error_field" => null,
                "error_message" => $user_logged_in->getMessage()
            ]);
        }

        // User session started, return success
        json_response(200, "Login success", [
            "user" => $user_logged_in
        ]);
    } catch (Throwable $error) {
        json_server_error($error);
    }
?>
