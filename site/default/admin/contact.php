<?php

/** @var App $app */
/** @var Site $site */

extend('template::admin/site/base');

block('page_content_inner');

require SERVER_PATH_FUNCTION . 'data.php';

$contacts = array_reverse(site_data_load($site, 'contacts'));

?>  <br>
    <div class="tips">
        <?php foreach ($contacts as $item): ?>
            <div class="tip subsection">
                <div class="icon">
                    <?= $item->date ?>
                </div>
                <div class="desc">
                    <b>E-mail</b> : <?= $item->date ?><br>
                    <b>Phone</b> : <?= $item->phone ?><br>
                    <b>Message</b> : <br><?= $item->message ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endblock();