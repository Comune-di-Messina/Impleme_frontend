(function ($) {
  Drupal.behaviors.pdfSelect = {
    attach: function (context, settings) {
      const $modals = $('.modal[data-user]', context);
      $modals.addClass('empty');
      const endpoint = settings.m_api.docEndpoint;
      $modals
        .once('pdf-select')
        .on('click', '.list-docs li', function (event) {
          event.preventDefault();
          event.stopPropagation();
          const element = event.currentTarget;
          const href = $('a', element).attr('href')
          if (typeof href != 'undefined') {
            const modal = event.delegateTarget;
            const $modal = $(modal);
            const docType = href.replace('#','');
            const cf = $modal.data('user');
            const uri = endpoint.replace('TYPE', docType).replace('CF_ENC', cf);
            const downloadUri = uri + '/download';
            const $wrapper = $('.col-doc-main', $modal);
            let $iframe = $('iframe', $wrapper);
            if ($iframe.length < 1) {
              $iframe = $('<iframe>').attr('id', 'frame-' + modal.id).addClass('visure-pdf-viewer').appendTo($wrapper);
              $iframe.on('load', function (event) {
                $modal.removeClass('loading');
              });
            }
            $modal.addClass('loading').removeClass('empty');
            $iframe.attr('src', uri);
            $('.col-doc-actions a[href$="download"]', $modal).attr('href', downloadUri);
          }
        })
        .on('click', '.col-doc-actions a[href="#print"]', function (event) {
          event.preventDefault();
          event.stopPropagation();
          const $frame = $('iframe', event.delegateTarget);
          if ($frame.length > 0) {
            const frameId = $frame[0].id;
            window.frames[frameId].focus();
            window.frames[frameId].contentWindow.print();
          }
        });
    }
  };
}(jQuery));
