<?php
    /**
     * Welcome template
     * Remove this template when you're ready to start building
     */

    $is_user_logged_in = (bool) get_logged_in_user();

    $features = [
        [
            "icon" => "layers",
            "title" => "Routing & components",
            "text" => "Create pages easily by dropping a file in <code>pages/</code>. Headers, forms, modals and toasts are ready-made components."
        ],
        [
            "icon" => "users",
            "title" => "User accounts",
            "text" => "Sign up, log in, edit your profile, reset a forgotten password or delete your account. All wired up and working."
        ],
        [
            "icon" => "shield",
            "title" => "Security baked in",
            "text" => "Hashed passwords, hardened sessions, same-origin checks, escaped output and safe uploads."
        ],
        [
            "icon" => "eye",
            "title" => "Accessible by default",
            "text" => "Boilerplated features are built with accessibility in mind - keyboard navigability, labelled errors, ARIA attributes and a skip link."
        ],
        [
            "icon" => "mail",
            "title" => "Email that just works",
            "text" => "PHPMailer is ready to go, and MailHog catches every email locally so you'll never spam a real inbox by accident."
        ],
        [
            "icon" => "zap",
            "title" => "Hot reload",
            "text" => "Vite swaps in CSS and JS changes instantly, and the page refreshes itself whenever you save a PHP file."
        ]
    ];

    $stack = [
        ["icon" => "server", "name" => "PHP 8.2 + Apache"],
        ["icon" => "database", "name" => "MySQL + migrations"],
        ["icon" => "mail", "name" => "PHPMailer + MailHog"],
        ["icon" => "code", "name" => "Vite + Tailwind v4"]
    ];

    $steps = [
        [
            "title" => "Start the app",
            "text" => "Spins up the Docker containers, waits for MySQL, installs dependencies and runs the migrations.",
            "code" => "./start-app.sh"
        ],
        [
            "title" => "Fire up the front end",
            "text" => "In a second terminal. Vite takes care of the CSS and JS and keeps everything hot-reloading.",
            "code" => "npm install\nnpm run dev"
        ],
        [
            "title" => "Open it up",
            "text" => "Stick with <code>localhost</code> rather than <code>127.0.0.1</code>. The endpoints are fussy about where requests come from (for good reason).",
            "code" => "http://localhost:8000"
        ]
    ];

    $services = [
        ["name" => "App", "url" => "localhost:8000"],
        ["name" => "MailHog inbox", "url" => "localhost:8025"],
        ["name" => "Vite dev server", "url" => "localhost:5178"],
        ["name" => "MySQL", "url" => "localhost:3306"]
    ];

    $architecture = [
        [
            "title" => "One front door",
            "text" => "Every request goes through <code>index.php</code>, which finds the matching file in <code>pages/</code>. No route files to maintain."
        ],
        [
            "title" => "Endpoints speak JSON",
            "text" => "Forms post to <code>includes/endpoints/</code> with <code>fetch()</code> and always get the same tidy JSON shape back."
        ],
        [
            "title" => "JavaScript on demand",
            "text" => "Add <code>data-component=\"name\"</code> to some markup and only <code>src/name.js</code> gets loaded. No dead weight."
        ],
        [
            "title" => "Migrations in order",
            "text" => "Timestamped migration files run once, in order, across every folder. Add one and run the migration script."
        ]
    ];

    // Shared classes
    $inline_code_class = "[&_code]:rounded [&_code]:bg-blue-50 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-sm [&_code]:text-blue-900";
    $code_block_class = "overflow-x-auto rounded-lg bg-blue-950 p-4 text-sm leading-relaxed text-blue-100";
    $secondary_dark_button_class = "button link-unset border-white/40 text-white hover:bg-white/10";
?>

