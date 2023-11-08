<template>
  <div class="page-prenota-ufficio-prenotazioni my-5">
    <div class="container">
      <div v-if="!loading" class="row">
        <div class="col-12">
          <div class="row p-3 py-5">
            <div class="col-12 col-lg-6">
              <pagome-select
                :value="filters.date"
                id="filtroDate"
                label="Data"
                placeholder=""
                :options="dates"
                @input="value => setFilter('date', value)"
              >
              </pagome-select>
            </div>
            <div class="col-12 col-lg-6">
              <pagome-select
                :value="filters.office"
                id="filtroOffice"
                label="Filtra per ufficio"
                placeholder=""
                :options="offices"
                @input="value => setFilter('office', value)"
              />
            </div>
          </div>
        </div>
        <div
          v-for="reservation in filteredReservationsList"
          class="col-12 col-lg-6 p-4"
        >
          <div class="row page-prenota-ufficio-prenotazioni__card shadow">
            <div
              class="col-12 page-prenota-ufficio-prenotazioni__card__header p-4"
            >
              <div class="page-prenota-ufficio-prenotazioni__card__name">
                {{ reservation.office.name }}
              </div>
              <div class="page-prenota-ufficio-prenotazioni__card__description">
                {{ reservation.office.description }}
              </div>
            </div>
            <div
              class="col-12 page-prenota-ufficio-prenotazioni__card__body p-4"
            >
              <div class="row">
                <div
                  class="col-6 page-prenota-ufficio-prenotazioni__card__info"
                >
                  <span>DATA APPUNTAMENTO</span>
                  <strong>{{ getDate(reservation.date) }}</strong>
                </div>
                <div
                  class="col-6 page-prenota-ufficio-prenotazioni__card__info"
                >
                  <span>N° TICKET</span>
                  <strong>{{ generateId(reservation.id) }}</strong>
                </div>
                <div class="col-6">
                  <div class="row">
                    <div
                      class="col-12 page-prenota-ufficio-prenotazioni__card__info"
                    >
                      <span>ORARIO APPUNTAMENTO</span>
                      <strong>
                        {{ removeSeconds(reservation.startTime) }} -
                        {{ removeSeconds(reservation.endTime) }}
                      </strong>
                    </div>
                    <div
                      class="col-12 page-prenota-ufficio-prenotazioni__card__info"
                    >
                      <span>STATO PRATICA</span>
                      <strong>
                        {{
                          reservation.status === "CONFIRMED"
                            ? "CONFERMATA"
                            : reservation.status === "ESEGUITA"
                            ? "CHIUSA"
                            : reservation.status
                        }}
                      </strong>
                    </div>
                  </div>
                </div>
                <div
                  class="col-6 page-prenota-ufficio-prenotazioni__card__qrcode"
                >
                  <img :src="`data:image/png;base64,${reservation.qrcode}`" />
                </div>
                <div
                  v-if="
                    reservation.status === 'RICHIESTA' ||
                      reservation.status === 'CONFIRMED'
                  "
                  class="col-12"
                >
                  <div
                    class="page-prenota-ufficio-prenotazioni__card__link"
                    v-on:click="showCancelRequestModal(reservation.id)"
                  >
                    Disdici appuntamento
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          class="col-12 col-lg-6 p-4"
          v-if="filteredReservationsList.length === 0"
        >
          Nessuna richiesta trovata
        </div>
      </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" ref="modal">
      <div class="modal-dialog-centered justify-content-center" role="document">
        <div class="modal-content">
          <div v-if="modalStep === 1" class="modal-body text-center">
            <strong
              >Confermi di voler annullare il tuo<br />appuntamento?</strong
            >
            <div class="py-4">
              <button
                type="button"
                class="btn btn-primary"
                data-dismiss="modal"
              >
                No
              </button>
              <button
                type="button"
                class="btn btn-primary"
                v-on:click="cancelRequest"
              >
                Si
              </button>
            </div>
          </div>
          <div v-if="modalStep === 2" class="modal-body text-center">
            <strong
              >Ti confermiamo l'avvenuta<br />cancellazione del tuo
              appuntamento</strong
            >
            <div class="py-4">
              <button
                type="button"
                class="btn btn-primary"
                data-dismiss="modal"
              >
                Ok
              </button>
            </div>
          </div>
          <div v-if="modalStep === 3" class="modal-body text-center">
            <strong
              >{{ errorMessage }}<br />Si prega di riprovare più tardi</strong
            >
            <div class="py-4">
              <button
                type="button"
                class="btn btn-primary"
                data-dismiss="modal"
              >
                Ok
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="loading" class="text-center">
      <spinner></spinner>
    </div>
  </div>
