<?php
/** @var App $app */
$site = $app->getSite($_GET['site']);

$path                   = 'tmp/preview/';
$site->clientPathAssets = '/' . $path;
$dir                    = SERVER_PATH_ROOT . $site->clientPathAssets;

// Build in temp folder.
$site->build(
    $site->recreateDir($dir)
);

// Display created page.
echo file_get_contents(
    $dir . '/index.html'
);

// Admin render stack is polluted by build data.
// No need to render extra data.
exit;
