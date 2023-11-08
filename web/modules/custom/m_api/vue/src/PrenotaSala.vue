<template>
  <div :class="{ hidden: specifiche == null }">
    <form
      ref="prenotazione"
      novalidate="novalidate"
      class="needs-validation"
      :class="{ 'was-validated': validationError }"
      @submit="validateData($event)"
    >
      <div v-if="!submitted" class="secondary-bg-light py-5">
        <div class="container container-specifiche">
          <prenotame-specifiche :sala="specifiche" />
          <prenotame-tariffe :sala="specifiche" />
          <prenotame-documenti
            v-if="documenti"
            :documenti="documenti"
            :authenticated="authenticated"
          />
          <prenotame-servizi
            v-if="specifiche"
            :servizi="specifiche.servizi"
            :selected="prenotazione.serviziPrenotati"
            @input="setServizi($event)"
            :authenticated="authenticated"
          />
        </div>
      </div>
      <div
        v-if="!submitted && authenticated"
        class="container container-disponibilita"
      >
        <div class="container-calendar">
          <h3 class="mb-4">Verifica la disponibilità</h3>
          <div class="row">
            <div class="col-12" v-if="specifiche">
              <pagome-select
                id="tipoEvento"
                v-model="prenotazione.tipoEvento"
                label="Tipologia dell'evento *"
                placeholder="Inserisci la tipologia"
                :required="true"
                error="Il campo tipologia è obbligatorio."
                :options="optionsTipologie"
              />
            </div>
          </div>
          <div
            class="row container-calendar__datepicker"
            :class="!prenotazione.tipoEvento && 'is-locked'"
          >
            <date-picker
              ref="datePicker"
              v-model="range"
              color="green"
              class="calendario"
              :is-range="isRange"
              :model-config="modelConfig"
              :disabled-dates="disabledDates"
              :available-dates="availableDates"
              :attributes="calendarAttrs"
              @input="setEndDate()"
            />
            <prenota-sala-fasce-orarie
              :booking-type="bookingType"
              :tariffario="currentTariffario"
              :days-map="daysMap"
              :specifiche="specifiche"
              :fasce-orarie="fasceOrarie"
              :current-day="prenotazione.giornoDa"
              :has-range="prenotazione.giornoDa !== prenotazione.giornoA"
              @interaGiornata="onInteraGiornata($event)"
              @fasce="onFasceOrarie($event)"
            />
          </div>
          <div class="col-12 col-lg-4 col-importo">
            <prenotame-item
              v-if="importoPrevisto != null && errorePrezzo == ''"
              id="prezzoPrevisto"
              label="Importo previsto"
              :value="prezzoPrevisto | format_number"
              suffix=" €"
            />
            <div
              v-else-if="errorePrezzo != ''"
              class="alert alert-danger"
              role="alert"
            >
              {{ errorePrezzo }}
            </div>
            <spinner v-else-if="loading" />
            <a
              v-else
              class="btn btn-outline-success"
              :class="{ disabled: !canCheckPrice || calculateWeekHours().oraDa === null }"
              :disabled="!canCheckPrice || calculateWeekHours().oraDa === null"
              @click.prevent="checkPrice()"
            >
              Verifica l'importo
            </a>
          </div>
        </div>
      </div>
      <div v-if="!submitted && authenticated" class="container container-form">
        <prenotame-richiedente
          v-if="prenotazione.richiedente.fromLogin"
          :richiedente="prenotazione.richiedente"
          :enabled="true"
          :errors="{}"
          :disabled="disabledFields"
        />
        <prenotame-organizzatore
          :organizzatore="prenotazione.organizzatore"
          :errors="{}"
        />

        <fieldset
          v-if="specifiche"
          class="fieldset-step my-5"
          :class="{ 'h-disabled': false }"
        >
          <legend>
            <span class="fieldset-step-num">3</span> Descrizione evento
          </legend>
          <div class="fieldset-content">
            <div class="row my-2">
              <div class="col-6 col-lg-4">
                <pagome-input
                  id="titoloEvento"
                  v-model="prenotazione.titoloEvento"
                  label="Titolo dell'evento *"
                  :required="true"
                  error="Il campo titolo è obbligatorio."
                />
              </div>
            </div>
            <div class="row my-2">
              <div class="col-12">
                <pagome-input
                  id="descrizioneEvento"
                  v-model="prenotazione.descrizioneEvento"
                  label="Descrizione dell'evento *"
                  placeholder="Inserisci una descrizione"
                  :required="true"
                  error="Il campo descrizione è obbligatorio."
                />
              </div>
            </div>
          </div>
        </fieldset>
      </div>
    </form>
    <div
      v-if="!submitted && authenticated"
      class="container container-documenti"
    >
      <prenotame-documentazione
        :prenotazione="prenotazione"
        @load="prenotazione.allegati = $event"
        @removeFile="removeFile($event)"
      />
      <div class="container-privacy">
        <h5>Privacy *</h5>
        <div class="row justify-content-between">
          <div class="col-12 col-lg-5 privacy-item form-check">
            <input
              id="privacy-1"
              v-model="prenotazione.flagPrivacy1"
              type="checkbox"
              required
            />
            <label for="privacy-1">
              Dichiaro di aver letto l'informativa sulla privacy, autorizzo al
              trattamento dei miei dati personali *
            </label>
          </div>
          <div class="col-12 col-lg-5 privacy-item form-check">
            <input
              id="privacy-2"
              v-model="prenotazione.flagPrivacy2"
              type="checkbox"
              required
            />
            <label for="privacy-2">
              Dichiaro che lo statuto del soggetto richiedente o altro atto
              analogo non è in contrasto coni principi fondamentali della
              Costituzione, della legge, dell'ordine pubblico e dello Statuto
              Comunale *
            </label>
          </div>
          <div class="col-12 col-lg-5 privacy-item form-check">
            <input
              id="privacy-3"
              v-model="prenotazione.flagPrivacy3"
              type="checkbox"
              required
            />
            <label for="privacy-3">
              Confermo, consapevole della responsabilità penale in caso di
              dichiarazioni non veritiere ai sensi dell'art 76 del DPR 445/2000
              e ss.mm.ii., la veridicità di quanto indicato nella presente
              domanda e nei relativi allegati *
            </label>
          </div>
          <div class="col-12 col-lg-5 privacy-item form-check">
            <input
              id="privacy-4"
              v-model="prenotazione.flagPrivacy4"
              type="checkbox"
              required
            />
            <label for="privacy-4" v-if="specifiche">
              Dichiaro di essere consapevole delle condizioni che regolano la
              concessione dei locali richiesti riportate nel
              <strong
                ><i
                  >"Disciplinare utilizzo temporaneo spazi e sale comunali Atrio
                  Sala Ovale e Sala Giunta "Falcone e Borsellino" di Palazzo
                  Zanca"</i
                ></strong
              >
              approvati dalla Giunta Comunale e che, con la presente, le accetta
              integralmente *
              {{ /*specifiche.condizioniUtilizzo*/ }}
            </label>
          </div>
        </div>
      </div>
    </div>
    <div
      v-if="!submitted && authenticated"
      class="container actions text-center neutral-bg-light p-5 my-5"
    >
      <a
        v-if="!loading && !completed"
        class="btn btn-outline-success"
        :class="{ disabled: loading }"
        :disabled="loading"
        @click="submitPrenotazione($event)"
      >
        Invia la richiesta
      </a>
      <spinner v-if="loading" />
      <div v-if="errors.length > 0">
        <div class="alert alert-danger mt-4" role="alert">
          Errore nell'invio della prenotazione:
          <ul class="errors">
            <li v-for="(error, key) in errors" :key="key" class="error-item">
              {{ error }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PrenotameSpecifiche from "./components/PrenotameSpecifiche.vue";
import PrenotameTariffe from "./components/PrenotameTariffe.vue";
import PrenotameServizi from "./components/PrenotameServizi.vue";
import PrenotameDocumenti from "./components/PrenotameDocumenti.vue";
import PrenotameRichiedente from "./components/PrenotaMe/PrenotameRichiedente.vue";
import PrenotameOrganizzatore from "./components/PrenotaMe/PrenotameOrganizzatore.vue";
import PrenotameDocumentazione from "./components/PrenotaMe/PrenotameDocumentazione.vue";
import PrenotaSalaFasceOrarie from "./components/PrenotaMe/PrenotaSalaFasceOrarie.vue";
import PagomeInput from "./components/form/PagomeInput.vue";
import PagomeSelect from "./components/form/PagomeSelect.vue";
import PrenotameItem from "./components/ui/Item.vue";
import dataPrenotazione from "./data/prenotazione.js";
// import dataPrenotazione from './data/prenotazione-example.js';
import Spinner from "./components/ui/Spinner.vue";
import prenotazione from "./data/prenotazione.js";

export default {
  components: {
    PrenotameDocumenti,
    PrenotameSpecifiche,
    PrenotameTariffe,
    PrenotameServizi,
    PrenotameRichiedente,
    PrenotameOrganizzatore,
    PrenotameDocumentazione,
    PrenotaSalaFasceOrarie,
    PagomeInput,
    PagomeSelect,
    PrenotameItem,
    Spinner,
    DatePicker: () =>
      import(
        /* webpackChunkName: "date-picker" */
        "v-calendar/lib/components/date-picker.umd"
      )
  },
  data: function() {
    return {
      modelConfig: {
        type: "string",
        mask: "YYYY-MM-DD"
      },
      range: {
        end: null,
        start: null
      },
      daysMap: {
        DOM: 1,
        LUN: 2,
        MAR: 3,
        MER: 4,
        GIO: 5,
        VEN: 6,
        SAB: 7
      },
      documenti: [],
      specifiche: null,
      fasceOrarie: [],
      loading: false,
      submitted: false,
      submittedId: null,
      errors: [],
      errorePrezzo: "",
      importoPrevisto: null,
      prenotazione: dataPrenotazione,
      calendarAttrs: [],
      validationError: false,
      specificheError: false,
      disabledFields: {},
      bookingType: null
    };
  },
  computed: {
    availableDates() {
      const dates = {
        start: this.dateStart,
        end: null
      };
      if (this.specifiche) {
        dates.weekdays = this.mapDays(
          this.specifiche.aperture.map(item => item.giorno)
        ).sort();
      }
      return dates;
    },
    disabledDates() {
      const dates = [];
      // dates.push({start: this.dateStart, end: null});
      return dates;
    },
    dateStart() {
      if (this.specifiche == null) {
        return null;
      }
      const start = new Date();
      start.setDate(start.getDate() + this.specifiche.giorniAnticipo);
      return start;
    },
    optionsTipologie() {
      return this.specifiche.eventi.map(function(item) {
        return { text: item.evento, value: item.id };
      });
    },
    canCheckPrice() {
      if (
        this.prenotazione.giornoDa &&
        this.currentTariffario &&
        this.currentTariffario.flagInteraSettimana
      ) {
        return true;
      }
      if (this.prenotazione.interaGiornata) {
        return (
          this.prenotazione.giornoDa != null &&
          this.prenotazione.giornoA === this.prenotazione.giornoDa
        );
      } else {
        return (
          this.prenotazione.oraDa != null && this.prenotazione.oraA != null
        );
      }
    },
    prezzoPrevisto() {
      if (
        this.importoPrevisto != null &&
        Object.prototype.hasOwnProperty.call(this.importoPrevisto, "importo")
      ) {
        return this.importoPrevisto.importo;
      }
      return null;
    },
    completed() {
      return (
        this.submitted &&
        this.submittedId != null &&
        Object.prototype.hasOwnProperty.call(
          this.submittedId,
          "numeroPratica"
        ) &&
        this.submittedId.numeroPratica != null
      );
    },
    currentTariffario() {
      if (this.specifiche == null || this.prenotazione.tipoEvento == null) {
        return null;
      }
      return this.specifiche.tariffario.find(
        element => element.id == this.prenotazione.tipoEvento
      );
    },
    isRange() {
      if (
        this.currentTariffario != null &&
        typeof this.currentTariffario == "object"
      ) {
        return this.currentTariffario.flagInteraGiornata;
      }
      return null;
    },
    authenticated() {
      return (
        Object.prototype.hasOwnProperty.call(drupalSettings.m_api, "cf") &&
        !(drupalSettings.m_api.cf == null)
      );
    }
  },
  watch: {
    range: function(val) {
      if (typeof val == "object") {
        this.prenotazione.giornoDa = val.start;
        this.prenotazione.giornoA = val.end;
      } else {
        this.prenotazione.giornoA = val;
        this.prenotazione.giornoDa = val;
      }
      this.errors = [];
      this.errorePrezzo = false;
      this.getAvailability();
    },
    dateStart: function(val) {
      if (val != null && this.authenticated) {
        this.moveStart();
      }
    },
    "prenotazione.tipoEvento": function() {
      const val = prenotazione.tipoEvento;
      prenotazione.tariffa = Number(val);
      prenotazione.tipoEvento = Number(val);
      this.resetCalendar();
    }
  },
  mounted() {
    this.loadSala();
    this.prenotazione.roomId = drupalSettings.m_api.id;
    this.documenti = drupalSettings.m_api.documenti;
    this.prenotazione.richiedente.codiFisc = drupalSettings.m_api.cf;
    if (!Object.prototype.hasOwnProperty.call(drupalSettings.m_api, "length")) {
      if(drupalSettings.m_api.userinfo.given_name) {
        this.setDisabled("name");
        this.prenotazione.richiedente.name = drupalSettings.m_api.userinfo.given_name;
      }

      this.prenotazione.richiedente.surname =
        drupalSettings.m_api.userinfo.family_name;
      this.prenotazione.richiedente.email = drupalSettings.m_api.userinfo.email;
      this.prenotazione.richiedente.dataNascita =
        drupalSettings.m_api.userinfo.birthdate;
      if(drupalSettings.m_api.userinfo.address.address) {
        this.prenotazione.richiedente.indirizzo.indirizzo = drupalSettings.m_api.userinfo.address.address;
        this.setDisabled("indirizzo");
      }
      this.setDisabled("codiFisc");
      this.setDisabled("surname");
      this.setDisabled("email");
      this.setDisabled("dataNascita");
    }
    this.prenotazione.richiedente.fromLogin = JSON.parse(
      JSON.stringify(this.prenotazione.richiedente)
    );
  },
  methods: {
    loadSala() {
      if (this.loading) {
        return;
      }
      const endpoint = drupalSettings.m_api.endpoint.dettagliSala;
      const component = this;
      this.loading = true;
      return this.$http
        .get(endpoint)
        .then(response => {
          component.specifiche = response.data;
          component.loading = false;
        })
        .catch(function(error) {
          component.specifiche = null;
          component.loading = false;
          // console.log(error);
          this.specificheError = true;
        });
    },
    mapDays(val) {
      return val.map(item => this.daysMap[item]);
    },
    moveStart() {
      const component = this;
      this.$nextTick(function() {
        component.$refs.datePicker.move(this.dateStart);
      });
    },
    setServizi(val) {
      this.prenotazione.serviziPrenotati = val;
      this.resetPrice();
    },
    checkPrice() {
      if (this.loading) {
        return;
      }
      const endpoint = drupalSettings.m_api.endpoint.verificaPrezzo;
      const component = this;
      this.loading = true;
      this.errorePrezzo = "";
      return this.$http
        .post(endpoint, this.transformPrenotazione(false))
        .then(response => {
          component.importoPrevisto = response.data;
          component.loading = false;
        })
        .catch(function(error) {
          component.importoPrevisto = null;
          component.loading = false;
          if (
            Object.prototype.hasOwnProperty.call(
              error.response.data,
              "message"
            ) &&
            error.response.data != null
          ) {
            component.errorePrezzo = error.response.data.message;
          }
          console.log(error);
        });
    },
    getAvailability() {
      if (this.loading || !this.prenotazione.giornoDa) {
        return;
      }
      const endpoint = drupalSettings.m_api.endpoint.recuperaDisponibilita;
      const component = this;
      component.fasceOrarie = [];
      this.loading = true;
      return this.$http
        .post(endpoint, this.transformPrenotazione(true))
        .then(response => {
          component.fasceOrarie = response.data.giorni;
          component.loading = false;
        })
        .catch(function(error) {
          console.log(error);
          component.loading = false;
        });
    },
    resetPrice() {
      this.importoPrevisto = null;
      this.errorePrezzo = false;
    },
    onInteraGiornata(value) {
      this.prenotazione.interaGiornata = value;
      this.resetPrice();
      if (value == true) {
        this.onFasceOrarie({ start: null, end: null });
      }
    },
    onFasceOrarie(value) {
      this.prenotazione.oraDa = value.start;
      this.prenotazione.oraA = value.end;
      this.resetPrice();
      this.errors = [];
    },
    removeFile(index) {
      this.prenotazione.allegati.splice(index, 1);
    },
    validateData(event) {
      const form = this.$refs.prenotazione;
      if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        const errorElement = document.querySelector('.form-control:invalid');
        const coords = errorElement.getBoundingClientRect();
        const top = window.scrollY + coords.top - 200;
        window.scrollTo({ behavior: 'smooth', left: 0, top: top });
        return false;
      }
      return true;
    },
    submitPrenotazione(event) {
      event.preventDefault();
      event.stopPropagation();

      if (this.loading) {
        return;
      }

      if (this.currentTariffario.flagInteraSettimana) {
        this.prenotazione = this.transformPrenotazione(false);
      }

      if (this.validateData(event) == false) {
        // event.stopPropagation();
        this.validationError = true;
        return;
      }
      if (this.prenotazione.giornoDa == "") {
        // event.stopPropagation();
        this.errors.push("Seleziona una data");
        return;
      }

      if (
        this.prenotazione.flagPrivacy1 === false ||
        this.prenotazione.flagPrivacy2 === false ||
        this.prenotazione.flagPrivacy3 === false ||
        this.prenotazione.flagPrivacy4 === false
      ) {
        // event.stopPropagation();
        const privacyError = "Tutti i campi privacy sono obbligatori";
        if (!this.errors.includes(privacyError)) {
          this.errors.push("Tutti i campi privacy sono obbligatori");
        }
        return;
      }

      if (
        this.prenotazione.interaGiornata == false &&
        (this.prenotazione.oraDa == null || this.prenotazione.oraA == null)
      ) {
        // event.stopPropagation();
        this.errors.push("Seleziona una fascia oraria");
        return;
      }

      const endpoint = drupalSettings.m_api.endpoint.submit;
      const component = this;
      this.loading = true;
      this.errors = [];
      const data = this.prenotazione.interaGiornata
        ? { ...this.prenotazione, giornoA: undefined }
        : this.prenotazione;
      this.prenotazione.richiedente.fromLogin = undefined;

      if (!this.prenotazione.interaGiornata) {
        delete this.prenotazione.giornoA;
      }

      return this.$http
        .post(endpoint, data)
        .then(response => {
          component.submitted = true;
          component.submittedId = response.data;
          component.loading = false;

          // Go to thankyou page.
          window.location = drupalSettings.m_api.endpoint.prenotazioneOk + '?room_name=' + this.specifiche.nome;
        })
        .catch(function(error) {
          component.importoPrevisto = false;
          component.loading = false;
          if (
            Object.prototype.hasOwnProperty.call(
              error.response.data.error,
              "message"
            ) &&
            error.response.data != null
          ) {
            component.errors.push(error.response.data.error.message);
          }

          // Go to thankyou page.
          window.location = drupalSettings.m_api.endpoint.prenotazioneKo + '?room_name=' + this.specifiche.nome;
        });
    },
    setDisabled(field) {
      this.disabledFields[field] = !(
        this.prenotazione.richiedente[field] == ""
      );
    },
    resetCalendar() {
      this.calendarAttrs = [];
      this.resetPrice();
      this.fasceOrarie = [];
      this.range = {
        end: null,
        start: null
      };
      this.checkDailyWeek();
      prenotazione.oraA = null;
      prenotazione.oraDa = null;

      // Force datepicker reset
      this.$refs.datePicker.$data.inputValues = [];
      this.$refs.datePicker.$data.dateParts = [];
      this.$refs.datePicker.$data.value_ = new Date();
    },
    checkDailyWeek() {
      const hasDay = this.currentTariffario.flagInteraGiornata;
      const hasWeek = this.currentTariffario.flagInteraSettimana;
      this.prenotazione.interaGiornata = false;
      if (hasDay) {
        return (this.bookingType = "day");
      }
      if (hasWeek) {
        return (this.bookingType = "week");
      }
      return (this.bookingType = false);
    },
    calculateWeekHours() {
      const fasce = this.fasceOrarie.find(
        x => x.giorno === this.prenotazione.giornoDa
      );
      if (fasce) {
        const ore = fasce.ore.filter(x => x.stato === "DISPONIBILE");
        return {
          oraDa: (ore[0] && ore[0].oraDa) || null,
          oraA: (ore[ore.length - 1] && ore[ore.length - 1].oraA) || null
        };
      }
    },
    calculateNextWeek() {
      const date = new Date(
        new Date(this.prenotazione.giornoDa).getTime() + 1000 * 60 * 60 * 24 * 6
      );
      let result = date.toISOString().split('T')[0];
      return result;
    },
    transformPrenotazione(ignoreHours) {
      let res = {
        ...this.prenotazione,
        interaGiornata: this.currentTariffario.flagInteraSettimana
          ? false
          : this.prenotazione.interaGiornata,
        giornoA: this.currentTariffario.flagInteraSettimana
          ? this.calculateNextWeek()
          : this.prenotazione.giornoA
      };
      if (!ignoreHours) {
        res = {
          ...res,
          oraDa: this.currentTariffario.flagInteraSettimana
            ? this.calculateWeekHours().oraDa
            : this.prenotazione.oraDa,
          oraA: this.currentTariffario.flagInteraSettimana
            ? this.calculateWeekHours().oraA
            : this.prenotazione.oraA
        };
      }
      return res;
    },
    setEndDate() {
      this.$root.$emit('resetFasce');
      this.resetPrice();
      if (
        this.currentTariffario.flagInteraSettimana &&
        typeof this.range === "string"
      ) {
        this.calendarAttrs = [
          {
            key: "weekRange",
            highlight: true,
            dates: [
              {
                start: this.range || new Date().getDate(),
                span: 7
              }
            ]
          }
        ];
      } else {
        this.calendarAttrs = [];
      }
    }
  }
};
</script>
