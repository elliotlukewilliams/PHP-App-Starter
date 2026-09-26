<?php
    // Set args
    $args = parse_component_args([
        "id" => uniqid("toast-"),
        "message" => "", // Leave empty if populating dynamically via JS
        "icon" => "alert-circle",
        "position" => "bottom-right",
        "type" => "notice"
    ]);
    $id = $args["id"];
    $message = $args["message"];
    $icon = $args["icon"];
    $icon_svg = get_svg_icon($icon);
    $position = $args["position"];
    $type = $args["type"];

    // Conditional classes. Below lg the toast is always a full width bar fixed to the bottom of the screen;
    // these set its position from lg upwards (resetting the full width insets it has on smaller screens)
    $position_class = match($position) {
        "bottom-right" => "lg:bottom-12 lg:right-12 lg:left-auto",
        "bottom-left" => "lg:bottom-12 lg:left-12 lg:right-auto",
        "top-right" => "lg:top-12 lg:right-12 lg:left-auto lg:bottom-auto",
        "top-left" => "lg:top-12 lg:left-12 lg:right-auto lg:bottom-auto",
        default => "lg:bottom-12 lg:right-12 lg:left-auto"
    };

    $border_class = match($type) {
        "notice" => "border-blue-500",
        "success" => "border-green",
        "warning" => "border-red",
        default => "border-blue-500"
    };
    $icon_class = match($type) {
        "notice" => "text-blue-500",
        "success" => "text-green",
        "warning" => "text-red",
        default => "text-blue-500"
    };
    $toast_class = "{$position_class} {$border_class}";
    $aria_live = $type === "warning" ? "assertive" : "polite";
?>
<div
    id="<?php echo($id); ?>"
    data-component="toast-notification"
    role="status"
    aria-live="<?php echo($aria_live); ?>"
    aria-atomic="true"
    class="
        toast-notification hidden animate-drift-up fixed z-40 inset-x-0 bottom-0 flex items-center gap-6 justify-between bg-white p-6 shadow-md border-0 border-t
        lg:max-w-md lg:gap-12 lg:rounded-md lg:border
        <?php echo($toast_class); ?>
    "
>
    <div class="flex items-center gap-4">
        <?php if ($icon_svg) : ?>
            <span class="block size-8 shrink-0 <?php echo($icon_class); ?>">
                <?php echo($icon_svg); ?>
            </span>
        <?php endif; ?>
        <p class="toast-text"><?php echo(esc($message)); ?></p>
    </div>
    <button
        type="button"
        aria-label="Dismiss notification" 
        class="toast-dismiss block size-4 shrink-0 cursor-pointer hover:opacity-50 transition-opacity duration-200"
    >
        <?php echo(get_svg_icon("x")); ?>
    </button>
</div>