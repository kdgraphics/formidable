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