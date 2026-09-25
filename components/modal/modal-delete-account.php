<div class="rounded-md shadow-md p-6 bg-white z-20 fixed top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-9/10 sm:max-w-lg lg:w-1/3 max-h-11/12 overflow-y-auto animate-fade-in">
    <form id="delete-account-form" class="relative flex flex-col gap-6" data-component="profile">
        <h2 class="text-3xl text-center">Are you sure?</h2>
        <p class="text-center">
            Your account and profile will be permanently deleted. This can't be undone.
            You'll then be redirected to the homepage.
        </p>
        <output role="alert"></output>
        <div class="flex items-center gap-6">
            <button type="submit" class="button primary w-1/2">
                Confirm
            </button>
            <button type="button" data-autofocus class="close-modal button secondary w-1/2">
                Cancel
            </button>
        </div>

        <!-- Loading overlay -->
        <div 
            id="delete-account-loader"
            aria-hidden="true" 
            class="hidden absolute inset-0 bg-white/70 flex items-center justify-center"
        >
            <span class="block size-8 animate-spin">
                <?php echo(get_svg_icon("loader")); ?>
            </span>
            <span class="sr-only">Deleting account</span>
        </div>
    </form>
</div>
