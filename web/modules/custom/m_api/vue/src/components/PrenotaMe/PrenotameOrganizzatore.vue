<template>
  <div class="organizzatore">
    <div class="form-row">
    </div>
    <fieldset
      class="fieldset-step my-5"
      :class="{'h-disabled': !organizzatoreEnabled}"
    >
      <legend>
        <span class="fieldset-step-num">2</span>
        Dati organizzatore
        <div class="form-check">
          <input
            id="checkbox-oraganizzatore"
            v-model="organizzatoreEnabled"
            type="checkbox"
          >
          <label for="checkbox-oraganizzatore">I dati dell'organizzatore sono diversi dal referente</label>
        </div>
      </legend>
      <div class="fieldset-content">
        <div class="row my-2">
          <div class="col-6 col-lg-4 my-2">
            <pagome-input
              id="anagraficaOraganizzatoreNome"
              v-model="organizzatore.name"
              label="Nome del organizzatore *"
              placeholder="Inserisci il nome"
              :required="organizzatoreEnabled"
              error="Il campo nome è obbligatorio."
            />
          </div>
          <div class="col-6 col-lg-4 my-2">
            <pagome-input
              id="anagraficaOraganizzatoreCognome"
              v-model="organizzatore.surname"
              label="Cognome del organizzatore *"
              placeholder="Inserisci il cognome"
              :required="organizzatoreEnabled"
              error="Il campo cognome è obbligatorio."
            />
          </div>
          <div class="col-6 col-lg-4 my-2">
            <pagome-input
              id="organizzzatoreRagioneSociale"
              v-model="organizzatore.ragioneSociale"
              label="Ragione sociale del organizzatore"
            />
          </div>
        </div>
        <div class="row my-2">
          <div class="col-6 col-lg-4 my-2">
            <pagome-input
              id="anagraficaOraganizzatoreCodFisc"
              v-model="organizzatore.codiFisc"
              label="Codice Fiscale / Partita IVA del organizzatore *"
              placeholder="----------------"
              error="Inserisci un codice fiscale o una partita IVA validi."
              :required="organizzatoreEnabled"
              length="16"
            />
          </div>
          <div class="col-6 col-lg-4 my-2">
            <pagome-input
              id="anagraficaOraganizzatoreEmail"
              v-model="organizzatore.email"
              label="Email del organizzatore *"
              placeholder="Inserisci l'indirizzo email"
              type="email"
              error="Inserisci un indirizzo email valido."
              :required="organizzatoreEnabled"
            />
          </div>
          <div class="col-6 col-lg-4 my-2">
            <pagome-input
              id="anagraficaOraganizzatoreTelefono"
              v-model="organizzatore.telephoneNumber"
              label="Recapito telefonico del organizzatore *"
              placeholder="Inserisci il contatto telefonico"
              :required="organizzatoreEnabled"
            />
          </div>
        </div>
      </div>
    </fieldset>
  </div>
</template>

<script>
import PagomeInput from '../form/PagomeInput.vue';

export default {
  components: {
    PagomeInput,
  },
  props: {
    organizzatore: {
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
  data() {
    return {organizzatoreEnabled: false};
  },
  computed: {
    isDisabled() {
      return !this.enabled;
    },
    getError() {
      return (elementId) => {
        if (Object.prototype.hasOwnProperty.call(this.errors, elementId)) {
          return this.errors[elementId];
        }
        return null;
      };
    }
  }
};
</script>
