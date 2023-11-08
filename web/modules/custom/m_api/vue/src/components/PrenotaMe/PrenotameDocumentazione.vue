<template>
  <fieldset class="fieldset-step my-5" :class="{ 'h-disabled': isDisabled }">
    <legend><span class="fieldset-step-num">4</span> Documentazione</legend>
    <div class="fieldset-content">
      <form
        ref="formUpload"
        class="needs-validation"
        :class="{ 'was-validated': hasError }"
        novalidate
        @submit.prevent="processFile($event)"
      >
        <div class="row my-2">
          <div class="col-12 col-lg-4 my-2">
            <div class="form-wrapper form-group">
              <label for="fileUpload">Documentazione</label>
              <input
                id="fileUpload"
                ref="uploadInput"
                type="file"
                required
                @change="loadTextFromFile"
              />
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
          <div class="col-12 col-lg-8 my-2 px-4 col-caricati">
            <h5>Documenti caricati</h5>
            <ul class="dettaglio-list">
              <li v-for="(file, index) in prenotazione.allegati" :key="index">
                <a
                  class="file d-inline-flex align-items-center"
                  @click="$emit('removeFile', index)"
                >
                  <svg class="icon icon-primary icon-xs mr-1">
                    <use
                      xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-delete"
                    />
                  </svg>
                  {{ file.description }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </form>
    </div>
  </fieldset>
</template>

<script>
import PagomeInput from "../form/PagomeInput.vue";
const JsBase64 = () =>
  import(
    /* webpackChunkName: "js-base64" */
    "js-base64"
  );

export default {
  components: {
    PagomeInput
  },
  props: {
    prenotazione: {
      type: Object,
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
      const data = [
        ...this.prenotazione.allegati,
        Object.assign({}, this.allegato)
      ];
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
      reader.onload = function(e) {
        JsBase64().then(
          ({ encode }) =>
            (component.allegato.base64Content = encode(e.target.result))
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
