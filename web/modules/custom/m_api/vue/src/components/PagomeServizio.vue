<template>
  <fieldset class="fieldset-step my-5" :class="{ 'h-disabled': isDisabled }">
    <legend><span class="fieldset-step-num">1</span> Servizio</legend>
    <div class="fieldset-content">
      <div class="row my-2">
        <div class="col-6 col-lg-4 my-2">
          <pagome-select
            id="servizioAnno"
            v-model="anno"
            label="Anno di riferimento *"
            :options="yearOptions"
          />
        </div>
        <div class="col-6 col-lg-4 my-2">
          <pagome-select
            id="servizioTipo"
            v-model="pagamento.tributo"
            label="Tipo del servizio *"
            :options="optionsServizi"
            @input="resetTariffa()"
          />
        </div>
      </div>
      <div class="row my-2">
        <div class="col my-2">
          <pagome-select
            id="servizioTariffa"
            v-model="pagamento.tariffa"
            label="Tariffa *"
            :options="optionsTariffe"
          />
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script>
import PagomeInput from "./form/PagomeInput.vue";
import PagomeTextarea from "./form/PagomeTextarea.vue";
import PagomeSelect from "./form/PagomeSelect.vue";

export default {
  components: {
    PagomeInput,
    PagomeTextarea,
    PagomeSelect,
  },
  props: {
    pagamento: {
      required: true,
      type: Object,
    },
    tariffe: {
      type: Object,
      required: true,
    },
    enabled: {
      type: Boolean,
      default: true,
    },
  },
  data: function () {
    return {
      endpoints: {
        tariffe: "/servizi/messina/pagamento/api/tariffe/{idTributo}",
      },
      loading: false,
    };
  },
  computed: {
    isDisabled: function () {
      return !this.enabled;
    },
    optionsServizi: function () {
      this.$root.$nextTick(function () {
        jQuery("#servizioTipo").selectpicker("refresh");
      });
      const servizi = [];
      const component = this;
      drupalSettings.m_api.spontaneo.servizi.forEach(function (element) {
        if (
          element.spontaneo == true &&
          element.anno == component.pagamento.anno
        ) {
          servizi.push({ text: element.NomeTributo, value: element.IDTributo });
        }
      });
      if (servizi.length) {
        servizi.unshift({
          text: "Seleziona",
          value: "",
        });
      }
      return servizi;
    },
    optionsTariffe: function () {
      this.$root.$nextTick(function () {
        jQuery("#servizioTariffa").selectpicker("refresh");
      });
      if (this.tariffe.hasOwnProperty(this.pagamento.tributo)) {
        return this.getOptionsTariffe(this.tariffe[this.pagamento.tributo]);
      } else {
        this.getTariffe();
        return this.getOptionsTariffe(this.tariffe[this.pagamento.tributo]);
      }
    },
    anno: {
      get: function () {
        return this.pagamento.anno;
      },
      set: function (value) {
        if (value != "" && Number(value) != this.pagamento.anno) {
          this.pagamento.anno = Number(value);
          this.getTariffe();
        }
      },
    },
    yearOptions: function () {
      return drupalSettings.m_api.spontaneo.anni;
    },
  },
  methods: {
    getTariffe() {
      if (this.loading || this.pagamento.tributo.length == 0) {
        return;
      }
      const endpoint = this.endpoints.tariffe.replace(
        "{idTributo}",
        this.pagamento.tributo
      );
      const component = this;
      this.loading = true;

      return this.$http
        .get(endpoint)
        .then((response) => {
          component.tariffe[component.pagamento.tributo] = response.data;
          component.loading = false;
        })
        .catch(function (error) {
          component.tariffe[component.pagamento.tributo] = [];
          component.loading = false;
          console.log(error);
        });
    },
    getOptionsTariffe: function (tariffe) {
      const options = [];
      if (Array.isArray(tariffe)) {
        tariffe.forEach((element) => {
          options.push({ value: element.id, text: element.descrizione });
        });
        if (options.length > 0) {
          options.unshift({
            text: "Seleziona",
            value: "",
          });
        }
      }
      return options;
    },
    resetTariffa: function () {
      this.pagamento.tariffa = "";
    },
  },
};
</script>
