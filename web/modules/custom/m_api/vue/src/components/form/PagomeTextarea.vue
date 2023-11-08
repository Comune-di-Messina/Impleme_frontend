<template>
  <div class="form-wrapper">
    <label :for="id">{{ label }}</label>
    <textarea
      :id="id"
      :rows="rows"
      :required="required"
      :placeholder="placeholder"
      :value="value"
      v-on:input="$emit('input', $event.target.value)">

    </textarea>
    <div class="invalid-feedback">{{ error }}</div>
    <div class="valid-feedback"></div>
  </div>
</template>

<script>
export default {
  props: {
    id: {
      required: true,
      type: String
    },
    rows: {
      default: 5,
      type: Number
    },
    label: {
      required: true,
      type: String
    },
    placeholder: {
      type: String,
      default: ''
    },
    value: {
      type: String,
      default: ''
    },
    error: {
      type: [String, null]
    },
    required: {
      type: Boolean,
      dafault: false
    }
  }
}
</script>
