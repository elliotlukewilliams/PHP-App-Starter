<?php
    $unique_id_fallback = uniqid("input-");
    $args = parse_component_args([
        "name" => $unique_id_fallback,
        "id" => $unique_id_fallback,
        "type" => "text",
        "textarea" => false,
        "label" => "",
        "description" => "",
        "placeholder" => "",
        "required" => false,
        "value" => null,
        "max" => 200,
        "autocomplete" => "", // e.g. "email", "current-password", "new-password", "given-name"
        "validation_error_message" => false // Use empty string if dynamically populating
    ]);
    $name = $args["name"];
    $id = $args["id"];
    $type = $args["type"];
    $label = $args["label"];
    $description = $args["description"];
    $placeholder = $args["placeholder"];
    $required = $args["required"];
    $value = $args["value"];
    $max = $args["max"];
    $autocomplete = $args["autocomplete"];
    $validation_error_message = $args["validation_error_message"];
    $html_tag = $args["textarea"] ? "textarea" : "input";
    $description_id = "{$id}-description";
    $error_id = "{$id}-error";

    // Link the description and error message to the field so screen readers read them with it
    $described_by = [];
    if ($description) {
        $described_by[] = $description_id;
    }
    if (is_string($validation_error_message)) {
        $described_by[] = $error_id;
    }
?>
<label class="[&>input:has(+output.active:not(.success))]:!border-red">
    <?php echo($label); ?>
    <<?php echo($html_tag); ?> 
        <?php 
            if ($html_tag !== "textarea") 
                echo("type=\"{$type}\""); 
        ?>
        name="<?php echo(esc($name)); ?>"
        id="<?php echo(esc($id)); ?>"
        maxlength="<?php echo(intval($max)); ?>"
        <?php 
            if ($placeholder) : 
        ?>
            placeholder="<?php echo(esc($placeholder)); ?>"
        <?php 
            endif;
            if ($required) :
        ?>
            required
        <?php 
            endif;
            if ($value && $html_tag !== "textarea") :
        ?>
            value="<?php echo(esc($value)); ?>"
        <?php 
            endif;
            if ($autocomplete) :
        ?>
            autocomplete="<?php echo(esc($autocomplete)); ?>"
        <?php 
            endif;
            if (!empty($described_by)) :
        ?>
            aria-describedby="<?php echo(esc(implode(" ", $described_by))); ?>"
        <?php 
            endif;
        ?>
        class="peer"
    ><?php if ($html_tag === "textarea") { echo(esc($value) . "</textarea>");} // Output value and close tag if textarea ?>
    <?php
        if (is_string($validation_error_message)) : 
    ?>
        <output id="<?php echo(esc($error_id)); ?>" role="alert">
            <?php echo($validation_error_message); ?>
        </output>
    <?php 
        endif; 
        if ($description) :
    ?>
        <span 
            id="<?php echo(esc($description_id)); ?>" 
            class="description text-sm text-gray-700 max-h-0 overflow-hidden peer-focus:max-h-screen transition-all duration-400 delay-300 ease-in-out"
        >
            <?php echo($description); ?>
        </span>
    <?php 
        endif; 
    ?>
</label>