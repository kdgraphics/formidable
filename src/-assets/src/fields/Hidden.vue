<template>
  <div :class="field.type+'-field' + ' formidablefield-container'" ref="field">
    <slot name="fields">
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
      <div class="field field-placeholder">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.default">
        </div>
      </div>
      <div class="field field-fieldClass">
        <div class="heading">
          <label>{{ t('Field class') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" :class="'text fullwidth' + (errors.fieldClass ? ' error' : '')" v-model="field.fieldClass" @input="validateAlnum('fieldClass')">
        </div>
      </div>
      <div class="field first">
        <div class="heading">
          <label for="form-active">{{ t('Autocomplete') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-autocomplete" :class="{lightswitch:true, on:field.autocomplete}">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="autocomplete" v-model="field.autocomplete">
          </div>
        </div>
      </div>
    </slot>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { reduce } from 'lodash';
import slugify from 'slugify';

export default {
  props: {
    field: Object
  },
  data() {
    return {
      errors: {}
    }
  },
  created() {
    this.validateName();
    this.validateHandle();
  },
  mounted() {
    Craft.initUiElements(this.$refs.field);
    let _this = this;
    $(this.$el).find('.lightswitch').on('change', function(e){
      let input = $(e.target).find('input');
      let value = input.val() == 1;
      _this.field[input.attr('name')] = value;
    });
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
  methods: {
    validateName() {
      let error = (!this.field.name || this.field.name.trim() == '');
      this.$set(this.errors, 'name', error);
    },
    generateHandle() {
      if (this.field.handle) {
        return;
      }
      this.$set(this.field, 'handle', slugify(this.field.name, {lower: true}));
      this.validateHandle();
    },
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
    }
  },
  watch: {
    hasErrors: function () {
      this.$emit('error', {uid: this.field.uid, error: this.hasErrors});
    },
    otherFieldHandles: function () {
      this.validateHandle();
    }
  }
};
</script>