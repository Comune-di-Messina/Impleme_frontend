<template>
  <div class="page-prenota-ufficio-offices-list">
    <div class="secondary-bg py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-filters col-lg-5">
            <div class="col-filters__title">Filtra per servizio</div>
            <div class="col-filters__list">
              <div
                v-for="service in serviceTypes"
                class="col-filters__item"
                :class="{ active: filters.serviceType === service.id }"
                v-on:click="setFilter('serviceType', service.id)"
              >
                {{ service.name }}
              </div>
            </div>
          </div>
          <div class="col-content col-lg-7">
            <p class="color-white">
              Prenota il tuo appuntamento presso gli uffici Comunali evitando le
              attese.
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row my-5">
              <div class="col-12 col-lg-6">
                <h3>Filtra per servizio</h3>
                <p>
                  Per aiutarti nella selezione dell'ufficio più indicato, scegli
                  dalla tendina qui a fianco il servizio di tuo interesse per
                  indicazioni su quali uffici offrono il servizio richiesto
                </p>
              </div>
              <div class="col-12 col-lg-6">
                <pagome-select
                  :value="filters.publicService"
                  id="filtroPublicService"
                  label="Servizi disponibili"
                  placeholder="Tutti"
                  :options="publicServices"
                  @input="(value) => setFilter('publicService', value)"
                />
              </div>
            </div>
          </div>
          <div v-if="!loading" class="col-12 col-lg-7">
            <div class="row">
              <div v-for="office in pagedItems" class="col-12 col-lg-6">
                <prenota-ufficio-list-card
                  :office="office"
                  :badgeLabel="currentServiceTypeLabel"
                ></prenota-ufficio-list-card>
              </div>
              <div class="col-12 col-lg-6 p-4" v-if="pagedItems.length === 0">
                Nessun ufficio trovato
              </div>
            </div>
          </div>
          <div v-if="pagedItems.length > 0" class="col-12 col-lg-5">
            <prenota-ufficio-map
              :markers="markers"
              :serviceTypeLabel="currentServiceTypeLabel"
            ></prenota-ufficio-map>
          </div>
        </div>
      </div>
    </div>
    <div v-if="loading" class="col-12 my-2 text-center">
      <spinner></spinner>
    </div>
    <div class="container">
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
</template>

<script>
import PrenotaUfficioListCard from "./components/PrenotaUfficio/PrenotaUfficioListCard.vue";
import PrenotaUfficioMap from "./components/PrenotaUfficio/PrenotaUfficioMap.vue";
import PagomeSelect from "./components/form/PagomeSelect.vue";
import Spinner from "./components/ui/Spinner.vue";

export default {
  name: "PrenotaUfficioOfficesList",
  components: {
    Spinner,
    PagomeSelect,
    PrenotaUfficioMap,
    PrenotaUfficioListCard,
  },
  data() {
    return {
      loading: false,
      itemsPerPage: 4,
      currentPage: 0,
      serviceTypes: drupalSettings.m_api.data.serviceTypes,
      publicServices: [
        {
          value: "false",
          text: "Tutti",
        },
        ...drupalSettings.m_api.data.publicServices.map((service) => {
          return {
            value: service.id,
            text: service.name,
          };
        }),
      ],
      filters: {
        serviceType: drupalSettings.m_api.data.serviceTypes[0]
          ? drupalSettings.m_api.data.serviceTypes[0].id
          : "false",
        publicService: "false",
      },
      currentServiceTypeLabel: drupalSettings.m_api.data.serviceTypes[0]
        ? drupalSettings.m_api.data.serviceTypes[0].name
        : false,
      officesList: [],
    };
  },
  computed: {
    pages() {
      return Math.ceil(this.officesList.length / this.itemsPerPage);
    },
    pagedItems() {
      const start = this.currentPage * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.officesList.slice(start, end);
    },
    markers() {
      return this.pagedItems.map((office) => {
        return {
          officeName: office.name,
          link: office.link,
          center: {
            lat: office.coordinates.latitude,
            lng: office.coordinates.longitude,
          },
        };
      });
    },
  },
  methods: {
    setFilter(type, value) {
      this.filters[type] = value;
      if (type === "serviceType") {
        this.currentServiceTypeLabel = this.serviceTypes.find(
          (x) => x.id === value
        ).name;
      }
      let filters = this.generateStringFilters();
      const endpoint = `${drupalSettings.m_api.endpoints.officesList}${filters}`;
      this.loading = true;
      this.$http.get(endpoint).then((res) => {
        this.officesList = res.data.map(this.updateOfficeFormLink);
        this.getPublicServicesList();
        this.loading = false;
      });
    },
    changePage: function (page) {
      this.currentPage = page - 1;
    },
    updateOfficeFormLink(office) {
      let filters = this.generateStringFilters();
      return { ...office, link: `${office.link}${filters}` };
    },
    generateStringFilters() {
      let filters = this.filters.serviceType
        ? `/${this.filters.serviceType}`
        : "";
      filters =
        this.filters.publicService && this.filters.publicService !== "false"
          ? `${filters}/${this.filters.publicService}`
          : filters;
      return filters;
    },
    getPublicServicesList: function () {
      const endpoint = `${drupalSettings.m_api.endpoints.publicServicesList}/${this.filters.serviceType}`;
      this.loading = true;
      this.$http.get(endpoint).then((res) => {
        this.publicServices = [
          {
            value: "false",
            text: "Tutti",
          },
          ...res.data.map((service) => {
            return {
              value: service.id,
              text: service.name,
            };
          }),
        ];
        this.loading = false;
      });
    },
  },
  mounted() {
    this.officesList = drupalSettings.m_api.data.officesList.map(
      this.updateOfficeFormLink
    );
  },
};
</script>
