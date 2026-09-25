<?php 
    $is_dev = is_dev();
    $vite_port = env("VITE_DEV_PORT", "5178");
?>

    <footer>
        This is the footer
    </footer>
    <script
        src="<?php echo($is_dev ? "http://localhost:{$vite_port}/src/main.ts" : "/dist/main.js"); ?>"
        <?php if ($is_dev) echo("type=\"module\""); ?>
    ></script>
</body>
</html>