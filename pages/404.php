<?php
    get_component(path: "global/header", args: [
        "title" => "My PHP App | Page not found",
        "meta_description" => "The page you're looking for can't be found"
    ]);
?>

<main id="main" tabindex="-1" class="bg-slate-50 min-h-screen outline-none">
    <section class="py-32">
        <div class="container text-center">
            <h1 class="text-5xl mb-6">Page not found</h1>
            <p class="mb-8">The page you're looking for can't be found.</p>
            <a href="/">Back to home</a>
        </div>
    </section>
</main>

<?php get_component(path: "global/footer"); ?>
