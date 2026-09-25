<?php
    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    init_app();
    require_same_origin_post();

    try {
        // Get/check email in request
        $user_email = json_decode(file_get_contents("php://input"), true);
        if (empty($user_email) || !is_string($user_email)) {
            json_response(400, "Email missing in request body", [
                "error_message" => "Email address is a required field"
            ]);
        }

        // Verify email address is valid
        $user_email = filter_var($user_email, FILTER_VALIDATE_EMAIL);
        if (!$user_email) {
            json_response(400, "Email address not valid", [
                "error_message" => "Email address not valid"
            ]);
        }

        // Generate token and send to user
        $user_obj = new User();
        $email_sent = $user_obj->send_password_reset($user_email);
        if ($email_sent instanceof Error || $email_sent !== true) {
            // Log the failure but respond the same way, so the response never reveals whether the account exists
            debug_log("Password reset email failed: " . ($email_sent instanceof Error ? $email_sent->getMessage() : var_export($email_sent, true)));
        }

        // Return success
        json_response(200, "If an account exists for that email address, a password reset link has been sent");
    } catch (Throwable $error) {
        json_server_error($error);
    }
?>
