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
          <input type="text" class="text fullwidth" v-model="field.default">
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