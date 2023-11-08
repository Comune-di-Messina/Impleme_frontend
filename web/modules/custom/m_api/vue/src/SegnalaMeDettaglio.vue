<template>
  <div class="page-segnalame-detail">
    <div class="module hero hero-simple">
      <div class="container-fluid p-0">
        <div class="row">
          <div class="col-content col-12 col-md-6 py-5 px-4 px-md-0">
            <div class="hero-content pr-3">
              <nav class="breadcrumb-container" aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a class="breadcrumb-link" href="/it">Home</a>
                    <span class="separator">/</span>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">
                    <span class="breadcrumb-link">Segnala ME</span>
                    <span class="separator">/</span>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">
                    <span class="breadcrumb-link">Segnalazioni</span>
                    <span class="separator">/</span>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">
                    <span class="breadcrumb-item active">{{
                      segnalazione.title
                    }}</span>
                  </li>
                </ol>
              </nav>

              <h1 class="no_toc">{{ segnalazione.title }}</h1>
              <div class="hero-content-main text-serif">
                {{ segnalazione.sector.name }} -
                {{ segnalazione.subSector.name }}
              </div>
            </div>
          </div>
          <div class="col-image col-12 col-md-5 offset-md-1 p-0">
            <img src="/themes/custom/portalemessina/dist/images/hero/imu.jpg" />
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid p-0">
      <div class="dettaglio-info shadow dettaglio-grid d-flex row">
        <div class="dettaglio-col-left col-7 py-4">
          <h3>Riepilogo segnalazione</h3>
          <div class="row">
            <div class="dati-block col-4 py-3">
              <div class="dati-label text-uppercase">Data segnalazione</div>
              <div class="dati-content text-truncate">{{ parseDate() }}</div>
            </div>
            <div class="dati-block col-4 py-3">
              <div class="dati-label text-uppercase">ID segnalazione</div>
              <div class="dati-content text-truncate">
                {{ segnalazione.id }}
              </div>
            </div>
            <div class="dati-block col-4 py-3">
              <div class="dati-label text-uppercase">Presa in carico da</div>
              <div class="dati-content">
                <span v-if="segnalazione.assignedTo">
                  {{ segnalazione.assignedTo.firstName }}
                  {{ segnalazione.assignedTo.lastName }}
                </span>
                <span v-else>---</span>
              </div>
            </div>
          </div>
        </div>
        <div class="dettaglio-col-right col-5 pl-0">
          <div
            class="col-top p-3 d-flex align-items-center justify-content-between"
          >
            <div class="dati-block">
              <div class="dati-label text-uppercase">Stato segnalazione</div>
              <div class="dati-content">{{ statusLabel }}</div>
            </div>
            <span class="h-icon h-prenotazione-stato-neg-7">
              <img
                :src="
                  `/themes/custom/portalemessina/dist/images/icons/segnala-me/icon-status-id-white-${segnalazione.status.id}.svg`
                "
              />
            </span>
          </div>
          <div class="col-bottom p-3 secondary-bg"></div>
        </div>
      </div>
    </div>
    <div class="container py-5">
      <div class="page-segnalame-detail__map-wrapper">
        <div class="row my-5">
          <div class="col-12 col-lg-4 col-content p-4 d-flex flex-column">
            <div class="col-content-header">
              <div class="block-title pb-2">
                {{ segnalazione.title }}
              </div>
            </div>
            <div class="col-content-main py-2">
              <div class="page-segnalame-detail__map-wrapper__result">
                <span class="h-icon h-pin-green"></span>
                <strong v-if="segnalazione.address">{{
                  segnalazione.address
                }}</strong>
                <strong v-else>Nessun indirizzo inserito</strong>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-8 p-lg-0">
            <div class="google-map-container" ref="mapContainer"></div>
          </div>
        </div>
      </div>
      <div class="row d-flex justify-content-center">
        <div class="col-12 col-lg-6 my-5">
          <h3>Descrizione</h3>
          <p>{{ segnalazione.description }}</p>
        </div>
      </div>

      <div
        class="row d-flex justify-content-center my-5"
        v-if="segnalazione.images && segnalazione.images.length > 0"
      >
        <div
          id="carouselExampleControls"
          class="carousel slide col-12 col-lg-10"
          data-ride="carousel"
          ref="carousel"
        >
          <div class="carousel-inner">
            <div
              v-for="(image, index) in segnalazione.images"
              class="carousel-item"
              :class="{ active: index === 0 }"
            >
              <div class="page-segnalame-detail__carousel-item">
                <img :src="image.base64" :alt="image.name" />
              </div>
            </div>
          </div>
          <a
            class="carousel-control-prev"
            href="#carouselExampleControls"
            role="button"
            data-slide="prev"
          >
            <span class="page-segnalame-detail__carousel-control">
              <svg class="icon icon-md">
                <use
                  xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-chevron-left"
                ></use>
              </svg>
            </span>
          </a>
          <a
            class="carousel-control-next"
            href="#carouselExampleControls"
            role="button"
            data-slide="next"
          >
            <span class="page-segnalame-detail__carousel-control">
              <svg class="icon icon-md">
                <use
                  xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-chevron-right"
                ></use>
              </svg>
            </span>
          </a>
        </div>
      </div>

      <div
        class="page-segnalame-detail__carousel-info row d-flex justify-content-center"
        v-if="segnalazione.images && segnalazione.images.length > 0"
      >
        <div class="col-12 col-lg-10">
          <div class="row d-flex justify-content-between">
            <div class="col font-italic">
              {{ this.segnalazione.images[this.currentSlide].name }}
            </div>
            <div class="col text-right">
              {{ this.currentSlide + 1 }}/{{ this.segnalazione.images.length }}
            </div>
          </div>
        </div>
      </div>

      <div
        class="row d-flex justify-content-center"
        v-if="segnalazione.documents && segnalazione.documents.length > 0"
      >
        <div class="col-12 col-lg-10 my-5 page-segnalame-detail__documents">
          <fieldset class="fieldset-step">
            <legend>
              DOCUMENTI
            </legend>
          </fieldset>
          <div class="page-segnalame-detail__documents__list">
            <h5>File allegati</h5>
            <div class="row py-2">
              <div
                v-for="document in segnalazione.documents"
                class="col-12 col-lg-4 page-segnalame-detail__documents__item my-3"
              >
                <a :href="document.base64" :download="document.name">
                  <svg class="icon icon-xs">
                    <use
                      xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-download"
                    ></use>
                  </svg>
                  {{ document.name }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row d-flex justify-content-center">
        <div class="col-12 col-lg-10">
          <fieldset class="fieldset-step my-5">
            <legend>
              Commenti dell'amministrazione
            </legend>
            <div v-if="segnalazione.note">
              {{ segnalazione.note }}
            </div>
            <div v-else>
              Non sono presenti note
            </div>
          </fieldset>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Loader } from "@googlemaps/js-api-loader";

