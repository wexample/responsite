<?php extend('template::section/modal/content') ?>

<?php block('content') ?>
    <form id="form-contact" class="form-contact" method="POST"
          action="<?= page('contact.php'); ?>">
        <input name="phone" placeholder="Téléphone">
        <input name="email" type="email" placeholder="example@domain.com">
        <textarea name="message"
                  placeholder="Décrivez nous votre projet..."></textarea>
        <input type="submit" value="Envoyer">
    </form>
<?php endblock() ?>