<!-- Hero -->
<section class="relative overflow-hidden bg-blue-950 text-white">
    <!-- Decorative background glow -->
    <div aria-hidden="true" class="pointer-events-none absolute -top-40 -right-32 size-[36rem] rounded-full bg-blue-600/40 blur-3xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -bottom-48 -left-32 size-[28rem] rounded-full bg-teal-500/30 blur-3xl"></div>

    <div class="container relative grid items-center gap-16 py-24 lg:grid-cols-12 lg:py-32">
        <div class="lg:col-span-7">
            <p class="mb-6 inline-flex items-center gap-2 rounded-full border border-teal-300/30 px-4 py-1.5 text-sm font-medium text-teal-300">
                <span class="block size-4"><?php echo(get_svg_icon("coffee")); ?></span>
                PHP 8.2 · MySQL · Vite · Tailwind
            </p>
            <h1 class="mb-6 text-5xl leading-tight text-balance xl:text-6xl">
                Skip the boilerplate.
                <span class="block text-teal-300">Build the fun bit.</span>
            </h1>
            <p class="mb-10 max-w-xl text-lg text-blue-200">
                A no-framework PHP starter with accounts, a database, email and a modern front-end
                build already plugged in. One command to spin it up, then it's straight on to the good stuff.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="#get-started" class="button primary link-unset">
                    Get started
                    <span class="block size-4"><?php echo(get_svg_icon("arrow-down")); ?></span>
                </a>
                <?php if ($is_user_logged_in) : ?>
                    <a href="/account" class="<?php echo($secondary_dark_button_class); ?>">Go to your account</a>
                <?php else : ?>
                    <a href="/signup" class="<?php echo($secondary_dark_button_class); ?>">Take it for a spin</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Terminal -->
        <figure class="overflow-hidden rounded-xl border border-white/10 bg-black/40 shadow-2xl lg:col-span-5">
            <div aria-hidden="true" class="flex items-center gap-2 border-b border-white/10 px-4 py-3">
                <span class="size-3 rounded-full bg-red"></span>
                <span class="size-3 rounded-full bg-teal-300"></span>
                <span class="size-3 rounded-full bg-blue-300"></span>
            </div>
            <pre class="overflow-x-auto p-6 text-sm leading-loose text-blue-200"><code><span class="text-teal-300">$</span> <span class="text-white">./start-app.sh</span>
Starting Docker containers...
Waiting for the database to be ready...
Installing Composer dependencies...
Running migrations...
<span class="text-teal-200">App is ready!</span> 🎉</code></pre>
            <figcaption class="sr-only">Example output from running the start-app script</figcaption>
        </figure>
    </div>
</section>

<!-- Features -->
<section class="bg-white py-20 lg:py-28">
    <div class="container">
        <div class="mb-8 max-w-2xl lg:mb-14">
            <h2 class="mb-4 text-4xl text-balance">Everything you need, nothing you don't</h2>
            <p class="text-lg text-gray-800">
                All the boring-but-essential bits are done, so you gan get straight to work on building features.
            </p>
        </div>
        <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($features as $feature) : ?>
                <li class="rounded-xl border border-gray-200 p-6 transition-shadow duration-200 hover:shadow-md">
                    <span class="mb-5 flex size-12 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <span class="block size-6"><?php echo(get_svg_icon($feature["icon"])); ?></span>
                    </span>
                    <h3 class="mb-2 text-xl"><?php echo(esc($feature["title"])); ?></h3>
                    <p class="text-gray-800 <?php echo($inline_code_class); ?>"><?php echo($feature["text"]); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<!-- Stack -->
<section class="border-y border-gray-200 bg-gray-50 py-12">
    <div class="container xl:flex-row xl:justify-between">
        <h2 class="shrink-0 text-lg mb-6">Built on friendly, familiar tech</h2>
        <ul class="flex flex-wrap gap-x-8 gap-y-4 xl:flex-nowrap">
            <?php foreach ($stack as $item) : ?>
                <li class="flex items-center gap-2 whitespace-nowrap font-medium text-gray-900">
                    <span class="block size-5 text-teal-600"><?php echo(get_svg_icon($item["icon"])); ?></span>
                    <?php echo(esc($item["name"])); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<!-- Getting started -->
