<template>
  <div
    v-if="sala != null"
    class="specifiche"
  >
    <h3>Specifiche della sala</h3>
    <div class="row">
      <form-item
        id="capienza"
        label="Capienza"
        :value="capienza"
      />
      <form-item
        id="tipoStruttura"
        label="Tipologia della struttura"
        :value="tipoStruttura"
      />
      <form-item
        id="catering"
        label="Servizio catering"
        :value="isAllowed(sala.catering)"
      />
      <form-item
        id="terzeParti"
        label="Strutture tecniche terze parti"
        :value="isAllowed(sala.terzeParti)"
      />
    </div>
  </div>
</template>

<script>

import FormItem from './ui/Item.vue';

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
  computed: {
    tipoStruttura: function () {
      const tipi = drupalSettings.m_api.tipiStrutture;
      if (Object.prototype.hasOwnProperty.call(tipi, this.sala.tipoStruttura)) {
        return tipi[this.sala.tipoStruttura];
      }
      return '';
    },
    capienza: function () {
      if (this.sala) {
        return this.sala.capienza + " persone";
      }
      return null;
    }
  },
  methods: {
    isAllowed: function (value) {
      return (value) ? 'Consentito' : 'Non consentito';
    }
  }
};
</script>
