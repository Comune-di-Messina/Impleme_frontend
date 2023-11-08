<template>
  <div class="container-privacy">
    <h5>Privacy *</h5>
    <div class="row justify-content-between">
      <div class="col-12 privacy-item form-check">
        <input
          id="privacy-1"
          v-model="praticaRef.flagPrivacy1"
          type="checkbox"
          required
        />
        <label for="privacy-1">
          Dichiaro di aver letto l'informativa sulla privacy, autorizzo al
          trattamento dei miei dati personali *
        </label>
      </div>
      <div class="col-12 privacy-item form-check">
        <input
          id="privacy-2"
          v-model="praticaRef.flagPrivacy2"
          type="checkbox"
          required
        />
        <label for="privacy-2">
          Dichiaro che lo statuto del soggetto richiedente o altro atto
          analogo non è in contrasto coni principi fondamentali della
          Costituzione, della legge, dell'ordine pubblico e dello Statuto
          Comunale *
        </label>
      </div>
      <div class="col-12 privacy-item form-check">
        <input
          id="privacy-3"
          v-model="praticaRef.flagPrivacy3"
          type="checkbox"
          required
        />
        <label for="privacy-3">
          Confermo, consapevole della responsabilità penale in caso di
          dichiarazioni non veritiere ai sensi dell'art 76 del DPR 445/2000
          e ss.mm.ii., la veridicità di quanto indicato nella presente
          domanda e nei relativi allegati *
        </label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CasefilesPrivacy",
  props: {
    pratica: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      praticaRef: this.pratica
    };
  }
};
</script>

<style scoped>

</style>
