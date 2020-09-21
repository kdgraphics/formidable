<template>
  <forms-field v-on="$listeners"
    :field="field" 
    :index="index"
    :original="original"
    :errors="errors"
  >
    <template v-slot:extraFields>
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
      <div class="field field-step">
        <div class="heading">
          <label>{{ t('Step') }}</label>
        </div>
        <div class="input ltr">
          <input type="number" step="0.00000000001" :class="'text fullwidth' + (errors.step ? ' error' : '')" v-model="field.step" @input="validateStep">
        </div>
      </div>
      <div class="field field-placeholder">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <input type="number" :step="field.step" :class="'text fullwidth' + (errors.default ? ' error' : '')" v-model="field.default" @input="validateDefault">
        </div>
      </div>
    </template>
  </forms-field>
</template>

<script>
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
  methods: {
    validateStep() {
      if (!this.field.step.match(/^[0-9]+(\.[0-9]+)?$/)) {
        this.$set(this.errors, 'step', true);
      } else {
        this.$set(this.errors, 'step', false);
      }
    },
    validateDefault() {
      if (!this.field.step.match(/^[0-9]+(\.[0-9]+)?$/)) {
        this.$set(this.errors, 'default', true);
      } else {
        this.$set(this.errors, 'default', false);
      }
    },
  }
};
</script>