<template>
  <div class="segnalame-card">
    <div class="segnalame-card__header">
      <strong>{{ segnalazione.title }}</strong>
      <br />
      <span>{{ segnalazione.sector.name }}</span>
    </div>
    <div class="segnalame-card__body">
      <div class="segnalame-card__status d-flex justify-content-between">
        <div class="">
          <span>STATO SEGNALAZIONE</span>
          <br />
          <strong>{{ statusLabel }}</strong>
        </div>
        <div class="">
          <img
            :src="
              `/themes/custom/portalemessina/dist/images/icons/segnala-me/icon-status-id-${segnalazione.status.id}.svg`
            "
          />
        </div>
      </div>
      <div class="segnalame-card__details row">
        <div class="segnalame-card__detail col-12">
          <span>SOTTOCATEGORIA</span>
          <br />
          <strong>{{ segnalazione.subSector.name }}</strong>
        </div>
        <div class="segnalame-card__detail col-12" v-if="segnalazione.type">
          <span>TIPOLOGIA SEGNALAZIONE</span>
          <br />
          <strong>{{ segnalazione.type }}</strong>
        </div>
        <div class="segnalame-card__detail col-6">
          <span>DATA SEGNALAZIONE</span>
          <br />
          <strong>{{ parseDate() }}</strong>
        </div>
        <div class="segnalame-card__detail col-6">
          <span>ID SEGNALAZIONE</span>
          <br />
          <strong>{{ segnalazione.id }}</strong>
        </div>
        <div class="segnalame-card__detail col-12" v-if="segnalazione.address">
          <span>INDIRIZZO</span>
          <br />
          <strong>{{ segnalazione.address }}</strong>
        </div>
        <div class="segnalame-card__link col-12">
          <a :href="detailUrl"
            ><strong>Vai al dettaglio <sub> ⃗</sub></strong>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SegnalameCard",
  props: {
    detailUrl: {
      type: String,
      required: true
    },
    segnalazione: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      statusLabel:
        this.segnalazione.status.id === 6 ? "Chiusa" : this.segnalazione.status.value,
      months: [
        "gennaio",
        "febbraio",
        "marzo",
        "aprile",
        "maggio",
        "giugno",
        "luglio",
        "agosto",
        "settembre",
        "ottobre",
        "novembre",
        "dicembre"
      ]
    };
  },
  methods: {
    fixZero(value) {
      return value < 10 ? `0${value}` : value;
    },
    parseDate() {
      const date = new Date(this.segnalazione.insertTs);
      const day = this.fixZero(date.getDate());
      const month = this.months[date.getMonth()];
      const year = date.getFullYear();
      return `${day} ${month} ${year}`;
    }
  }
};
</script>
