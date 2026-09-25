<?php
    // Check logged in status of current user
    $current_user = get_logged_in_user();
    if (!$current_user) {
        header("Location: /login");
        exit();
    }

    // Get header
    get_component(path: "global/header", args: [
        "title" => "My PHP App | Account",
        "meta_description" => "View and edit your profile"
    ]);

    // Merge expected keys
    $current_user = array_merge([
        "email" => "",
        "first_name" => "",
        "last_name" => "",
        "bio" => "",
        "image_url" => "",
        "created_at" => ""
    ], $current_user);

    // Repetitive classes
    $label_class = "flex items-center justify-between py-2 px-1 border-b border-blue-900/50 mb-6";
    $label_span_class = "font-semibold text-lg";
    $value_class = "profile-edit-field ml-4 text-right placeholder:text-blue-950/70 outline-0";

    $modal_id = uniqid("modal-");
    $delete_modal_id = uniqid("modal-");
?>
<main class="bg-slate-50 min-h-screen">
    <section class="py-16">
        <div class="container">
            <div class="max-w-sm mx-auto">
                <h1 class="text-5xl mb-12 text-center">
                    Profile
                </h1>
                
                <!-- Profile Picture -->
                <figure class="mx-auto flex items-center justify-center size-40 overflow-hidden rounded-full mb-6">
                    <?php if (!$current_user["image_url"]) : ?>
                        <span class="block size-12 text-blue-200">
                            <?php echo(get_svg_icon("user")); ?>
                        </span>
                    <?php else : ?>
                        <img src="<?php echo(esc($current_user["image_url"])); ?>" class="size-full object-cover object-center">
                    <?php endif; ?>
                </figure>
                
                <!-- Bio -->
                <?php if (!empty($current_user["bio"])) : ?>
                    <p class="w-full text-center mb-8">
                        <?php echo(esc($current_user["bio"])); ?>
                    </p>
                <?php endif; ?>

                <!-- First Name -->
                <div class="<?php echo($label_class); ?>">
                    <span class="<?php echo($label_span_class); ?>">First Name</span>
                    <p class="<?php echo($value_class); ?>">
                        <?php echo(esc($current_user["first_name"])); ?>
                    </p>
                </div>

                <!-- Last Name -->
                <div class="<?php echo($label_class); ?>">
                    <span class="<?php echo($label_span_class); ?>">Last Name</span>
                    <p class="<?php echo($value_class); ?>">
                        <?php echo(esc($current_user["last_name"])); ?>
                    </p>
                </div>
                
                <!-- Date Registered -->
                <div class="<?php echo($label_class); ?>">
                    <span class="<?php echo($label_span_class); ?>">Date Registered</span>
                    <time class="ml-4 text-right">
                        <?php echo(date("d/m/Y", (int) $current_user["created_at"])); ?>
                    </time>
                </div>
                
                <!-- Edit Profile / Delete Account Buttons -->
                <div class="flex flex-col items-center gap-4">
                    <button data-modal-trigger="<?php echo($modal_id); ?>" class="button primary mx-auto">
                        Edit profile
                        <span class="block size-4 pointer-events-none"><?php echo(get_svg_icon("edit")); ?></span>
                    </button>
                    <button 
                        data-modal-trigger="<?php echo($delete_modal_id); ?>" 
                        class="text-blue-600 underline hover:text-blue-400 transition-colors duration-200 cursor-pointer"
                    >
                        Delete account
                    </button>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
    // Profile edit modal
    get_component(
        path: "modal/modal-base",
        args: [
            "modal_id" => $modal_id,
            "modal_template" => "profile-edit",
            "persistent" => true,
            "modal_args" => [ "user" => $current_user ]
        ]
    );

    // Delete account confirmation modal
    get_component(
        path: "modal/modal-base",
        args: [
            "modal_id" => $delete_modal_id,
            "modal_template" => "delete-account",
            "persistent" => true
        ]
    );

    // Toast notification
    get_component(path: "toast-notification", args: [
        "id" => "user-edited-toast-notification",
        "type" => "success",
        "icon" => "check-circle"
    ]);

    // Footer
    get_component(path: "global/footer"); 
?>