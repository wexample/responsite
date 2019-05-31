<?php
/** @var App $app */

extend('../common/_layout');

block('head');

?>
<?php if (isset($metaDescription)): ?>
    <meta name="description" content="<?= $metaDescription ?>">
<?php endif ?>
<?php if ($hasStyle): ?>
    <link rel="stylesheet" href="<?= path("style.css") ?>">
<?php endif ?>
    <script src="<?= path("script.js") ?>" defer></script>
<?php if (isset($site->config['progressiveWebApp'])) : ?>
    <link rel="apple-touch-icon" href="icon.png">
    <link rel="manifest" href="site.webmanifest">
    <meta name="theme-color"
          content="<?= $site->config['progressiveWebApp']['theme_color'] ?>">
<?php endif; ?>
<?php

endblock();

block('body');

echo $body;

endblock();
