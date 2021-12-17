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
      <div class="field field-instructions">
        <div class="heading">
          <label>{{ t('Instructions') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.instructions">
        </div>
      </div>
    </slot>
    <slot name="extraFields"></slot>
    <slot name="advanced">
      <div class="advanced">
        <div class="header" @click="showAdvanced = !showAdvanced" :class="{'closed': !showAdvanced}">{{ t('Advanced') }}</div>
        <div class="content" v-show="showAdvanced">
          <div class="field field-fieldClass">
            <div class="heading">
              <label>{{ t('Field class') }}</label>
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
          <slot name="extraAdvanced"></slot>
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
    field: Object,
    errors: Object
  },
  data: function() {
    return {
      showAdvanced: false
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
<style lang="scss">
@import "~craftcms-sass/mixins";

.formidablefield-container {
  display:flex;
  flex-wrap: wrap;
  width: 100%;
  .field {
    width: calc(50% - 10px);
    margin-top: 0;
    &:nth-child(2n) {
      margin-left: 10px;
    }
    &:nth-child(2n+1) {
      margin-right: 10px;
    }
  }
  .advanced {
    width: 100%;
    .header{
      box-shadow: none;
      border-radius: 0;
      padding: 5px 10px;
      cursor: pointer;
      border-top-right-radius: 5px;
      border-top-left-radius: 5px;
      &.closed {
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
      }
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
  }
}
</style>