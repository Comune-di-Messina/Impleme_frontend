(function ($) {
  Drupal.behaviors.pageSwitcherSelect = {
    attach: function (context, settings) {
      // var path = .serviceUrl;
      // var id = settings.m_api.elementId;
      // if (!Array.isArray(id)) {
      //   id = [id];
      // }
      for (var id in settings.m_api.switcher) {
        // skip loop if the property is from prototype
        if (!settings.m_api.switcher.hasOwnProperty(id)) { continue;
        }
        $('#' + id, context).once(id).on('change', function (event) {
          const elementId = event.currentTarget.id;
          const path = settings.m_api.switcher[elementId];
          let prefix = '?'
          if (path.includes('?')) {
            prefix = '&'
          }
          window.location.href = path + prefix + elementId + '=' + $(this).val();
        });
      }
    }
  };

  Drupal.behaviors.pageSwitcherSelectTari = {
    attach: function (context, settings) {
      const classSelector = '.select-tari--cod-switcher';
      const $tabs = $('.nav-tabs .active');

      $(document).ready(function () {
        if (window.location.hash.length > 0) {
          $('.nav-tabs .nav-link').removeClass('active');
          $('.nav-tabs .nav-link[href="' + window.location.hash + '"]').click();
          $('.nav-tabs .nav-link[href="' + window.location.hash + '"]').addClass('active');
        }
      });

      $(classSelector, context).on('change', function (event) {
        const elementId = event.currentTarget.id;
        const activeTabs = $('.nav-tabs .active').attr('href');

        window.location.href = '?' + elementId + '=' + $(this).val() + activeTabs;
      });
    }
  };
}(jQuery));
