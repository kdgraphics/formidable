<template>
  <div :class="selectedField.type+'-field' + ' formidablefield-container'">
    <slot name="fields">
      <div class="field first">
        <div class="heading">
          <label for="form-active">{{ t('Active') }}</label>
        </div>
        <div class="input ltr">
          <div
            id="form-active"
            :class="{lightswitch:true, on:selectedField.active}"
          >
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.active"
              type="hidden"
              name="active"
            >
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required">{{ t('Name') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.name"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.name"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required">{{ t('Handle') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.handle"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.handle"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div
        v-if="hasInstructions"
        class="field"
      >
        <div class="heading">
          <label>{{ t('Instructions') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.instructions"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div
        v-if="hasPlaceholder"
        class="field"
      >
        <div class="heading">
          <label>{{ t('Placeholder') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.placeholder"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div
        v-if="hasLabel"
        class="field"
      >
        <div class="heading">
          <label>{{ t('Label') }}</label>&nbsp;
          <a
            href="#"
            @click.prevent="selectedField.labelHtml = !selectedField.labelHtml"
          >
            {{ selectedField.labelHtml ? 'Plain' : 'Html' }}
          </a>
        </div>
        <div class="input ltr">
          <textarea
            v-model="selectedField.label"
            class="text fullwidth field-label-value"
            type="text"
            rows="1"
          />
        </div>
      </div>
      <div
        v-if="hasTooltip"
        class="field"
      >
        <div class="heading">
          <label>{{ t('Tooltip label') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.tooltipLabel"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div
        v-if="hasTooltip"
        class="field field-tooltip"
      >
        <div class="heading">
          <label>{{ t('Tooltip') }}</label>
        </div>
        <div class="input ltr">
          <textarea
            v-model="selectedField.tooltip"
            class="text fullwidth field-tooltip-value"
            type="text"
            rows="1"
          />
        </div>
      </div>
    </slot>
    <slot name="extraFields" />
    <slot name="advanced">
      <div class="advanced">
        <div class="header">
          {{ t('Advanced') }}
        </div>
        <div class="content">
          <div
            v-if="hasFieldClass"
            class="field"
          >
            <div class="heading">
              <label>{{ t('Field class') }}</label>
            </div>
            <div class="input ltr">
              <input
                v-model="selectedField.fieldClass"
                type="text"
                class="text fullwidth"
              >
            </div>
            <ul class="errors">
              <li
                v-for="error, index in errors.fieldClass"
                :key="index"
                class="error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div
            v-if="hasContainerClass"
            class="field"
          >
            <div class="heading">
              <label>{{ t('Container class') }}</label>
            </div>
            <div class="input ltr">
              <input
                v-model="selectedField.containerClass"
                type="text"
                class="text fullwidth"
              >
            </div>
            <ul class="errors">
              <li
                v-for="error, index in errors.containerClass"
                :key="index"
                class="error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div
            v-if="hasLabel"
            class="field"
          >
            <div class="heading">
              <label>{{ t('Label class') }}</label>
            </div>
            <div class="input ltr">
              <input
                v-model="selectedField.labelClass"
                type="text"
                class="text fullwidth"
              >
            </div>
            <ul class="errors">
              <li
                v-for="error, index in errors.labelClass"
                :key="index"
                class="error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div
            v-if="hasAutocomplete"
            class="field first"
          >
            <div class="heading">
              <label for="form-active">{{ t('Autocomplete') }}</label>
            </div>
            <div class="input ltr">
              <div
                id="form-autocomplete"
                :class="{lightswitch:true, on:selectedField.autocomplete}"
              >
                <div class="lightswitch-container">
                  <div class="handle" />
                </div>
                <input
                  v-model="selectedField.autocomplete"
                  type="hidden"
                  name="autocomplete"
                >
              </div>
            </div>
          </div>
          <slot name="extraAdvanced" />
        </div>
      </div>
    </slot>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import Vue from 'vue';
import { reduce, isEqual } from 'lodash';
import slugify from 'slugify';

export default {
  props: {
    hasLabel: {
      type: Boolean,
      default: true
    },
    hasPlaceholder: {
      type: Boolean,
      default: true
    },
    hasTooltip: {
      type: Boolean,
      default: true
    },
    hasInstructions: {
      type: Boolean,
      default: true
    },
    hasAutocomplete: {
      type: Boolean,
      default: true
    },
    hasFieldClass: {
      type: Boolean,
      default: true
    },
    hasContainerClass: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    errors: function () {
      if (!this.selectedField.errors) {
        return {};
      }
      return this.selectedField.errors;
    },
    ...mapState(['fields', 'selectedField'])
  },
  watch: {
    'selectedField.name': function () {
      this.slugifyHandle();
    },
    'selectedField.labelHtml': function () {
      if (this.selectedField) {
        this.toggleRedactor();
      }
    }
  },
  mounted() {
    Craft.initUiElements(this.$el);
    let _this = this;
    $(this.$el).find('.lightswitch').on('change', function(e){
      let input = $(e.target).find('input');
      let value = input.val() == 1;
      _this.selectedField[input.attr('name')] = value;
    });
    this.toggleRedactor();
    if (this.hasTooltip) {
      this.enableTooltipRedactor();
    }
  },
  methods: {
    enableLabelRedactor: function () {
      $R('.field-label-value', {
        callbacks: {
          changed: (html) => {
            this.selectedField.label = html;
          }
        }
      });
    },
    toggleRedactor: function() {
      if (this.selectedField.labelHtml) {
        this.enableLabelRedactor();
      } else {
        $R('.field-label-value', 'destroy');
      }
    },
    enableTooltipRedactor: function () {
      $R('.field-tooltip-value', {
        callbacks: {
          changed: (html) => {
            this.selectedField.tooltip = html;
          }
        }
      });
    },
    slugifyHandle(){
      if (this.selectedField && !this.selectedField.id) {
        this.selectedField.handle = slugify(this.selectedField.name ?? '', {lower: true});
      }
    },
  }
};
</script>
<style lang="scss">
@import "~craftcms-sass/mixins";

.formidablefield-container {
  max-height: calc(100vh - 280px);
  overflow-y: auto;
  display:flex;
  flex-wrap: wrap;
  width: 100%;
  .field {
    width: calc(50% - 10px);
    margin-top: 0;
    &:nth-child(2n) {
      margin-left: 10px;
      padding-right: 3px;
    }
    &:nth-child(2n+1) {
      margin-right: 10px;
      padding-left: 3px;
    }
  }
  .redactor-toolbar, .redactor-styles {
    padding: 6px 9px !important;
  }
  .redactor-in:focus-visible {
    border: none !important;
    box-shadow: none;
  }
  .redactor-box.redactor-focus {
    border-color: $blue700 !important;
    box-shadow: 0 0 0 2px rgba(18,127,191,.5);
  }
  .redactor-toolbar {
    a.re-button-icon {
      padding: 6px 14px 5px;
      font-size: 14px;
    }
  }
  .advanced {
    width: 100%;
    .header{
      box-shadow: none;
      border-radius: 0;
      padding: 5px 10px;
      border-top-right-radius: 5px;
      border-top-left-radius: 5px;
    }
    .content {
      padding: 10px;
      border: 1px solid #e4edf6;
      border-top: none;
      border-bottom-right-radius: 5px;
      border-bottom-left-radius: 5px;
      display:flex;
      flex-wrap: wrap;
    }
    .field:nth-last-child(2) {
      margin-bottom: 0;
    }
  }
}
</style>