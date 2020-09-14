<template>
    <component :is="'field-'+field.type" :field="field" :index="index">
      <template v-slot:name>
        <div class="field first">
          <div class="heading">
            <label class="required" :for="'field-'+field.index+'-name'">{{ t('Name') }}</label>
          </div>
          <div class="input ltr">
            <input type="text" :id="'field-'+field.index+'-name'" :class="'text fullwidth' + (errors.name ? ' error' : '')" v-model="field.name" required="required">
          </div>
        </div>
      </template>
      <template v-slot:instructions>
        <div class="field field-instructions">
          <div class="heading">
            <label :for="'field-'+field.index+'-instructions'">{{ t('Instructions') }}</label>
          </div>
          <div class="input ltr">
            <input type="text" class="text fullwidth" :id="'field-'+field.index+'-instructions'" v-model="field.instructions">
          </div>
        </div>
      </template>
      <template v-slot:advanced>
        <div class="advanced">
          <div class="header" @click="showAdvanced = !showAdvanced" :class="{'closed': !showAdvanced}">{{ t('Advanced') }}</div>
          <div class="content" v-show="showAdvanced">
            <div class="field field-fieldClass">
              <div class="heading">
                <label>{{ t('Field class') }}</label>
              </div>
              <div class="input ltr">
                <input type="text" class="text fullwidth" v-model="field.fieldClass">
              </div>
            </div>
            <div class="field field-containerClass">
              <div class="heading">
                <label>{{ t('Container class') }}</label>
              </div>
              <div class="input ltr">
                <input type="text" class="text fullwidth" v-model="field.containerClass">
              </div>
            </div>
            <div class="field field-labelClass">
              <div class="heading">
                <label>{{ t('Label class') }}</label>
              </div>
              <div class="input ltr">
                <input type="text" class="text fullwidth" v-model="field.labelClass">
              </div>
            </div>
          </div>
        </div>
      </template>
    </component>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import Vue from 'vue';
import { isEqual, cloneDeep, reduce } from 'lodash';

export default {
  props: {
    index: Number,
    field: Object
  },
  data: function () {
    return {
      showAdvanced: false,
      original: {},
      errors: {}
    }
  },
  computed: {
    ...mapState(['fields']),
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    otherFieldNames: function () {
      let _this = this;
      return this.fields.filter(function (field) {
        return field.order != _this.field.order;
      }).map((field) => field.name);
    }
  },
  watch: {
    field: {
      handler: function () {
        this.validate();
        this.$emit('changed', {index: this.index, value: !isEqual(this.field, this.original)});
      },
      deep: true
    },
    hasErrors: function () {
      this.$emit('errors', {index: this.index, value: this.hasErrors});
    }
  },
  created() {
    this.original = cloneDeep(this.field);
    this.validate();
  },
  methods: {
    /**
     * Validate field's values
     */
    validate() {
      this.errors = {
        name: (!this.field.name || this.otherFieldNames.includes(this.field.name))
      };
    }
  }
};
</script>

<style lang="scss" scoped>
@import "~craftcms-sass/mixins";

.inspirefield-container {
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