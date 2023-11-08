<template>
  <div 
    class="form-item"
    :class="classes"
  >
    <label :for="id">{{ label }}</label>
    <span class="item-value">{{ value }}{{ suffix }}</span>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: [String, Number],
      required: true
    },
    label: {
      type: String,
      required: true
    },
    id: {
      type: String,
      required: true
    },
    suffix: {
      type: String,
      default: ''
    },
    classes: {
      type: String,
      default: 'col-6 col-lg-3'
    }
  }
};
</script>
