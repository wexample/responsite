<?php

/** @var App $app */
require '_init.php';

$sites = $app->getSites();

// Only one site to build.
if (true === isset($_GET['site']) && true === isset($sites[$_GET['site']]))
{
    $sites = [$sites[$_GET['site']]];
}

/** @var Site $site */
foreach ($sites as &$site)
{
    $site->build($site->serverPathBuild);
}

goBack();