<section id="get-started" class="scroll-mt-8 bg-white py-20 lg:py-28">
    <div class="container">
        <div class="mb-14 max-w-2xl">
            <h2 class="mb-4 text-4xl text-balance">Up and running in three steps</h2>
            <p class="text-lg text-gray-800 text-pretty">
                You'll need Node and Docker installed, plus a working knowledge of PHP, JavaScript and Tailwind.
                On Windows? Run it from WSL and you're good to go.
            </p>
        </div>
        <div class="grid gap-12 lg:grid-cols-3">
            <ol class="grid gap-10 lg:col-span-2">
                <?php foreach ($steps as $index => $step) : ?>
                    <li class="flex gap-6">
                        <span aria-hidden="true" class="flex size-7 shrink-0 items-center justify-center rounded-full bg-blue-700 text-sm font-bold text-white md:size-10 md:text-base">
                            <?php echo($index + 1); ?>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="mb-2 text-xl"><?php echo(esc($step["title"])); ?></h3>
                            <p class="mb-4 text-gray-800 <?php echo($inline_code_class); ?>"><?php echo($step["text"]); ?></p>
                            <pre class="<?php echo($code_block_class); ?>"><code><?php echo(esc($step["code"])); ?></code></pre>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>

            <aside class="h-fit rounded-xl bg-teal-50 p-6">
                <h3 class="mb-4 flex items-center gap-2 text-lg">
                    <span class="block size-5 text-teal-600"><?php echo(get_svg_icon("compass")); ?></span>
                    Handy URLs
                </h3>
                <dl class="grid gap-3">
                    <?php foreach ($services as $service) : ?>
                        <div class="flex flex-wrap items-baseline justify-between gap-x-4 border-b border-teal-100 pb-3 last:border-0 last:pb-0">
                            <dt class="text-gray-900"><?php echo(esc($service["name"])); ?></dt>
                            <dd class="font-mono text-sm font-medium text-teal-600"><?php echo(esc($service["url"])); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
                <p class="mt-5 text-sm text-gray-900">Ports and credentials all live in <code class="font-mono">.env</code>.</p>
            </aside>
        </div>
    </div>
</section>

<!-- Architecture -->
<section class="bg-gray-50 py-20 lg:py-28">
    <div class="container grid items-start gap-12 lg:grid-cols-2">
        <div>
            <h2 class="mb-4 text-4xl text-balance mb-8">How it all fits together</h2>
            
            <dl class="grid gap-8 sm:grid-cols-2">
                <?php foreach ($architecture as $item) : ?>
                    <div>
                        <dt class="mb-2 flex items-center gap-2 font-bold">
                            <span class="block size-4 text-teal-600"><?php echo(get_svg_icon("check-circle")); ?></span>
                            <?php echo(esc($item["title"])); ?>
                        </dt>
                        <dd class="text-gray-800 <?php echo($inline_code_class); ?>"><?php echo($item["text"]); ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>

        <figure class="overflow-hidden rounded-xl bg-blue-950 shadow-xl">
            <figcaption class="border-b border-white/10 px-6 py-3 text-sm font-medium text-blue-200">Project structure</figcaption>
            <pre class="overflow-x-auto p-6 text-sm leading-relaxed text-blue-100"><code>index.php         <span class="text-teal-200"># Front door + routing</span>
pages/            <span class="text-teal-200"># One file per route</span>
components/       <span class="text-teal-200"># Reusable PHP partials</span>
includes/
  functions.php   <span class="text-teal-200"># Handy helpers</span>
  classes/        <span class="text-teal-200"># The User class</span>
  endpoints/      <span class="text-teal-200"># AJAX handlers (JSON)</span>
src/              <span class="text-teal-200"># JS modules + Tailwind</span>
migrations/       <span class="text-teal-200"># Database changes</span>
public/images/    <span class="text-teal-200"># Icons + uploads</span></code></pre>
        </figure>
    </div>
</section>

<!-- Call to action -->
<section class="bg-blue-800 py-20 text-center text-white">
    <div class="container max-w-2xl">
        <h2 class="mb-4 text-4xl text-balance">Right, over to you</h2>
        <p class="mb-8 text-lg text-blue-100">
            The groundwork's done. Your only limit now is your imagination (and possibly your coffee supply).
            The full tour of helpers and the User class lives in <code class="font-mono">README.md</code>.
        </p>
        <?php if ($is_user_logged_in) : ?>
            <a href="/account" class="button link-unset bg-white text-blue-800 hover:bg-blue-50">Go to your account</a>
        <?php else : ?>
            <a href="/signup" class="button link-unset bg-white text-blue-800 hover:bg-blue-50">Create a test account</a>
        <?php endif; ?>
    </div>
</section>
