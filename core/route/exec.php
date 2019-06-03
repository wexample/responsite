<?php

/** @var App $app */

$siteName = $_REQUEST['site'];

if ($siteName === Site::SITE_PREVIEW_NAME)
{
    $sitePath = SERVER_PATH_PREVIEW;
}
else
{
    $sitePath = SERVER_PATH_BUILD . $_REQUEST['site'];
}

$site = $app->getSite($_REQUEST['site']);

return require $sitePath . '/section/' . $_REQUEST['section'] . '/' . $_REQUEST['page'];