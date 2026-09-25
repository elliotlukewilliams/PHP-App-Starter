<?php
    // Get ulility functions
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/functions.php");

    // Check request origin for security
    if (!is_http_same_site_origin()) {
        http_response_code(403);
        die();
    }

    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    try {
        // Get/check signup data
        $submit_data = json_decode(file_get_contents("php://input"), true);
        if (empty($submit_data) || !is_array($submit_data)) {
            echo(json_encode([
                "status" => 400,
                "message" => "Signup data missing in request body",
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
            "password" => "",
            "password_confirm" => ""
        ], $submit_data);
        $email = $submit_data["email"];
        $password = $submit_data["password"];
        $password_confirm = $submit_data["password_confirm"];

        // Check passwords are the same
        if ($password !== $password_confirm) {
            echo(json_encode([
                "status" => 400,
                "message" => "Passwords do not match",
                "data" => [
                    "error_field" => "password_confirm",
                    "error_message" => "Passwords do not match"
                ]
            ]));
            http_response_code(400);
            die();
        }
    
        // Create new user
        $user_obj = new User();
        $user = $user_obj->create($email, $password);
        if ($user instanceof Error) {
            echo(json_encode([
                "status" => 500,
                "message" => $user->getMessage(),
                "data" => [
                    "error_field" => match($user->getCode()) { 
                        1 => "email", 
                        2 => "password", 
                        default => "" 
                    },
                    "error_message" => $user->getMessage()
                ]
            ]));
            http_response_code(500);
            die();
        }

        // Return success
        echo(json_encode([
            "status" => 200,
            "message" => "User account created. <a href=\"/login\">Log in</a>",
            "data" => [
                "user" => $user
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