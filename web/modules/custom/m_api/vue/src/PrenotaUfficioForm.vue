<template>
  <div class="page-prenota-ufficio-form">
    <div v-if="!isAnonymous">
    <div v-if="!submitted">
      <div class="secondary-bg-light py-5">
        <div class="container">
          <div class="page-prenota-ufficio-form__map-wrapper row">
            <div class="col-12 col-lg-4 col-content p-4 d-flex flex-column">
              <div class="col-content-header">
                <div class="block-title pb-2">
                  {{ office.name }}
                </div>
              </div>
              <div class="col-content-main">
                <div
                  v-for="detail in officeDetails"
                  class="page-prenota-ufficio-form__map-info d-flex"
                >
                  <div class="page-prenota-ufficio-form__map-info__icon">
                    <span :class="`h-icon ${detail.icon}`"></span>
                  </div>
                  <div
                    class="page-prenota-ufficio-form__map-info__content d-flex flex-wrap"
                  >
                    <strong v-if="detail.intro">{{ detail.intro }}</strong>
                    <a v-if="detail.link" :href="detail.link" target="_blank">
                      <span>{{ detail.value }}</span>
                    </a>
                    <span v-else>{{ detail.value }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-8 p-lg-0">
              <prenota-ufficio-map
                :center="office.coordinates"
              ></prenota-ufficio-map>
            </div>
          </div>

          <div class="page-prenota-ufficio-form__services my-5">
            <prenota-ufficio-form-services
              :services="publicServices"
              :value="model.publicServiceId"
              @onChange="onChangeService"
            ></prenota-ufficio-form-services>
          </div>
        </div>
      </div>
      <div class="page-prenota-ufficio-form__gray-bg shadow py-2">
        <div class="container">
          <div class="page-prenota-ufficio-form__calendar">
            <div class="row d-flex justify-content-center">
              <div class="col-12 col-lg-10">
                <h3>Verifica la disponibilità</h3>
              </div>
              <div class="col-12 col-lg-10">
                <prenota-ufficio-calendar
                  v-if="model.publicServiceId"
                  :public-service-id="model.publicServiceId"
                  @onTimeSelect="updateTime"
                ></prenota-ufficio-calendar>
              </div>
            </div>
          </div>

          <div class="page-prenota-ufficio-form__user my-5">
            <div class="row d-flex justify-content-center">
              <div class="col-12">
                <h3>I tuoi dati</h3>
              </div>
              <div class="col-12">
                <div class="row">
                  <div v-for="info in userInfo" class="col-12 col-lg-4">
                    <div class="page-prenota-ufficio-form__user__label">
                      {{ info.label }}
                    </div>
                    <div class="page-prenota-ufficio-form__user__value">
                      {{ info.value }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="page-prenota-ufficio-form__white-bg py-2">
        <div class="container">
          <form
            novalidate="novalidate"
            class="needs-validation"
            @submit="onSubmit"
            ref="segnalazione"
            :class="{ 'was-validated': validationError }"
          >
            <prenota-ufficio-user
              :user-data="model.user"
              :descriptionPlaceholder="descriptionPlaceholder"
              :descriptionRequired="descriptionRequired"
              @onModelChange="onUserModelChange"
            ></prenota-ufficio-user>
          </form>
        </div>

        <div v-if="!submitted" class="container actions text-center p-5 my-5">
          <a
            v-if="!loading"
            :class="{ disabled: loading }"
            :disabled="loading"
            class="btn btn-outline-success"
            @click="validateData"
          >
            Invia la richiesta
          </a>
          <spinner v-if="loading" />
          <div v-if="errors.length > 0">
            <div class="alert alert-danger mt-4" role="alert">
              Errore nell'invio della richiesta:
              <ul class="errors">
                <li
                  v-for="(error, key) in errors"
                  :key="key"
                  class="error-item"
                >
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="page-segnalame page-segnalame--success">
      <div class="container">
        <fieldset class="fieldset-step my-5">
          <legend>
            {{ currentServiceType }}
          </legend>
        </fieldset>
        <h3>Grazie</h3>
        <div class="my-2">
          La tua prenotazione è avvenuta con successo. Puoi scaricare
          l'autorizzazione di accesso o visualizzarla nella casella email da te
          indicata.
        </div>
        <div class="page-segnalame__button-nav">
          <a
            class="btn btn-primary"
            :class="{ disabled: loading }"
            :disabled="loading"
            :href="downloadUrl"
            download
            target="_blank"
          >
            Scarica
            <svg class="icon icon-white icon-sm">
              <use
                xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-download"
              ></use>
            </svg>
          </a>
          <a
            class="btn btn-primary"
            :class="{ disabled: loading }"
            :disabled="loading"
            :href="mainUrl"
          >
            Continua a navigare
          </a>
          <a
            class="btn btn-primary"
            :class="{ disabled: loading }"
            :disabled="loading"
            :href="reservationsListUrl"
          >
            Appuntamenti
          </a>
        </div>
      </div>
    </div>
    </div>
      <div v-else class="prenota-cta" v-html="openIdConnect"></div>
  </div>
</template>

<script>
import PrenotaUfficioMap from "./components/PrenotaUfficio/PrenotaUfficioMap.vue";
import PrenotaUfficioFormServices from "./components/PrenotaUfficio/PrenotaUfficioFormServices.vue";
import PrenotaUfficioCalendar from "./components/PrenotaUfficio/PrenotaUfficioCalendar.vue";
import PrenotaUfficioUser from "./components/PrenotaUfficio/PrenotaUfficioUser.vue";
import Spinner from "./components/ui/Spinner.vue";

export default {
  components: {
    PrenotaUfficioUser,
    PrenotaUfficioCalendar,
    PrenotaUfficioFormServices,
    PrenotaUfficioMap,
    Spinner
  },
  data() {
    return {
      errors: [],
      loading: false,
      submitted: false,
      validationError: false,
      isAnonymous: drupalSettings.m_api.data.isAnonymous,
      openIdConnect: drupalSettings.m_api.data.openIdConnect,
      currentServiceType:
        drupalSettings.m_api.data.currentServiceType.name || "",
      serviceTypes: drupalSettings.m_api.data.serviceTypes,
      publicServices: drupalSettings.m_api.data.publicServices,
      office: drupalSettings.m_api.data.office,
      user: drupalSettings.m_api.data.userinfo,
      downloadUrl: drupalSettings.m_api.routes.downloadUrl,
      mainUrl: drupalSettings.m_api.routes.mainUrl,
      reservationsListUrl: drupalSettings.m_api.routes.reservationsListUrl,
      model: {
        date: null,
        municipalityId: drupalSettings.m_api.data.municipalityId,
        officeId: drupalSettings.m_api.data.officeId,
        timeslotId: "",
        publicServiceId:
          drupalSettings.m_api.data.currentPublicService.id || null,
        user: {
          name: drupalSettings.m_api.data.userinfo.given_name,
          surname: drupalSettings.m_api.data.userinfo.family_name,
          fiscalCode: drupalSettings.m_api.data.userinfo.cf,
          email: drupalSettings.m_api.data.userinfo.email,
          telNumber: "",
          description: ""
        },
        notes: ""
      }
    };
  },
  computed: {
    officeDetails() {
      return [
        {
          intro: this.office.name,
          icon: "h-pin-green",
          value: this.office.address
        },
        {
          icon: "h-mail-green",
          value: this.office.email,
          link: `mailto:${this.office.email}`
        },
        {
          icon: "h-phone-green",
          value: this.office.telephoneNumber,
          link: `tel:${this.office.telephoneNumber}`
        },
        {
          icon: "h-external-link-green",
          value: this.office.site,
          link: this.office.site
        }
      ];
    },
    userInfo() {
      return [
        {
          label: "NOME",
          value: this.user.given_name
        },
        {
          label: "COGNOME",
          value: this.user.family_name
        },
        {
          label: "CODICE FISCALE",
          value: this.user.cf
        }
      ];
    },
    descriptionPlaceholder() {
      const currentPublicService = this.publicServices.find(
        x => x.id === this.model.publicServiceId
      );
      return currentPublicService ? currentPublicService.notes : null;
    },
    descriptionRequired() {
      const currentPublicService = this.publicServices.find(
        x => x.id === this.model.publicServiceId
      );
      return currentPublicService ? currentPublicService.mandatoryField : false;
    }
  },
  watch: {},
  mounted() {},
  methods: {
    onChangeService(data) {
      this.model.publicServiceId = data.id;
    },
    onSubmit(e) {
      e.preventDefault();
      this.validateData();
    },
    validateData() {
      this.errors = [];
      if (!this.model.date || !this.model.timeslotId) {
        return this.errors.push("Slot orario obbligatorio");
      }
      if (!this.model.user.telNumber || this.model.user.telNumber === "") {
        return this.errors.push("Numero di telefono obbligatorio");
      }
      if (!this.model.user.email || this.model.user.email === "") {
        return this.errors.push("Email obbligatoria");
      }
      if (
        this.descriptionRequired &&
        (!this.model.user.description || this.model.user.description === "")
      ) {
        return this.errors.push("Descrizione obbligatoria");
      }
      if (!this.model.user.privacy || this.model.user.privacy === "") {
        return this.errors.push("Consenso privacy obbligatorio");
      }
      this.sendForm();
    },
    onUserModelChange(model) {
      this.model.user = model;
      this.model.notes = model.description;
      this.errors = [];
    },
    updateTime(data) {
      this.model.timeslotId = data.id;
      this.model.date = data.date;
      this.errors = [];
    },
    sendForm() {
      this.loading = true;
      const endpoint = drupalSettings.m_api.endpoints.addReservation;
      this.$http.post(endpoint, this.model).then(res => {
        this.loading = false;
        if (res.data.id) {
          this.submitted = true;
          this.downloadUrl = this.generateUrl(res.data.qrcodeUrl);
          /**
           * Hide scheda-serivzio header.
           **/
          jQuery(".module.hero.hero-simple.prenota-ufficio").hide();
        } else {
          console.log(res);
          if (res.data.errors) {
            this.errors = res.data.errors.map(x => x.defaultMessage);
          } else {
            console.log(res);
          }
        }
      });
    },
    generateUrl(url) {
      if (url.indexOf("http") === 0) {
        return url;
      }
      return `https://${url}`;
    }
  }
};
</script>
