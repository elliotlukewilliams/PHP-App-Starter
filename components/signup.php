<form id="signup-form" data-component="signup" class="p-6 rounded-md border border-gray-100 shadow-md max-w-sm mx-auto bg-white">
    <h3 class="text-3xl font-bold text-center mb-4 border-b border-slate-400 pb-6">Sign up</h3>
    <div class="flex flex-col gap-6">
        <?php
            // Email
            get_component(path: "form/input", args: [
                "name" => "email",
                "type" => "email",
                "label" => "Email",
                "placeholder" => "Enter email address...",
                "required" => true,
                "validation_error_message" => ""
            ]);

            // Password
            get_component(path: "form/input", args: [
                "name" => "password",
                "type" => "password",
                "label" => "Password",
                "description" => "
                    Passwords must be at least 8 characters long and 
                    contain at least 1 uppercase character, 1 lowercase character and  1 number
                ",
                "placeholder" => "Think of a strong password",
                "required" => true,
                "validation_error_message" => ""
            ]);

            get_component(path: "form/input", args: [
                "name" => "password_confirm",
                "type" => "password",
                "label" => "Confirm Password",
                "placeholder" => "Re-type password",
                "required" => true,
                "validation_error_message" => ""
            ]);
        ?>
        <output class="any-error"></output>
        <button type="submit" class="button primary">
            <span class="btn-text">Submit</span>
            <?php get_component(path: "loader"); // Loading state ?>
        </button>
    </div>
</form>
<?php
    // Toast notification
    get_component(path: "toast-notification", args: [
        "id" => "account-created-toast-notification",
        "type" => "success",
        "icon" => "check-circle"
    ]);
?>