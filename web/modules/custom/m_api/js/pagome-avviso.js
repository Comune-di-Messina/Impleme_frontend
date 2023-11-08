(function ($) {

  Drupal.behaviors.pagomeAvviso = {
    attach: function (context, settings) {
      const submit_selector = 'btn-paga';
      const cf_selector = 'cod_fiscale';
      const iuv_selector = 'iuv';
      $submit = $('#' + submit_selector);
      $inputCf = $('#' + cf_selector);
      $inputIUV = $('#' + iuv_selector);
      $error = $('.form-error');
      const checkData = function (event) {
        if ($inputCf.val().length == 16 && $inputIUV.val().length >= 15) {
          $submit.removeClass('disabled').prop('disabled', false);
        } else {
          $submit.addClass('disabled').prop('disabled', true);
        }
      }
      const displayError = function (message) {
        $('strong', $error).text(message)
        $error.removeClass('hidden');
      }
      const events = 'change keyup';
      const once = 'pagome-paga';
      $inputCf.once(once).on(events, checkData);
      $inputIUV.once(once).on(events, checkData);
      checkData();
      $submit.once(once).on('click', function (event) {
        const params = {};
        params[cf_selector] = $inputCf.val(),
        params[iuv_selector] = $inputIUV.val()
        params['destination'] = window.location.pathname + window.location.search
        $error.addClass('hidden');
        const dest = settings.m_api.pagome.url;
        $.ajax(dest, {
          data: params,
          success: function (data, stringResponse, jqXHR) {
            if (typeof data == 'string') {
              window.location.href = data
            } else {
              messaggio = data.hasOwnProperty('descrizione') ? data.descrizione : "Errore";
              displayError(messaggio);
            }
          },
          error: function (jqXHR, status, message) {
            displayError(message);
          }
        });
      });
    }
  };
}(jQuery));
