<template>
  <div v-if="sala != null" class="sezione-tariffe">
    <div v-for="tariffa in sala.tariffario" :key="tariffa.id" class="tariffa">
      <h3>Tariffe {{ titoloEvento(tariffa.id) }}</h3>
      <div class="row">
        <form-item
          id="costoOrario"
          label="Quota oraria"
          :value="tariffa.costoOrario | format_number"
          v-if="tariffa.costoOrario || tariffa.costoOrario === 0"
          suffix=" €"
        />
        <form-item
          id="costoInteraGiornata"
          label="Giornata intera"
          :value="tariffa.costoInteraGiornata | format_number"
          v-if="tariffa.flagInteraGiornata"
          suffix=" €"
        />
        <form-item
          id="costoSettimanale"
          label="Settimana intera"
          :value="tariffa.costoSettimanale | format_number"
          v-if="tariffa.flagInteraSettimana"
          suffix=" €"
        />
      </div>
      <!-- TODO: costoFasce -->
    </div>
  </div>
</template>

<script>
import FormItem from "./ui/Item.vue";

export default {
  components: {
    FormItem
  },
  props: {
    sala: {
      type: Object,
      default: null
    }
  },
  computed: {},
  methods: {
    evento: function(value) {
      return this.sala.eventi.find(item => item.id == value);
    },
    titoloEvento: function(value) {
      const evento = this.evento(value);
      if (evento) {
        return evento.evento;
      }
      return "";
    }
  }
};
</script>
