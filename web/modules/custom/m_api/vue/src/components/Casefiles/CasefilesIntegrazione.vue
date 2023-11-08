<template>
  <form>
    <span>Documentazione *</span>
    <div class="upload-input d-flex align-items-center bg-white">
      <input type="file" class="reset-appearance p-2 w-100 d-inline-block" placeholder="Carica">
      <a
        class="btn btn-outline-success my-4"
        @click="processFile($event)"
      >
        carica
      </a>
    </div>
    <input
      type="submit"
      class="btn btn-success mt-3"
      value="Invia documentazione"
      :disabled="attachments.length === 0"
    >
  </form>
</template>

<script>
export default {
  name: "CasefilesIntegrazione",
  data() {
    return {
      attachments: []
    };
  },
  methods: {
    uploadAttachments() {
    },
    processFile(e) {
      e.preventDefault();
      e.stopPropagation();
    }
  }
};
</script>
