<template>
  <div class="page-prenota-ufficio-offices-list__card">
    <div class="page-prenota-ufficio-offices-list__card__media">
      <img
        :src="
          office.imageUrl ||
            '/themes/custom/portalemessina/dist/images/hero/placeholder.jpg'
        "
      />
    </div>
    <div class="module card card-teaser no-after p-0">
      <div class="p-0 py-2">
        <div class="card-badge" v-if="badgeLabel">{{ badgeLabel }}</div>
      </div>
      <div class="card-content d-flex flex-column py-0">
        <h3 class="card-title">{{ office.name }}</h3>
        <div class="card-content text-serif">
          {{ office.description || office.descritpion }}
        </div>
        <div class="page-prenota-ufficio-offices-list__card__address">
          {{ office.address }}
        </div>
        <div class="card-link">
          <a :href="office.link" hreflang="it">ACCEDI AL SERVIZIO</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "PrenotaUfficioListCard",
  props: {
    office: {
      type: Object,
      required: true
    },
    badgeLabel: {
      type: String,
      required: false
    }
  }
};
</script>
