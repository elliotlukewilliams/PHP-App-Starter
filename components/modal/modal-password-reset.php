<div class="relative container" data-component="password-reset-request">
    <div class="rounded-md shadow-md p-6 bg-white z-20 fixed top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-10/12 lg:max-w-sm animate-fade-in">
        <div class="flex justify-end">
            <button type="button" aria-label="Close modal" class="close-modal block size-5 hover:opacity-50 transition-opacity duration-200 cursor-pointer">
                <?php echo(get_svg_icon("x")); ?>
            </button>
        </div>
        <h2 class="text-2xl font-bold mb-2">Reset Password</h2>
        <p class="mb-4">Enter the email you registered your account with to send a password reset link.</p>
        <form id="password-reset-request-form">
            <?php 
                get_component(path: "form/input", args: [
                    "name" => "password_reset_request_email",
                    "type" => "email",
                    "label" => "Email",
                    "placeholder" => "Enter email address...",
                    "autocomplete" => "email",
                    "validation_error_message" => "",
                    "required" => true
                ]);
            ?>
            <button type="submit" class="button primary relative w-full mt-4">
                <span class="btn-text">Reset password</span>
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
        </form>
        <button type="button" id="cancel-password-reset" class="close-modal button secondary w-full mt-4">
            Cancel
        </button>
    </div>
</div>