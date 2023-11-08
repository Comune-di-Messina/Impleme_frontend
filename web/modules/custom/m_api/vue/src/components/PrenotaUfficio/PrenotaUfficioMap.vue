<template>
  <div
    class="google-map-container"
    ref="mapContainer"
    :style="{
      width: '100%',
      height: '100%'
    }"
  ></div>
</template>

<script>
import { Loader } from "@googlemaps/js-api-loader";

export default {
  name: "PrenotaUfficioMap",
  props: {
    center: {
      type: Object,
      required: false
    },
    markers: {
      type: Array,
      required: false
    },
    serviceTypeLabel: {
      type: String,
      required: false
    }
  },
  data() {
    return {
      map: null,
      marker: null,
      infoWindow: null,
      pins: []
    };
  },
  watch: {
    markers: function() {
      if (this.markers && this.markers.length > 0) {
        if (this.pins) {
          this.pins.map(x => x.setMap(null));
          this.pins = [];
        }
        if (window.google) {
          this.addMarkerToMap();
        }
      }
    }
  },
  mounted() {
    const loader = new Loader({
      apiKey: "AIzaSyDXjOCDBEIWap1f5f3w0MPN7LAntBSvYSw",
      version: "weekly"
    });
    loader.load().then(res => {
      this.map = new window.google.maps.Map(this.$refs.mapContainer, {
        center: {
          lat: this.center ? this.center.latitude : 0,
          lng: this.center ? this.center.longitude : 0
        },
        zoom: 14
      });
      if (this.center) {
        this.marker = new google.maps.Marker({
          position: new google.maps.LatLng(
            this.center.latitude,
            this.center.longitude
          ),
          map: this.map,
          icon: `/themes/custom/portalemessina/dist/images/icons/segnala-me/pin-default.png`
        });
      }
      if (this.markers && this.markers.length > 0) {
        this.addMarkerToMap();
      }
    });
  },
  methods: {
    addMarkerToMap() {
      this.markers.map(markerInfo => {
        const marker = new google.maps.Marker({
          position: new google.maps.LatLng(
            markerInfo.center.lat,
            markerInfo.center.lng
          ),
          map: this.map,
          icon: `/themes/custom/portalemessina/dist/images/icons/segnala-me/pin-default.png`
        });
        const infoWindow = new google.maps.InfoWindow({
          content: `<div class="page-segnalame-list__info-window">
              <div class="page-segnalame-list__info-window__content">
              <span>${this.serviceTypeLabel}</span>
              <strong>${markerInfo.officeName}</strong>
              </div>
              <div class="page-segnalame-list__info-window__link"><a href="${markerInfo.link}"><strong>Scopri <sub> ⃗</sub></strong></a></div>
            </div>`
        });
        marker.addListener("click", () => {
          if (this.infoWindow) {
            this.infoWindow.close();
          }
          infoWindow.open(this.map, marker);
          this.infoWindow = infoWindow;
        });
        this.pins.push(marker);
      });
      if (this.markers[0]) {
        this.map.setCenter(
          new google.maps.LatLng(
            this.markers[0].center.lat,
            this.markers[0].center.lng
          )
        );
      }
      this.map.addListener("click", () => {
        if (this.infoWindow) {
          this.infoWindow.close();
        }
      });
    }
  }
};
</script>
