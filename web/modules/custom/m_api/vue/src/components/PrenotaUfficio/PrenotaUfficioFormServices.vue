<template>
  <div>
    <h3>Servizi disponibili</h3>
    <div class="row my-4">
      <div
        v-for="service in services"
        class="col-12 col-lg-4 privacy-item form-check"
      >
        <input
          :id="`service-${service.id}`"
          v-model="selectedService"
          type="radio"
          :value="service.id"
        />
        <label :for="`service-${service.id}`">
          {{ service.name }}
        </label>
      </div>
    </div>
    <div class="container row d-flex justify-content-center my-5">
      <fieldset class="col-12 col-lg-8 fieldset-step">
        <legend>
          {{ currentService.labelField }}
        </legend>
      </fieldset>
      <div class="col-12 col-lg-8 my-2">
        {{ currentService.fieldNotes }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "PrenotaUfficioFormServices",
  props: {
    services: {
      type: Array,
      required: true
    },
    value: {
      type: String,
      required: false
    }
  },
  data() {
    return {
      selectedService: this.value
        ? this.services.find(service => service.id === this.value).id
        : this.services[0].id
    };
  },
  computed: {
    currentService() {
      const currentService = this.services.find(
        service => service.id === this.selectedService
      );
      this.$emit("onChange", currentService);
      return currentService;
    }
  }
};
</script>
