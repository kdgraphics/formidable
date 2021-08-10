<template>
  <forms-field v-on="$listeners"
    :field="field" 
    :index="index"
    :original="original"
    :errors="errors"
  >
    <template v-slot:extraFields>
      <div class="field field-placeholder">
        <div class="heading">
          <label>{{ t('Placeholder') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.placeholder">
        </div>
      </div>
      <div class="field field-label">
        <div class="heading">
          <label>{{ t('Label') }}</label>
        </div>
        <div class="input ltr">
          <textarea type="text" :class="'text fullwidth field-label-value'+index" v-model="field.label" rows="1"></textarea>
          <a href="#" @click.prevent="field.labelHtml = !field.labelHtml">{{ field.labelHtml ? 'Plain' : 'Html' }}</a>
        </div>
      </div>
      <div class="field field-default">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <textarea class="text fullwidth" v-model="field.default"></textarea>
        </div>
      </div>
      <div class="field field-tooltip">
        <div class="heading">
          <label>{{ t('Tooltip') }}</label>
        </div>
        <div class="input ltr">
          <textarea type="text" :class="'text fullwidth field-tooltip-value'+index" v-model="field.tooltip" rows="1"></textarea>
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
  },
  mounted() {
    if (this.field.labelHtml) {
      this.enableLabelRedactor();
    }
    this.enableTooltipRedactor();
  },
  watch: {
    'field.labelHtml': function () {
      this.toggleRedactor();
    } 
  },
  methods: {
    enableTooltipRedactor: function () {
      $R('.field-tooltip-value'+this.index, {
        callbacks: {
          changed: (html) => {
            this.field.tooltip = html;
          }
        }
      });
    },
    enableLabelRedactor: function () {
      $R('.field-label-value'+this.index, {
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
        $R('.field-label-value'+this.index, 'destroy');
      }
    }
  }
};
</script>