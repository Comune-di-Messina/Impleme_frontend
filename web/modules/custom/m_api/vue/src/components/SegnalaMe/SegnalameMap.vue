<template>
  <div class="segnalame-map container">
    <div class="row">
      <div class="col-12 col-lg-4 col-content p-4 d-flex flex-column">
        <div class="col-content-header">
          <div class="block-title pb-2">
            Inserisci il pin sulla mappa o digita l'indirizzo
          </div>
        </div>
        <div class="col-content-main segnalame-map__form">
          <span><strong>Indirizzo</strong></span>
          <input ref="autocompleteInput" />
          <div class="segnalame-map__form__result">
            <span class="h-icon h-pin-green"></span>
            <strong v-if="!segnalazione.address"
              >Nessun indirizzo inserito</strong
            >
            <strong v-else class="segnalame-map__form__result-address">{{ segnalazione.address }}</strong>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8 p-lg-0">
        <div class="google-map-container" ref="mapContainer"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { Loader } from "@googlemaps/js-api-loader";

export default {
  name: "SegnalameMap.vue",
  props: {
    segnalazione: {
      type: Object,
      required: true
    }
  },
  data: function() {
    return {
      map: null,
      marker: null,
      apiKey: "AIzaSyDXjOCDBEIWap1f5f3w0MPN7LAntBSvYSw"
    };
  },
  mounted() {
    this.loadGoogleMap();
  },
  methods: {
    loadGoogleMap: function() {
      const loader = new Loader({
        apiKey: this.apiKey,
        version: "weekly",
        libraries: ["places"]
      });
      loader.load().then(res => {
        this.map = new window.google.maps.Map(this.$refs.mapContainer, {
          center: { lat: 38.19394, lng: 15.55256 },
          zoom: 16
        });
        const autocomplete = new google.maps.places.Autocomplete(
          this.$refs.autocompleteInput,
          {
            componentRestrictions: {
              country: "it"
            },
            fields: ["formatted_address", "geometry", "name"]
          }
        );

        this.map.addListener("click", this.addMarker);

        autocomplete.addListener("place_changed", () => {
          if (this.marker) {
            this.marker.setMap(null);
          }
          const place = autocomplete.getPlace();
          if (place.geometry.viewport) {
            this.map.fitBounds(place.geometry.viewport);
          } else {
            this.map.setCenter(place.geometry.location);
            this.map.setZoom(17);
          }

          this.marker = new google.maps.Marker({
            position: place.geometry.location,
            map: this.map,
            icon: `/themes/custom/portalemessina/dist/images/icons/segnala-me/pin-default.png`
          });
          this.$emit("onAddress", {
            address: place.formatted_address,
            latitude: place.geometry.location.lat(),
            longitude: place.geometry.location.lng()
          });
        });
      });
    },
    addMarker: function(e) {
      if (this.marker) {
        this.marker.setMap(null);
      }
      this.marker = new google.maps.Marker({
        position: e.latLng,
        map: this.map,
        icon: `/themes/custom/portalemessina/dist/images/icons/segnala-me/pin-default.png`
      });
      this.map.panTo(e.latLng);
      this.$refs.autocompleteInput.value = '';
      this.getPlace(e.latLng);
    },
    getPlace: function(latLng) {
      const coords = `${latLng.lat()},${latLng.lng()}`;
      const endpoint = `${drupalSettings.m_api.endpoints.geodecode}/${coords}`;
      this.$http
        .get(endpoint)
        .then(res => {
          this.$emit("onAddress", {
            address: res.data,
            latitude: latLng.lat(),
            longitude: latLng.lng()
          });
        })
        .catch(err => {
          console.log("err");
          console.log(err);
        });
    }
  }
};
</script>
