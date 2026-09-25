<?php
    // First check we have a unique token in the url
    $token = isset($_GET["token"]) ? $_GET["token"] : null;
    if (empty($token) || !is_string($token)) {
        exit();
    }
?>
<div class="p-6 rounded-md border border-gray-100 shadow-md max-w-sm mx-auto bg-white">
    <form id="password-reset-form" class="mb-6" data-component="password-reset">
        <h1 class="text-3xl font-bold text-center mb-4 border-b border-slate-400 pb-6">Reset Password</h1>
        <div class="flex flex-col gap-6">
            <?php
                // Password
                get_component(path: "form/input", args: [
                    "name" => "new_password",
                    "type" => "password",
                    "label" => "New Password",
                    "description" => "
                        Passwords must be at least 8 characters long and 
                        contain at least 1 uppercase character, 1 lowercase character and 1 number
                    ",
                    "placeholder" => "Enter password...",
                    "required" => true,
                    "validation_error_message" => ""
                ]);

                // Password Confirm
                get_component(path: "form/input", args: [
                    "name" => "new_password_confirm",
                    "type" => "password",
                    "label" => "Confirm Password",
                    "placeholder" => "Re-type password",
                    "required" => true,
                    "validation_error_message" => ""
                ]);
            ?>
            <input name="unique_token" type="hidden" value="<?php echo(esc($token)); // Hidden input containing unique token ?>">
            <output class="message"></output>
            <button type="submit" class="button primary relative">
                <span class="btn-text">Reset Password</span>
                <?php
                    // Loading state 
                    get_component(path: "loader", args: [
                        "wrapper_class" => "
                            absolute block size-6 animate-spin 
                            top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                        "
                    ]); 
                ?>
            </button>
        </div>
    </form>
</div>
<?php
    // Toast notification
    get_component(path: "toast-notification", args: [
        "id" => "password-reset-toast-notification",
        "type" => "success",
        "icon" => "check-circle"
    ]);
?>