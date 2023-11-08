<template>
  <fieldset class="fieldset-step my-5" :class="{ 'h-disabled': isDisabled }">
    <legend><span class="fieldset-step-num">1</span> Dati richiedente</legend>
    <div class="fieldset-content">
      <div class="row my-2">
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaNome"
            v-model="richiedente.name"
            label="Nome del richiedente *"
            placeholder="Inserisci il nome"
            :required="true"
            :readonly="richiedente.fromLogin.name !== ''"
            error="Il campo nome è obbligatorio."
            :disabled="isFieldDisabled('name')"
            :class="{ disabled: isFieldDisabled('name') }"
          />
        </div>
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaCognome"
            v-model="richiedente.surname"
            label="Cognome del richiedente *"
            placeholder="Inserisci il cognome"
            :required="true"
            :readonly="richiedente.fromLogin.surname !== ''"
            error="Il campo cognome è obbligatorio."
            :disabled="isFieldDisabled('surname')"
            :class="{ disabled: isFieldDisabled('surname') }"
          />
        </div>
        <div class="col-6 col-lg-4">
          <pagome-input
            id="ragioneSociale"
            v-model="richiedente.ragioneSociale"
            :readonly="richiedente.fromLogin.ragioneSociale !== ''"
            label="Ragione sociale del richiedente"
          />
        </div>
      </div>
      <div class="row my-2">
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaCodFisc"
            v-model="richiedente.codiFisc"
            :readonly="richiedente.fromLogin.codiFisc !== ''"
            label="Codice Fiscale / Partita IVA del richiedente *"
            placeholder="----------------"
            error="Inserisci un codice fiscale o una partita IVA validi."
            :required="true"
            length="16"
            :disabled="isFieldDisabled('codiFisc')"
            :class="{ disabled: isFieldDisabled('codiFisc') }"
          />
        </div>
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaEmail"
            v-model="richiedente.email"
            :readonly="richiedente.fromLogin.email !== ''"
            label="Email del richiedente *"
            placeholder="Inserisci l'indirizzo email"
            type="email"
            error="Inserisci un indirizzo email valido."
            :required="true"
            :disabled="isFieldDisabled('email')"
            :class="{ disabled: isFieldDisabled('email') }"
          />
        </div>
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaTelefono"
            v-model="richiedente.telephoneNumber"
            :readonly="richiedente.fromLogin.telephoneNumber !== ''"
            label="Recapito telefonico del richiedente *"
            placeholder="Inserisci il contatto telefonico"
            :required="true"
          />
        </div>
        <div class="col-6 col-lg-4">
          <pagome-input
            id="luogoNascita"
            v-model="richiedente.luogoNascita"
            :readonly="richiedente.fromLogin.luogoNascita !== ''"
            label="Luogo di nascita *"
            placeholder="Inserisci il luogo di nascita"
            :required="true"
          />
        </div>
        <div class="col-6 col-lg-4">
          <div
            class="js-form-item js-form-type-date form-type-date form-wrapper"
          >
            <label for="dataNascita">Data di nascita *</label>
            <input
              id="dataNascita"
              type="date"
              class="form-date"
              v-model="richiedente.dataNascita"
              :readonly="richiedente.fromLogin.dataNascita !== ''"
              placeholder="Inserisci la data di nascita"
              :required="true"
              :disabled="isFieldDisabled('dataNascita')"
              :class="{ disabled: isFieldDisabled('dataNascita') }"
            />
          </div>
        </div>
        <div class="col-6 col-lg-4">
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
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaCAP"
            v-model="richiedente.indirizzo.cap"
            label="CAP *"
            placeholder="Inserisci il CAP"
            :required="true"
            error="Il campo CAP è obbligatorio."
          />
        </div>
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaComune"
            v-model="richiedente.indirizzo.comune"
            label="Comune di residenza *"
            placeholder="Inserisci il comune"
            :required="true"
            error="Il campo comune è obbligatorio."
          />
        </div>
        <div class="col-6 col-lg-4">
          <pagome-input
            id="anagraficaIndirizzo"
            v-model="richiedente.indirizzo.indirizzo"
            label="Indirizzo di residenza *"
            placeholder="Inserisci via/corso/piazza n°"
            :required="true"
            error="Il campo indirizzo è obbligatorio."
          />
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script>
import PagomeInput from "../form/PagomeInput.vue";

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
    },
    disabled: {
      type: Object,
      required: true
    }
  },
  watch: {
    richiedente: function(val) {
      console.log(val);
    }
  },
  computed: {
    isDisabled() {
      return !this.enabled;
    },
    getError() {
      return elementId => {
        if (Object.prototype.hasOwnProperty.call(this.errors, elementId)) {
          return this.errors[elementId];
        }
        return null;
      };
    }
  },
  methods: {
    isFieldDisabled(field) {
      if (Object.prototype.hasOwnProperty.call(this.disabled, field)) {
        return this.disabled[field];
      }
      return false;
    }
  }
};
</script>
