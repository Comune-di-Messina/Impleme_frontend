<template>
  <div class="bootstrap-select-wrapper">
    <label :for="id">{{ label }}</label>
    <select
      :id="id"
      :title="title"
      :value="value"
      v-on:change="$emit('input', $event.target.value); $root.$emit('resetFasce')"
      :class="{ disabled: disabled }"
      :disabled="disabled"
      :required="required"
      class="form-control"
    >
      <option value="" v-if="!!placeholder">{{ placeholder }}</option>
      <option v-for="option in
      options" :value="option.value">
        <!-- :disabled="option.value == ''" -->
        {{ option.text }}
      </option>
    </select>
    <div
      v-if="showError"
      :style="{ display: 'block' }"
      class="invalid-feedback"
    >
      {{ error }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: {
      required: true,
      type: String
    },
    title: {
      default: "",
      type: String
    },
    label: {
      required: true,
      type: String
    },
    options: {
      type: Array,
      default: () => []
    },
    value: {
      type: [String, Number],
      default: ""
    },
    disabled: {
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
    showError: {
      type: Boolean,
      default: false
    },
    placeholder: {
      type: String,
      required: false,
      default: ''
    }
  },
  watch: {
    options() {
      const id = this.id;
      this.$root.$nextTick(function() {
        jQuery("#" + id).selectpicker("refresh");
      });
    }
  },
  mounted() {
    const id = this.id;
    this.$root.$nextTick(function() {
      jQuery("#" + id).selectpicker("refresh");
    });
  }
};
</script>
