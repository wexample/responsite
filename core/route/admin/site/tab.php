<?php

/** @var App $app */
/** @var Site $site */
require '_init.php';

$path = $site->serverPathRoot . 'admin/' . $_GET['name'] . '.php';

if (!is_file($path))
{
    $app->error404();
}

/** @var App $app */
return $app->render($path, $args);