(function ($) {

  Drupal.behaviors.m_maps = {
    attach: function (context, settings) {

      $(document).on('geofieldMapInit', function (e, mapid) {

        //Mappa Eventi
        if (mapid.substring(0, 36) == 'geofield-map-view-eventi-attachment-') {
          var map = Drupal.geoFieldMap.map_data[mapid].map;
          var markers = Drupal.geoFieldMap.map_data[mapid].markers;
          var map_center = map.getCenter();
          var map_zoom = map.getZoom();
          $('.view-id-eventi .module.card', context).once().each(function () {
            let elemento_mappa = $(this);
            elemento_mappa.click(
              function(e) {
                if (!$(e.target).parent().hasClass('card-link')) {
                  var marker_id = $(this).data('history-node-id');
                  map.infowindow.close();
                  map.setZoom(18);
                  setTimeout(function () {
                    if (markers[marker_id]) {
                      map.panTo(markers[marker_id].getPosition());
                      google.maps.event.trigger(markers[marker_id], 'click');
                    }
                  }, 500);
                }
              }
            );
         });
        }

        //Mappa Cultura Enogastronomia
        if (mapid.substring(0, 37) == 'geofield-map-view-schede-attachment-1') {
          var map = Drupal.geoFieldMap.map_data[mapid].map;
          var markers = Drupal.geoFieldMap.map_data[mapid].markers;
          var map_center = map.getCenter();
          var map_zoom = map.getZoom();
          $('.view-display-id-block_2 .module.card', context).once().each(function () {
            let elemento_mappa = $(this);
            elemento_mappa.click(
              function(e) {
                if (!$(e.target).parent().hasClass('card-link')) {
                  var marker_id = $(this).data('history-node-id');
                  map.infowindow.close();
                  map.setZoom(18);
                  setTimeout(function () {
                    if(markers[marker_id]) {
                      map.panTo(markers[marker_id].getPosition());
                      google.maps.event.trigger(markers[marker_id], 'click');
                    }
                  }, 500);
                }
              }
            );
         });
        }

        //Mappa Escursioni
        if (mapid.substring(0, 40) == 'geofield-map-view-itinerari-attachment-1') {
          var map = Drupal.geoFieldMap.map_data[mapid].map;
          var markers = Drupal.geoFieldMap.map_data[mapid].markers;
          var map_center = map.getCenter();
          var map_zoom = map.getZoom();
          $('.view-display-id-block_2', context).once().each(function () {
              for (marker in markers) {
                var marker_color = $(this).find("#marker-color-" + marker);
                if (marker_color.length) {
                  marker_color = marker_color.data("marker-color");
                } else {
                  marker_color = '#3b76bd';
                }
                markers[marker].setIcon({
                  path: "M10 0C15.4 0 19.8 4.4 19.8 9.8 19.8 13.5 18.3 16.7 15.9 19.5 14.3 21.4 12.4 22.7 10.7 23.7 10.3 24 9.7 24 9.3 23.7 7.6 22.7 5.7 21.3 4.1 19.5 1.7 16.7 0.3 13.5 0.3 9.8 0.3 4.4 4.6 0 10 0ZM10 8C9.2 8 8.5 8.7 8.5 9.5 8.5 10.3 9.2 11 10 11 10.8 11 11.5 10.3 11.5 9.5 11.5 8.7 10.8 8 10 8Z",
                  scale: 1.0,
                  fillColor: marker_color,
                  fillOpacity: 1.0,
                  strokeColor: marker_color,
                  strokeWeight: 0.3,
                  strokeOpacity: 0.6
                });
              }
            });
          $('.view-display-id-block_2 .module.card', context).once().each(function () {
            let elemento_mappa = $(this);
            elemento_mappa.click(
              function(e) {
                if (!$(e.target).parent().hasClass('card-link')) {
                  var marker_id = $(this).data('history-node-id');
                  map.infowindow.close();
                  map.setZoom(18);
                  setTimeout(function () {
                    if (markers[marker_id]) {
                      map.panTo(markers[marker_id].getPosition());
                      google.maps.event.trigger(markers[marker_id], 'click');
                    }
                  }, 500);
                }
              }
            );
         });
        }

        if (mapid.substring(0, 40) == 'geofield-map-view-itinerari-attachment-2') {
          var map = Drupal.geoFieldMap.map_data[mapid].map;
          var markers = Drupal.geoFieldMap.map_data[mapid].markers;
          var map_center = map.getCenter();
          var map_zoom = map.getZoom();
          $('.view-display-id-block_1', context).once().each(function () {
              for (marker in markers) {
                var marker_color = $(this).find("#marker-color-" + marker);
                if (marker_color.length) {
                  marker_color = marker_color.data("marker-color");
                } else {
                  marker_color = '#3b76bd';
                }
                markers[marker].setIcon({
                  path: "M10 0C15.4 0 19.8 4.4 19.8 9.8 19.8 13.5 18.3 16.7 15.9 19.5 14.3 21.4 12.4 22.7 10.7 23.7 10.3 24 9.7 24 9.3 23.7 7.6 22.7 5.7 21.3 4.1 19.5 1.7 16.7 0.3 13.5 0.3 9.8 0.3 4.4 4.6 0 10 0ZM10 8C9.2 8 8.5 8.7 8.5 9.5 8.5 10.3 9.2 11 10 11 10.8 11 11.5 10.3 11.5 9.5 11.5 8.7 10.8 8 10 8Z",
                  scale: 1.0,
                  fillColor: marker_color,
                  fillOpacity: 1.0,
                  strokeColor: marker_color,
                  strokeWeight: 0.3,
                  strokeOpacity: 0.6
                });
              }
            });
          $('.view-display-id-block_1 .module.card', context).once().each(function () {
            let elemento_mappa = $(this);
            elemento_mappa.click(
              function(e) {
                if (!$(e.target).parent().hasClass('card-link')) {
                  var marker_id = $(this).data('history-node-id');
                  map.infowindow.close();
                  map.setZoom(18);
                  setTimeout(function () {
                    if (markers[marker_id]) {
                      map.panTo(markers[marker_id].getPosition());
                      google.maps.event.trigger(markers[marker_id], 'click');
                    }
                  }, 500);
                }
              }
            );
         });
        }

        //Mappa indirizzi
        if (mapid.substring(0, 39) == 'geofield-map-view-paragraph-map-default') {
          var map = Drupal.geoFieldMap.map_data[mapid].map;
          var markers = Drupal.geoFieldMap.map_data[mapid].markers;
          var map_center = map.getCenter();
          var map_zoom = map.getZoom();

          if (!$('body').hasClass('maps-processed')) {
            for (marker in markers) {
              google.maps.event.addListener(markers[marker],'click',function() {
                let indirizzo_cliccato = $('.paragraph--type--elemento-mappa[data-marker-id=' + this.geojsonProperties.entity_id + ']') ; //this.options.icon.options.id.replace('divicon-', '');
                let tab = $('.group-content', indirizzo_cliccato);
                if (!tab.hasClass('h-active')) {
                  $('.group-content').removeClass('h-active');
                  tab.addClass('h-active');
                  tab.closest('.paragraph--type--mappa-indirizzi').attr('class').split(' ')[0].replace('paragraph-', '');
                  tab.closest('.paragraph--type--elemento-mappa').attr('class').split(' ')[0].replace('paragraph-', '');
                }
              });
            }
          }

          $('.paragraph--type--elemento-mappa').once().each(function () {
            let elemento_mappa = $(this);
            elemento_mappa.click(
              function() {
                var marker_id = $(this).data('marker-id');
                map.setZoom(18);
                setTimeout(function () {
                  if(markers[marker_id]) {
                    map.panTo(markers[marker_id].getPosition());
                    google.maps.event.trigger(markers[marker_id], 'click');
                  }
                }, 500);
              }
            );

            elemento_mappa.find('.field--name-field-titolo').off().on({
              mouseenter: function (e) {
                let childParagraphId = $(this).closest('.paragraph--type--elemento-mappa').attr('class').split(' ')[0].replace('paragraph-', '');
                $('.pin-' + childParagraphId).toggleClass('pin-zoom');
              },
              mouseleave: function (e) {
                let childParagraphId = $(this).closest('.paragraph--type--elemento-mappa').attr('class').split(' ')[0].replace('paragraph-', '');
                $('.pin-' + childParagraphId).toggleClass('pin-zoom');
              },
              click: function (e) {
                let parentParagraphId = $(this).closest('.paragraph--type--mappa-indirizzi').attr('class').split(' ')[0].replace('paragraph-', '');

                let childParagraphId = $(this).closest('.paragraph--type--elemento-mappa').attr('class').split(' ')[0].replace('paragraph-', '');

                $(this).siblings('.group-content').toggleClass('h-active');

              },
            });

            elemento_mappa.find('.field-titolo-dettaglio, .field-back').off().on({
              click: function (e) {
                e.stopPropagation();
                e.preventDefault();

                let parentParagraphId = $(this).closest('.paragraph--type--mappa-indirizzi').attr('class').split(' ')[0].replace('paragraph-', '');

                $(this).closest('.group-content').toggleClass('h-active');

                map.infowindow.close();
                map.setZoom(map_zoom);
                map.panTo(map_center);
                setTimeout(function () {
                  map.setZoom(map_zoom);
                  map.panTo(map_center);
                  map.infowindow.close();
                }, 1000);
              },
            });
          });

          $('body').addClass('maps-processed');
        }
      });
    }
  }
})(jQuery);
