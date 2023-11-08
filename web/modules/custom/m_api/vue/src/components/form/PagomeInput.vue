<template>
  <div class="form-wrapper" :class="{'was-validated': showError}">
    <label :for="id">{{ label }}</label>
    <input
      :type="type"
      class="form-control"
      :required="required"
      :class="[{disabled: disabled}, {'bg-transparent': bgTransparent}]"
      :disabled="disabled"
      :readonly="readonly"
      :id="id"
      :placeholder="placeholder"
      :value="value"
      :minlength="length"
      :maxlength="length"
      :step="step"
      :min="min"
      v-on:input="$emit('input', $event.target.value)">
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
    type: {
      type: String,
      default: 'text'
    },
    label: {
      type: String
    },
    placeholder: {
      type: String
    },
    value: {
      type: [String, Number]
    },
    disabled: {
      type: Boolean,
      default: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    error: {
      type: [String, null]
    },
    required: {
      type: Boolean,
      default: false
    },
    length: {
      type: [Number, String],
      required: false
    },
    step: {
      type: [Number, String],
      required: false,
      default: () => null
    },
    min: {
      type: [Number, String],
      required: false,
      default: () => null
    },
    showError: {
      type: Boolean,
      required: false,
      default: false
    },
    bgTransparent: {
      type: Boolean,
      required: false,
      default: false
    }
  }
}
</script>
