<template>
  <div class="container">
    <div v-if="!isSubmitted">
      <div class="row">
        <div class="col-12 mt-4 mb-3">
          <h3>Compila il form</h3>
        </div>
      </div>
      <form
        id="praticaForm"
        ref="praticaForm"
        novalidate="novalidate"
      >
        <!-- Tipologia pratica -->
        <div class="row">
          <div class="col-12">
            <pagome-select
              id="tipologiaPratica"
              v-model="pratica.tipologia"
              label="Tipologia pratica *"
              :required="true"
              error="Il campo tipologia è obbligatorio."
              :options="types"
              :show-error="validationErrors.tipologia"
              @input="changeType($event)"
            />
          </div>
        </div>
        <!-- Richiedente -->
        <casefiles-richiedente
          :richiedente="pratica.richiedente"
          :disabled-fields="disabledFields"
          :errors="validationErrors.richiedente"
        />
        <!-- Beneficiario -->
        <casefiles-beneficiario
          :beneficiario="pratica.fruitore"
          :show-fields="pratica.richiedente.flagFruitore"
          :errors="validationErrors.beneficiario"
          @showFields="handleOtherBeneficiary()"
        />
        <!-- Allegati -->
        <casefiles-allegati
          :attachments="pratica.attachments"
          @load="pratica.attachments = $event"
          @removeFile="removeFile($event)"
        />
        <!-- Testo libero -->
        <div class="row mb-5" v-if="showNoteField">
          <div class="col-12 p-0">
            <fieldset class="fieldset-step">
              <legend class="d-flex justify-content-between align-items-center mb-0">
                <div>
                  <span class="fieldset-step-num">4</span>
                  {{ note.placeholder }}
                </div>
              </legend>
              <casefiles-textarea
                id="praticaLibero"
                v-model="pratica.libero"
                :required="false"
                :placeholder="note.label"
                error="Il campo note non è valido"
                :show-error="validationErrors.libero"
                @input="validationErrors.libero = false"
              />
            </fieldset>
          </div>
        </div>
        <!-- Privacy -->
        <casefiles-privacy :pratica="pratica" />
        <!-- Submit -->
        <div class="row mt-5">
          <div class="col-12 text-center">
            <input
              type="button"
              class="btn btn-outline-success"
              value="Invia la richiesta"
              @click="handleSubmit($event)"
            >
          </div>
        </div>
      </form>
    </div>
    <div class="text-center mt-4" v-if="isLoading && !isSubmitted">
      <spinner />
    </div>
    <div v-if="!isLoading && isSubmitted">
      <div class="row text-center mt-5">
        <div class="col-12 mb-4">
          <h1>VAI AL PAGAMENTO</h1>
        </div>
        <div class="col-12">
          <pagopa-button :url="paymentUrl" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PagomeSelect from "./components/form/PagomeSelect.vue";
import praticaModel from "./data/pratica";
import CasefilesRichiedente from "./components/Casefiles/CasefilesRichiedente.vue";
import CasefilesBeneficiario from "./components/Casefiles/CasefilesBeneficiario.vue";
import CasefilesAllegati from "./components/Casefiles/CasefilesAllegati.vue";
import CasefilesPrivacy from "./components/Casefiles/CasefilesPrivacy.vue";
import Spinner from "./components/ui/Spinner.vue";
import PagopaButton from "./components/form/PagopaButton.vue";
import CasefilesTextarea from "./components/Casefiles/CasefilesTextarea.vue";

