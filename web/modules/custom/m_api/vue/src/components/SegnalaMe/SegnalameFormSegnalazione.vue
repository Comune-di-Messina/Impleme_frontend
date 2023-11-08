<template>
  <fieldset class="fieldset-step my-5">
    <legend><span class="fieldset-step-num">1</span> DATI SEGNALAZIONE</legend>
    <div class="fieldset-content">
      <div class="row my-2">
        <div class="col-6">
          <pagome-select
            id="segnalazioneArea"
            v-model="segnalazione.area"
            label="Sottocategoria intervento *"
            placeholder="Scegli l'area di intervento"
            :required="true"
            error="Il campo sottocategoria è obbligatorio."
            :options="optionsAree"
            :show-error="areaError"
          />
        </div>
        <div class="col-6">
          <pagome-select
            id="segnalazioneTipologia"
            v-model="segnalazione.type"
            label="Tipologia"
            placeholder="Scegli la tipologia"
            :required="false"
            error="Il campo tipologia è obbligatorio."
            :show-error="false"
            :options="optionsTipologie"
          />
        </div>
      </div>
      <div class="row my-2">
        <div class="col-6">
          <pagome-input
            id="segnalazioneTitolo"
            v-model="segnalazione.title"
            label="Titolo *"
            placeholder="Inserisci il titolo"
            error="Il campo titolo è obbligatorio"
            :required="true"
            :disabled="false"
          />
        </div>
        <div class="col-6">
          <pagome-input
            id="segnalazioneDescrizione"
            v-model="segnalazione.description"
            label="Descrizione della segnalazione *"
            placeholder="Digita qui il testo"
            error="Il campo descrizione è obbligatorio"
            :required="true"
            :disabled="false"
          />
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script>
import PagomeInput from "../form/PagomeInput.vue";
import PagomeSelect from "../form/PagomeSelect.vue";

export default {
  name: "SegnalameFormSegnalazione",
  components: {
    PagomeInput,
    PagomeSelect
  },
  props: {
    segnalazione: {
      required: true,
      type: Object
    },
    areaError: {
      required: true,
      type: Boolean
    }
  },
  mounted() {
    if (typeof drupalSettings.m_api.sub_sectors !== "undefined")
      this.optionsAree = [
        ...this.optionsAree,
        ...drupalSettings.m_api.sub_sectors
      ];
    else this.optionsAree = [];
  },
  data() {
    return {
      optionsAree: [
        {
          value: null,
          text: "Scegli la sottocategoria di intervento"
        }
      ],
      optionsTipologie: [
        {
          value: null,
          text: "Scegli la tipologia"
        },
        {
          value: "Guasto",
          text: "Guasto"
        },
        {
          value: "Disservizio",
          text: "Disservizio"
        },
        {
          value: "Criticità",
          text: "Criticità"
        }
      ]
    };
  }
};
</script>
