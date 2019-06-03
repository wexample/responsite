<?php

/** @var App $app */
require '_init.php';

require SERVER_PATH_FUNCTION . 'data.php';

$site = $app->getSite($_GET['site']);
$site->isPreview(true);

// Rebuild data dir.
$app->recreateDir(site_data_dir($site));
// Build in temp folder.
$site->build($app->recreateDir($site->serverPathBuild));

// Display created page.
return file_get_contents(
    $site->serverPathBuild . 'index.html'
);
