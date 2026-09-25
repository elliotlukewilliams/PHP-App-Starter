<?php
    // Set modal args
    $args = parse_component_args([
        "modal_id" => uniqid(),
        "modal_template" => "example",
        "modal_args" => [],
        "persistent" => false
    ]);
    $modal_id = $args["modal_id"];

    // If modal template not found, fetch example template
    $modal_template = $args["modal_template"];
    $modal_template_path = "modal/modal-{$modal_template}";
    if (!file_exists(APP_ROOT . "/components/{$modal_template_path}.php")) {
        $modal_template_path = "modal/modal-example";
    }

    $persistent = $args["persistent"];
?>
<div 
    id="<?php echo($modal_id); ?>"
    data-component="modal"
    class="modal hidden fixed size-full top-0 z-10 bg-black/70" 
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
>
    <?php if (!$persistent) : ?>
        <button type="button" tabindex="-1" aria-hidden="true" class="close-modal absolute inset-0"></button>
    <?php endif; ?>

    <div class="z-20">
        <?php get_component(path: $modal_template_path, args: $args["modal_args"]); ?>
    </div>
</div>