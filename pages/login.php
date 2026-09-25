<?php 
    // Redirect to account if already logged in
    if (get_logged_in_user()) {
        header("Location: /account");
        exit();
    }

    get_component(path: "global/header", args: [
        "title" => "My PHP App | Log-in",
        "meta_description" => "Log in to your account"
    ]);
?>

<main id="main" tabindex="-1" class="bg-slate-50 min-h-screen outline-none">
    <section class="py-32">
        <?php get_component(path: "login"); ?>
    </section>
</main>

<?php get_component(path: "global/footer"); ?>