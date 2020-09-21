<template>
  <div :class="field.type+'-field' + ' formidablefield-container'">
    <div class="field first">
      <div class="heading">
        <label class="required" :for="'field-'+field.index+'-name'">{{ t('Name') }}</label>
      </div>
      <div class="input ltr">
        <input type="text" :class="'text fullwidth' + (errors.name ? ' error' : '')" v-model="field.name" @input="validateName">
      </div>
    </div>
    <div class="field field-instructions">
      <div class="heading">
        <label :for="'field-'+field.index+'-instructions'">{{ t('Instructions') }}</label>
      </div>
      <div class="input ltr">
        <input type="text" class="text fullwidth" :id="'field-'+field.index+'-instructions'" v-model="field.instructions">
      </div>
    </div>
    <slot name="extraFields"></slot>
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
        <slot name="extraAdvanced"></slot>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import Vue from 'vue';
import { reduce, isEqual } from 'lodash';

export default {
  props: {
    index: Number,
    field: Object,
    original: Object,
    errors: Object
  },
  data: function() {
    return {
      showAdvanced: false
    }
  },
  created() {
    this.validateName();
  },
  computed: {
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    hasChanges: function () {
      return !isEqual(this.field, this.original);
    },
    ...mapState(['fields'])
  },
  methods: {
    /**
     * Validates the field name
     */
    validateName() {
      let error = (!this.field.name || !this.isAlnum('name') || this.otherFieldNames().includes(this.field.name));
      this.$set(this.errors, 'name', error);
    },
    /**
     * @return {Array} list of names for all the field except this
     */
    otherFieldNames: function () {
      let _this = this;
      return this.fields.filter((field) => field.order != this.field.order).map((field) => field.name);
    },
    isAlnum(field) {
      return this.field[field].match(/^[0-9a-zA-Z-_]*$/);
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
      this.$emit('error', {index: this.index, error: this.hasErrors});
    },
    hasChanges: function () {
      this.$emit('change', {index: this.index, change: this.hasChanges});
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