<?php 
    $modal_id = uniqid("modal-");
?>
<div data-component="login" class="p-6 rounded-md border border-gray-100 shadow-md max-w-sm mx-auto bg-white">
    <form id="login-form" class="mb-6">
        <h1 class="text-3xl font-bold text-center mb-4 border-b border-slate-400 pb-6">Log in</h1>
        <div class="flex flex-col gap-6">
            <?php
                // Email
                get_component(path: "form/input", args: [
                    "name" => "login_email",
                    "type" => "email",
                    "label" => "Email",
                    "placeholder" => "Enter email address...",
                    "autocomplete" => "email",
                    "required" => true,
                    "validation_error_message" => ""
                ]);

                // Password
                get_component(path: "form/input", args: [
                    "name" => "login_password",
                    "type" => "password",
                    "label" => "Password",
                    "placeholder" => "Enter password...",
                    "autocomplete" => "current-password",
                    "required" => true,
                    "validation_error_message" => ""
                ]);
            ?>
            <output class="any-error" role="alert"></output>
            <button type="submit" class="button primary relative">
                <span class="btn-text">Log in</span>
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
    <div class="flex justify-center">
        <button 
            type="button"
            data-modal-trigger="<?php echo($modal_id); ?>" 
            aria-haspopup="dialog"
            class="text-blue-700 underline hover:text-blue-900 transition-colors duration-200 cursor-pointer"
        >
            Forgot password?
        </button>
        <?php 
            get_component(
                path: "modal/modal-base",
                args: [
                    "modal_id" => $modal_id,
                    "modal_template" => "password-reset",
                    "persistent" => true
                ]
            );
        ?>
    </div>
</div>
<?php
    // Toast notification
    get_component(path: "toast-notification", args: [
        "id" => "email-sent-toast-notification",
        "type" => "success",
        "icon" => "mail"
    ]);
?>