<template>
  <forms-field v-on="$listeners"
    :field="field"
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
          <textarea type="text" :class="'text fullwidth field-label-value'+field.uid" v-model="field.label" rows="1"></textarea>
          <a href="#" @click.prevent="field.labelHtml = !field.labelHtml">{{ field.labelHtml ? 'Plain' : 'Html' }}</a>
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
      <div class="field field-format">
        <div class="heading">
          <label>{{ t('Format') }}</label>
        </div>
        <div class="instructions">
          {{ t('Flatpickr valid format, as seen ') }} <a href="https://flatpickr.js.org/formatting/" target="_blank">here</a>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.format">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-disablemobile">{{ t('Disable on mobile') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-disablemobile" :class="{lightswitch:true, on:field.disableMobile}">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="disableMobile" v-model="field.disableMobile">
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-enableTime">{{ t('Enable time') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-disablemobile" :class="{lightswitch:true, on:field.enableTime}">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="enableTime" v-model="field.enableTime">
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-noCalendar">{{ t('Disable calendar') }}</label>
        </div>
        <div class="instructions">
          {{ t('Use it along with enable time to create a time picker.') }}
        </div>
        <div class="input ltr">
          <div id="form-disablemobile" :class="{lightswitch:true, on:field.noCalendar}">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="noCalendar" v-model="field.noCalendar">
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-enableSeconds">{{ t('Enable seconds in the time picker') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-disablemobile" :class="{lightswitch:true, on:field.enableSeconds}">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="enableSeconds" v-model="field.enableSeconds">
          </div>
        </div>
      </div>
      <div class="field field-mindate">
        <div class="heading">
          <label>{{ t('Min date') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.minDate">
        </div>
      </div>
      <div class="field field-maxdate">
        <div class="heading">
          <label>{{ t('Max date') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.maxDate">
        </div>
      </div>
      <div class="field field-minuteincrement">
        <div class="heading">
          <label>{{ t('Minute increment') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.minuteIncrement">
        </div>
      </div>
    </template>
  </forms-field>
</template>

<script>
export default {
  props: {
    field: Object
  },
  data() {
    return {
      currentLabelEditor: null,
      currentTooltipEditor: null,
      ckEditorSettings: {
        plugins: [],
        removePlugins: [],
        "toolbar": {
          "items": [
            "heading",
            "|",
            "bold",
            "italic",
            "underline",
            "strikethrough",
            "sourceEditing"
          ]
        }
      },
      errors: {}
    }
  },
  watch: {
    'field.labelHtml': function () {
      this.toggleCkEditor();
    } 
  },
  mounted() {
    if (this.field.labelHtml) {
      this.enableLabelCKEditor();
    }
    this.enableTooltipCKEditor();
  },
  methods: {
    enableTooltipCKEditor: async function () {
      this.currentTooltipEditor = await window.CKEditor5.craftcms
        .create(document.querySelector('.field-tooltip-value'+this.field.uid), this.ckEditorSettings)
        .catch(error => {
            console.error('CKEditor init error:', error);
        });

      if (this.currentTooltipEditor) {
        this.handleStatusChanges('tooltip', this.currentTooltipEditor);
      }
    },
    enableLabelCKEditor: async function () {
      if (this.currentLabelEditor) {
        return;
      }
      this.currentLabelEditor = await window.CKEditor5.craftcms
        .create(document.querySelector('.field-label-value'+this.field.uid), this.ckEditorSettings)
        .catch(error => {
            console.error('CKEditor init error:', error);
        });
      
      if (this.currentLabelEditor) {
        this.handleStatusChanges('label', this.currentLabelEditor);
      }
    },
    toggleCkEditor: function() {
      if (this.field.labelHtml) {
        this.enableLabelCKEditor();
      } else {
        if (this.currentLabelEditor) {
          this.currentLabelEditor
          .destroy()
          .then(() => {
            this.currentLabelEditor = null;
          })
          .catch(error => {
              console.error('CKEditor destroy error:', error);
          });
        }
      }
    },
    handleStatusChanges: function(type, editor) {
      if (type == 'label') {
        editor.model.document.on( 'change:data', () => {
          this.field.label = editor.getData();
        });
      } else if (type == 'tooltip') {
        editor.model.document.on( 'change:data', () => {
          this.field.tooltip = editor.getData();
        });
      }
    }
  }
};
</script>