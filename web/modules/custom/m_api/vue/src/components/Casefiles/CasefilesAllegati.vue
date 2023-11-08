<template>
  <fieldset class="fieldset-step my-5">
    <legend><span class="fieldset-step-num" v-if="showStep">3</span> Documentazione</legend>
    <div class="fieldset-content">
      <form
        ref="formUpload"
        class="needs-validation"
        :class="{ 'was-validated': hasError }"
        novalidate
        @submit.prevent="processFile($event)"
      >
        <div class="row my-2">
          <div class="col-6">
            <div class="form-wrapper form-group mb-1">
              <label for="fileUpload">Documentazione</label>
              <input
                id="fileUpload"
                ref="uploadInput"
                type="file"
                required
                @change="prepareFile($event)"
                accept="application/pdf"
              >
              <div class="invalid-feedback">
                Il campo documento è obbligatorio.
              </div>
            </div>
            <pagome-input
              id="descrizione"
              v-model="attachment.description"
              label="Descrizione"
              :required="true"
              error="Il campo descrizione è obbligatorio."
              @input="hasError = false"
              :bg-transparent="true"
            />
            <a class="btn btn-outline-success my-4" @click="processFile($event)">
              carica
            </a>
          </div>
          <div class="col-6" style="background: #E6F7EE;">
            <div class="row">
              <div class="col-12">
                <h5>Documenti caricati</h5>
              </div>
              <div class="col-12">
                <ul class="dettaglio-list">
                  <li v-for="(file, index) in attachments" :key="index" @click="$emit('removeFile', index)">
                    <a class="file d-inline-flex align-items-center">
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
    attachments: {
      type: Array,
      required: true
    },
    showStep: {
      type: Boolean,
      required: false,
      default: true
    }
  },
  data() {
    return {
      attachment: {
        base64Content: null,
        description: null,
        fileName: null
      },
      hasError: false
    };
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
      const data = [
        ...this.attachments,
        Object.assign({}, this.attachment)
      ];
      this.$emit("load", data);
      this.$refs.uploadInput.value = "";
      this.attachment.description = "";
      this.hasError = false;
      return true;
    },
    prepareFile(ev) {
      const file = ev.target.files[0];
      this.attachment.fileName = file.name;
      this.attachment.size = file.size;
      const reader = new FileReader();
      const component = this;
      reader.onload = function(e) {
        JsBase64().then(
          ({ encode }) =>
            (component.attachment.base64Content = encode(e.target.result))
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
