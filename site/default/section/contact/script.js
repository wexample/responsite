var form = document.getElementById('form-contact');

form.addEventListener('submit', function (e) {
  e.preventDefault();

  app.formPost(e.currentTarget, function (r) {
    var message = '';
    switch (r.responseText) {
      case 'SENT':
        message = 'Message envoyé ! Merci !';

        form.phone.value =
        form.email.value =
        form.message.value = '';

        app.section.modal.close('contact');
        break;
      case 'INCOMPLETE':
        message = 'Le formulaire semble incomplet.';
        break;
      // Other cases are not triggering
    }

    message && alert(message);
  });
});