export default {
  components: {},
  data() {
    return {
      segnalazione: drupalSettings.m_api.segnalazione_details,
      map: null,
      marker: null,
      apiKey: "AIzaSyDXjOCDBEIWap1f5f3w0MPN7LAntBSvYSw",
      currentSlide: 0,
      statusLabel:
        drupalSettings.m_api.segnalazione_details.status.id === 6
          ? "Chiusa"
          : drupalSettings.m_api.segnalazione_details.status.value,
      months: [
        "gennaio",
        "febbraio",
        "marzo",
        "aprile",
        "maggio",
        "giugno",
        "luglio",
        "agosto",
        "settembre",
        "ottobre",
        "novembre",
        "dicembre"
      ]
    };
  },
  computed: {},
  watch: {},
  mounted() {
    this.loadGoogleMap();
    if (this.$refs.carousel) {
      jQuery(this.$refs.carousel).on("slide.bs.carousel", ({ to }) => {
        this.currentSlide = to;
      });
    }
  },
  methods: {
    fixZero(value) {
      return value < 10 ? `0${value}` : value;
    },
    parseDate() {
      const date = new Date(this.segnalazione.insertTs);
      const day = this.fixZero(date.getDate());
      const month = this.months[date.getMonth()];
      const year = date.getFullYear();
      return `${day} ${month} ${year}`;
    },
    loadGoogleMap: function() {
      const loader = new Loader({
        apiKey: this.apiKey,
        version: "weekly"
      });
      loader.load().then(res => {
        this.map = new window.google.maps.Map(this.$refs.mapContainer, {
          center: {
            lat: this.segnalazione.latitude || 38.19394,
            lng: this.segnalazione.longitude || 15.55256
          },
          zoom: this.segnalazione.address ? 16 : 14
        });
        this.marker = new google.maps.Marker({
          position: new google.maps.LatLng(
            this.segnalazione.latitude,
            this.segnalazione.longitude
          ),
          map: this.map,
          icon: `/themes/custom/portalemessina/dist/images/icons/segnala-me/pin-status-id-${this.segnalazione.status.id}.png`
        });
      });
    }
  }
};
</script>
