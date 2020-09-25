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
      <div class="field field-placeholder">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <textarea class="text fullwidth" v-model="field.default"></textarea>
        </div>
      </div>
    </template>
    <template v-slot:extraAdvanced>
      <div class="field field-rows">
        <div class="heading">
          <label>{{ t('Rows') }}</label>
        </div>
        <div class="input ltr">
          <input type="number" class="text fullwidth" v-model="field.rows">
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
  }
};
</script>