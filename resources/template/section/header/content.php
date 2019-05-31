<header>
    <?php if ($companyName) : ?>
        <div class="companyName"><?= $companyName; ?></div>
    <?php endif ?>
    <?php if ($ctaTitle) : ?>
        <a class="cta" href="<?= $ctaLink; ?>">
            <?= $ctaTitle; ?>
        </a>
    <?php endif ?>
</header>