document
  .getElementById('form-contact')
  .addEventListener('submit', function (e) {
    e.preventDefault();

    app.formPost(e.currentTarget, function (r) {
      var message = '';
      switch (r.responseText) {
        case 'SENT':
          message = 'Message envoyé ! Merci !';
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
