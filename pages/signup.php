<?php
    // Check logged in status of current user. Redirect to account if already logged in.
    if (get_logged_in_user()) {
        header("Location: /account");
        exit();
    }

    get_component(path: "global/header", args: [
        "title" => "My PHP App | Sign up",
        "meta_description" => "Create an account"
    ]);
?>

<main id="main" tabindex="-1" class="bg-slate-50 min-h-screen outline-none">
    <section class="py-16 lg:py-32">
        <div class="container">
            <?php get_component(path: "signup"); ?>
        </div>
    </section>
</main>

<?php get_component(path: "global/footer"); ?>