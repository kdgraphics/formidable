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
          <textarea type="text" :class="'text fullwidth field-label-value'+index" v-model="field.label" rows="1"></textarea>
          <a href="#" @click.prevent="switchHtml">{{ field.labelHtml ? 'Plain' : 'Html' }}</a>
          <input type="hidden" v-model="field.labelHtml">
        </div>
      </div>
      <field-choices
        :field="field"
        :default="''"
        :errors="errors"
      ></field-choices>
    </template>
    <template v-slot:extraAdvanced>
      <div class="field field-choiceContainerClass">
        <div class="heading">
          <label>{{ t('Choice container class') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" :class="'text fullwidth' + (errors.choiceContainerClass ? ' error' : '')" v-model="field.choiceContainerClass" @input="validateAlnum('choiceContainerClass')">
        </div>
      </div>
      <div class="field field-choiceLabelClass">
        <div class="heading">
          <label>{{ t('Choice label class') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" :class="'text fullwidth' + (errors.choiceLabelClass ? ' error' : '')" v-model="field.choiceLabelClass" @input="validateAlnum('choiceLabelClass')">
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
  mounted() {
    this.$nextTick(() => {
      if (this.field.labelHtml) {
        document.addEventListener("DOMContentLoaded", this.enableRedactor);
      }
    })
  },
  methods: {
    isAlnum(field) {
      return this.field[field].match(/^[0-9a-zA-Z-_]*$/);
    },
    validateAlnum(field) {
      if (!this.isAlnum(field)) {
        this.$set(this.errors, field, true);
      } else {
        this.$set(this.errors, field, false);
      }
    },
    enableRedactor() {
      $R('.field-label-value'+this.index, {
        callbacks: {
          changed: (html) => {
            this.field.label = html;
          }
        }
      });
    },
    switchHtml: function() {
      if (!this.field.labelHtml) {
        this.enableRedactor();
        this.field.labelHtml = true;
      } else {
        $R('.field-label-value'+this.index, 'destroy');
        this.field.labelHtml = false;
      }
    }
  }
};
</script>