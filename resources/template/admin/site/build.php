<?php
/** @var App $app */

$sites = $app->getSites();

// Only one site to build.
if (true === isset($_GET['site']) && true === isset($sites[$_GET['site']]))
{
    $sites = [$sites[$_GET['site']]];
}

/** @var Site $site */
foreach ($sites as &$site)
{
    $site->clientPathAssets = '/build/' . $site->name . '/';
    $site->build($site->createBuildDir());
}

if (true === isset($_GET['destination']))
{
    // Go back to index.
    header('location:' . $_GET['destination']);
}

exit;
