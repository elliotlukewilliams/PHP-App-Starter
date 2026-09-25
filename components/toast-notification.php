<?php
    // Set args
    $args = parse_component_args([
        "id" => uniqid("toast-"),
        "message" => "", // Leave empty if populating dynmically via JS
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

    // Conditional classes
    $position_class = match($position) {
        "bottom-right" => "lg:bottom-12 lg:right-12",
        "bottom-left" => "lg:bottom-12 lg:left-12",
        "top-right" => "lg:top-12 lg:right-12",
        "top-left" => "lg:top-12 lg:left-12",
        default => "lg:bottom-12 lg:right-12"
    };

    $border_class = match($type) {
        "notice" => "border-blue-500",
        "success" => "border-green",
        "warning" => "border-red"
    };
    $icon_class = match($type) {
        "notice" => "text-blue-500",
        "success" => "text-green",
        "warning" => "text-red"
    };
    $toast_class = "{$position_class} {$border_class}";
    $aria_live = $type = "warning" ? "assertive" : "polite";
?>
<div
    id="<?php echo($id); ?>"
    data-component="toast-notification"
    role="status"
    aria-live="<?php echo($aria_live); ?>"
    aria-atomic="true"
    class="toast-notification hidden animate-drift-up fixed flex items-center gap-12 justify-between max-w-md bg-white rounded-md p-6 shadow-md border <?php echo($toast_class); ?>"
>
    <div class="flex items-center gap-4">
        <?php if ($icon_svg) : ?>
            <span class="block size-8 shrink-0 <?php echo($icon_class); ?>">
                <?php echo($icon_svg); ?>
            </span>
        <?php endif; ?>
        <p class="toast-text"><?php echo($message); ?></p>
    </div>
    <button
        aria-label="Dismiss notifiction" 
        class="toast-dismiss block size-4 shrink-0 cursor-pointer hover:opacity-50 transition-opacity duration-200"
    >
        <?php echo(get_svg_icon("x")); ?>
    </button>
</div>