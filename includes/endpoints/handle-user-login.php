<?php
    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    try {
        // Get/check signup data
        $submit_data = json_decode(file_get_contents("php://input"), true);
        if (empty($submit_data) || !is_array($submit_data)) {
            echo(json_encode([
                "status" => 400,
                "message" => "Login data missing in request body",
                "data" => [
                    "error_field" => null,
                    "error_message" => "Sorry, something went wrong. Please try again later"
                ]
            ]));
            http_response_code(400);
            die();
        }
        
        // Merge expected keys
        $submit_data = array_merge([
            "email" => "",
            "password" => ""
        ], $submit_data);
        $email = $submit_data["email"];
        $password = $submit_data["password"];

        // Log user in, return errors if not successful
        $user = new User();
        $user_logged_in = $user->login($email, $password);
        if ($user_logged_in instanceof Error) {
            echo(json_encode([
                "status" => 401,
                "message" => $user_logged_in->getMessage(),
                "data" => [
                    "error_field" => match($user_logged_in->getCode()) { 
                        1 => "login_email", 
                        2 => "login_password", 
                        default => "" 
                    },
                    "error_message" => $user_logged_in->getMessage()
                ]
            ]));
            http_response_code(401);
            die();
        }

        // User session started, return success
        echo(json_encode([
            "status" => 200,
            "message" => "Login success",
            "data" => [
                "user" => $user_logged_in
            ]
        ]));
        http_response_code(200);
        die();

    } catch (Error $error) {
        echo(json_encode([
            "status" => 500,
            "message" => $error->getMessage(),
            "data" => [
                "error_field" => null,
                "error_message" => "Sorry, something went wrong. Please try again later"
            ]
        ]));
        http_response_code(500);
        die();
    }
?>