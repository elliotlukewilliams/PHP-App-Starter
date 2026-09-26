<?php 
    global $component_args;
    $args = parse_component_args([
        "title" => "",
        "meta_description" => ""
    ]);
    $title = $args["title"];
    $meta_description = $args["meta_description"];
    $is_user_logged_in = get_logged_in_user();
    $is_dev = is_dev();

    // Nav links: no underline, colour change on hover
    $nav_link_class = "link-unset flex items-center gap-1.5 font-medium text-blue-950 hover:text-blue-700 transition-colors duration-200";
    $nav_icon_class = "block size-4 shrink-0";
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo(esc($meta_description)); ?>">
    <?php if (!$is_dev) : // In development Vite injects the CSS via main.ts ?>
        <link href="/dist/style.css" rel="stylesheet" />
    <?php endif; ?>
    <title><?php echo(esc($title)); ?></title>
</head>
<body class="text-blue-950 font-poppins">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:p-3 focus:rounded-md focus:shadow-md">
        Skip to main content
    </a>
    <header class="shadow-md bg-white">
        <div class="container mx-auto flex justify-between items-center gap-6 py-6">
            <a href="/" class="<?php echo($nav_link_class); ?> font-bold">
                <span class="<?php echo($nav_icon_class); ?>"><?php echo(get_svg_icon("home")); ?></span>
                Home
            </a>
            <nav aria-label="Main">
                <ul class="flex items-center gap-6">
                    <?php if (!$is_user_logged_in): ?>
                        <li>
                            <a href="/login" class="<?php echo($nav_link_class); ?>">
                                <span class="<?php echo($nav_icon_class); ?>"><?php echo(get_svg_icon("log-in")); ?></span>
                                Log in
                            </a>
                        </li>
                        <li>
                            <a href="/signup" class="<?php echo($nav_link_class); ?>">
                                <span class="<?php echo($nav_icon_class); ?>"><?php echo(get_svg_icon("user-plus")); ?></span>
                                Sign up
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                        // Show log-out button as well as account if user is logged in
                        if ($is_user_logged_in) :
                    ?>
                        <li>
                            <a href="/account" class="<?php echo($nav_link_class); ?>">
                                <span class="<?php echo($nav_icon_class); ?>"><?php echo(get_svg_icon("user")); ?></span>
                                Account
                            </a>
                        </li>
                        <li>
                        <form id="logout-form" data-component="login">
                            <button type="submit" class="relative button primary">
                                <span class="btn-text flex items-center gap-2">
                                    <span class="<?php echo($nav_icon_class); ?>"><?php echo(get_svg_icon("log-out")); ?></span>
                                    Log out
                                </span>
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
                            <output class="logout-error" role="alert"></output>
                        </form>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>