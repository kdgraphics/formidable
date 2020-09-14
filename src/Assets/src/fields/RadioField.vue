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
    <field-choices 
      class="field choices"
      :field="field"
      :default="''"
    ></field-choices>
    <slot name="advanced"></slot>
  </div>
</template>

<script>

export default {
  props: {
    index: Number,
    field: Object,
  }
};
</script>