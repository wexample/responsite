<?php
/** @var App $app */
/** @var Site $site */

extend('base');

block('page_content_inner');

$site = get('site');
$site->loadConfig();

?>
    <br>
    <div class="subsection">
        <div class="desc">
            <h3>Respomètre</h3>
            Voici la comparaison des performances de votre site par rapport à
            des sites similaires. Notre algorithme se base sur des critères de
            taille des pages, de temps de chargements ou encore du nombre de
            ressources complémentaires utilisées (CSS, Javascript, etc.)
        </div>
        <?php
        $reportPathFolder = $reportPath = SERVER_PATH_ROOT . 'data/stats/' . $site->name;
        $reportPath       = $reportPathFolder . '/report.php';
        $siteDomain       = $site->name . '.responsite.wex';

        require SERVER_PATH_FUNCTION . 'respometer.php';
        analyze(
            file_get_contents(
                SERVER_PATH_ROOT . 'build/' . $site->name . '/index.html'
            ),
            $siteDomain,
            $siteDomain,
            $reportPathFolder
        );

        require $reportPath;

        require SERVER_PATH_CORE . 'data/stats.php';

        $margin                = 20;
        $space                 = 40;
        $radius                = 10;
        $diameter              = $radius * 2;
        $offset                = ($radius * 2) + $space;
        $count                 = 10;
        $siteRank              = (int) round($report['index'] / $count);
        $milestones            = [];
        $milestones[$siteRank] = 'Vous';
        $siteRank              = (int) round($report['index'] / $count);
        $width                 = ($count * $offset) + $margin + $space;
        $height                = $diameter + ($margin * 2) + 20;

        foreach ($respometerSitesReference as $name => $item)
        {
            $milestones[(int) round($item['index'] / $count)] = $name;
        }

        ?>
        <div id="site-graph-respometer">
            <svg
                 viewBox="0,0,<?= $width ?>,<?= $height ?>">

                <?php for ($i = 0; $i < $count; $i++) : ?>
                    <?php $x = ($i * $offset) + $radius + $space ?>
                    <!--                    <svg width="--><? //= $diameter ?><!--" height="100px">-->
                    <?php if (isset($milestones[$i + 1])): ?>
                        <text x="<?= $x ?>"
                              y="<?= $margin + $diameter + 20 ?>"
                              dominant-baseline="middle"
                              text-anchor="middle"><?= $milestones[$i + 1] ?>
                        </text>
                    <?php endif ?>
                    <!--                    </svg>-->
                    <circle class="unselected" r="<?= $radius ?>"
                            cx="<?= $x ?>"
                            cy="<?= $radius + $margin ?>"></circle>
                    <?php if (($i + 1) === $siteRank): ?>
                        <circle class="selected" r="<?= $radius * 1.5 ?>"
                                cx="<?= ($i * $offset) + $radius + $space ?>"
                                cy="<?= $radius + $margin ?>"></circle>
                    <?php endif ?>
                <?php endfor ?>
            </svg>
        </div>
    </div>
<?php

$months = [
    date('Y-m'),
    date('Y-m', strtotime('previous month')),
];

$dir  = SERVER_PATH_DATA . 'stats/' . $site->name . '/';
$data = [];

foreach ($months as &$fineName)
{
    $filePath = $dir . $fineName . '.json';
    if (is_file($filePath))
    {
        $data = array_merge(
            $data,
            json_decode(file_get_contents($filePath), JSON_OBJECT_AS_ARRAY)
        );
    }
}

$x         = 0;
$height    = 200;
$barW      = 10;
$barSpace  = 4;
$days      = 31;
$max       = 10;
$dailyHits = [];
foreach ($data as $date => &$hourly)
{
    $key             = substr($date, 0, 10);
    $dailyHits[$key] = isset($dailyHits[$key]) ? $dailyHits[$key] : 0;
    $dailyHits[$key] += (int) $hourly['hits'];
    if ($dailyHits[$key] > $max)
    {
        $max = $dailyHits[$key];
    }
}

?>
    <div id="site-graph-hits" class="subsection">
        <svg height="<?= $height ?>"
             width="<?= (($barW + $barSpace) * $days) ?>">
            <?php for ($i = -($days - 1); $i <= 0; $i++): ?>
                <?php
                $barH = 10;

                $total = 0;

                for ($j = 0; $j <= 23; $j++)
                {
                    $key = date(
                            'Y-m-d',
                            strtotime($i . ' days')
                        ) . ' ' . sprintf("%02d", $j);

                    if (isset($data[$key]))
                    {
                        echo $key;
                        $total += $data[$key]['hits'];
                    }
                }

                $barH = ($total / $max) * $height;

                ?>
                <rect class="bar" x="<?= $x ?>"
                      y="<?= ($height - $barH) ?>"
                      width="<?= $barW ?>"
                      height="<?= $barH ?>"/>
                <?php
                $x += $barW + $barSpace;
                ?>
            <?php endfor; ?>
        </svg>
    </div>
    <div class="action-section subsection">
        <div class="desc">
            Les changements que vous appliquez à votre site ne seront visibles
            par les utilisateurs qu'une fois celui-ci déployé.
            Le déploiement va construire toutes les pages de votre site, les
            optimiser, et le rendre visible par l'utilisateur.
            Si vous souhaitez voir les changements avant le déploiement,
            rendez-vous dans l'onglet <b>Prévisualisation</b>
        </div>
        <div class="action">
            <a href="/admin/site/build?site=<?= $site->name ?>&destination=/admin/site?site=<?= $site->name ?>"
               class="btn">Déployer</a>
        </div>
    </div>
    <div id="site-tips">
        <?php if ($site->config['rootSiteFallback']): ?>
            <div class="tip subsection">
                <div class="icon"><?php inc('template::icon/perf') ?></div>
                <div class="desc">
                    <h3>Configurez votre domaine principal</h3>
                    Le domaine principal de votre site doit pointer vers le
                    répertoire
                    <code><?= SERVER_PATH_BUILD . $site->name ?></code>.
                    Cela permettra aux requêtes d'accéder directement à vos
                    pages
                    sans subir de redirection, et ainsi améliorer la
                    performance.
                    Une fois fait, vous pouvez désactiver l'affichage du site
                    par
                    défaut à la racine du l'application en ajoutant la directive
                    <code>$config['rootSiteFallback'] = false</code>.
                </div>
            </div>
        <?php endif ?>
        <div class="tip subsection">
            <div class="icon"><?php inc('template::icon/perf') ?></div>
            <div class="desc">
                <h3>Configurez un domaine statique</h3>
                Les fichiers statiques (Images, CSS, JavaScripts, etc.) peuvent
                disposer d'un sous domaine à part. Cela leur
                permet d'être téléchargés en même temps que la page web, plutôt
                que progressivement un par un. Une fois votre sous domaine
                configuré (ex: static.respon.site), vous pouvez l'ajouter à
                votre fichier de configuration. Plusieurs domaines peuvent être
                utilisés simultanément pour les fichiers statiques, cela réduira
                à chaque fois un peu plus les temps de chargement liés au
                chargement de nombreuses ressources.
            </div>
        </div>
    </div>
<?php

endblock();