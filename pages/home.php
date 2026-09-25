<?php 
    get_component(path: "global/header", args: [
        "title" => "My PHP App | Home",
        "meta_description" => "Welcome to the PHP-Docker-Starter template."
    ]);
?>
<main class="bg-slate-50 min-h-screen">
    <section class="py-56 bg-gradient-to-r from-blue-700 to-blue-200 text-white">
        <div class="container">
            <div class="max-w-4xl">
                <h1 class="text-7xl mb-8">PHP-Docker Starter Template</h1>
                <p class="text-xl mb-8">Your new app starts here!</p>
                <a href="#main-content" class="group flex justify-center items-center bg-white rounded-full size-16">
                    <span class="block size-10 group-hover:translate-y-1 transition-transform duration-200 ease-out">
                        <?php echo(get_svg_icon("arrow-down")); ?>
                    </span>
                </a>
            </div>
        </div>
    </section>
    
    <section id="main-content" class="py-12">
        <div class="container">
            <h2 class="text-3xl mb-8">Getting started</h2>
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
                <div>
                    <h3 class="text-lg mb-4">Install Docker</h3>
                    <p>
                        Download and install Docker on your machine.
                        This will enable you to run the project on a local server.
                        If running on Windows it is recommended to run the project from 
                        WSL.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg mb-4">Start App</h3>
                    <p>
                        Simply run <code>./start-app.sh</code> to start the containers,
                        install the project dependencies and run the database migrations.
                        Then, in a separate terminal, run <code>npm install</code> and
                        <code>npm run dev</code> to spin up the front-end assets.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg mb-4">Get Building!</h3>
                    <p>
                        Your only limit is your imagination. See what you can build.
                    </p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_component(path: "global/footer"); ?>