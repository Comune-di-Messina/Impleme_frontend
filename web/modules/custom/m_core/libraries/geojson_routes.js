(function ($) {

  Drupal.behaviors.m_maps = {
    attach: function (context, settings) {

      $(document).on('geofieldMapInit', function(e, mapid) {
        var map = Drupal.geoFieldMap.map_data[mapid].map;
        var markers = Drupal.geoFieldMap.map_data[mapid].markers;

        // var directionsRenderer = new google.maps.DirectionsRenderer();

        // Set directionsRenderer's map.
        // directionsRenderer.setMap(map);

        // Get directions variable builded in m_core.module.
        var directions = settings.m_maps.directions;
        var waypts = [];

        // Create waypoints array.
        for (var i = 0; i < directions.waypoints.length; i++) {
          var point = directions.waypoints[i];
          // Build a LatLng object and push it into waypts array.
          var latlng = new google.maps.LatLng(point[1], point[0]);
          waypts.push({location: latlng});
        }

        const origin = new google.maps.LatLng(directions.origin[1], directions.origin[0]);
        const destination = new google.maps.LatLng(directions.destination[1], directions.destination[0]);

        // Create the request for directionsService.
        var request = {
          origin: origin,
          destination: destination,
          waypoints: waypts,
          optimizeWaypoints: true,
          travelMode: google.maps.DirectionsTravelMode.DRIVING
        };

        var result = Object.assign({}, {request: request}, JSON.parse(directions.parsedResponse));

        var decodedPath = google.maps.geometry.encoding.decodePath(result.routes[0].overview_polyline.points);

        var poly = new google.maps.Polyline({
          path: decodedPath,
          strokeColor: directions.strokeColor,
          strokeOpacity: 0.25,
          strokeWeight: 4,
          map: map
        });
      });
    }
  };
})(jQuery);
