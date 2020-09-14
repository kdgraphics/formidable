<template>
  <component :is="'rule-'+rule.type">
    <template v-slot:fields>
      <div :class="'rule-fields' + (errors.rule.fields ? ' error' : '')">
        <div class="field">
          <div class="heading">
            <label>{{ t('Fields') }}</label>
          </div>
          <div class="instructions">
            {{ t('Fields to which this rule apply') }}
          </div>
          <div class="input ltr">
            <multiselect v-model="multiselectedFields"
              :options="fields"
              track-by="name"
              label="name"
              :multiple="true" 
              :searchable="false" 
              :placeholder="t('Select some fields')"
              select-label=""
              :selected-label="t('Selected')"
              deselect-label=""
              :close-on-select="false"
            />
          </div>
        </div>
      </div>
    </template>
    <template v-slot:value>
      <div class="rule-value" v-if="rule.requiresValue">
        <div class="field">
          <div class="heading">
            <label>{{ t('Value') }}</label>
          </div>
          <div class="instructions">
            {{ t('Value to compare against') }}
          </div>
          <div class="input ltr">
            <input :class="'text fullwidth' + (errors.rule.value ? ' error' : '')" v-model="rule.value" name="value" type="number" step="0.1"/>
          </div>
        </div>
      </div>
    </template>
    <template v-slot:conditions>
      <div class="rule-conditions">
        <div class="field">
          <div class="heading">
            <label>{{ t('Conditions') }}</label>
            <div class="actions">
              <a href="#" @click.prevent="addCondition">{{ t('Add condition') }}</a>
              <a href="#" @click.prevent="addGroup">{{ t('Add group') }}</a>
            </div>
          </div>
          <div class="instructions">
            {{ t('Conditions for this rule to apply') }}
          </div>
        </div>
        <div class="conditions-list">
          <condition v-for="(condition, index) in rule.conditions" :key="'condition-'+index" :index="index" :condition="condition" v-on:delete="deleteCondition(index)" v-on:errors="setConditionError"/>
          <condition-group v-for="(group, index) in rule.conditionGroups" :key="'group-'+index" :has-conditions="rule.conditions.length > 0" :index="index" :group="group" v-on:delete="deleteGroup(index)"/>
          <p v-if="rule.conditions.length == 0 && rule.conditionGroups.length == 0">{{ t('No conditions') }}</p>
        </div>
      </div>
    </template>
    <template v-slot:message>
      <div class="rule-message field">
        <div class="heading">
          <label>{{ t('Error message')}}</label>
        </div>
        <div class="instructions">{{ t('Message to display when this rule doesn\'t pass. Every field parameters are accessible with the shortcut {field.parameter}. Use {value} for the posted value.') }}</div>
        <div class="input ltr">
          <input class="text fullwidth" v-model="rule.message" :placeholder="rule.defaultMessage">
        </div>
      </div>
    </template>
  </component>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import Vue from 'vue';
import { isEqual, cloneDeep, reduce, filter, intersectionBy } from 'lodash';

export default {
  props: {
    rule: Object,
    index: Number
  },
  data() {
    return {
      errors: {
        rule: {},
        conditionGroups: {},
        conditions: {},
      },
      original: {},
      selectedFields:{}
    }
  },
  computed: {
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || reduce(value, function(result2, value2, key) {
          return (result2 || value2);
        }, false));
      }, false);
    },
    multiselectedFields: {
      get: function () {
        return this.selectedFields;
      },
      set: function (value) {
        this.selectedFields = value;
        this.rule.fields = value.map((field) => field.name);
      }
    },
    ...mapState(['fields']),
  },
  watch: {
    fields: {
      handler: function () {
        let fields = intersectionBy(this.selectedFields, this.fields, 'order').map((field) => field.name);
        if (!isEqual(fields.sort(), this.rule.fields.sort())) {
          this.rule.fields = fields;
        }
      },
      deep: true
    },
    rule: {
      handler: function () {
        this.validate();
        this.$emit('changed', {index: this.index, value: !isEqual(this.rule, this.original)});
      },
      deep: true
    },
    hasErrors: function () {
      this.$emit('errors', {index: this.index, value: this.hasErrors});
    }
  },
  created () {
    this.original = cloneDeep(this.rule);
    this.selectedFields = filter(this.fields, (field) => this.rule.fields.includes(field.name));
    this.validate();
  },
  methods: {
    validate() {
      this.errors.rule = {
        value: (this.rule.requiresValue && !this.rule.value),
        fields: this.rule.fields.length == 0
      };
    },
    addCondition() {
      this.rule.conditions.push({type: 'present', negate: false, value: '', operator: 'and', field: this.fields[0].name ?? ''});
    },
    addGroup() {
      this.rule.conditionGroups.push({negate: false, operator: 'and', conditions: [{type: 'present', negate: false, value: '', operator: 'and', field: this.fields[0].name ?? ''}]});
    },
    deleteCondition(index) {
      this.$delete(this.rule.conditions, index);
    },
    deleteGroup(index) {
      this.$delete(this.rule.conditionGroups, index);
    },
    setConditionError (data) {
      this.$set(this.errors.conditions, data.index, data.value);
    }
  }
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style lang="scss">
@import "~craftcms-sass/mixins";

.rule-conditions {
  margin-top: 15px;
  .field {
    margin-bottom: 10px;
  }
  .heading {
    display: flex;
    justify-content: space-between
  }
  .conditions-list {
    border: 1px solid $grey200;
      box-shadow: inset 0 1px 4px -1px $grey200;
      background-color: #fbfcfe;
      padding: 10px;
      border-radius: 3px;
  }
}

.rule-fields.error {
  .multiselect__tags {
    border-color: $red400;
  }
}

.rule-value {
  margin-top: 15px;
}
</style>