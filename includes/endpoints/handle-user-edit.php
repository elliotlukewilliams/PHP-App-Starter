<?php
    // Include User class
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/classes/class-user.php");

    init_app();
    require_same_origin_post();

    try {
        // Get current user from session data
        $current_user = get_logged_in_user();
        if (!$current_user) {
            json_response(401, "User not logged in", [
                "error_field" => null,
                "error_message" => "You do not have permission to edit this user"
            ]);
        }

        // Only accept the profile fields users may edit, with their max lengths. Anything else
        // (e.g. password or image_url) is ignored so it can't be set through this endpoint
        $editable_fields = [
            "first_name" => 100,
            "last_name"  => 100,
            "bio"        => 200
        ];
        $submit_data = [];
        foreach ($editable_fields as $field_name => $max_length) {
            if (!isset($_POST[$field_name]) || !is_string($_POST[$field_name])) {
                continue;
            }
            $value = trim($_POST[$field_name]);
            if (mb_strlen($value) > $max_length) {
                json_response(400, "Field too long", [
                    "error_field" => $field_name,
                    "error_message" => "Must be {$max_length} characters or fewer"
                ]);
            }
            $submit_data[$field_name] = $value;
        }

        if (empty($submit_data) && empty($_FILES["image_file"])) {
            json_response(400, "Profile data missing in request body", [
                "error_field" => null,
                "error_message" => "Sorry, something went wrong. Please try again later"
            ]);
        }

        // Upload submitted image and merge url into data
        if (!empty($_FILES["image_file"]["name"])) {
            $image_url = upload_image("image_file");
            if ($image_url instanceof Error) {
                json_response(400, "Image upload failed", [
                    "error_field" => null,
                    "error_message" => $image_url->getMessage()
                ]);
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
            json_response(500, "Failed to update user", [
                "error_message" => "Sorry, something went wrong. Please try again later"
            ]);
        }

        // Profile edited, return success
        json_response(200, "Profile update success");
    } catch (Throwable $error) {
        json_server_error($error);
    }
?>
