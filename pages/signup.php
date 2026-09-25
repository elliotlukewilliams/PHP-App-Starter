<?php
    // Check logged in status of current user. Redirect if not logged in.
    if (get_logged_in_user()) {
        header("Location: /account");
        exit();
    }

    get_component(path: "global/header", args: [
        "title" => "My PHP App | Home",
        "meta_description" => "Welcome to the PHP-Docker-Starter template."
    ]);
?>

<main class="bg-slate-50 min-h-screen">
    <section class="py-32">
        <?php get_component(path: "signup"); ?>
    </section>
</main>

<?php get_component(path: "global/footer"); ?>