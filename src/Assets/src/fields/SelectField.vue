<template>
  <forms-field v-on="$listeners"
    :field="field"
    :original="original"
    :errors="errors"
  >
    <template v-slot:extraFields>
    	<div class="field field-label">
        <div class="heading">
          <label>{{ t('Label') }}</label>
        </div>
        <div class="input ltr">
          <textarea type="text" :class="'text fullwidth field-label-value'+field.uid" v-model="field.label" rows="1"></textarea>
          <a href="#" @click.prevent="field.labelHtml = !field.labelHtml">{{ field.labelHtml ? 'Plain' : 'Html' }}</a>
        </div>
      </div>
      <div class="field field-tooltip-label">
        <div class="heading">
          <label>{{ t('Tooltip label') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.tooltipLabel">
        </div>
      </div>
      <div class="field field-tooltip">
        <div class="heading">
          <label>{{ t('Tooltip') }}</label>
        </div>
        <div class="input ltr">
          <textarea type="text" :class="'text fullwidth field-tooltip-value'+field.uid" v-model="field.tooltip" rows="1"></textarea>
        </div>
      </div>
      <field-choices
        :field="field"
        :default="t('Select')"
        :errors="errors"
      ></field-choices>
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
  watch: {
    'field.labelHtml': function () {
      this.toggleRedactor();
    } 
  },
  mounted() {
    if (this.field.labelHtml) {
      this.enableLabelRedactor();
    }
    this.enableTooltipRedactor();
  },
  methods: {
    enableTooltipRedactor: function () {
      $R('.field-tooltip-value'+this.field.uid, {
        callbacks: {
          changed: (html) => {
            this.field.tooltip = html;
          }
        }
      });
    },
    enableLabelRedactor: function () {
      $R('.field-label-value'+this.field.uid, {
        callbacks: {
          changed: (html) => {
            this.field.label = html;
          }
        }
      });
    },
    toggleRedactor: function() {
      if (this.field.labelHtml) {
        this.enableLabelRedactor();
      } else {
        $R('.field-label-value'+this.field.uid, 'destroy');
      }
    }
  }
};
</script>