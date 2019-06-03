<?php $site->loadConfig() ?>
<nav class="tabs">
    <ul>
        <li class="<?= ($requestPath === 'admin/site' ? 'selected' : '') ?>">
            <a href="/admin/site?site=<?= $site->name ?>">Statut</a>
        </li>
        <li class="<?= ($requestPath === 'admin/site/preview' ? 'selected' : '') ?>">
            <a target="_blank"
               href="<?= siteURL() ?>admin/site/preview?site=<?= $site->name ?>"
            >Prévisualisation</a>
        </li>
        <li>
            <a target="_blank" href="<?= $site->getUrl() ?>">Site</a>
        </li>
        <?php foreach ($site->config['admin']['tabs'] as $tab => $title): ?>
            <li>
                <a href="/admin/site/tab?site=<?= $site->name ?>&name=<?= $tab ?>">
                    <?= $title ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
