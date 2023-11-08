<template>
  <div class="casefile module card card-dati card-pagamento no-after p-0 rounded shadow">
    <div class="card-header secondary-bg p-4">
      <h3 class="card-title mb-2 mt-0">
        {{ pratica.tipologia }}
      </h3>
    </div>
    <div class="card-content">
      <div class="card-content-main p-2">
        <div class="w-100 row">
          <div class="dati-block col-6">
            <div class="dati-label text-uppercase">
              Ente
            </div>
            <div class="dati-content text-truncate">
              {{ pratica.ente }}
            </div>
          </div>
          <div class="dati-block col-6">
            <div class="dati-label text-uppercase">
              Causale
            </div>
            <div class="dati-content text-truncate">
              {{ pratica.causale }}
            </div>
          </div>
        </div>
        <div class="w-100 row">
          <div class="dati-block col-8">
            <div class="dati-label text-uppercase">
              Note
            </div>
            <div class="dati-content text-truncate">
              {{ pratica.note }}
            </div>
          </div>
        </div>
        <div class="w-100 row">
          <div class="dati-block col-4">
            <div class="dati-label text-uppercase">
              Importo
            </div>
            <div class="dati-content">
              {{ pratica.importo }} €
            </div>
          </div>
          <div class="dati-block col-4">
            <div class="dati-label text-uppercase">
              Allegati
            </div>
            <div class="dati-content">
              {{ pratica.attachments.length }}
            </div>
          </div>
          <div class="dati-block col-4">
            <div class="dati-label text-uppercase">
              Stato
            </div>
            <div class="dati-content">
              {{ pratica.state.stato }}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <a :href="`/servizi/pratiche/${pratica.idCaseFile}`">Vai al dettaglio &#8594;</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    pratica: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      detailsUrl: ''
    };
  }
};
</script>

<style scoped>

</style>
