<?php
    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    try {
        // Get/check email in request
        $user_email = json_decode(file_get_contents("php://input"), true);
        if (empty($user_email)) {
            echo(json_encode([
                "status" => 400,
                "message" => "Email missing in request body",
                "data" => [
                    "error_message" => "Email address is a required field"
                ]
            ]));
            http_response_code(400);
            die();
        }
        
        // Verify email address is valid
        $user_email = filter_var($user_email, FILTER_VALIDATE_EMAIL);
        if (!$user_email) {
            echo(json_encode([
                "status" => 400,
                "message" => "Email address not valid",
                "data" => [
                    "error_message" => "Email address not valid"
                ]
            ]));
            http_response_code(400);
            die();
        }

        // Generate token and send to user
        $user_obj = new User();
        $email_sent = $user_obj->send_password_reset($user_email);
        if ($email_sent instanceof Error) {
            $error_message = $email_sent->getMessage();
            echo(json_encode([
                "status" => $email_sent->getCode(),
                "message" => $error_message,
                "data" => [
                    "error_message" => $error_message
                ]
            ]));
            http_response_code(500);
            die();
        } elseif (!$email_sent) {
            echo(json_encode([
                "status" => 500,
                "message" => "Email send failure",
                "data" => [
                    "error_message" => "Unable to send password reset link. Please try again later."
                ]
            ]));
            http_response_code(500);
        }

        // Return success
        echo(json_encode([
            "status" => 200,
            "message" => "Password reset email sent"
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