<?php
    // Get ulility functions
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/includes/functions.php");

    class User {

        public $id = -1;
        public string | null $email = null;
        public string | null $first_name = null;
        public string | null $last_name = null;
        public string | null $bio = null;
        public string | null $image_url = null;
        public int $created_at = -1;
        public bool $is_logged_in = false;

        /**
         * Utility function to validate a password. Regex patter kep here as a source of truth to validate against
         * @param password - pssword to validate
         * @return boolean
         */
        public function validate_password(string $password) {
            return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $password);
        }

        /**
         * Create a new user in the database
         * @return this/Error - Returns own instance with updated propterties on success, Error on failtue
         */
        public function create(string $email, string $password) {
            /* Get/check db connection */
            global $db_connection;
            init_db();

            /* Sanitize and validate email field */
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (!$email) {
                return new Error(message: "Invalid email", code: 1);
            }

            /* Validate password */
            if (!$this->validate_password($password)) {
                return new Error(message: "Password too weak!", code: 2);
            }

            /* Hash password */
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            if (empty($password_hash)) {
                return new Error(message: "Password encryption failed", code: 2);
            }
    
            /* Check if user already exists in database */
            if ($this->get(user_email_or_id: $email)) {
                return new Error(message: "User with the email address \"{$email}\" already exists", code: 1);
            }

            /* Insert user into database (only populate minimum required fields) */
            $sql = "
                INSERT INTO users (email, password, created_at)
                VALUES (:email, :password, :created_at)
            ";
            $created_at = time();
            $stmt = $db_connection->prepare($sql);
            $user_created = $stmt->execute([
                ":email" => $email,
                ":password" => $password_hash,
                ":created_at" => $created_at
            ]);

            // Update properties of current instance and return self as new user
            if ($user_created) {
                $this->email = $email;
                $this->created_at = $created_at;
                return $this;
            }
            return new Error(message: "Sorry, unable to create new user. Please try again later");
        }

        /**
         * Gets a user by their unique identifier which can be either an email address or user id
         * @param user_email_or_id
         * @return array - The User data if found in the database or false if not
         */
        public function get(string | int $user_email_or_id) {
            /* Get/check db connection */
            global $db_connection;
            init_db();

            // Check Users table exists
            if (!table_exists("users")) {
                return false;
            }

            // Set SQL
            $field = is_int($user_email_or_id) ? "id" : "email";
            $sql = "SELECT * FROM users WHERE {$field} = :{$field}";

            // Prepare statement
            $stmt = $db_connection->prepare($sql);
    
            // Fetch from db
            $stmt->execute([":{$field}" => $user_email_or_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        }

        /**
         * Updates a user by a specific field of a specific value
         * @param email - the user to be updated
         * @param fields - associative array with key/value pairs for field name and value
         * @return Bool - True on successful update, false on failure
         */
        public function update(string $email, array $fields) {
            try {
                global $db_connection;
                init_db();

                // Safelist allowed fields
                $allowed_fields = ["password", "first_name", "last_name", "bio", "image_url"];
                $fields_to_update = [];
                foreach ($fields as $name => $value) {
                    if (!in_array($name, $allowed_fields)) {
                        continue;
                    }
                    $fields_to_update[$name] = $value;
                }

                // No valid fields to update
                if (empty($fields_to_update)) {
                    debug_log("2");
                    return false;
                }

                // Build SET clause and params
                $set_parts = [];
                $params = [":email" => $email];

                foreach ($fields_to_update as $key => $value) {
                    // Check if request is to update password and hash it if not done externally
                    if ($key === "password" && !password_get_info($value)["algo"]) {
                        $value = password_hash($value, PASSWORD_DEFAULT);
                    }

                    $set_parts[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }

                $set_str = implode(", ", $set_parts);

                // Prepare and execute
                $sql = "UPDATE users SET {$set_str} WHERE email = :email";
                $stmt = $db_connection->prepare($sql);
                $stmt->execute($params);

                // Check row count
                if ($stmt->rowCount() < 1) {
                    return false;
                }

                return true;
            } catch(Exception $error) {
                debug_log($error->getMessage());
                return false;
            }
        }

        /**
         * Delete user in database
         * @param user_email_or_id
         * @return bool - True on successful deletion or false on failue
         */
        public function delete(string | int $user_email_or_id) {
            /* Get/check db connection */
            global $db_connection;
            init_db();

            // Set SQL
            $field = is_int($user_email_or_id) ? "id" : "email";
            $sql = "DELETE FROM users WHERE {$field} = :{$field}";

            // Prepare statement
            $stmt = $db_connection->prepare($sql);
    
            // Fetch from db
            return $stmt->execute([":{$field}" => $user_email_or_id]);
        }

        /**
         * Set session login for an existing user
         * @param email - Any valid email address to reference existing user by
         * @param password - The un-hashed user password
         * @return this/Error - Returns updates user object on success, false on failure
         */
        public function login(string $email, string $password) {
            // Check if user exists in database
            $searched_user = $this->get($email);
            if (!$searched_user) {
                return new Error(message: "User with the email address {$email} does not exist", code: 1);
            }

            // Excplicitly check array keys for important info
            if (empty($searched_user["id"]) || empty($searched_user["password"])) {
                return new Error(message: "Error: Unexpected result returned fron database");
            }

            // Merge expected keys in user for additional info
            $searched_user = array_merge([
                "email" => $email,
                "first_name" => "",
                "last_name" => "",
                "bio" => "",
                "image_url" => ""
            ], $searched_user);

            // Hash password and check if match
            if (!password_verify($password, $searched_user["password"])) {
                return new Error(message: "Incorrect password", code: 2);
            }
            
            // User exists + password matches, start session
            if (session_start()) {
                $this->is_logged_in = true;

                // Set user properties
                $this->id = $searched_user["id"];
                $this->email = $searched_user["email"];
                $this->first_name = $searched_user["first_name"];
                $this->last_name = $searched_user["last_name"];
                $this->bio = $searched_user["bio"];
                $this->image_url = $searched_user["image_url"];

                // Set session current user
                $_SESSION["current_user_id"] = $this->id;

                // Return updated instance on success
                return $this;
            } else {
                // Edge-case 
                return new Error(message: "Error: unable to start user session");
            }
        }

        /**
         * Send a password reset token to an existing user via email
         * @param email - Email address of existing user
         * @return true|Error - Returns true if email sent successfully or Error object on failure
         */
        public function send_password_reset(string $email) {            
            /* Get/check db connection */
            global $db_connection;
            init_db();

            // First check if user exists
            if (!$this->get($email)) {
                return new Error("User with this email address does not exists", 401);
            }
            // Generate random token to send in email
            $token = bin2hex(random_bytes(50));
            
            // Create a hashed token to store in the DB
            $token_hash = hash("sha256", $token);

            // Check if there are already other existing password-reset-requests made by this user
            $sql = "SELECT * FROM password_reset_requests WHERE email = :email";
            $stmt = $db_connection->prepare($sql);
            $stmt->execute([":email" => $email]);
            $existing_rows = $stmt->fetchAll();

            // If there are existing pending password-reset-requests, delete them before creating the new one
            if (count($existing_rows)) {
                $sql = "DELETE FROM password_reset_requests WHERE email = :email";
                $stmt = $db_connection->prepare($sql);
                $rows_deleted = $stmt->execute([":email" => $email]);
                if (!$rows_deleted) {
                    return new Error("Error: duplicate reset requests found. Please try again later.", 500);
                }
            }

            // Add new row to table referencing the current user email token
            $created_at = time();
            $sql = "
                INSERT INTO password_reset_requests (email, token, created_at)
                VALUES (:email, :token, :created_at)
            ";
            // Execute actual user email on prepared statement
            $stmt = $db_connection->prepare($sql);
            $password_Reset_request_created = $stmt->execute([
                ":email"      => $email,
                ":token"      => $token_hash,
                ":created_at" => $created_at
            ]);
            if (!$password_Reset_request_created) {
                return new Error("Unable to create reset request", 500);
            }
            
            // Set up email content
            $subject = "Reset your password";
            $to = $email;
            $link = "{$_SERVER["HTTP_HOST"]}/reset-password?token={$token}";
            $message = "
                <p><a href=\"{$link}\" target=\"_blank\">Click here</a> to reset your password.</p>
                <p>This link will expire in one hour. If you did not request to reset your password, please disregard.</p>
            ";

            // Send email
            $mail_sent = send_email($subject, $to, $message);
            return $mail_sent;
        }

        /**
         * Resets a users password by referencing a unique token
         * @param token - The unique token that was sent to the user via email
         * @param password - The new password to replace the forgotten one
         * @return true|Error - Returns true on success or Error object on failure
         */
        public function reset_password(string $token, string $password) {
            /* Get/check db connection */
            global $db_connection;
            init_db();

            /* Validate password */
            if (!$this->validate_password($password)) {
                return new Error(message: "Password too weak!");
            }

            // Hash the token to match against item in DB
            $token_hash = hash("sha256", $token);

            // Search token and get associated user
            $sql = "SELECT * FROM password_reset_requests WHERE token = :token";
            $stmt = $db_connection->prepare($sql);
            $stmt->execute([":token" => $token_hash]);
            $found_row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$found_row) {
                return new Error(message: "
                    This password reset token has already been used. 
                    Please <a href=\"/login\">try again</a>.
                ");
            }

            // Merge expected keys
            $found_row = array_merge([
                "email" => "",
                "token" => "",
                "created_at" => 0
            ], $found_row);

            // Check if token has expired (> 1hr)
            $now = time();
            $one_hour = 60*60;
            $token_created_at = $found_row["created_at"];
            if ($token_created_at + $one_hour < $now) {
                return new Error(message: "
                    Password reset token has expired. 
                    Please <a href=\"/log-in\">try again</a>.
                ");
            }

            // Get/check associated user
            $user_email = $found_row["email"];
            $user = $this->get($found_row["email"]);
            if (!$user) {
                return new Error(message: "
                    No found user associated with this reset token. 
                    Please <a href=\"/log-in\">try again</a>.
                ");
            }

            // Now that the user has been found we can delete the password-reset-request row in the DB
            $sql = "DELETE FROM password_reset_requests WHERE email = :email";
            $stmt = $db_connection->prepare($sql);
            $rows_deleted = $stmt->execute([":email" => $user_email]);
            if (!$rows_deleted) {
                return new Error("Error: unable to remove expired password reset request.", 500);
            }

            /* Hash password */
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            if (empty($password_hash)) {
                return new Error(message: "Password encryption failed");
            }

            // Udate user password
            return $this->update($user_email, ["password" => $password_hash]);
        }
    }
?>