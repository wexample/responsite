<?php

/** @var App $app */

set('pageTitle', $site->name);

extend('template::layout/admin');

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