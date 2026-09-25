<?php
    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    try {
        // Get/check profile edit data
        $submit_data = $_POST ?? [];
        if (empty($submit_data) && empty($_FILES["image_file"])) {
            echo(json_encode([
                "status" => 400,
                "message" => "Profile data missing in request body",
                "data" => [
                    "error_field" => null,
                    "error_message" => "Sorry, something went wrong. Please try again later"
                ]
            ]));
            http_response_code(400);
            die();
        }

        // Start session to check currently logged in user
        session_start();
        
        // Get current user from session data
        $current_user = get_logged_in_user();
        if (!get_logged_in_user()) {
            echo(json_encode([
                "status" => 401,
                "message" => "User not logged in",
                "data" => [
                    "error_field" => null,
                    "error_message" => "You do not have permission to edit this user"
                ]
            ]));
            http_response_code(401);
            die();
        }

        // Upload submitted image and merge url into data
        if (!empty($_FILES["image_file"]["name"])) {
            $image_url = upload_image("image_file");
            if ($image_url instanceof Error) {
                echo(json_encode([
                    "status" => 500,
                    "data" => [
                        "error_field" => null,
                        "error_message" => $image_url->getMessage()
                    ]
                ]));
                http_response_code(500);
                die();
            }
            $submit_data["image_url"] = $image_url;
        } elseif (!empty($_FILES["image_file"])) {
            
            // Remove user image if the currently have one but are requesting removal
            $submit_data["image_url"] = "";
        }
    
        // Update user info
        $user_obj = new User();
        $updated_user = $user_obj->update($current_user["email"], $submit_data);

        if (!$updated_user) {
            echo(json_encode([
                "status" => 500,
                "message" => "Failed to update user",
                "data" => [
                    "error_message" => "Sorry, something went wrong. Please try again later"
                ]
            ]));
            http_response_code(500);
            die();
        }

        // Profile edited, return success
        echo(json_encode([
            "status" => 200,
            "message" => "Profile update success"
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