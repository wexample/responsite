<?php
/** @var App $app */

extend('_layout');

$site = get('site');

block('tabs');
parent();
?><a
    href="/admin/site/build?site=<?= $site->name ?>&destination=/admin/site/preview?site=<?= $site->name ?>"
    class="btn">Rebuild</a><?php
endblock();

block('page_content_inner');
?>
<iframe id="preview-frame"
        src="<?= siteURL() ?>admin/site/preview_frame?site=<?= $site->name ?>"></iframe><?php

endblock();