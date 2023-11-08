<template>
  <div class="row mt-5">
    <div class="col-12">
      <fieldset class="fieldset-step">
        <legend class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fieldset-step-num">2</span>
            Dati fruitore
          </div>
          <div class="form-check">
            <input id="checkbox-oraganizzatore" type="checkbox" @click="$emit('showFields', !showFields)">
            <label for="checkbox-oraganizzatore">
              I dati del richiedente sono diversi dal fruitore
            </label>
          </div>
        </legend>
        <div class="fieldset-content" v-if="showFields">
          <div class="row mt-3">
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioNome"
                v-model="beneficiarioRef.name"
                label="Nome *"
                placeholder="Inserisci il nome"
                :required="true"
                error="Il campo nome è obbligatorio"
                :show-error="errors.name"
                @input="errorsRef.name = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioCognome"
                v-model="beneficiarioRef.surname"
                label="Cognome *"
                placeholder="Inserisci il cognome"
                :required="true"
                error="Il campo cognome è obbligatorio."
                :show-error="errors.surname"
                @input="errorsRef.surname = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioEmail"
                v-model="beneficiarioRef.email"
                label="Email *"
                placeholder="Inserisci l'email"
                :required="true"
                error="Il campo email non è valido."
                :show-error="errors.email"
                @input="errorsRef.email = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioCodiFisc"
                v-model="beneficiarioRef.codiFisc"
                label="Codice fiscale *"
                placeholder="Inserisci il codice fiscale"
                :required="true"
                error="Il campo codice fiscale è obbligatorio."
                :show-error="errors.codiFisc"
                @input="errorsRef.codiFisc = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioTelephone"
                v-model="beneficiarioRef.telephoneNumber"
                label="Recapito telefonico *"
                placeholder="Inserisci il contatto telefonico"
                :required="true"
                error="Il campo numero di telefono è obbligatorio."
                :show-error="errors.telephoneNumber"
                @input="errorsRef.telephoneNumber = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioIndirizzo"
                v-model="beneficiarioRef.indirizzo.indirizzo"
                label="Indirizzo *"
                placeholder="Inserisci l'indirizzo"
                :required="true"
                error="Il campo indirizzo è obbligatorio."
                :show-error="errors.indirizzo.indirizzo"
                @input="errorsRef.indirizzo.indirizzo = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioCap"
                v-model="beneficiarioRef.indirizzo.cap"
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
                id="beneficiarioComune"
                v-model="beneficiarioRef.indirizzo.comune"
                label="Comune *"
                placeholder="Inserisci il comune"
                :required="true"
                error="Il campo comune è obbligatorio."
                :show-error="errors.indirizzo.comune"
                @input="errorsRef.indirizzo.comune = false"
              />
            </div>
            <div class="col-6 col-lg-4 mb-lg-5 px-3">
              <pagome-input
                id="beneficiarioProvincia"
                v-model="beneficiarioRef.indirizzo.provincia"
                label="Provincia *"
                placeholder="Inserisci la provincia"
                :required="true"
                error="Il campo provincia è obbligatorio."
                length="2"
                :show-error="errors.indirizzo.provincia"
                @input="errorsRef.indirizzo.provincia = false"
              />
            </div>
            <div class="col-6 col-lg-4 px-3">
              <pagome-input
                id="beneficiarioCivico"
                v-model="beneficiarioRef.indirizzo.civico"
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
                id="beneficiarioStato"
                v-model="beneficiarioRef.indirizzo.stato"
                label="Stato "
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
  name: "CasefilesBeneficiario",
  components: {PagomeInput},
  props: {
    beneficiario: {
      type: Object,
      required: true
    },
    showFields: {
      type: Boolean,
      required: true
    },
    errors: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      beneficiarioRef: this.beneficiario,
      errorsRef: this.errors
    };
  },
};
</script>

<style scoped>

</style>
