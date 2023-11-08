<template>
  <div class="page-segnalame-list">
    <div class="container">
      <div>
        <div class="row my-5">
          <div class="col-6">
            <pagome-select
              id="filtroArea"
              v-model="filtro.area"
              label="Filtra per categoria di intervento"
              placeholder="Tutte"
              :options="optionsAree"
            />
          </div>
          <div class="col-6">
            <pagome-select
              id="filtroStato"
              v-model="filtro.stato"
              label="Filtra per stato"
              placeholder="Tutti"
              :options="optionsStato"
            />
          </div>
        </div>
        <div class="row my-5">
          <div class="col-8 my-5" v-if="!loading && segnalazioni.length > 0">
            <div class="row">
              <div
                v-for="segnalazione in segnalazioni"
                class="col-6 my-4"
                v-if="!loading"
              >
                <segnalame-card
                  :segnalazione="segnalazione"
                  :detail-url="getDetailUrl(segnalazione.id)"
                ></segnalame-card>
              </div>
            </div>
          </div>
          <div class="col-4">
            <div class="google-map-container" ref="mapContainer"></div>
          </div>
        </div>
        <div class="col-8 my-5" v-if="!loading && segnalazioni.length === 0">
          Nessuna segnalazione trovata
        </div>
        <div v-if="loading" class="col-12 my-2 text-center">
          <spinner></spinner>
        </div>
        <ul class="pagination" v-if="pages > 1">
          <li
            :class="{ disabled: currentPage === 0 }"
            class="page-item"
            @click="changePage(currentPage)"
          >
            <
          </li>
          <li
            v-for="page in pages"
            :class="{ 'is-active': currentPage === page - 1 }"
            class="page-item"
          >
            <div @click="changePage(page)">{{ page }}</div>
          </li>
          <li
            :class="{ disabled: currentPage + 1 === pages }"
            class="page-item"
            @click="changePage(currentPage + 2)"
          >
            >
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { Loader } from "@googlemaps/js-api-loader";
import PagomeSelect from "./components/form/PagomeSelect.vue";
import SegnalameCard from "./components/SegnalaMe/SegnalameCard.vue";
import Spinner from "./components/ui/Spinner.vue";

export default {
  components: {
    PagomeSelect,
    SegnalameCard,
    Spinner
  },
  data() {
    return {
      loading: true,
      map: null,
      pages: 0,
      apiKey: "AIzaSyDXjOCDBEIWap1f5f3w0MPN7LAntBSvYSw",
      markers: [],
      infoWindow: null,
      segnalazioni: [],
      currentPage: 0,
      filtro: {},
      optionsAree: [
        {
          value: false,
          text: "Tutte"
        }
      ],
      optionsStato: [
        {
          value: false,
          text: "Tutti"
        }
      ]
    };
  },
  computed: {},
  watch: {
    "filtro.area": function() {
      if (this.currentPage === 0) {
        this.getSegnalazioni();
      } else {
        this.currentPage = 0;
      }
    },
    "filtro.stato": function() {
      if (this.currentPage === 0) {
        this.getSegnalazioni();
      } else {
        this.currentPage = 0;
      }
    },
    currentPage: function() {
      this.getSegnalazioni();
    }
  },
  mounted() {
    if (this.$refs.mapContainer) {
      this.loadGoogleMap();
    }
  },
  methods: {
    loadGoogleMap: function() {
      const loader = new Loader({
        apiKey: this.apiKey,
        version: "weekly"
      });
      loader.load().then(() => {
        this.map = new window.google.maps.Map(this.$refs.mapContainer, {
          center: { lat: 38.19394, lng: 15.55256 },
          zoom: 14
        });

        this.map.addListener("click", () => {
          if (this.infoWindow) {
            this.infoWindow.close();
          }
        });

        this.optionsAree = [...this.optionsAree, ...drupalSettings.m_api.aree];
        this.optionsStato = [
          ...this.optionsStato,
          ...drupalSettings.m_api.stati
        ];

        this.getSegnalazioni();
      });
    },
    addMarkers: function() {
      this.markers.map(marker => {
        marker.setMap(null);
      });
      this.segnalazioni.map(x => {
        const marker = new google.maps.Marker({
          position: new google.maps.LatLng(x.latitude, x.longitude),
          map: this.map,
          icon: `/themes/custom/portalemessina/dist/images/icons/segnala-me/pin-status-id-${x.status.id}.png`
        });
        const infoWindow = new google.maps.InfoWindow({
          content: `<div class="page-segnalame-list__info-window">
              <div class="page-segnalame-list__info-window__title">${
                x.institute.name
              }</div>
              <div class="page-segnalame-list__info-window__content"><strong>${
                x.title
              }</strong></div>
              <div class="page-segnalame-list__info-window__link"><a href="${this.getDetailUrl(
                x.id
              )}"><strong>Vai al dettaglio <sub> ⃗</sub></strong></a></div>
            </div>`
        });
        marker.addListener("click", () => {
          if (this.infoWindow) {
            this.infoWindow.close();
          }
          infoWindow.open(this.map, marker);
          this.infoWindow = infoWindow;
        });
        this.markers.push(marker);
      });
    },
    getSegnalazioni: function() {
      this.loading = true;
      const endpoint = `${drupalSettings.m_api.endpoints.lista_segnalazioni}/${this.currentPage}`;
      this.$http
        .post(endpoint, {
          statusID:
            this.filtro.stato !== "false" ? this.filtro.stato : undefined,
          sectorID: this.filtro.area !== "false" ? this.filtro.area : undefined
        })
        .then(res => {
          this.pages = res.data.pages;
          this.segnalazioni = res.data.items;
          this.addMarkers();
          this.loading = false;
          window.scrollTo({
            top: 0,
            behavior: "smooth"
          });
        })
        .catch(err => {
          console.log("err");
          console.log(err);
          this.loading = false;
        });
    },
    changePage: function(page) {
      this.currentPage = page - 1;
    },
    getDetailUrl: function(id) {
      return `${drupalSettings.m_api.url_lista_seganalazioni}/${id}`;
    }
  }
};
</script>
