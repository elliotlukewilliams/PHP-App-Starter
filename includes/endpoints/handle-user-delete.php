<?php
    // Include User class
    require_once(__DIR__ . "/../classes/class-user.php");

    init_app();
    require_same_origin_post();

    try {
        // Only a logged in user can delete an account, and only their own. The user is always
        // taken from the session, never from the request, so one user can't delete another
        $current_user = get_logged_in_user();
        if (!$current_user || empty($current_user["id"])) {
            json_response(401, "User not logged in", [
                "error_message" => "You need to be logged in to delete your account. Please <a href=\"/login\">log in</a> and try again."
            ]);
        }

        // Delete user
        $user_obj = new User();
        if (!$user_obj->delete((int) $current_user["id"])) {
            json_response(500, "Failed to delete user", [
                "error_message" => "Sorry, we were unable to delete your account. Please try again later"
            ]);
        }

        // Log the user out now their account no longer exists. Don't fail the request if this goes wrong,
        // as the account is already deleted and get_logged_in_user() won't find it
        if (!end_session()) {
            debug_log("Unable to destroy session after deleting user {$current_user["id"]}");
        }

        // Account deleted, return success
        json_response(200, "User account deleted");
    } catch (Throwable $error) {
        json_server_error($error);
    }
?>
