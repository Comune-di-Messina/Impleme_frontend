<template>
  <div
    v-if="servizi.length > 0"
    class="container-servizi"
  >
    <h3
      v-if="authenticated"
    >
      Seleziona i servizi
    </h3>
    <h3 v-else>
      Servizi disponibili
    </h3>
    <div class="list-servizi">
      <div class="row">
        <div
          v-for="(servizio) in servizi"
          :key="servizio.id"
          class="form-check col-6 col-lg-4"
          :class="{'h-check-display': !authenticated}"
        >
          <div
            class="form-check-wrapper"
            :class="'servizio-' + servizio.id"
          >
            <input
              :id="id(servizio.id)"
              v-model="options"
              type="checkbox"
              :value="servizio.id"
            >
            <label :for="id(servizio.id)">
              <strong class="form-label">{{ servizio.descrizione }}</strong>
              <small class="form-text">{{ servizio.importo|format_number }} €/h </small>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  components: {

  },
  props: {
    servizi: {
      type: Array,
      default: () => []
    },
    authenticated: {
      type: Boolean,
      default: false
    }
  },
  data: function() {
    return {
      options: []
    };
  },
  watch: {
    options: function () {
      const values = this.servizi.filter(item => jQuery.inArray(item.id ,this.options) > -1);
      this.$emit('input', values);
    }
  },
  methods: {
    id: function (id) {
      return 'servizio-' + id;
    }
  }
};
</script>
