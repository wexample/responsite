<?php

declare(strict_types=1);

if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
{
    $_SERVER['HTTPS']='on';
}

require 'src/App.php';

echo (new App(__FILE__))
    ->handleRequest();
