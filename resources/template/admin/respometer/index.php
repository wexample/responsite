<?php
/** @var App $app */

set('pageTitle', 'Respomètre');
set('respometer', 1);

extend('../_layout');

block('page_content');

// The respometer script needs a session to send back data without javascript
session_start();

?><div class="page-content-body">
    <div class="page-content-body-inner">
        <form class="respometer-form" action="/admin/site/respometer">
            <label for="site-to-analyze" class="respometer-form--label">Site à analyser</label>
            <input id="site-to-analyze"
                   name="siteToAnalyze"
                   class="respometer-form--input"<?php
                        if (isset($_SESSION) === true && array_key_exists('analyzedSite', $_SESSION)) {
                            echo 'value="' . $_SESSION['analyzedSite'] . '""';
                        }
                    ?> autofocus required />
            <input type="submit" class="btn respometer-form--submit" value="Tester" />
        </form>

        <div class="respometer">
            <div class="respometer--comparison respometer--heavy-site">Un site lourd</div>
            <div class="respometer--comparison respometer--recommended-site">Recommandé</div>
            <div class="respometer--our-site">Votre site</div>
        </div>

        <br><br><br>
        <?php

        if (isset($_SESSION) === true && array_key_exists('respometer', $_SESSION) === true) {
            $respometer = $_SESSION['respometer'];
            echo 'Taille du code HTML : ', $respometer['html']['size'], ' caractères<hr>';

            echo 'Fichiers CSS<hr>';
            echo 'Taille totale : ', $respometer['css']['totalSize'], ' caractères<br><br>';
            echo '&nbsp;&nbsp;Externes : ', $respometer['css']['external']['numberOfResources'], ' ressources<br><br>';
            echo '&nbsp;&nbsp;Internes : ', $respometer['css']['internal']['numberOfResources'], ' ressources<br><br>';

            echo 'Fichiers JS<hr>';
            echo 'Taille totale : ', $respometer['js']['totalSize'], ' caractères<br><br>';
            echo '&nbsp;&nbsp;Externes : ', $respometer['js']['external']['numberOfResources'], ' ressources<br><br>';
            echo '&nbsp;&nbsp;Internes : ', $respometer['js']['internal']['numberOfResources'], ' ressources<br><br>';

        }
        ?>
    </div>
</div>
<?php

endblock();