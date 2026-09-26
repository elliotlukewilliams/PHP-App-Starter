<?php
    get_component(path: "global/header", args: [
        "title" => "My PHP App | Home",
        "meta_description" => "A no-framework PHP starter with accounts, a database, email and a modern front-end build ready to go."
    ]);
?>
<main id="main" tabindex="-1" class="min-h-screen outline-none">
    <?php get_component(path: "welcome-content"); ?>
</main>

<?php get_component(path: "global/footer"); ?>
