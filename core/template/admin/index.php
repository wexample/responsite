<?php
/** @var App $app */

set('pageTitle', 'Accueil');

extend('../layout/admin');

block('page_content_inner');

$sites = $app->getSites();

?>
    <div id="admin-welcome" class="subsection">
        <div class="desc">
            <h3>Bonjour</h3>
            Les respon.site ont été créés avec amour et savoir faire par le réseau de développeurs <a target="_blank" href="http://wexample.com">Wexample</a>.
            Vous pouvez ici déployer les modifications et consulter l'état de vos sites.
        </div>
    </div><?php

endblock();