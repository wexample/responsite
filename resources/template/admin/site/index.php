<?php
/** @var App $app */

extend('_layout');

block('page_content_inner');

$site = get('site');

$months = [
    date('Y-m'),
    date('Y-m', strtotime('previous month')),
];

$dir  = SERVER_PATH_DATA . 'stats/' . $site->name . '/';
$data = [];

foreach ($months as $fineName)
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
foreach ($data as $date => $hourly)
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
    <div id="site-tips">
        <div class="tip subsection">
            <div class="icon">i</div>
            <div class="desc">
                <h3>Configurez votre domaine principal</h3>
                Le domaine principal de votre site doit pointer vers le
                répertoire <code><?= SERVER_PATH_BUILD . $site->name ?></code>.
                Cela permettra aux requête d'accéder directement à vos pages
                sans subir de redirection, et ainsi améliorer la performance.
                Une fois fait, vous pouvez désactiver l'affichage du site par
                défaut à la racine du l'application en ajoutant la directive <code>$config['rootSiteFallback'] = false</code>.
            </div>
        </div>
        <div class="tip subsection">
            <div class="icon">i</div>
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