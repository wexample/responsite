<?php
/** @var App $app */

set('pageTitle', $pageTitle . ' | Admin');

$sites = $app->getSites();

extend('../common/_layout');

block('head'); ?>
    <link rel="stylesheet" href="/resources/css/admin.css">
<?php endblock(); ?>

<?php block('body'); ?>
    <section class="layout-section">
        <nav id="sidenav">
            <div id="logo" class="site-name">
                <div id="logo-wrapper">
                    <svg id="logo-svg" xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 250 250"
                         version="1">
                        <path d="M68.39230346679688,3 c-8,15 -18,43 -17,97 c-2,-12 -3,-29 -2,-45 c0,-5 -2,-5 -3,0 c-4,22 -5,49 -2,66 c-8,-18 -35,-21 -44,0 c-1,3 0,4 2,2 c10,-10 27,-4 21,17 c-9,33 -8,55 -5,69 c2,14 9,16 16,18 c10,3 68,12 108,9 c9,-2 2,-7 15,-7 c21,0 47,-1 64,-6 c7,-1 7,-7 6,-15 c-2,-20 -10,-89 18,-117 c2,-2 0,-4 -2,-3 c-14,5 -26,15 -36,35 c-3,-47 -32,-38 -50,-68 c-1,0 -2,-2 -4,-1 c-3,3 -6,12 -7,23 c-8,-24 -34,-26 -37,-53 c-1,-3 -3,-4 -4,-1 c-6,12 -12,48 -8,69 c-14,-13 -22,-66 -23,-89 c0,-2 -1,-3 -2,-3 l-4,3 "
                              fill="#070"></path>
                        <path d="M135.39230346679688,204 c9,-1 9,-14 1,-18 s-21,8 -36,8 c-4,0 -6,12 2,12 l33,-2 M77.39230346679688,205 c9,2 11,-11 0,-13 c-8,-1 -14,-2 -20,0 c-7,2 -8,11 -1,11 l21,2 "
                              fill="#fff"></path>
                    </svg>
                </div>
                <div>
                    <span class="respon">respon.</span><span
                            class="site">site</span>
                </div>
            </div>
            <?php block('sidenav'); ?>
            <ul>
                <?php if (isset($respometer) === true) : ?>
                    <li class="sidebar-active">
                        <a href="/admin/respometer"
                           class="sidebar-active--link">Respomètre</a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="/admin/respometer">Respometer</a>
                    </li>
                <?php endif ?>

                <li class="separator">Sites</li>
                <?php foreach ($sites as &$site) { ?>
                    <?php if (isset($_GET['site']) === true && $_GET['site'] === $site->name) : ?>
                        <li class="sidebar-active">
                            <a href="/admin/site?site=<?= $site->name ?>"
                               class="sidebar-active--link">
                                <?= $site->name ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="/admin/site?site=<?= $site->name ?>">
                                <?= $site->name ?>
                            </a>
                        </li>
                    <?php endif ?>
                <?php } ?>
            </ul>
            <?php endblock(); ?>
        </nav>
    </section>
    <section id="page-content">
        <?php block('page_content', true); ?>
    </section>
<?php endblock(); ?>