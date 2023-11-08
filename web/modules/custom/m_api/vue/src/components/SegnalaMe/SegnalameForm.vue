<template>
  <div class="segnalame-form">
    <div class="container">
      <h3>Compila il form</h3>
      <div class="container container-form">
        <segnalame-form-segnalazione
          :segnalazione="segnalazione"
          :area-error="areaError"
        ></segnalame-form-segnalazione>
      </div>
    </div>
  </div>
</template>

<script>
import SegnalameFormSegnalazione from "./SegnalameFormSegnalazione.vue";
export default {
  name: "SegnalameForm.vue",
  components: {
    SegnalameFormSegnalazione
  },
  props: {
    segnalazione: {
      type: Object,
      required: true
    },
    areaError: {
      required: true,
      type: Boolean
    }
  }
};
</script>
