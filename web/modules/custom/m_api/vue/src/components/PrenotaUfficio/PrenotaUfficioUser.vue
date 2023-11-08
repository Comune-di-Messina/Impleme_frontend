<template>
  <div class="page-prenota-ufficio-form__user__form row my-5">
    <div class="col-12 col-lg-4">
      <pagome-input
        id="prenotazioneUfficioUserPhone"
        v-model="model.telNumber"
        label="Contatto telefonico *"
        placeholder="Inserisci il numero di telefono"
        error="Il campo telefono è obbligatorio"
        @input="updateModel"
        :required="true"
        :disabled="false"
      />
    </div>
    <div class="col-12 col-lg-4">
      <pagome-input
        id="prenotazioneUfficioUserEmail"
        v-model="model.email"
        label="Email *"
        placeholder="Inserisci l'indirizzo email"
        error="Il campo email è obbligatorio"
        @input="updateModel"
        :required="true"
        :disabled="false"
      />
    </div>
    <div class="col-12 col-lg-8">
      <pagome-textarea
        id="prenotazioneUfficioUserDescription"
        v-model="model.description"
        :label="`Descrizione${descriptionRequired ?' *' : ''}`"
        :placeholder="descriptionPlaceholder"
        error="Il campo email è obbligatorio"
        @input="updateModel"
        :required="descriptionRequired"
        :disabled="false"
      />
    </div>
    <div class="container container-privacy">
      <h5>Privacy *</h5>
      <div class="row justify-content-between">
        <div class="col-12 col-lg-5 privacy-item form-check">
          <input
            id="privacy-1"
            v-model="model.privacy"
            type="checkbox"
            @input="updateModel"
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
  </div>
</template>

<script>
import PagomeInput from "../form/PagomeInput.vue";
import PagomeTextarea from "../form/PagomeTextarea.vue";

export default {
  name: "PrenotaUfficioUser",
  components: {
    PagomeInput,
    PagomeTextarea
  },
  props: {
    userData: {
      type: Object,
      required: false
    },
    descriptionPlaceholder: {
      type: String,
      required: false,
      default: "Inserisci la descrizione"
    },
    descriptionRequired: {
      type: Boolean,
      required: false,
      default: false
    }
  },
  data() {
    return {
      model: {
        ...this.userData,
        privacy: false
      },
      hasPrivacyError: false
    };
  },
  methods: {
    updateModel() {
      return this.$emit("onModelChange", this.model);
    }
  }
};
</script>
