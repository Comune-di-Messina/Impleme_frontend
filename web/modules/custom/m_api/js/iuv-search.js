(function ($) {
  Drupal.behaviors.iuvSearch = {
    attach: function (context, settings) {
      const $btn = $('.page-user-ricevute .btn-primary', context);
      const $input = $('#codiceIUV', context);
      const path = settings.m_api.iuv_search.url;
      $input.once('iuvsearch').on('change keyup', function (event) {
        if ($input.val().length == 15) {
          $btn.removeClass('disabled').prop('disabled', false);
        } else {
          $btn.addClass('disabled').prop('disabled', true);
        }
      });
      $btn.once('iuvsearch').on('click', function (event) {
        window.location.href = path + '/' + $input.val();
      });
    }
  };
}(jQuery));
