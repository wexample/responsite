<?php

/** @var App $app */

$site = $app->getSite($_GET['site']);
set('site', $site);

set('pageTitle', $site->name);

extend('../_layout');

block('page_content');

?>
    <div class="page-content-upper">
        <div class="page-content-upper-inner"><?php

            block('tabs');
            inc('partials/tabs');
            endblock();

            ?></div>
    </div>
    <div class="page-content-body">
        <div class="page-content-body-inner">
            <?php block('page_content_inner', true) ?>
        </div>
    </div>
<?php

endblock();