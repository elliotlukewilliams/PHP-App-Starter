<?php
    // Get/check user array passed in args. Re-fetch current user as fallback
    $args = parse_component_args(args: [
        "user" => get_logged_in_user()
    ]);
    $current_user = $args["user"];
    if (!$current_user) {
        return;
    }

    // Merge expected keys
    $current_user = array_merge([
        "email" => "",
        "first_name" => "",
        "last_name" => "",
        "bio" => "",
        "image_url" => "",
        "created_at" => ""
    ], $current_user);

    // Repetetive classes
    $label_class = "flex flex-col gap-2 py-2 px-1 border-b border-blue-900/50 mb-6";
    $label_span_class = "font-semibold text-lg";
    $value_class = "profile-edit-field";
?>

<div class="rounded-md shadow-md p-6 bg-white z-20 fixed top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-9/10 sm:max-w-lg lg:w-1/3 max-h-11/12 overflow-y-auto animate-fade-in">
    <form id="profile-edit-form" enctype="multipart/form-data" class="relative flex flex-col gap-6" data-component="profile-edit">
        <h2 class="text-3xl text-center">Edit your details</h2>

        <!-- Profile Picture -->
        <div class="relative mx-auto flex items-center">
            <div role="button" id="profile-image-edit-panel-toggle" class="hover:opacity-60 transition-all duration-200 transition-transform cursor-pointer">
                <figure id="profile-picture" class="relative flex items-center justify-center size-32 overflow-hidden rounded-full border border-black/20">
                    <?php if (!$current_user["image_url"]) : ?>
                        <span class="block size-12 text-blue-200">
                            <?php echo(get_svg_icon("user")); ?>
                        </span>
                    <?php else : ?>
                        <img src="<?php echo(esc($current_user["image_url"])); ?>" class="profile-image-preview size-full object-cover object-center">
                    <?php endif; ?>
                </figure>
            </div>
            <div id="profile-image-edit-panel" class="hidden animate-fade-in text-sm text-blue-600">
                <div class="relative flex items-center gap-1 hover:underline transition-colors duration-200 mb-1.5">
                    Edit <span class="block shrink-0 size-3"><?php echo(get_svg_icon("edit")) ?></span>
                    <input
                        name="image_file"
                        id="profile-edit-image" 
                        type="file" 
                        accept="image/png, image/jpeg" 
                        class="absolute inset-0 z-10 opacity-0"
                    >
                </div>
                <div id="remove-profile-image" role="button" class="relative flex items-center gap-1 cursor-pointer hover:underline transition-colors duration-200">
                    Remove <span class="block shrink-0 size-3"><?php echo(get_svg_icon("trash")) ?></span>
                </div>
            </div>
        </div>
        <?php
            // Bio
            get_component(path: "form/input", args: [
                "name" => "bio",
                "textarea" => true,
                "label" => "Bio",
                "value" => $current_user["bio"]
            ]);

            // First name
            get_component(path: "form/input", args: [
                "name" => "first_name",
                "type" => "text",
                "label" => "First Name",
                "value" => $current_user["first_name"],
                "max" => 100,
                "required" => true
            ]);

            // Last name
            get_component(path: "form/input", args: [
                "name" => "last_name",
                "type" => "text",
                "label" => "Last Name",
                "value" => $current_user["last_name"],
                "max" => 100,
                "required" => true
            ]);
        ?>
        <output></output>
        <div class="flex items-center gap-6">
            <button type="submit" class="button primary w-1/2">
                Update
            </button>
            <div role="button" class="close-modal button secondary w-1/2">
                Cancel
            </div>
        </div>

        <!-- Loading overlay -->
        <div 
            id="profile-edit-loader"
            aria-hidden="true" 
            class="hidden absolute inset-0 bg-white/70 flex items-center justify-center"
        >
            <span class="block size-8 animate-spin">
                <?php echo(get_svg_icon("loader")); ?>
            </span>
        </div>
    </form>
</div>