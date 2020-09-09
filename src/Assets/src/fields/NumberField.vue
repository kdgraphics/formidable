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
    <div class="field field-step">
      <div class="heading">
        <label>{{ t('Step') }}</label>
      </div>
      <div class="input ltr">
        <input type="number" step="0.00000000001" class="text fullwidth" v-model="field.step">
      </div>
    </div>
    <div class="field field-placeholder">
      <div class="heading">
        <label>{{ t('Default value') }}</label>
      </div>
      <div class="input ltr">
        <input type="number" :step="field.step" class="text fullwidth" v-model="field.default">
      </div>
    </div>
    <slot name="advanced"></slot>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';

export default {
  props: {
    index: Number,
    field: Object
  }
};

</script>