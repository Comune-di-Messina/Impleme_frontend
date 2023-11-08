<template>
  <div>
    <form
      v-if="!submitted"
      ref="formUpload"
      class="needs-validation"
      :class="{ 'was-validated': hasError }"
      novalidate
      @submit.prevent="processFile($event)"
    >
      <div class="row my-2">
        <div class="col-12  my-2">
          <div class="form-wrapper form-group">
            <label for="fileUpload">Documentazione</label>
            <input
              id="fileUpload"
              ref="uploadInput"
              type="file"
              required
              @change="loadTextFromFile"
            >
            <div class="invalid-feedback">
              Il campo documento è obbligatorio.
            </div>
          </div>
          <pagome-input
            id="descrizione"
            v-model="allegato.description"
            label="Descrizione"
            :required="true"
            error="Il campo descrizione è obbligatorio."
          />
          <a
            class="btn btn-outline-success my-4"
            @click="processFile($event)"
          >
            carica
          </a>
        </div>
        <div class="col-12 my-2 col-caricati">
          <!-- <h5>Documenti caricati</h5> -->
          <ul class="dettaglio-list">
            <li
              v-for="(file, index) in allegati"
              :key="index"
            >
              <a
                class="file d-inline-flex align-items-center"
                @click="$emit('removeFile', index)"
              >
                <svg class="icon icon-primary icon-xs mr-1">
                  <use xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-delete"/>
                </svg>
                {{ file.fileName }} ({{ file.size / 1024 / 1024 |format_number }} Mb)
              </a>
            </li>
          </ul>
        </div>
      </div>
    </form>
    <div v-else>
      <div class="dettaglio-step">
        <h2>Caricamento avvenuto</h2>
        <div class="dettaglio-step-content text-serif">
          Il caricamento e salvataggio dei documenti è avvenuto con successo. Può scaricare copia dei documenti in qualsiasi momento dall'aerea apposita.<br>
          <br>
          Grazie.
        </div>
        <div class="dettaglio-step-cta text-center mt-4">
          <a href="/user" class="btn btn-primary ">Vai all'area personale</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PagomeInput from '../form/PagomeInput.vue';
const JsBase64 = () => import(
  /* webpackChunkName: "js-base64" */
  'js-base64'
);

export default {
  components: {
    PagomeInput
  },
  props: {
    allegati: {
      type: Array,
      required: true
    },
    submitted: {
      type: Boolean,
      required: true
    }
  },
  data() {
    return {
      allegato: {
        base64Content: null,
        description: null,
        fileName: null
      },
      hasError: false
    };
  },
  computed: {
    isDisabled: () => false
  },
  methods: {
    processFile(event) {
      const form = this.$refs.formUpload;
      if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        this.hasError = true;
        return false;
      }
      const component = this;
      const data = [...this.allegati, Object.assign ({}, this.allegato)];
      this.$emit("load", data);
      component.$refs.uploadInput.value = "";
      component.allegato.description = "";
      component.hasError = false;
      return true;
    },
    loadTextFromFile(ev) {
      const file = ev.target.files[0];
      this.allegato.fileName = file.name;
      this.allegato.size = file.size;
      const reader = new FileReader();
      const component = this;
      reader.onload = function (e) {
        JsBase64().then(
          ({encode}) => component.allegato.base64Content = encode(e.target.result)
        );

      };
      reader.readAsText(file);
    },
    fileSize(file) {
      return file.size / 1024;
    }
  }
};
</script>