export default {
  components: {
    CasefilesTextarea,
    PagopaButton,
    Spinner,
    CasefilesPrivacy,
    CasefilesAllegati,
    CasefilesRichiedente,
    CasefilesBeneficiario,
    PagomeSelect,
  },
  data() {
    return {
      pratica: praticaModel,
      types: [],
      validationErrors: {
        tipologia: false,
        libero: false,
        richiedente: {
          codiFisc: false,
          email: false,
          indirizzo: {
            comune: false,
            indirizzo: false,
            provincia: false,
            cap: false,
            civico: false,
            stato: false,
          },
          name: false,
          surname: false,
          telephoneNumber: false
        },
        beneficiario: {
          codiFisc: false,
          email: false,
          indirizzo: {
            comune: false,
            indirizzo: false,
            provincia: false,
            cap: false,
            civico: false,
            stato: false
          },
          name: false,
          surname: false,
          telephoneNumber: false
        }
      },
      disabledFields: [],
      isLoading: false,
      isSubmitted: false,
      paymentUrl: null,
      note: {
        label: null,
        placeholder: null
      },
      showNoteField: false
    };
  },
  watch: {
    'pratica.tipologia': function (type) {
      this.casefileHasNote(type);
      this.updateNoteState();
    }
  },
  created() {
    this.getTypes();
    this.getUserInfo();
  },
  mounted() {
    console.log(drupalSettings.m_api);
    this.getTypeParam();
  },
  methods: {
    getTypes() {
      drupalSettings.m_api.tipologie.forEach(t => {
        const option = {text: t.nome, value: t.id};
        if ('note' in t) {
          option.note = t.note;
        }
        if ('libero' in t) {
          option.libero = t.libero;
        }
        this.types.push(option);
      });
    },
    getUserInfo() {
      const richiedente = this.pratica.richiedente;
      richiedente.name = drupalSettings.m_api.userinfo.given_name;
      richiedente.surname = drupalSettings.m_api.userinfo.family_name;
      richiedente.email = drupalSettings.m_api.userinfo.email;
      richiedente.indirizzo.indirizzo = drupalSettings.m_api.userinfo.address.address;
      richiedente.codiFisc = drupalSettings.m_api.userinfo.cf;
      this.disabledFields.push('name');
      this.disabledFields.push('surname');
      this.disabledFields.push('email');
      this.disabledFields.push('indirizzo');
      this.disabledFields.push('codiFisc');
    },
    changeType(e) {
      this.pratica.tipologia = Number(e);
      this.validationErrors.tipologia = false;
      // this.casefileHasNote(e);
      // this.updateNoteState();
    },
    handleOtherBeneficiary() {
      this.pratica.richiedente.flagFruitore = !this.pratica.richiedente.flagFruitore;
      this.resetErrors(this.validationErrors.beneficiario);
    },
    resetErrors(obj) {
      obj.codiFisc = false;
      obj.email = false;
      obj.indirizzo.indirizzo = false;
      obj.indirizzo.comune = false;
      obj.indirizzo.provincia = false;
      obj.indirizzo.cap = false;
      obj.indirizzo.civico = false;
      obj.indirizzo.stato = false;
      obj.name = false;
      obj.surname = false;
      obj.telephoneNumber = false;
    },
    hasErrors(obj) {
      const e = [];
      for (const k in obj) {
        if (typeof obj[k] === 'object') {
          const hasErrors = this.hasErrors(obj[k]);
          e.push(hasErrors);
        } else {
          e.push(obj[k]);
        }
      }
      return e.includes(true);
    },
    validateField(field) {
      return field.checkValidity();
    },
    validateRichiedente() {
      const form = this.$refs.praticaForm;
      const errors = this.validationErrors.richiedente;
      // TODO: da fare refactoring
      if (!this.validateField(form.richiedenteNome)) {
        errors.name = true;
      }
      if (!this.validateField(form.richiedenteCognome)) {
        errors.surname = true;
      }
      if (!this.validateField(form.richiedenteEmail)) {
        errors.email = true;
      }
      if (!this.validateField(form.richiedenteCodiFisc)) {
        errors.codiFisc = true;
      }
      if (!this.validateField(form.richiedenteTelephone)) {
        errors.telephoneNumber = true;
      }
      if (!this.validateField(form.richiedenteIndirizzo)) {
        errors.indirizzo.indirizzo = true;
      }
      if (!this.validateField(form.richiedenteCap)) {
        errors.indirizzo.cap = true;
      }
      if (!this.validateField(form.richiedenteComune)) {
        errors.indirizzo.comune = true;
      }
      if (!this.validateField(form.richiedenteProvincia)) {
        errors.indirizzo.provincia = true;
      }
      if (!this.validateField(form.richiedenteCivico)) {
        errors.indirizzo.civico = true;
      }
      // if (!this.validateField(form.richiedenteStato)) {
      //   errors.indirizzo.stato = true;
      // }

      return this.hasErrors(errors);
    },
    validateBeneficiario() {
      const form = this.$refs.praticaForm;
      const errors = this.validationErrors.beneficiario;
      // TODO: da fare refactoring
      if (!this.validateField(form.beneficiarioNome)) {
        errors.name = true;
      }
      if (!this.validateField(form.beneficiarioCognome)) {
        errors.surname = true;
      }
      if (!this.validateField(form.beneficiarioEmail)) {
        errors.email = true;
      }
      if (!this.validateField(form.beneficiarioCodiFisc)) {
        errors.codiFisc = true;
      }
      if (!this.validateField(form.beneficiarioTelephone)) {
        errors.telephoneNumber = true;
      }
      if (!this.validateField(form.beneficiarioIndirizzo)) {
        errors.indirizzo.indirizzo = true;
      }
      if (!this.validateField(form.beneficiarioCap)) {
        errors.indirizzo.cap = true;
      }
      if (!this.validateField(form.beneficiarioComune)) {
        errors.indirizzo.comune = true;
      }
      if (!this.validateField(form.beneficiarioProvincia)) {
        errors.indirizzo.provincia = true;
      }
      if (!this.validateField(form.beneficiarioCivico)) {
        errors.indirizzo.civico = true;
      }
      // if (!this.validateField(form.beneficiarioStato)) {
      //   errors.indirizzo.stato = true;
      // }

      return this.hasErrors(errors);
    },
    validateForm() {
      // Validate type
      if (!this.pratica.tipologia) {
        this.validationErrors.tipologia = true;
      }

      // Causale
      if (this.pratica.libero.length === 0) {
        this.validationErrors.libero = true;
      }

      // Validate richiedente
      const richiedenteErrors = this.validateRichiedente();
      // Validate beneficiario
      let beneficiarioErrors;
      if (this.pratica.richiedente.flagFruitore) {
        beneficiarioErrors = this.validateBeneficiario();
      } else {
        this.pratica.fruitore = this.pratica.richiedente;
      }

      if (this.validationErrors.tipologia
        || richiedenteErrors
        || beneficiarioErrors
      ) {
        this.scrollToError();
        return false;
      }

      return true;
    },
    scrollToError() {
      const errorElement = document.querySelector('.form-control:invalid');
      const coords = errorElement.getBoundingClientRect();
      const top = window.scrollY + coords.top - 200;
      window.scrollTo({ behavior: 'smooth', left: 0, top: top });
    },
    async handleSubmit(e) {
      e.preventDefault();
      e.stopPropagation();
      const formIsValid = this.validateForm();
      if (formIsValid) {
        this.isLoading = true;
        this.transformCasefile();
        try {
          // New pratica
          const creaPraticaEndpoint = '/servizi/casefiles/pratiche/crea';
          const {data} = await this.$http.post(creaPraticaEndpoint, this.pratica);
          // Get payment url
          const params = {
            iuv: data.iuv,
            tributo: data.tributo,
            cod_fiscale: data.codiceFiscale,
            importo: data.importo
          };
          const paymentEndpoint = `/servizi/messina/pagamento/iuv?${this.buildQueryString(params)}`;
          this.paymentUrl = (await this.$http.get(paymentEndpoint)).data;
          this.isSubmitted = true;
        } finally {
          window.scrollTo({top: 0, behavior: 'smooth'});
          this.isLoading = false;
        }
      }
    },
    buildQueryString(paramsObj) {
      let url = '';
      let count = 1;
      const length = Object.keys(paramsObj).length;
      for (const k in paramsObj) {
        const separator = '&';
        url += `${k}=${paramsObj[k]}${count < length ? separator : ''}`;
        count++;
      }
      return url;
    },
    resetFormState() {
      this.isLoading = false;
      this.isSubmitted = false;
      this.paymentUrl = null;
    },
    removeFile(index) {
      this.pratica.attachments.splice(index, 1);
    },
    casefileHasNote(id) {
      const type = this.types.filter(t => Number(t.value) === Number(id))[0];
      if ('note' in type) {
        this.note.label = type.note;
      } else {
        this.note.label = null;
      }
      if ('libero' in type) {
        this.note.placeholder = type.libero;
      } else {
        this.note.placeholder = null;
      }
    },
    updateNoteState() {
      this.showNoteField = this.note.placeholder && this.note.label;
    },
    getTypeParam() {
      const query = window.location.search;
      const params = new URLSearchParams(query);
      const type = Number(params.get('type'));
      if (type) {
        this.pratica.tipologia = type;
        const typeSelect = jQuery('#tipologiaPratica');
        typeSelect.selectpicker('val', type);
        typeSelect.selectpicker('refresh');
      }
    },
    transformCasefile() {
      this.pratica.richiedente.indirizzo.provincia = this.pratica.richiedente.indirizzo.provincia.toUpperCase();
      this.pratica.fruitore.indirizzo.provincia = this.pratica.fruitore.indirizzo.provincia.toUpperCase();
    }
  }
};
</script>
