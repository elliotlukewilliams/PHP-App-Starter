<?php
    // Check token in GET param
    if (empty($_GET["token"]) || !is_string($_GET["token"])) {
        header("Location: /login");
        exit();
    }
    
    get_component(path: "global/header", args: [
        "title" => "My PHP App | Reset Password",
        "meta_description" => "Reset your password"
    ]);
?>

<main id="main" tabindex="-1" class="bg-slate-50 min-h-screen outline-none">
    <section class="py-32">
        <?php get_component(path: "password-reset"); ?>
    </section>
</main>

<?php get_component(path: "global/footer"); ?>