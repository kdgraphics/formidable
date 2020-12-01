<template>
  <div class="form-condition no-shrink">
    <a href="#" class="delete icon" @click.prevent="$emit('delete')" :title="t('Delete')"></a>
    <div class="operator">
      <a href="#" v-if="index != 0" @click="changeOperator">{{ capitalize(condition.operator) }}</a>
    </div>
    <div class="negate no-shrink" v-if="condition.negate">
      <a href="#" @click.prevent="negate">{{ t('not') }}</a>
    </div>
    <div class="select no-shrink" :class="{'error' : errors.field}">
      <select v-model="selectedField">
        <option v-for="field in fields" :value="field">{{ field.name }}</option>
      </select>
    </div>
    <div class="select type no-shrink" :class="{'error' : errors.type}">
      <select v-model="selectedType">
        <option v-for="item in types" :value="item">{{ item.conditionName }}</option>
      </select>
    </div>
    <div class="value flex-grow" v-if="selectedType && selectedType.requiresValue" :class="{'error' : errors.value}">
      <input v-model="condition.value" :placeholder="t('Value')" size="">
    </div>
    <div class="value select no-shrink" v-if="selectedType && selectedType.requiresField" :class="{'error' : errors.value}">
      <select v-model="condition.value">
        <option v-for="field in fields" :value="field.name">{{ field.name }}</option>
      </select>
    </div>
    <a class="icon-negate no-shrink" href="#" v-if="!condition.negate" @click.prevent="negate"></a>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { isEqual, cloneDeep, reduce, find, has } from 'lodash';

export default {
  props: {
    condition: Object,
    index: Number
  },
  data() {
    return {
      errors: {},
      field: null,
      rule: null
    }
  },
  computed: {
    /**
     * @return {Boolean} global error status
     */
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    selectedType: {
      get: function() {
        return this.rule;
      },
      set: function(rule) {
        this.rule = rule;
        if (!rule) {
          this.$set(this.condition, 'type', null);
        } else {
          this.condition.type = rule.type;
        }
      }
    },
    types: function() {
      if (!this.selectedField) {
        return [];
      }
      return this.selectedField.validRules.map((type) => this.registeredRules[type]);
    },
    selectedField: {
      get: function () {
        return this.field;
      },
      set: function (field) {
        this.field = field;
        if (!field) {
          this.selectedType = null;
          this.$set(this.condition, 'value', null);
          this.$set(this.condition, 'field', null);
          return;
        }
        this.$set(this.condition, 'field', field.handle);
      }
    },
    ...mapState(['fields', 'registeredRules'])
  },
  created() {
    this.selectedField = find(this.fields, (field) => (field.handle == this.condition.field));
    this.selectedType = find(this.registeredRules, (rule) => (rule.type == this.condition.type));
    this.validate();
  },
  watch: {
    hasErrors: function () {
      this.$emit('error', {index: this.index, error: this.hasErrors});
    },
    fields: {
      handler: function () {
        let field = find(this.fields, (field) => this.selectedField ? field.handle == this.selectedField.handle : null);
        this.selectedField = field;
        this.validate();
      },
      deep: true
    },
    condition: {
      handler: function () {
        this.validate();
      },
      deep: true
    }
  },
  methods: {
    validate() {
      this.errors = {
        field: !this.condition.field,
        type: !this.selectedType,
        value: this.validateValue()
      };
    },
    validateValue() {
      if (this.selectedType && (this.selectedType.requiresField || this.selectedType.requiredValue) && !this.condition.value) {
        return true;
      }
      return false;
    },
    changeOperator(){
      this.$set(this.condition, 'operator', this.condition.operator == 'and' ? 'or' : 'and');
    },
    negate() {
      this.$set(this.condition, 'negate', !this.condition.negate);
    },
  }
};
</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

.form-condition, .form-condition-group {
  margin-bottom: 10px;
  display: flex;
  &:last-child {
    margin-bottom: 0;
  }
  .negate, .operator, .icon-negate, .type, .select, .value {
    line-height: 24px;
    margin-left: 5px;
  }
  .delete, .icon-negate {
    font-size: 13px;
    line-height: 26px;
  }
  .delete:before {
    margin-top: -4px;
  }
}

.form-condition {
  .select {
    &.error {
      border: 1px solid $red400;
    }
    select {
      padding: 3px 22px 3px 5px !important;
    }
  }
  .flex-grow {
    flex-grow: 1;
  }
  .value {
    input {
      width: 100%;
      box-sizing: border-box;
    }
  }
  .no-shrink {
    flex-shrink: 0;
  }
}
</style>