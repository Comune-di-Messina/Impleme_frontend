<template>
  <fieldset class="fieldset-step my-5" :class="{'h-disabled': isDisabled}">
    <legend><span class="fieldset-step-num">3</span> Dati anagrafici del debitore</legend>
    <div class="fieldset-content">
      <div class="row my-2">
        <div class="col-6 col-lg-4 my-2">
          <pagome-input
            id="anagraficaCodFisc"
            v-model="richiedente.codiFisc"
            label="Codice Fiscale *"
            placeholder="Inserisci il codice fiscale"
            error="Inserisci un codice fiscale valido."
            :required="true"
            length="16"
          />
        </div>
        <div class="col-6 col-lg-4 my-2">
          <pagome-input
            id="anagraficaNome"
            v-model="richiedente.name"
            label="Nome *"
            placeholder="Inserisci il nome"
            :required="true"
            error="Il campo nome è obbligatorio."
          />
        </div>
        <div class="col-6 col-lg-4 my-2">
          <pagome-input
            id="anagraficaCognome"
            v-model="richiedente.surname"
            label="Cognome *"
            placeholder="Inserisci il cognome"
            :required="true"
            error="Il campo cognome è obbligatorio."
          />
        </div>
      </div>
      <div class="row my-2">
        <div class="col-6 col-lg-4 my-2">
          <pagome-input
            id="anagraficaIndirizzo"
            v-model="richiedente.indirizzo.indirizzo"
            label="Indirizzo di residenza *"
            placeholder="Inserisci via/corso/piazza n°"
            :required="true"
            error="Il campo indirizzo è obbligatorio."
          />
        </div>
        <div class="col-6 col-lg-4 my-2">
          <pagome-input
            id="anagraficaComune"
            v-model="richiedente.indirizzo.comune"
            label="Comune di residenza *"
            placeholder="Inserisci il comune"
            :required="true"
            error="Il campo comune è obbligatorio."
          />
        </div>
        <div class="col-6 col-lg-2 my-2">
          <pagome-input
            id="anagraficaCAP"
            v-model="richiedente.indirizzo.cap"
            label="CAP *"
            placeholder="Inserisci il CAP"
            :required="true"
            error="Il campo CAP è obbligatorio."
          />
        </div>
        <div class="col-6 col-lg-2 my-2">
          <pagome-input
            id="anagraficaProvincia"
            v-model="richiedente.indirizzo.provincia"
            label="Provincia di residenza *"
            placeholder="Inserisci la provincia"
            :required="true"
            :length="2"
            error="Il campo provincia è obbligatorio."
          />
        </div>
      </div>
      <div class="row my-2">
        <div class="col-6 col-lg-4 my-2">
          <pagome-input
            id="anagraficaEmail"
            v-model="richiedente.email"
            label="Email *"
            placeholder="Inserisci l'indirizzo email"
            type="email"
            error="Inserisci un indirizzo email valido."
            :required="true"
          />
        </div>
        <div class="col-6 col-lg-4 my-2">
          <pagome-input
            id="anagraficaTelefono"
            v-model="richiedente.telephoneNumber"
            label="Contatto telefonico"
            placeholder="Inserisci il contatto telefonico"
          />
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script>
import PagomeInput from './form/PagomeInput.vue';

export default {
  components: {
    PagomeInput
  },
  props: {
    richiedente: {
      required: true,
      type: Object
    },
    enabled: {
      type: Boolean,
      default: false
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    isDisabled() {
      return !this.enabled;
    },
    getError() {
      return (elementId) => {
        if (Object.prototype.hasOwnProperty.call(this.errors, 'elementId')) {
          return this.errors[elementId];
        }
        return null;
      };
    }
  },
  methods: {

  }
};
</script>