</template>

<script>
import PagomeSelect from "./components/form/PagomeSelect.vue";
import Spinner from "./components/ui/Spinner.vue";

export default {
  name: "PrenotaUfficioReserevationsList",
  components: { Spinner, PagomeSelect },
  data() {
    return {
      loading: false,
      modalStep: 1,
      errorMessage: false,
      filters: {
        date: "false",
        office: "false"
      },
      reservationsList: drupalSettings.m_api.data.reservationsList,
      currentReservationId: false
    };
  },
  computed: {
    dates() {
      return [
        {
          value: "false",
          text: "Tutte le date"
        },
        ...drupalSettings.m_api.data.reservationsList
          .reduce((res, x) => {
            if (res.findIndex(y => y.value === x.date) < 0) {
              res.push({
                value: x.date,
                text: x.date
                  .split("-")
                  .reverse()
                  .join("/")
              });
            }
            return res;
          }, [])
          .sort((a, b) => {
            const keyA = new Date(a.value).getTime();
            const keyB = new Date(b.value).getTime();
            if (keyA < keyB) return 1;
            if (keyA > keyB) return -1;
            return 0;
          })
      ];
    },
    offices() {
      return [
        {
          value: "false",
          text: "Tutti gli uffici"
        },
        ...drupalSettings.m_api.data.reservationsList.reduce((res, x) => {
          if (res.findIndex(y => y.value === x.office.id) < 0) {
            res.push({
              value: x.office.id,
              text: x.office.name
            });
          }
          return res;
        }, [])
      ];
    },
    filteredReservationsList() {
      let result = this.reservationsList;
      if (this.filters.date !== "false") {
        result = result.filter(x => x.date === this.filters.date);
      }
      if (this.filters.office !== "false") {
        result = result.filter(x => x.office.id === this.filters.office);
      }
      return result.sort((a, b) => {
        const keyA = new Date(a.date).getTime();
        const keyB = new Date(b.date).getTime();
        if (keyA < keyB) return 1;
        if (keyA > keyB) return -1;
        return 0;
      });
    }
  },
  methods: {
    removeSeconds(val) {
      const hours = val.split(":")[0];
      const minutes = val.split(":")[1];
      return `${hours}:${minutes}`;
    },
    generateId(id) {
      const idString = id.toString();
      if (idString.length >= 9) {
        const substring = idString.substring(
          idString.length - 9,
          idString.length
        );
        return `${substring.substring(0, 6)}-${substring.substring(6, 9)}`;
      }
      const lengthDiff = 9 - idString.length;
      let res = "";
      for (let i = 0; i < lengthDiff; i++) {
        res = res + "0";
      }
      res = res + idString;
      return `${res.substring(0, 6)}-${res.substr(6, 9)}`;
    },
    fixZero(value) {
      return value === 0 ? `0${value}` : value;
    },
    setFilter(type, value) {
      return (this.filters[type] = value);
    },
    getDate(value) {
      const array = value.split("-");
      const months = [
        "Gennaio",
        "Febbraio",
        "Marzo",
        "Aprile",
        "Maggio",
        "Giugno",
        "Luglio",
        "Agosto",
        "Settembre",
        "Ottobre",
        "Novembre",
        "Dicembre"
      ];
      return `${array[2]} ${months[parseInt(array[1], 10) - 1]}`;
    },
    showCancelRequestModal(reservationId) {
      this.modalStep = 1;
      this.currentReservationId = reservationId;
      $(this.$refs.modal).modal("show");
    },
    hideCancelRequestModal() {
      $(this.$refs.modal).modal("hide");
    },
    cancelRequest() {
      this.modalStep = false;
      this.loading = true;
      const endpoint = `${drupalSettings.m_api.endpoints.cancelRequest}/${this.currentReservationId}`;
      this.$http
        .patch(endpoint, {
          status: "CANCELLATA"
        })
        .then(res => {
          if (res.data && res.data.status === 500) {
            this.modalStep = 3;
            this.currentReservationId = false;
            this.loading = false;
            this.errorMessage = res.data.message;
            return;
          }
          this.modalStep = 2;
          this.currentReservationId = false;
          this.loading = false;
          this.updateList();
        });
    },
    updateList() {
      this.loading = true;
      const endpoint = `${drupalSettings.m_api.endpoints.reservationsList}`;
      this.$http.get(endpoint).then(res => {
        this.reservationsList = res.data;
        this.loading = false;
      });
    }
  },
  mounted() {
    $(this.$refs.modal).on("hidden.bs.modal", () => {
      this.modalStep = 1;
      this.currentReservationId = false;
    });
  }
};
</script>
