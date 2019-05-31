<nav class="tabs">
    <ul>
        <li class="<?= ($requestPath === 'admin/site' ? 'selected' : '') ?>">
            <a href="/admin/site?site=<?= $site->name ?>">Statut</a>
        </li>
        <li class="<?= ($requestPath === 'admin/site/preview' ? 'selected' : '') ?>">
            <a href="/admin/site/preview?site=<?= $site->name ?>">Preview</a>
        </li>
        <li>
            <a target="_blank" href="<?= $site->getUrl() ?>">Site</a>
        </li>
    </ul>
</nav>
