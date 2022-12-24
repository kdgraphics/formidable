<template>
  <div class="form-condition no-shrink">
    <div class="inner">
      <a
        href="#"
        class="delete icon"
        :title="t('Delete')"
        @click.prevent="$emit('delete')"
      />
      <div
        v-if="!isFirst"
        class="operator"
      >
        <a
          href="#"
          @click="changeOperator"
        >{{ capitalize(condition.operator) }}</a>
      </div>
      <div
        v-if="condition.negate"
        class="negate no-shrink"
      >
        <a
          href="#"
          @click.prevent="negate"
        >{{ t('not') }}</a>
      </div>
      <div
        class="select no-shrink"
      >
        <select v-model="selectedField">
          <option
            v-for="field2, index in allFields"
            :key="index"
            :value="field2"
          >
            {{ field2.name }}
          </option>
        </select>
      </div>
      <div
        class="select type no-shrink"
      >
        <select v-model="condition.type">
          <option
            v-for="item, index in types"
            :key="index"
            :value="item.type"
          >
            {{ item.conditionName }}
          </option>
        </select>
      </div>
      <div
        v-if="selectedType && selectedType.requiresValue"
        class="value flex-grow"
      >
        <input
          v-model="condition.value"
          :placeholder="t('Value')"
          size=""
        >
      </div>
      <div
        v-if="selectedType && selectedType.requiresField"
        class="value select no-shrink"
      >
        <select v-model="condition.value">
          <option
            v-for="field2, index in allFields"
            :key="index"
            :value="field2"
          >
            {{ field2.name }}
          </option>
        </select>
      </div>
      <a
        v-if="!condition.negate"
        class="icon-negate no-shrink"
        href="#"
        @click.prevent="negate"
      />
    </div>
    <ul
      v-if="errors.field || errors.value"
      class="errors"
    >
      <li
        v-for="error, index in errors.field"
        :key="index"
        class="error"
      >
        {{ error }}
      </li>
      <li
        v-for="error, index in errors.value"
        :key="index"
        class="error"
      >
        {{ error }}
      </li>
    </ul>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { isEqual, cloneDeep, reduce, find, has, capitalize } from 'lodash';

export default {
  props: {
    uid: {
      type: String,
      default: ''
    },
    group: {
      type: Object,
      default: null
    }
  },
  emits: ['delete'],
  data() {
    return {
      rule: null,
      field: null
    }
  },
  computed: {
    errors: function () {
      if (!this.condition.errors) {
        return {};
      }
      return this.condition.errors;
    },
    isFirst: function () {
      let condition;
      if (this.group) {
        condition = this.group.conditions[0];
      } else {
        condition = this.selectedRule.conditions[0];
      }
      return condition.uid == this.condition.uid;
    },
    condition: function () {
      if (this.group) {
        return this.group.conditions.filter(c => c.uid == this.uid)[0];
      }
      return this.selectedRule.conditions.filter(c => c.uid == this.uid)[0];
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
    selectedType: function () {
      if (!this.condition.type) {
        return null;
      }
      return find(this.registeredRules, (rule) => (rule.type == this.condition.type));
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
          this.condition.value = null;
          this.condition.field = null;
          return;
        }
        this.condition.field = field.handle;
      }
    },
    ...mapState(['fields', 'registeredRules', 'selectedRule'])
  },
  created() {
    this.selectedField = find(this.allFields, (field) => (field.handle == this.condition.field));
  },
  methods: {
    changeOperator(){
      this.condition.operator = this.condition.operator == 'and' ? 'or' : 'and';
    },
    negate() {
      this.condition.negate = !this.condition.negate;
    },
    capitalize(str) {
      return capitalize(str);
    }
  }
};
</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

.form-condition {
  margin-bottom: 10px;
  .inner {
    display: flex;
  }
  &:last-child {
    margin-bottom: 0;
  }
  .select {
    max-width: 33%;
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