<template>
  <div class="modal fade" tabindex="-1" role="dialog" id="modal-pagamento_iuv" aria-labelledby="modal-pagamento_iuv-title" data-user="pagamento_url_paga" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg rounded" role="document" >
      <div class="modal-content container">
        <div class="row">
          <div class="p-4" :class="{'col-8': !loading, 'col-12': loading}">
            <h3 class="m-0 col-header mb-3" id="modal-pagamento_url_paga-title">Riepilogo pagamento</h3>
            <div class="text-align-center mt-5" v-if="loading">
              <spinner />
            </div>
            <div class="mt-5" v-else-if="hasErrors">
              <ul>
                <li v-for="error in errors">
                  {{ error}}
                </li>
              </ul>

            </div>
            <div class="row" v-else>
              <div class="col">
                <div class="dati-label text-uppercase">Codice Fiscale</div>
                <div class="dati-content">{{ pagamentoResponse.codiceFiscale }}</div>
              </div>
              <div class="col">
                <div class="dati-label text-uppercase">Codice IUV</div>
                <div class="dati-content">{{ pagamentoResponse.iuv }}</div>
              </div>
              <div class="col">
                <div class="dati-label text-uppercase">Codice Servizio</div>
                <div class="dati-content">{{ pagamentoResponse.tributo }}</div>
              </div>
            </div>
            <div class="mt-2">
              <button class="btn btn-inline mt-5" type="button" data-dismiss="modal" aria-label="Torna indietro">
                <svg class="icon icon-sm">
                  <use :xlink:href="getDistUrl('it-arrow-left-circle')"></use>
                </svg>
                Torna indietro
              </button>
            </div>
          </div>
          <div class="secondary-bg-light col-4 p-4" v-if="!loading && !hasErrors">
            <div class="col-header mb-3">
              <svg class="icon icon-white">
                <use :xlink:href="getDistUrl('it-card')"></use>
              </svg>
            </div>
            <div class="dati-label text-uppercase">Totale importo</div>
            <div class="dati-content">{{ pagamentoResponse.importo|format_number(2, ',', '.') }} €</div>
            <div class="mt-5">
              <a class="btn btn-primary pagopa btn-xs btn-icon" :href="pagamentoResponse.url" v-if="pagamentoResponse.url">
                <svg class="icon icon-white mr-2">
                  <use :xlink:href="getDistUrl('it-file')"></use>
                </svg>
                Paga
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from './Spinner.vue';

export default {
  components: {
    Spinner
  },
  props: {
    pagamentoResponse: {
      type: Object
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    getDistUrl: function(icon) {
      return '/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#' + icon
    }
  },
  computed: {
    loading: function() {
      return this.pagamentoResponse == null;
    },
    hasErrors: function() {
      return Object.keys(this.errors).length > 0;
    }
  }
}
</script>
