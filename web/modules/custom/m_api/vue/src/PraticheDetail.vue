<template>
  <div class="container">
    <div class="row">
      <div class="col-8 d-flex justify-content-center align-items-start flex-column pl-3">
        <h3>RIEPILOGO PRATICA</h3>
        <div class="row">
          <div class="col-12">
            <h4 class="m-0">ID Pratica</h4>
            <p>{{ truncateCasefileID(pratica.idCaseFile) }}</p>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="row white-color secondary-bg p-3">
          <div class="col-12 font-weight-bold p-0">
            <h5 class="mb-0">STATO PRATICA</h5>
          </div>
          <div class="col-12 p-0">
            {{ currentState.stato }}
          </div>
        </div>
        <div class="row white-color secondary-bg p-3">
          <div class="col-12 p-0">
            <div class="col-12 font-weight-bold p-0">
              <h5 class="mb-0">IMPORTO RICHIESTO</h5>
            </div>
            <div class="col-12 p-0">{{ pratica.importo }} €</div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-4 secondary-bg-light p-5">
        <div class="row">
          <div class="col-12 font-weight-bold">
            Documenti caricati
          </div>
          <div v-if="pratica.attachments" class="col-12 d-flex flex-column">
            <a v-for="(allegato, i) in pratica.attachments"
               :key="`allegato-${i}`"
               class="w-100 d-flex"
               @click="getAttachment(allegato)"
            >
              <div class="mr-1">
                <svg class="icon icon-primary icon-xs">
                  <use
                    xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-download"
                  />
                </svg>
              </div>
              <span class="text-truncate d-block">{{ allegato.fileName }}</span>
            </a>
          </div>
          <div class="col-12" v-else>
            <span>Nessun documento caricato</span>
          </div>
        </div>
      </div>
      <div class="col-8 lightgrey-bg-c2 p-5" v-if="currentState.id === 2">
        <h5>CARICAMENTO DOCUMENTI</h5>
        <p>{{ pratica.note }}</p>
        <!-- <casefiles-upload-input @upload="newAttachments = $event"/>-->
        <casefiles-allegati
          :attachments="newAttachments"
          :show-step="false"
          @load="newAttachments = $event"
          @removeFile="removeFile($event)"
        />
        <input
          type="submit"
          class="btn btn-success mt-3"
          value="Invia documentazione"
          :disabled="newAttachments.length === 0"
          @click="handleUpload($event)"
        >
        <div class="row mt-4">
          <div class="col-12" v-if="isLoading">
            <spinner />
          </div>
          <div class="col-12" v-if="isSubmitted">
            <h4>Documenti caricati con successo</h4>
          </div>
        </div>
      </div>
      <div class="col-8 lightgrey-bg-c2 p-5 d-flex justify-content-center align-items-center" v-else>
        <h2 class="text-uppercase">{{ currentState.stato }}</h2>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from "./components/ui/Spinner.vue";
import CasefilesAllegati from "./components/Casefiles/CasefilesAllegati.vue";

export default {
  components: {CasefilesAllegati, Spinner},
  data() {
    return {
      states: [],
      pratica: drupalSettings.m_api.casefile,
      currentState: drupalSettings.m_api.casefile.state,
      newAttachments: [],
      isLoading: false,
      isSubmitted: false
    };
  },
  created() {
    this.getStates();
  },
  methods: {
    async getStates() {
      const endpoint = '/servizi/casefiles/pratiche/stati';
      const {data:states} = await this.$http.get(endpoint);
      const statesToHide = ['Annullata', 'Revocata', 'Inserita'];
      this.states = states.filter(s => !statesToHide.includes(s.stato));
    },
    async getAttachment(attachment) {
      const endpoint = `/servizi/casefiles/pratiche/${this.pratica.idCaseFile}/allegato/${attachment.idDocumentale}`;
      const {data} = await this.$http.get(endpoint);
      const link = document.createElement('a');
      link.href = `data:application/pdf;base64,${data.content}`;
      link.download = attachment.fileName;
      link.click();
    },
    async handleUpload(e) {
      e.preventDefault();
      this.isLoading = true;
      const endpoint = `/servizi/casefiles/pratiche/${this.pratica.idCaseFile}/allega`;
      try {
        await this.$http.post(endpoint, this.newAttachments);
      } finally {
        this.isLoading = false;
        this.isSubmitted = true;
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      }
    },
    removeFile(index) {
      this.newAttachments.splice(index, 1);
    },
    truncateCasefileID(id) {
      let v = id.toString();
      if (v.length > 9) {
        v = v.slice(v.length - 9, v.length);
      }
      if (v.length < 9) {
        for (let index = v.length; index < 9; index++) {
          v = '0' + v;
        }
      }
      return v.slice(0, 6) + '-' + v.slice(6);
    }
  },
};
</script>
