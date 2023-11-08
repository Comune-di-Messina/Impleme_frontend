<template>
  <div
    v-if="documenti.length > 0"
    class="container-servizi container-servizi--documenti"
  >
    <h3>
      Doumenti disponibili
    </h3>
    <div class="list-servizi">
      <div class="row">
        <div
          v-for="(documento) in documenti"
          :key="documento.id"
          class="form-check col-6 col-lg-4"
        >
          <a :href="documento.url" download>
            <div
              class="form-check-wrapper hide-check"
              :class="'documento-' + documento.id"
            >
              <div class="icon-file-wrapper">
                <svg class="icon icon-lg">
                  <use xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-clip"></use>
                </svg>
                <p class="filename">
                  <strong class="form-label">{{ documento.linkText }}</strong>
                  <small v-if="documento.body.length > 0" class="form-text">{{ documento.body[0].value }} </small>
                </p>
              </div>
            </div>
          </a>
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
    documenti: {
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
  },
  methods: {
    id: function (id) {
      return 'documento-' + id;
    }
  }
};
</script>
