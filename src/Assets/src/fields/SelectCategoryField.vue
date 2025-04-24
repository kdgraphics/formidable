<template>
  <forms-field v-on="$listeners"
    :field="field"
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
      <div class="lightswitch-field field-multiple field">
        <div class="heading">
          <label>{{ t('Multiple') }}</label>
        </div>
        <div class="input ltr">
          <div :class="'lightswitch ' + (field.multiple ? 'on' : '')" tabindex="0" role="switch" :aria-checked="field.multiple">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="multiple" v-model="field.multiple">
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Category') }}</label>
        </div>
        <div class="select">
          <select multiple v-model="field.groups" :class="errors.groups ? 'error' : ''">
            <option v-for="name, id in categories" :key="id" :value="id">{{ name }}</option>
          </select>
        </div>
      </div>
      <div class="lightswitch-field field-default field">
        <div class="heading">
          <label>{{ t('Include Select option') }}</label>
        </div>
        <div class="input ltr">
          <div :class="'lightswitch ' + (field.includeSelect ? 'on' : '')" tabindex="0" role="switch" :aria-checked="field.includeSelect">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="includeSelect" v-model="field.includeSelect">
          </div>
        </div>
      </div>
      <div class="field field-label">
        <div class="heading">
          <label>{{ t('Select Label') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.selectLabel">
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
    </template>
  </forms-field>
</template>

<script>
export default {
  computed: {
    fields: function () {
      if (!this.field.category) {
        return {};
      }
      return this.allFields[this.field.category];
    }
  },
  watch: {
    'field.category': function () {
      this.validate();
    },
    'field.labelHtml': function () {
      this.toggleRedactor();
    }
  },
  props: {
    index: Number,
    field: Object
  },
  mounted() {
    /**
     * Copies the lightswitch value on the field object
     */
    this.$nextTick(() => {
      Craft.initUiElements(this.$el);
      let _this = this;
      $(this.$el).find('.lightswitch').on('change', function(e){
        let input = $(e.target).find('input');
        let value = (input.val() == 1);
        _this.field[input.attr('name')] = value;
      });
      if (this.field.labelHtml) {
        document.addEventListener("DOMContentLoaded", this.enableRedactor);
      }
    });
    if (this.field.labelHtml) {
      this.enableLabelRedactor();
    }
    this.enableTooltipRedactor();
  },
  created() {
    let _this = this;
    if (typeof window.selectCategoryFieldCategories == 'undefined') {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      window.selectCategoryFieldCategories = axios.post(Craft.getCpUrl('formidable/categories-groups'), data)
      .catch((error) => {
        console.log(error);
      })
    }
    window.selectCategoryFieldCategories.then(function (res) {
      _this.categories = res.data.groups;
    })
  },
  methods: {
    validate() {
      this.$set(this.errors, 'category', false);
      if (!this.field.category) {
        this.$set(this.errors, 'category', true);
      }
    },
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
  },
  data() {
    return {
      errors: {},
      categories: []
    }
  }
};
</script>