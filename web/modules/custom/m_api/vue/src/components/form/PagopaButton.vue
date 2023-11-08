<template>
  <a class="btn btn-primary pagopa btn-xs btn-icon" :href="url">
    <span class="h-icon h-pago-pa mr-1"></span>
    Paga
  </a>
</template>

<script>
export default {
  name: "PagopaButton",
  props: {
    url: {
      type: [String],
      default: null
    }
  },
};
</script>

<style scoped>

</style>
