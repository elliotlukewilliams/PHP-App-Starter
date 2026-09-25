<?php 
    global $component_args;
    $args = parse_component_args([
        "title" => "",
        "meta_description" => ""
    ]);
    $title = $args["title"];
    $meta_description = $args["meta_description"];
    $is_user_logged_in = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo(esc($meta_description)); ?>">
    <link href="dist/style.css" rel="stylesheet" />
    <title><?php echo(esc($title)); ?></title>
</head>
<body class="text-blue-950 font-poppins [&:has(.modal-trigger:checked)]:overflow-y-hidden">
    <header class="shadow-md bg-white">
        <div class="container mx-auto flex justify-between items-center gap-6 py-6">
            <a href="/" class="font-bold underline">
                Home
            </a>
            <nav>
                <ul class="flex items-center gap-4">
                    <?php if (!$is_user_logged_in): ?>
                        <li><a href="/login">Log in</a></li>
                        <li><a href="/signup">Sign up</a></li>
                    <?php endif; ?>
                    <?php
                        // Show log-out button as well as account if user is logged in
                        if ($is_user_logged_in) :
                    ?>
                        <li><a href="/account">Account</a></li>
                        <form id="logout-form" data-component="login">
                            <button type="submit" class="relative logout-error button primary">
                                <span class="btn-text">Log out</span>
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
                            <output></output>
                        </form>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>