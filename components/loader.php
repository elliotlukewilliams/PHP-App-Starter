<?php 
    $args = parse_component_args([
        "wrapper_class" => "block shrink-0 size-6 animate-spin",
        "hidden" => true
    ]);
    if ($args["hidden"]) {
        $args["wrapper_class"] .= " hidden";
    }
?>
<span <?php if ($args["hidden"]) echo("aria-hidden=\"true\""); ?> class="loader <?php echo($args["wrapper_class"]); ?>">
    <?php echo(get_svg_icon("loader")); ?>
</span>