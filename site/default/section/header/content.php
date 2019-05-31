<?php extend('template::section/header/content'); ?>

<?php block('company'); ?>
<a href="<?php path('/') ?>">
    <span class="respon">respon.</span><span class="site">site</span>
</a>
<?php endblock(); ?>


<?php block('nav'); ?>
<ul>
    <li>
        <a id="github-link" target="_blank" href="https://github.com/wexample/responsite">
            <?php inc('template::icon/responsite') ?>
        </a>
    </li>
</ul>
<?php endblock(); ?>
