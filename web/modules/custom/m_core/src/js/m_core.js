(function ($) {

  Drupal.behaviors.m_core = {
    attach: function (context, settings) {
      let $body = $('body');

      let leaflet = settings.leaflet;
      for (let mapId in leaflet) {
        let mapInfo = settings.leaflet[mapId];

        if (mapInfo.features.length > 0) {
          if (mapInfo.features["0"].type === 'linestring') {
            let map = mapInfo.lMap;
            if (map !== undefined) {
              if (map._loaded) {

                let $mapDiv = $('#' + mapId);
                if (!$mapDiv.hasClass('processed-map')) {
                  let points = mapInfo.features[0].points
                  points.forEach(function (lngLat) {
                    let icon   = L.divIcon({
                      className: 'pin-body',
                      html: '<div class="pin-arrow"></div>',
                      iconAnchor: L.point(10, 34)
                    });
                    var marker = L.marker(lngLat, {icon: icon}).addTo(map);
                    // $('.pin-body').css({
                    //   width: '22px',
                    //   height: '22px',
                    //   border: '8px solid ' + mapInfo.features["0"].path.color,
                    // });

                    // $('.pin-arrow').css({
                    //   'border-top': '17px solid ' + mapInfo.features["0"].path.color,
                    // });

                  });

                  $mapDiv.addClass('processed-map');
                }
              }
            }
          }
        }
      };

      if ($('.view-id-guide_agenzie_turistiche.view-display-id-block_2').length > 0) { // Tab di default = "Agenzie turistiche" (block_2)
        if (!$body.hasClass('mcore-processed')) {
          $('.view-id-guide_agenzie_turistiche.view-display-id-block_1').addClass('hidden');
        }

        $('.tab-element').find('a').off().on('click', function (e) {
          e.preventDefault();
          let show = 'block_1';
          let hide = 'block_2';
          switch ($(this).attr('data-show')) {
            case 'block_1':
              show = 'block_2';
              hide = 'block_1';
              break;
            case 'block_2':
              show = 'block_1';
              hide = 'block_2';
              break;
          }

          $('.view-id-guide_agenzie_turistiche.view-display-id-' + show).toggleClass('hidden');
          $('.view-id-guide_agenzie_turistiche.view-display-id-' + hide).toggleClass('hidden');

          $('.tab-element').find('a').toggleClass('active')
        });

        $body.addClass('guide-agenzie-page');
      }

      if (!$body.hasClass('mcore-processed')) {
        $body.addClass('mcore-processed');
      }

      let $filtersWrapper = $('.filters-wrapper');
      let $mapWrapper = $('.map-wrapper');
      if ($mapWrapper.length > 0) {
        let $mapAttached   = $('.view').find('.attachment.attachment-after');
        let $viewContainer = $mapAttached.closest('.view');

        let $filters = $viewContainer.find('.view-filters');
        if ($filters.length > 0) {
          if (!$filtersWrapper.hasClass('processed')) {
            $filters.appendTo('.filters-wrapper');
            $filtersWrapper.addClass('processed');
          }
          else {
            $filters.remove();
          }
        }

        if ($mapAttached.length > 0) {
          $mapWrapper.replaceWith('<div class="map-wrapper"></div>');
          setTimeout(function () {
            $mapAttached.appendTo('.map-wrapper');
            // let mapId = $mapAttached.find('.leaflet-container').attr('id');

            // let center = settings.leaflet[mapId].lMap.getCenter();
            // let zoom = settings.leaflet[mapId].lMap.getZoom();
            //settings.leaflet[mapId].lMap.invalidateSize();
          }, 1000);
        }
      }

      if ($('.page-user').length > 0) {
        let $filters = $('.view-servizi-area-personale').find('.view-filters');
        if ($filters.length > 0) {
          if (!$filtersWrapper.hasClass('processed')) {
            $filters.appendTo('.filters-wrapper');
            $filtersWrapper.addClass('processed');
          }
          else {
            $filters.remove();
          }
        }
      }

      // Manage video or external links condition.
      let fieldName = false;
      if ($("#edit-field-tipologia").length > 0) {
        fieldName = 'tipologia';
      }
      if ($("#edit-field-tipologia-itinerario").length > 0) {
        fieldName = 'tipologia-itinerario';
      }

      if (fieldName !== false) {
        manageVideoExternalLinkFieldset(fieldName);
        let chosenForm = $('#edit-field-' + fieldName + '-wrapper').find('.form-select');
        chosenForm.change(function (e) {
          manageVideoExternalLinkFieldset(fieldName);
        });
      }

      if ($('.swipebox-video').length > 0) {
        if (!$('.swipebox-video').hasClass('processed')) {
          $('.swipebox-video').swipebox();
          $('.swipebox-video').addClass('processed');
        }
      }

      $(document).click('.pin-arrow', function (e) {
        if ($(e.target).hasClass('pin-arrow')) {
          if (!$(e.target).hasClass('swipebox-processed')) {
            $('.swipebox-video').swipebox();
            $(e.target).addClass('swipebox-processed');
          }
        }
      });

      $('form[id^=views-exposed-form-eventi]').find('input[name=reset]').on('click', function (e) {
        e.preventDefault();
        $(this).closest('form').find("input[type=radio][value=All]").prop('checked', true);
        $(this).closest('form').find("input.form-date").val("");
        $(this).closest('form').trigger("reset");
      })
    }
  }

  function manageVideoExternalLinkFieldset(fieldName) {
    let processed = false;
    let selection = $("#edit-field-" + fieldName).chosen().val();
    if (selection.toLowerCase() === 'video') {
      $('#edit-field-video-url-wrapper').slideDown();
      $('#edit-field-moduli-wrapper').slideUp();
      $('#edit-field-external-link-wrapper').slideUp();
      processed = true;
    }

    if (selection.toLowerCase() === 'link esterno') {
      $('#edit-field-video-url-wrapper').slideUp();
      $('#edit-field-moduli-wrapper').slideUp();
      $('#edit-field-external-link-wrapper').slideDown();
      processed = true;
    }

    if (processed === false) {
      $('#edit-field-video-url-wrapper').slideUp();
      $('#edit-field-external-link-wrapper').slideUp();
      $('#edit-field-moduli-wrapper').slideDown();
    }
}

})(jQuery);
