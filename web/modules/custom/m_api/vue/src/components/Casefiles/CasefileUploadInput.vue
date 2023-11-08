<template>
  <div class="upload-input d-flex align-items-center p-2">
    <div class="label d-flex align-items-center">
      <svg class="icon icon-primary icon-xs mr-1">
        <use
          xlink:href="/themes/custom/portalemessina/dist/vendors/bootstrap-italia/svg/sprite.svg#it-upload"
        />
      </svg>
      <span v-if="attachments.length === 0">Carica il file</span>
      <span v-if="attachments.length === 1">{{ attachments[0].fileName }}</span>
      <span v-if="attachments.length > 1">{{ attachments.length }} elementi selezionati</span>
    </div>
    <input multiple accept="application/pdf" ref="file-input" @change="prepareFiles($event)" type="file" class="reset-appearance w-100 d-inline-block" placeholder="Carica">
    <button
      class="btn btn-outline-success py-1 px-4 font-weight-bold"
      @click="$emit('upload', attachments)"
      :disabled="attachments.length === 0"
      type="button"
    >
      carica
    </button>
  </div>
</template>

<script>
const JsBase64 = () =>
  import(
    /* webpackChunkName: "js-base64" */
    "js-base64"
    );

export default {
  name: "CasefilesUploadInput",
  data() {
    return {
      attachments: [],
    };
  },
  methods: {
    prepareFiles(e) {
      e.preventDefault();
      e.stopPropagation();
      this.attachments = [];
      const files = e.target.files;
      for (let i = 0; i < files.length; i++) {
        const file = files.item(i);
        const attachment = {
          base64Content: null,
          description: file.name,
          fileName: file.name
        };
        const reader = new FileReader();
        reader.onload = (e) => {
          JsBase64().then(({ encode }) => (attachment.base64Content = encode(e.target.result)));
          this.attachments.push(attachment);
        };
        reader.readAsText(file);
      }
    },
  }
};
</script>
