<template>
  <div v-if="!submitted" class="page-segnalame">
    <form
      novalidate="novalidate"
      class="needs-validation"
      @submit="onSubmit"
      ref="segnalazione"
      :class="{ 'was-validated': validationError }"
    >
      <div class="secondary-bg-light py-5">
        <segnalame-map
          :segnalazione="segnalazione"
          @onAddress="updateAddress"
        ></segnalame-map>
        <segnalame-form
          :segnalazione="segnalazione"
          :area-error="hasAreaError"
        ></segnalame-form>
      </div>
    </form>
    <segnalame-form-upload
      :files="segnalazione.immagini"
      id="immagini"
      accept=".png, .jpg, .jpeg"
      title="Immagini"
      :list-title="`Immagini caricate - ${segnalazione.immagini.length}/4`"
      :limit="10"
      counter="2"
      :disabled="segnalazione.immagini.length >= 4"
      @load="segnalazione.immagini = $event"
      @removeFile="removeFile($event, 'immagini')"
    ></segnalame-form-upload>
    <segnalame-form-upload
      :files="segnalazione.documenti"
      id="documenti"
      accept="application/pdf"
      title="Documenti"
      list-title="Documenti caricati"
      counter="3"
      @load="segnalazione.documenti = $event"
      @removeFile="removeFile($event, 'documenti')"
    ></segnalame-form-upload>
    <div class="container container-privacy">
      <h5>Privacy *</h5>
      <div class="row justify-content-between">
        <div class="col-12 col-lg-5 privacy-item form-check">
          <input
            id="privacy-1"
            v-model="segnalazione.privacy"
            type="checkbox"
          />
          <label for="privacy-1">
            Dichiaro di aver letto l'informativa sulla privacy, autorizzo al
            trattamento dei miei dati personali
          </label>
        </div>
        <div
          v-if="hasPrivacyError"
          :style="{ display: 'block' }"
          class="invalid-feedback"
        >
          Il campo privacy è obbligatorio
        </div>
      </div>
    </div>
    <div
      v-if="!submitted"
      class="container actions text-center neutral-bg-light p-5 my-5"
    >
      <a
        v-if="!loading"
        :class="{ disabled: loading }"
        :disabled="loading"
        class="btn btn-outline-success"
        @click="validateData"
      >
        Invia la segnalazione
      </a>
      <spinner v-if="loading" />
      <div v-if="errors.length > 0">
        <div class="alert alert-danger mt-4" role="alert">
          Errore nell'invio della segnalazione:
          <ul class="errors">
            <li v-for="(error, key) in errors" :key="key" class="error-item">
              {{ error }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="page-segnalame page-segnalame--success">
    <div class="container">
      <fieldset class="fieldset-step my-5">
        <legend>
          INSERIMENTO SEGNALAZIONE
        </legend>
      </fieldset>
      <h3>Grazie</h3>
      <div class="my-2">
        La tua richiesta è stata inoltrata.
      </div>
      <div class="page-segnalame__button-nav">
        <a
          class="btn btn-primary"
          :class="{ disabled: loading }"
          :disabled="loading"
          :href="segnalazioneUrl"
        >
          Vai alla segnalazione
        </a>
        <a
          class="btn btn-primary"
          :class="{ disabled: loading }"
          :disabled="loading"
          :href="listaSegnalazioniUrl"
        >
          Le tue segnalazioni
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import SegnalameMap from "./components/SegnalaMe/SegnalameMap.vue";
import SegnalameForm from "./components/SegnalaMe/SegnalameForm.vue";
import SegnalameFormUpload from "./components/SegnalaMe/SegnalameFormUpload.vue";
import Spinner from "./components/ui/Spinner.vue";

export default {
  components: {
    Spinner,
    SegnalameMap,
    SegnalameForm,
    SegnalameFormUpload
  },
  data() {
    return {
      idSector: 0,
      idInstitute: 0,

      segnalazioneUrl: "",
      listaSegnalazioniUrl: drupalSettings.m_api.url_lista_seganalazioni,
      segnalazione: {
        immagini: [],
        documenti: []
      },
      errors: [],
      loading: false,
      submitted: false,
      validationError: false,
      hasAreaError: false,
      hasPrivacyError: false
    };
  },
  computed: {},
  watch: {
    "segnalazione.area": function() {
      this.hasAreaError = false;
    },
    "segnalazione.privacy": function() {
      this.hasPrivacyError = false;
    }
  },
  mounted() {
    this.idInstitute = drupalSettings.m_api.id_institute;
    this.idSector = drupalSettings.m_api.id_sector;
  },
  methods: {
    updateAddress(address) {
      this.segnalazione = {
        ...this.segnalazione,
        ...address
      };
    },
    removeFile(index, arrayName) {
      this.segnalazione[arrayName].splice(index, 1);
    },
    onSubmit: function(e) {
      e.preventDefault();
      this.validateData();
      return false;
    },
    validateData: function() {
      this.hasAreaError = false;
      this.hasPrivacyError = false;
      if (!this.segnalazione.area) {
        this.hasAreaError = true;
      }
      if (!this.segnalazione.privacy) {
        this.hasPrivacyError = true;
      }
      if (!this.$refs.segnalazione.checkValidity()) {
        return (this.validationError = true);
      }
      if (this.hasAreaError || this.hasPrivacyError) {
        return;
      }
      this.validationError = false;
      this.sendForm();
    },
    sendForm: function() {
      this.loading = true;
      const data = {
        ...this.segnalazione,
        fileIdList: [
          ...this.segnalazione.immagini.map(x => x.id),
          ...this.segnalazione.documenti.map(x => x.id)
        ],
        area: undefined,
        immagini: undefined,
        documenti: undefined
      };
      const endpoint = `${drupalSettings.m_api.endpoints.nuova_segnalazione}/${this.segnalazione.area}/${this.idInstitute}/${this.idSector}`;
      this.$http
        .post(endpoint, data)
        .then(res => {
          if (res.data.id) {
            this.segnalazioneUrl = `${this.listaSegnalazioniUrl}/${res.data.id}`;
            this.loading = false;
            this.submitted = true;
            window.scrollTo({
              top: 0,
              behavior: "smooth"
            });
          } else {
            console.log("err");
            console.log(res);
            this.loading = false;
            window.scrollTo({
              top: 0,
              behavior: "smooth"
            });
          }
        })
        .catch(err => {
          console.log("err");
          console.log(err);
          this.loading = false;
          window.scrollTo({
            top: 0,
            behavior: "smooth"
          });
          //this.errors.push(error.response.data.error.message);
        });
    }
  }
};
</script>
