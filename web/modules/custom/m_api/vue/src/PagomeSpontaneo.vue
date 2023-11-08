<template>
  <div class="container py-5">
    <form class="needs-validation" novalidate
      @submit="validateData($event)" ref="spontaneo"
      :class="{ 'was-validated': showErrors }">
      <h3>Compila il form</h3>

      <pagome-servizio :pagamento="pagamento" :tariffe="tariffe"/>

      <pagome-dati-pagamento
        :pagamento="pagamento"
        :tariffa="currentTariffa"
        :servizio="currentServizio"
        :enabled="datiPagamentoEnabled"
        @change="updateTotale($event)"
      />

      <pagome-richiedente
        :richiedente="pagamento.debitore"
        :enabled="datiRichiedenteEnabled"
        :errors="errors.debitore"
      />

      <div class="text-center mt-4">
        <button
          class="btn btn-primary btn-lg"
          :class="{disabled: !canPay}"
          :disabled="!canPay"
          @click="checkPaymentData($event)"
        >
          Prosegui
        </button>
      </div>
    </form>
    <modal :pagamentoResponse="pagamentoResponse" :errors="errors.debitore"/>
  </div>
</template>

<script>

import PagomeRichiedente from './components/PagomeRichiedente.vue';
import PagomeServizio from './components/PagomeServizio.vue';
import PagomeDatiPagamento from './components/PagomeDatiPagamento.vue';
import Modal from './components/ui/Modal.vue';

