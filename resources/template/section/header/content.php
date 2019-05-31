<header>
    <div class="company">
        <?php block('company'); ?>
        <?= $company; ?>
        <?php endblock(); ?>
    </div>
    <nav>
        <?php block('nav'); ?>
        <a class="cta" href="<?= $ctaLink; ?>">
            <?= $ctaTitle; ?>
        </a>
        <?php endblock(); ?>
    </nav>
</header>