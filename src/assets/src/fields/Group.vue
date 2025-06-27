<template>
  <forms-field v-on="$listeners"
    :field="field"
    :errors="errors"
  >
    <template v-slot:fields></slot>
      <div class="field first">
        <div class="heading">
          <label for="form-active">{{ t('Active') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-active" :class="{lightswitch:true, on:field.active}">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="active" v-model="field.active">
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required">{{ t('Name') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" :class="'text fullwidth' + (errors.name ? ' error' : '')" v-model="field.name" v-on:blur="generateHandle" @input="validateName">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required">{{ t('Handle') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" :class="'text fullwidth' + (errors.handle ? ' error' : '')" v-model="field.handle" @input="validateHandle">
        </div>
      </div>
    </template>
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
    </template>
    <template v-slot:advanced>
      <div class="advanced">
        <div class="header" @click="showAdvanced = !showAdvanced" :class="{'closed': !showAdvanced}">{{ t('Advanced') }}</div>
        <div class="content" v-show="showAdvanced">
          <div class="field field-fieldClass">
            <div class="heading">
              <label>{{ t('Fields class') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" :class="'text fullwidth' + (errors.fieldClass ? ' error' : '')" v-model="field.fieldClass" @input="validateAlnum('fieldClass')">
            </div>
          </div>
          <div class="field field-containerClass">
            <div class="heading">
              <label>{{ t('Container class') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" :class="'text fullwidth' + (errors.containerClass ? ' error' : '')" v-model="field.containerClass" @input="validateAlnum('containerClass')">
            </div>
          </div>
          <div class="field field-labelClass">
            <div class="heading">
              <label>{{ t('Label class') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" :class="'text fullwidth' + (errors.labelClass ? ' error' : '')" v-model="field.labelClass" @input="validateAlnum('labelClass')">
            </div>
          </div>
        </div>
      </div>
    </template>
  </forms-field>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { reduce, isEqual } from 'lodash';
import slugify from 'slugify';

export default {
  props: {
    index: Number,
    field: Object
  },
  computed: {
    otherFieldHandles: function () {
      let _this = this;
      return this.allFields.filter((field) => field.uid != this.field.uid).map((field) => field.handle);
    },
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    allFields: function () {
      let fields = [];
      for(let field of this.fields) {
        fields.push(field);
        if (field.type == 'group') {
          for(let subfield of field.fields) {
            fields.push(subfield);
          }
        }
      }
      return fields;
    },
    ...mapState(['fields'])
  },
  data() {
    return {
      currentLabelEditor: null,
      currentTooltipEditor: null,
      ckEditorSettings: {
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
      errors: {},
      showAdvanced: false
    }
  },
  mounted() {
    Craft.initUiElements(this.$refs.field);
    let _this = this;
    $(this.$el).find('.lightswitch').on('change', function(e){
      let input = $(e.target).find('input');
      let value = input.val() == 1;
      _this.field[input.attr('name')] = value;
    });
    if (this.field.labelHtml) {
      this.enableLabelCKEditor();
    }
    this.enableTooltipCKEditor();
  },
  watch: {
    'field.labelHtml': function () {
      this.toggleCkEditor();
    },
    hasErrors: function () {
      this.$emit('error', {uid: this.field.uid, error: this.hasErrors});
    },
    hasChanges: function () {
      this.$emit('change', {uid: this.field.uid, change: this.hasChanges});
    },
    otherFieldHandles: function () {
      this.validateHandle()
    }
  },
  methods: {
    /**
     * Validates the field name
     */
    validateName() {
      let error = (this.field.name.trim() == '');
      this.$set(this.errors, 'name', error);
    },
    generateHandle() {
      if (this.field.handle) {
        return;
      }
      this.$set(this.field, 'handle', slugify(this.field.name, {lower: true}));
      this.validateHandle();
    },
    /**
     * Validates the field handle
     */
    validateHandle() {
      let error = (!this.field.handle || !this.isAlnum('handle') || this.otherFieldHandles.includes(this.field.handle));
      this.$set(this.errors, 'handle', error);
    },
    isAlnum(field) {
      return this.field[field].match(/^[0-9a-zA-Z-_\s]*$/);
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

      this.handleStatusChanges('tooltip', this.currentTooltipEditor);
    },
    enableLabelCKEditor: async function () {
      this.currentLabelEditor = await window.CKEditor5.craftcms
        .create(document.querySelector('.field-label-value'+this.field.uid), this.ckEditorSettings)
        .catch(error => {
            console.error('CKEditor init error:', error);
        });
      
      this.handleStatusChanges('label', this.currentLabelEditor);
    },
    toggleCkEditor: function() {
      if (this.field.labelHtml) {
        this.enableLabelCKEditor();
      } else {
        if (this.currentLabelEditor) {
          this.currentLabelEditor
          .destroy()
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