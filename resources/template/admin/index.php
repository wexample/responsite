<?php
/** @var App $app */

set('pageTitle', 'Accueil');

extend('_layout');

block('page_content');

$sites = $app->getSites();

?>Hi !<?php

endblock();