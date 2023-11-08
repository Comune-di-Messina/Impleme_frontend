<template>
  <div>
    <div v-if="!submitted">
      <h4>Caricamento documenti</h4>
      <div class="dettaglio-step-content text-serif">
        Per completare la tua prenotazione è necessario integrare la modulistica inviata con questi ulteriori documenti:<br>
        {{ lista_documenti }}
      </div>
    </div>
    <div class="dettaglio-step-upload py-5">
      <prenotame-aggiungi-documentazione
        :allegati="allegati"
        :submitted="submitted"
        @load="allegati = $event"
        @removeFile="removeFile($event)"
      />
      <div
        v-if="!submitted"
        class="dettaglio-step-upload-submit text-center"
      >
        <input
          type="submit"
          class="btn btn-primary"
          :disabled="allegati.length < 1 || loading"
          :class="{disabled: allegati.length < 1 || loading}"
          value="Invia documentazione"
          @click="sendFiles()"
        >
      </div>
      <div v-if="errors.length > 0">
        <div class="col-md-10 alert alert-danger mt-4" role="alert">
          <div
            v-for="(error, index) in errors"
            :key="index"
          >
            {{ error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import PrenotameAggiungiDocumentazione from './components/PrenotaMe/PrenotameAggiungiDocumentazione.vue';

export default {
  components: {
    PrenotameAggiungiDocumentazione,
  },
  data: function () {
    return {
      errors: [],
      showErrors: false,
      allegati: [],
      pagamentoResponse: null,
      endpoints: {
        allegati: '/servizi/pagamento/api/allegati'
      },
      loading: false,
      submitted: false
    };
  },
  computed: {
  },
  methods: {
    removeFile(index) {
      this.allegati.splice(index, 1);
    },
    sendFiles() {
      const endpoint = this.endpoints.allegati + '/' + drupalSettings.m_api.caseId;
      const component = this;
      this.loading = true;
      this.errors = [];
      return this.$http.post(endpoint, this.allegati).then((response) => {
        component.submitted = true;
        component.loading = false;
        component.submitted = true;
      })
      .catch(function (error) {
        component.loading = false;
        if (Object.prototype.hasOwnProperty.call(error.response.data.error, 'message') && error.response.data != null) {
          component.errors.push(error.response.data.error.message);
        }
        // console.log(error);
      });
    }
  }
};
</script>
