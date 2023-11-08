<template>
  <div class="container container-upload">
    <fieldset class="fieldset-step my-5">
      <legend>
        <span class="fieldset-step-num">{{ counter }}</span> {{ title }}
      </legend>
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
                <label :for="`fileUpload${id}`">{{ title }}</label>
                <input
                  :id="`fileUpload${id}`"
                  ref="uploadInput"
                  type="file"
                  required
                  :accept="accept"
                  :disabled="loading || disabled"
                  @change="loadTextFromFile"
                />
                <div class="invalid-feedback">
                  Il campo {{ title.toLowerCase() }} è obbligatorio.
                </div>
                <div
                  v-if="limitError"
                  class="invalid-feedback"
                  style="display: block"
                >
                  {{ limitErrorMessage }}
                </div>
                <div
                  v-if="genericError"
                  class="invalid-feedback"
                  style="display: block"
                >
                  {{ genericErrorMessage }}
                </div>
              </div>
              <pagome-input
                :id="`descrizione${id}`"
                v-model="allegato.description"
                label="Descrizione"
                :required="true"
                error="Il campo descrizione è obbligatorio."
                :disabled="loading || disabled"
              />
              <a
                class="btn btn-outline-success my-4"
                :class="{ disabled: loading }"
                @click="processFile($event)"
              >
                carica
              </a>
            </div>
            <div class="col-12 col-lg-8 my-2 px-4 col-caricati">
              <h5>{{ listTitle }}</h5>
              <spinner v-if="loading"></spinner>
              <ul v-else class="dettaglio-list">
                <li v-for="(file, index) in files" :key="index">
                  <a
                    class="file d-inline-flex align-items-center"
                    @click="removeFile(index)"
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
  </div>
</template>

<script>
import PagomeInput from "../form/PagomeInput.vue";
import Spinner from "../ui/Spinner.vue";

const JsBase64 = () =>
  import(
    /* webpackChunkName: "js-base64" */
    "js-base64"
  );

export default {
  name: "SegnalameFormUpload.vue",
  components: {
    PagomeInput,
    Spinner
  },
  props: {
    files: {
      type: Array,
      required: true
    },
    id: {
      type: String,
      required: true
    },
    accept: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    listTitle: {
      type: String,
      required: true
    },
    counter: {
      type: String,
      required: true
    },
    disabled: {
      type: Boolean,
      required: false
    },
    limit: {
      type: Number,
      required: false
    }
  },
  data() {
    return {
      allegato: {
        base64Content: null,
        description: null,
        fileName: null
      },
      loading: false,
      hasError: false,
      limitError: false,
      genericError: false,
      limitErrorMessage: `Il file supera il limite di ${this.limit}MB`,
      genericErrorMessage: "Errore caricamento file, si prega di riprovare"
    };
  },
  computed: {
    isDisabled: () => false
  },
  methods: {
    processFile(event) {
      this.loading = true;
      const form = this.$refs.formUpload;
      if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        this.hasError = true;
        return false;
      }
      const file = this.$refs.uploadInput.files[0];
      const size = this.fileSize(file);
      if (this.limit && size / 1000 > this.limit) {
        this.limitError = true;
        return false;
      }
      const endpoint = drupalSettings.m_api.endpoints.upload_file;
      const formData = new FormData();
      formData.append("file", file);
      this.$http
        .post(endpoint, formData)
        .then(res => {
          this.loading = false;
          console.log("res");
          console.log(res);
          if (!res.data.id) {
            this.genericError = true;
            return;
          }
          this.allegato.id = res.data.id;
          const component = this;
          const data = [...this.files, Object.assign({}, this.allegato)];
          this.$emit("load", data);
          component.$refs.uploadInput.value = "";
          component.allegato.description = "";
          component.hasError = false;
          return true;
        })
        .catch(err => {
          this.loading = false;
          console.log("err");
          console.log(err);
        });
    },
    loadTextFromFile(ev) {
      const file = ev.target.files[0];
      if (!file) {
        this.hasError = false;
        this.limitError = false;
        this.genericError = false;
        return;
      }
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
      this.hasError = false;
      this.limitError = false;
      reader.readAsText(file);
    },
    fileSize(file) {
      return Math.round(file.size / 1024);
    },
    removeFile(index) {
      this.$emit("removeFile", index);
    }
  }
};
</script>
