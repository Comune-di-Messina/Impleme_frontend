<template>
  <div class="container mt-5">
    <div class="row mb-3 d-flex justify-content-start align-items-end">
      <div class="col-4">
        <pagome-select
          id="filterState"
          v-model="casefilesFilters.state"
          :options="states"
          label="Filtra per stato"
          placeholder="Seleziona uno stato"
          @input="setFilter('state', $event)"
        />
      </div>
      <div class="col-4">
        <pagome-select
          id="filterType"
          v-model="casefilesFilters.type"
          :options="types"
          label="Filtra per tipologia"
          placeholder="Seleziona una tipologia"
          @input="setFilter('type', $event)"
        />
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12 col-lg-6 mb-4" v-for="pratica in filteredCasefiles" :key="pratica.idCaseFile">
        <casefiles-card :pratica="pratica"></casefiles-card>
      </div>
      <div class="col-12 py-4" v-if="filteredCasefiles.length === 0">
        <h3>Nessuna pratica trovata con i criteri selezionati</h3>
      </div>
    </div>
  </div>
</template>

<script>
import CasefilesCard from "./components/Casefiles/CasefilesCard.vue";
import PagomeSelect from "./components/form/PagomeSelect.vue";

export default {
  components: {
    PagomeSelect,
    CasefilesCard
  },
  data() {
    return {
      pratiche: drupalSettings.m_api.lista_pratiche,
      types: [],
      enti: [],
      states: [],
      casefilesFilters: {
        state: null,
        type: null
      },
    };
  },
  computed: {
    filteredCasefiles() {
      const casefilesFilters = this.casefilesFilters;
      let filtered = this.pratiche;
      if (casefilesFilters.state) {
        filtered = filtered.filter(p => Number(p.state.id) === Number(casefilesFilters.state));
      }
      if (casefilesFilters.type) {
        filtered = filtered.filter(p => p.tipologia === casefilesFilters.type);
      }
      return filtered;
    }
  },
  watch: {
    enti() {
      this.replaceEnte();
    },
  },
  created() {
    this.getEnti();
    this.getTypes();
    this.getStates();
  },
  methods: {
    async getStates() {
      const endpoint = '/servizi/casefiles/pratiche/stati';
      const {data:states} = await this.$http.get(endpoint);
      const statesToHide = ['Annullata', 'Revocata', 'Inserita'];
      states.forEach(s => {
        if (!statesToHide.includes(s.stato)) {
          const newState = this.transformState(s);
          this.states.push(newState);
        }
      });
    },
    getTypes() {
      drupalSettings.m_api.tipologie.forEach(t => {
        const option = {text: t.nome, value: t.nome};
        this.types.push(option);
      });
      return this.types;
    },
    getEnti() {
      const endpoint = '/servizi/enti/lista';
      this.$http.get(endpoint).then(r => {
        this.enti = r.data;
      });
    },
    replaceEnte() {
      this.pratiche = this.pratiche.map(p => {
        p.ente = this.getEnte(p.ente).descrizione;
        return p;
      });
    },
    getEnte(code) {
      return this.enti.filter(e => e.codice === code)[0];
    },
    setFilter(filter, value) {
      this.casefilesFilters[filter] = Number(value) ? Number(value) : value;
    },
    transformState(state) {
      return {text: state.stato, value: state.id};
    }
  }
};
</script>
