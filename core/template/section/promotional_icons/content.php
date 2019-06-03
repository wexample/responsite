<section class="icons">
    <div class="row">
        <?php foreach ($blocks as &$block): ?>
            <div class="col s12 m3">
                <div class="icon"><?= parse($block['icon']); ?></div>
                <div class="title"><?= $block['title']; ?></div>
                <div class="desc"><?= parse($block['description']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>