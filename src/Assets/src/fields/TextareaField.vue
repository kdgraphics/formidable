<template>
  <div>
    <slot name="name"></slot>
    <slot name="instructions"></slot>
    <div class="field field-label">
      <div class="heading">
        <label>{{ t('Label') }}</label>
      </div>
      <div class="input ltr">
        <input type="text" class="text fullwidth" v-model="field.label">
      </div>
    </div>
    <div class="field field-placeholder">
      <div class="heading">
        <label>{{ t('Placeholder') }}</label>
      </div>
      <div class="input ltr">
        <input type="text" class="text fullwidth" v-model="field.placeholder">
      </div>
    </div>
    <div class="field field-placeholder">
      <div class="heading">
        <label>{{ t('Default value') }}</label>
      </div>
      <div class="input ltr">
        <textarea class="text fullwidth" v-model="field.default"></textarea>
      </div>
    </div>
    <slot name="advanced"></slot>
  </div>
</template>

<script>
import { isEqual } from 'lodash';

export default {
  props: {
    index: Number,
    field: Object,
    original: Object
  },
  data() {
    return {
      errors: {}
    }
  },
  computed: {
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    }
  },
  watch: {
    field: {
      hasErrors: function () {
        this.$emit('error', {index: this.index, error: this.hasErrors});
      },
      handler: function () {
        this.validate();
        this.$emit('change', {index: this.index, change: !isEqual(this.field, this.original)});
      },
      deep: true
    }
  },
  methods: {
    /**
     * Sets errors here in the errors object
     */
    validate () {
      // this.$set(this.errors, 'field', value);
    }
  }
};
</script>