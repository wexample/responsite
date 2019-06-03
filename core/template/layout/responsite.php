<?php
/** @var App $app */

extend('base');

block('head');

$forceCacheVar = $site->uniqueId();

?>
<?php if (isset($metaDescription)): ?>
    <meta name="description" content="<?= $metaDescription ?>">
<?php endif ?>
<?php if ($hasStyle): ?>
    <link rel="stylesheet" href="<?= path("style.css?" . $forceCacheVar) ?>">
<?php endif ?>
    <script src="<?= path("script.js") . '?' . $forceCacheVar ?>" defer></script>
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
