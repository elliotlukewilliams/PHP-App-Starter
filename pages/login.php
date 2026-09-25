<?php 
    get_component(path: "global/header", args: [
        "title" => "My PHP App | Log-in",
        "meta_description" => "Log in to your account"
    ]);
?>

<main class="bg-slate-50 min-h-screen">
    <section class="py-32">
        <?php get_component(path: "login"); ?>
    </section>
</main>

<?php get_component(path: "global/footer"); ?>