<section class="landing">
    <?php block('content') ?>
    <h1><?= $h1; ?></h1>
    <h2><?= $h2; ?></h2>
    <?php endblock() ?>

    <?php block('cta') ?>
    <a class="btn" href="<?= $ctaLink; ?>"><?= $ctaTitle; ?></a>
    <?php endblock() ?>
</section>