export default {
  components: {
    PagomeRichiedente,
    PagomeServizio,
    PagomeDatiPagamento,
    Modal
  },
  data: function () {
    return {
      errors: {
        debitore: {},
        pagamento: {}
      },
      showErrors: false,
      pagamento: {
        "anno": null,
        "causale": "",
        "debitore": {
          "indirizzo": {
            "cap": "",
            "civico": "",
            "comune": "",
            "indirizzo": "",
            "provincia": "",
            "stato": ""
          },
          "codiFisc": "",
          "email": "",
          "name": "",
          "surname": "",
          "telephoneNumber": ""
            // "cap": "00100",
            // "civico": "",
            // "comune": "Messina",
            // "indirizzo": "via Veneto 15",
            // "provincia": "ME",
            // "stato": "Italia"
          // },
          // "codiFisc": "PNCNDR80C25A390K",
          // "email": "m.rossi@gmail.com",
          // "name": "Mario",
          // "surname": "Rossi",
          // "telephoneNumber": "(+39)3450000000"
        },
        "details": {},
        "ente": drupalSettings.m_api.spontaneo.ente,
        "importo": 0,
        "quantita": 1,
        "tariffa": null,
        "tributo": ""
      },
      pagamentoResponse: null,
      anagrafica: {
        nome: '',
        cognome: ''
      },
      endpoints: {
        pagamento: '/servizi/messina/pagamento/api/pagamento'
      },
      tariffe: {},
      loading: false
    };
  },
  computed: {
    currentTariffa: function() {
      let tariffa = {};
      if (this.pagamento.tariffa != '' && this.pagamento.tributo in this.tariffe) {
        this.tariffe[this.pagamento.tributo].forEach((element) => {
          if (element.id === this.pagamento.tariffa ) {
            tariffa = element;
          }
        });
      }
      return tariffa;
    },
    currentServizio: function () {
      const servizi = drupalSettings.m_api.spontaneo.servizi;
      let current = {};
      if (servizi.length > 0) {
        servizi.forEach((element) => {
          if (element.IDTributo === this.pagamento.tributo) {
            current = element;
          }
        });
      }
      return current;
    },
    canPay: function () {
      return !this.hasErrors && this.datiRichiedenteEnabled;
    },
    datiRichiedenteEnabled: function () {
      return this.pagamento.importo > 0 && this.datiPagamentoEnabled
    },
    hasErrors: function () {
      return Object.keys(this.errors.pagamento).length > 0
             && Object.keys(this.errors.debitore).length > 0
    },
    datiPagamentoEnabled: function() {
      return Object.keys(this.currentTariffa).length !== 0;
    }
  },
  mounted() {
    this.pagamento.debitore.codiFisc = this.userInfo('cf');
    this.pagamento.debitore.name = this.userInfo('given_name');
    this.pagamento.debitore.surname = this.userInfo('family_name');
    this.pagamento.debitore.email = this.userInfo('email');
    let address = this.userInfo('address').address.split(" ");
    this.pagamento.debitore.indirizzo.provincia = address.pop();
    this.pagamento.debitore.indirizzo.comune = address.pop();
    this.pagamento.debitore.indirizzo.cap = address.pop();
    this.pagamento.debitore.indirizzo.indirizzo = address.join(" ");
    this.pagamento.debitore.telephoneNumber = this.userInfo('mobilePhone');
  },
  methods: {
    updateTotale: function (value) {
      this.pagamento.importo = value.importoUnitario;
      this.pagamento.quantita = value.quantita;
    },
    validateData: function(event) {
      // this.validateDebitore();
      // this.validatePagamento();
      const form = this.$refs.spontaneo;
      if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        return false;
      }
      return true;
    },
    validatePagamento: function() {

    },
    validateDebitore: function() {
      this.errors.debitore = {};
      // debitore": {
      //     "codiFisc": "PNCNDR80C25A390K",
      //     "email": "m.rossi@gmail.com",
      //     "indirizzo": {
      //       "cap": "00100",
      //       "civico": "",
      //       "comune": "Messina",
      //       "indirizzo": "via Veneto 15",
      //       "provincia": "ME",
      //       "stato": "Italia"
      //     },
      //     "name": "Mario",
      //     "surname": "Rossi",
      //     "telephoneNumber": "(+39)3450000000"
      //   },
      if (this.pagamento.debitore.email == "") {
        this.errors.debitore['anagraficaEmail'] = 'Il campo Email è obbligatorio';
      }
      if (this.pagamento.debitore.codiFisc == "") {
        this.errors.debitore['anagraficaCodFisc'] = 'Il campo Codice Fiscale è obbligatorio';
      }
      if (this.pagamento.debitore.name == "") {
        this.errors.debitore['anagraficaNome'] = 'Il campo Nome è obbligatorio';
      }
      if (this.pagamento.debitore.surname == "") {
        this.errors.debitore['anagraficaCognome'] = 'Il campo Cognome è obbligatorio';
      }
      if (this.pagamento.debitore.indirizzo.indirizzo == "") {
        this.errors.debitore['anagraficaIndirizzo'] = 'Il campo Indirizzo è obbligatorio';
      }
      if (this.pagamento.debitore.indirizzo.comune == "") {
        this.errors.debitore['anagraficaComune'] = 'Il campo Comune di residenza è obbligatorio';
      }
      if (this.pagamento.debitore.indirizzo.cap == "") {
        this.errors.debitore['anagraficaCAP'] = 'Il campo CAP è obbligatorio';
      }
      if (this.pagamento.debitore.indirizzo.provincia == "") {
        this.errors.debitore['anagraficaProvincia'] = 'Il campo Provincia è obbligatorio';
      }

      return this.hasErrors;
    },
    checkPaymentData: function(event) {

      event.preventDefault();

      if (this.validateData(event) === false) { //this.hasErrors) {
        event.stopPropagation();
        this.showErrors = true;
        return;
      }
      const endpoint = this.endpoints.pagamento;
      const component = this;
      this.loading = true;
      this.pagamentoResponse = null;

      jQuery('#modal-pagamento_iuv').modal('show');
      return this.$http.post(endpoint, this.pagamento).then((response) => {
        component.pagamentoResponse = response.data;
      })
      .catch(function (error) {
        console.log(error);
      });
    },
    userInfo(field) {
      const info = drupalSettings.m_api.spontaneo.userinfo;
      if (Object.prototype.hasOwnProperty.call(info, field)) {
        return info[field];
      }
      return null;
    }
  }
};
</script>
