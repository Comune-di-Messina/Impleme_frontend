<template>
  <fieldset class="fieldset-step my-5" :class="{'h-disabled': isDisabled}">
    <legend class="mb-0"><span class="fieldset-step-num">2</span> Dati pagamento</legend>
    <div class="fieldset-content">
      <div class="row">
        <div class="col-lg-8">
          <div class="row py-2">
            <div class="col-6 col-lg-5 my-2">
              <pagome-textarea
                id="datiCausale"
                :label="attributi.campo"
                :required="attributi.obbligatorio"
                :placeholder="attributi.causaleTemplate"
                :rows=1
                v-model="pagamento.causale"
                :error="errorCausale"
              />
            </div>
            <div class="col-6 col-lg-3 my-2 input-currency">
              <pagome-input
                id="datiCostoUnit"
                :label="labelUnitario"
                :value="importo"
                :disabled="!tariffa.isImportoEditable"
                type="number"
                step="0.01"
                min="0"
                @input="pagamento.importo = Number($event)"
              />
            </div>
            <div class="col-6 col-lg-3 my-2">
              <pagome-input
                id="datiQuantita"
                label="Quantità"
                type="number"
                min="1"
                step="1"
                :disabled="!tariffa.isQuantitaEditable"
                :value="pagamento.quantita"
                @input="pagamento.quantita = Number($event)"
              />
            </div>
            <div class="col-6 col-lg-12 mt-4">
              <div class="dati-label">Totale importo</div>
              <div class="dati-content">{{ prezzoTotale|format_number(2, ',', '.') }} €</div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 ml-auto secondary-bg-light p-4">
          <div class="dati-label">
            note
          </div>
          <div class="dati-content">
            {{ tariffa.note }}
          </div>
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script>
import PagomeInput from './form/PagomeInput.vue';
import PagomeTextarea from './form/PagomeTextarea.vue';

export default {
  components: {
    PagomeInput,
    PagomeTextarea
  },
  props: {
    pagamento: {
      required: true,
      type: Object
    },
    tariffa: {
      required: true,
      type: Object
    },
    servizio: {
      required: true,
      type: Object
    },
    enabled: {
      type: Boolean,
      default: false
    }
  },
  data: function () {
    return {
    }
  },
  computed: {
    isDisabled: function () {
      return !this.enabled;
    },
    prezzoTotale: function() {
      let totale = 0;
      // this.prezzi.forEach((prezzo) => {
      //   totale = totale + (prezzo.qta * prezzo.importo);
      // });
      let importo = this.tariffa.isImportoEditable ? this.pagamento.importo : this.tariffa.importoUnitario;
      if (typeof importo == 'undefined') {
        importo = 0;
      }
      totale = this.pagamento.quantita * importo;
      this.$emit('change', {importoUnitario: importo, quantita: this.pagamento.quantita});
      return totale;
    },
    attributi: function () {
      if (this.servizio.hasOwnProperty('attributi') && this.servizio.attributi.length > 0) {
        return this.servizio.attributi[0];
      }
      return {
        "campo":"Tipo del servizio",
        "tipoDato":"Testo",
        "obbligatorio":true,
        "ripetibile":false,
        "editabile":true,
        "lookup":"tab_comuni",
        "tributoId":"01",
        "ente":"SIF07",
        "jsonKey":"causale",
        "causaleTemplate":"prova %s prova"
      };
    },
    optionsQuantita: function () {
      const options = []
      this.$root.$nextTick(function() {
        jQuery('#datiQuantita').selectpicker('refresh');
      });
      if (this.tariffa.isQuantitaEditable) {
        for (var i = 1; i < 6; i++) {
          options.push({text: i, value: i});
        }
      } else {
        options.push({text: this.tariffa.quantita, value: this.tariffa.quantita});
      }
      return options;
    },
    errorCausale: function () {
      if (this.attributi.obbligatorio) {
        return 'Il campo ' + this.attributi.campo + ' è obbligatorio.';
      }
      return null;
    },
    importo: function () {
      if (this.tariffa.isImportoEditable) {
        return this.pagamento.importo;
      }
      else {
        return this.tariffa.importoUnitario;
      }
    },
    labelUnitario: function () {
      return (this.tariffa.isImportoEditable) ? 'Importo' : 'Costo unitario';
    }
  },
  watch: {
    tariffa: function () {
      this.$emit('change', {importoUnitario: this.tariffa.importoUnitario, quantita: this.tariffa.quantita});
    }
  }
};
</script>

