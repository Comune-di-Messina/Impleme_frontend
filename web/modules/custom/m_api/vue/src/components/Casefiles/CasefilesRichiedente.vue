<template>
  <div class="row mt-5">
    <div class="col-12">
      <fieldset class="fieldset-step">
        <legend>
          <span class="fieldset-step-num">1</span>
          Dati richiedente
        </legend>
        <div class="fieldset-content">
          <div class="row mt-3">
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteNome"
                v-model="richiedenteRef.name"
                label="Nome *"
                placeholder="Inserisci il nome"
                :required="true"
                error="Il campo nome è obbligatorio."
                :show-error="errors.name"
                :disabled="disabledFields.includes('name')"
                @input="errorsRef.name = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteCognome"
                v-model="richiedenteRef.surname"
                label="Cognome *"
                placeholder="Inserisci il cognome"
                :required="true"
                error="Il campo cognome è obbligatorio."
                :show-error="errors.surname"
                :disabled="disabledFields.includes('surname')"
                @input="errorsRef.surname = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteEmail"
                v-model="richiedenteRef.email"
                type="email"
                label="Email *"
                placeholder="Inserisci l'email"
                :required="true"
                error="Il campo email non è valido."
                :show-error="errors.email"
                :disabled="disabledFields.includes('email')"
                @input="errorsRef.email = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteCodiFisc"
                v-model="richiedenteRef.codiFisc"
                label="Codice Fiscale / Partita IVA *"
                placeholder="Inserisci il codice fiscale"
                :required="true"
                length="16"
                error="Il campo Codice Fiscale / Partita IVA deve essere di 16 numeri."
                :show-error="errors.codiFisc"
                :disabled="disabledFields.includes('codiFisc')"
                @input="errorsRef.codiFisc = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteTelephone"
                v-model="richiedenteRef.telephoneNumber"
                label="Recapito telefonico *"
                placeholder="Inserisci il contatto telefonico"
                :required="true"
                error="Il campo numero di telefono è obbligatorio"
                :show-error="errors.telephoneNumber"
                @input="errorsRef.telephoneNumber = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteIndirizzo"
                v-model="richiedenteRef.indirizzo.indirizzo"
                label="Indirizzo *"
                placeholder="Inserisci l'indirizzo"
                :required="true"
                error="Il campo indirizzo è obbligatorio."
                :show-error="errors.indirizzo.indirizzo"
                :disabled="disabledFields.includes('indirizzo')"
                @input="errorsRef.indirizzo.indirizzo = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteCap"
                v-model="richiedenteRef.indirizzo.cap"
                label="CAP *"
                placeholder="Inserisci il CAP"
                :required="true"
                error="Il campo CAP è obbligatorio."
                :show-error="errors.indirizzo.cap"
                @input="errorsRef.indirizzo.cap = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteComune"
                v-model="richiedenteRef.indirizzo.comune"
                label="Comune *"
                placeholder="Inserisci il comune"
                :required="true"
                error=" 'Il campo comune è obbligatorio."
                :show-error="errors.indirizzo.comune"
                @input="errorsRef.indirizzo.comune = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="richiedenteProvincia"
                v-model="richiedenteRef.indirizzo.provincia"
                label="Provincia *"
                placeholder="Inserisci la provincia"
                :required="true"
                length="2"
                error="Il campo provincia è obbligatorio."
                :show-error="errors.indirizzo.provincia"
                @input="errorsRef.indirizzo.provincia = false"
              />
            </div>
            <div class="col-6 col-lg-4 px-3">
              <pagome-input
                id="richiedenteCivico"
                v-model="richiedenteRef.indirizzo.civico"
                label="Numero civico *"
                placeholder="Inserisci il numero civico"
                :required="true"
                error="Il campo numero civico è obbligatorio."
                :show-error="errors.indirizzo.civico"
                @input="errorsRef.indirizzo.civico = false"
              />
            </div>
            <div class="col-6 col-lg-4 px-3">
              <pagome-input
                id="richiedenteStato"
                v-model="richiedenteRef.indirizzo.stato"
                label="Stato"
                placeholder="Inserisci stato"
                value="Italia"
                error="Il campo stato è obbligatorio."
                :show-error="errors.indirizzo.stato"
                @input="errorsRef.indirizzo.stato = false"
              />
            </div>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
</template>

<script>
import PagomeInput from "../form/PagomeInput.vue";

export default {
  name: "CasefilesRichiedente",
  components: {PagomeInput},
  props: {
    richiedente: {
      type: Object,
      required: true
    },
    errors: {
      type: Object,
      required: true
    },
    disabledFields: {
      type: Array,
      required: false,
      default: () => []
    }
  },
  data() {
    return {
      richiedenteRef: this.richiedente,
      errorsRef: this.errors
    };
  },
};
</script>

<style scoped>

</style>
