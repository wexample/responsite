<div id="<?= $id ?>" class="modal">
    <div class="modal-bg"></div>
    <div class="modal-inner">
        <a href="#" onclick="app.section.modal.close('<?= $id; ?>')">
            <svg class="modal-close" width="<?= $close['size'] ?>"
                 height="<?= $close['size'] ?>">
                <line x1="0" y1="0" x2="<?= $close['size'] ?>"
                      y2="<?= $close['size'] ?>" stroke="<?= $close['color'] ?>"
                      stroke-width="4"></line>
                <line x1="<?= $close['size'] ?>" y1="0" x2="0"
                      y2="<?= $close['size'] ?>" stroke="<?= $close['color'] ?>"
                      stroke-width="4"></line>
            </svg>
        </a>
        <?php block('content', true) ?>
    </div>
</div>