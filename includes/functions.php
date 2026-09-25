<?php
    // Project root directory.
    if (!defined("APP_ROOT")) {
        define("APP_ROOT", dirname(__DIR__));
    }

    // Includes
    require_once(APP_ROOT . "/includes/classes/class-user.php");

    // Namespace
    use PHPMailer\PHPMailer\PHPMailer;
    
    /**
     * Get an environment variable. Checks $_ENV and $_SERVER (populated by Dotenv) as well as
     * getenv() (populated by Docker) so values are found however they were loaded
     * @param key - The environment variable name
     * @param default - Returned if the variable isn't set
     * @return string|null
     */
    function env(string $key, ?string $default = null): ?string {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return ($value === false || $value === null) ? $default : (string) $value;
    }

    /**
     * Check if current environment is development
     * @return boolean
     */
    function is_dev() {
        return env("APP_ENV") === "local";
    }

    /**
     * Bootstraps a request. Call at the top of every entry point (index.php and each endpoint)
     * so they all share the same error display, security headers and session settings
     * @return void
     */
    function init_app(): void {
        // Never leak stack traces, file paths or DB errors outside of development
        ini_set("display_errors", is_dev() ? "1" : "0");

        send_security_headers();
        start_session();
    }

    /**
     * Send baseline security headers
     * @return void
     */
    function send_security_headers(): void {
        if (headers_sent()) {
            return;
        }
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("Content-Security-Policy: frame-ancestors 'none'");
        // Keeps password reset tokens in the URL from leaking to other sites via the Referer header
        header("Referrer-Policy: same-origin");
    }

    /**
     * Start the session with hardened cookie settings
     * @return void
     */
    function start_session(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        // Reject session ids that weren't issued by the server (prevents session fixation)
        ini_set("session.use_strict_mode", "1");
        session_set_cookie_params([
            // Allow plain http only for local development
            "secure"   => !is_dev() || !empty($_SERVER["HTTPS"]),
            "httponly" => true,
            "samesite" => "Strict"
        ]);
        session_start();
    }

    /**
     * Escape a value for safe output in HTML text or attributes
     * @param value - The value to escape
     * @return string
     */
    function esc(mixed $value): string {
        return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    }

    /**
     * Send a JSON response in the standard endpoint format and end the request
     * @param status - HTTP status code, also mirrored in the response body
     * @param message - Response message
     * @param data - Optional data, e.g. error_field and error_message
     * @return never
     */
    function json_response(int $status, string $message, array $data = []): never {
        if (!headers_sent()) {
            http_response_code($status);
            header("Content-Type: application/json");
        }
        $body = ["status" => $status, "message" => $message];
        if (!empty($data)) {
            $body["data"] = $data;
        }
        echo(json_encode($body));
        exit();
    }

    /**
     * Guard for state-changing endpoints. Only allows POST requests from this site's own origin
     * @return void
     */
    function require_same_origin_post(): void {
        if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
            json_response(405, "Method not allowed", [
                "error_message" => "Method not allowed"
            ]);
        }
        if (!is_http_same_site_origin()) {
            json_response(403, "Forbidden", [
                "error_message" => "Sorry, something went wrong. Please try again later"
            ]);
        }
    }

    /**
     * Respond to an unexpected error without leaking internal details
     * @param error - The caught error/exception
     * @return never
     */
    function json_server_error(Throwable $error): never {
        debug_log($error->getMessage());
        json_response(500, "Server error", [
            "error_field" => null,
            "error_message" => "Sorry, something went wrong. Please try again later"
        ]);
    }

    /**
     * Checks an HTTP request comes from the current domain.
     */
    function is_http_same_site_origin(): bool {
        $allowed_origin = env("APP_ORIGIN");

        // Fail closed if not configured
        if (empty($allowed_origin)) {
            return false;
        }

        // Prefer Origin header
        $origin = $_SERVER["HTTP_ORIGIN"] ?? null;

        // Fallback to Referer if Origin missing
        if ($origin === null && isset($_SERVER["HTTP_REFERER"])) {
            $parts = parse_url($_SERVER["HTTP_REFERER"]);
            if (!empty($parts["scheme"]) && !empty($parts["host"])) {
                $origin = "{$parts["scheme"]}://{$parts["host"]}";
                if (!empty($parts["port"])) {
                    $origin .= ":{$parts["port"]}";
                }
            }
        }

        if ($origin === null) {
            return false;
        }

        // Normalize
        $origin         = rtrim(strtolower($origin), "/");
        $allowed_origin = rtrim(strtolower($allowed_origin), "/");

        return hash_equals($allowed_origin, $origin);
    }

    /**
     * Initiates connection to MYSQL database
     * @return void
     */
    function init_db() {
        // Instantiate $db_connection global
        global $db_connection;

        // Check PDO class exists
        if (!class_exists("PDO")) {
            throw new Exception("PDO class does not exist. Unable to connect to MYSQL database");
        }

        // Check if connection is already established
        if ($db_connection instanceof PDO) {
            return;
        }

        // Get credentials from ENV and connect
        $host = env("DB_HOST", "");
        $port = env("DB_PORT", "3306");
        $db_name = env("DB_NAME", "");
        $user = env("DB_USER", "");
        $password = env("DB_PASSWORD", "");
        $dsn = "mysql:host={$host};port={$port};dbname={$db_name};charset=utf8mb4";
        
        try {
            $db_connection = new PDO($dsn, $user, $password);
        } catch (PDOException $error) {
            throw new Exception("Error: " . $error->getMessage());
        }
    }

    /**
     * Gets the database connection, connecting first if needed
     * @return PDO
     */
    function get_db_connection(): PDO {
        global $db_connection;
        if (!$db_connection instanceof PDO) {
            init_db();
        }
        return $db_connection;
    }

    /**
     * Check a table exists in the database
     * @param table_name - The table name to search
     * @return bool - True if table exists, false if not or if database connection not established
    */
    function table_exists(string $table_name) {
        global $db_connection;
        if (!$db_connection instanceof PDO) {
            return false;
        }
        $stmt = $db_connection->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table_name]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Gets a current user from a session
     * @return false|array - False if the session is not set or the user cannot be found, the current user data on success
     */
    function get_logged_in_user() {
        $is_logged_in = isset($_SESSION["current_user_id"]);
        if (!$is_logged_in) {
            return false;
        }
        $user_obj = new User();
        $user_id = $_SESSION["current_user_id"];
        $logged_in_user = $user_obj->get($user_id);
        return $logged_in_user;
    }

    /**
     * Get a template file from the components folder and set dynamic args
     * @param path - The file path relative to the components folder (omit .php from path)
     * @param args - An array containing the template arguments
     * @return void
     */
    function get_component(string $path, array $args = []) {
        global $component_args;
        $component_args = $args;
        $absolute_path = APP_ROOT . "/components/{$path}.php";
        if (file_exists($absolute_path)) {
            include($absolute_path);
        }
    }

    /**
     * Get the contents of an SVG icon so it can be output inline
     * @param file_name - File name of the svg file in the icons folder (omit .svg)
     * @return string|false - The SVG file contents if found, false if not
     */
    function get_svg_icon(string $file_name) {
        $file_path = APP_ROOT . "/public/images/icons/{$file_name}.svg";
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        return false;
    }

    /**
     * Set default components args by merging expected keys
     * @param args - the array of component specific arguments to set defaults for
     * @return array - the merged defaults for the component arguments
     */
    function parse_component_args(array $args = []) {
        global $component_args;
        if (!is_array($component_args)) {
            $component_args = [];
        }
        $args = array_merge($args, $component_args);
        return $args;
    }

    /**
     * Send SMTP email with PHPMailer
     * @param subject - Email subject
     * @param to - recipient email address
     * @param message - email message (HTML)
     * @param from_address - email "from" address
     * @return bool|Error - True on success, false if not configured, Error on send failure
     */
    function send_email(string $subject, string $to, string $message, string $from_address = "") {
        // Include composer plugins for access to PHPMailer
        require_once(APP_ROOT . "/vendor/autoload.php");

        // Get SMTP server credentials from ENV
        $host =     env("SMTP_HOST");
        $port =     env("SMTP_PORT");
        $auth =     env("SMTP_AUTH") === "true";
        $user =     env("SMTP_USER");
        $password = env("SMTP_PASSWORD");

        // Check minimum credentials, if on localhost don't fail silently
        if (empty($host) || empty($port)) {
            if (is_dev()) {
                throw new Error("SMTP credentials missing. Unable to send mail.");
            }
            return false;
        }
        // Get from address from ENV if not set
        if (empty($from_address)) {
            $from_address = env("SMTP_FROM_ADDRESS", "");
        }

        // Attempt mail send with PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->Port       = (int) $port;
            $mail->SMTPAuth   = $auth;
            if ($auth) {
                $mail->Username   = $user;                     
                $mail->Password   = $password;                               
            }                                
            if ($from_address) {
                $mail->setFrom($from_address);
            }
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->IsHTML(true);
            $mail->Timeout = 10; // seconds
            return $mail->send();
        } catch(Exception $error) {
            return new Error($error->getMessage());
        }
    }

    /**
     * Upload image to uploads directory at /public/images/uploads.
     * Expects submitted form data for a file input captured in the $_FILES global.
     * The file type is detected from the file contents (never the client supplied name or MIME type)
     * and the file is stored under a random name so it can't overwrite or be guessed.
     * @param filename - the file input name to search in submission data
     * @return string/Error - returns the relative file path to the image on successful upload or an Error on failure
     */
    function upload_image(string $filename) {
        // Check submitted image
        $file = $_FILES[$filename] ?? null;
        if (
            !is_array($file) ||
            ($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
            !is_uploaded_file($file["tmp_name"] ?? "")
        ) {
            return new Error("Submitted file not found");
        }

        // Limit file size (5MB)
        $image_size = $file["size"] ?? null;
        if (!is_numeric($image_size) || $image_size > 5e+6) {
            return new Error("File size too large");
        }

        // Check file type is allowed by inspecting the actual file contents
        $allowed_types = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp",
            "image/gif"  => "gif"
        ];
        $mime_type = (new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]);
        if (!isset($allowed_types[$mime_type]) || getimagesize($file["tmp_name"]) === false) {
            return new Error("File type not allowed");
        }

        // Ensure uploads directory exists
        $target_dir = "/public/images/uploads/";
        $absolute_dir = APP_ROOT . $target_dir;
        if (!is_dir($absolute_dir) && !mkdir($absolute_dir, 0755, true)) {
            return new Error("Unable to upload image");
        }

        // Attempt to copy image file to uploads under a random name, return relative path on success
        $new_file_name = bin2hex(random_bytes(16)) . ".{$allowed_types[$mime_type]}";
        if (move_uploaded_file($file["tmp_name"], $absolute_dir . $new_file_name)) {
            return $target_dir . $new_file_name;
        }
        return new Error("Unable to upload image");
    }

    /**
     * Utility to log messages to a log file (debug.log) in the directory root
     * Use only for development. Do not commit log file to source control
     * or invoke this function in a live environment.
     * @param message - message to log to debug file
     */
    function debug_log(string $message): void {
        if (!is_dev()) {
            return;
        }
        $time = date("d/m/Y H:i:s");
        file_put_contents(
            APP_ROOT . "/debug.log",
            "{$time}: {$message}" . PHP_EOL,
            FILE_APPEND
        );
    }