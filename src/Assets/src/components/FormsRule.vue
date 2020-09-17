<template>
  <div>
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
        <condition v-for="(condition, index) in rule.conditions" v-on:delete="deleteCondition(index)" v-on:error="onConditionError"
          :key="'condition-'+index" 
          :index="index" 
          :condition="condition"
        />
        <condition-group v-for="(group, index) in rule.conditionGroups" v-on:delete="deleteGroup(index)" v-on:error="onGroupError"
          :key="'group-'+index" 
          :has-conditions="rule.conditions.length > 0" 
          :index="index" 
          :group="group"
        />
        <p v-if="rule.conditions.length == 0 && rule.conditionGroups.length == 0">{{ t('No conditions') }}</p>
      </div>
    </div>
    <div class="rule-message field">
      <div class="heading">
        <label>{{ t('Error message')}}</label>
      </div>
      <div class="instructions">{{ t('Message to display when this rule doesn\'t pass. Every field parameters are accessible with the shortcut {field.parameter}. Use {value} for the posted value.') }}</div>
      <div class="input ltr">
        <input class="text fullwidth" v-model="rule.message" :placeholder="rule.defaultMessage">
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import Vue from 'vue';
import { isEqual, cloneDeep, reduce, filter, intersectionBy } from 'lodash';
import { EventBus } from '../EventBus';

export default {
  props: {
    rule: Object,
    index: Number,
    original: Object
  },
  data() {
    return {
      selectedFields:{},
      errors: {rule: {}, conditions: {}, groups: {}}
    }
  },
  computed: {
    /**
     * @return {Boolean} global error status
     */
    hasErrors: function () {
      return reduce(this.errors.conditions, function(result, value, key) {
        return (result || value);
      }, false) || reduce(this.errors.groups, function(result, value, key) {
        return (result || value);
      }, false) || reduce(this.errors.rule, function(result, value, key) {
        return (result || value);
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
    hasErrors: function () {
      this.$emit('error', {index: this.index, error: this.hasErrors});
    },
    'rule.fields'(){
      this.validate();
    },
    'rule.value'(){
      this.validate();
    },
    rule: {
      handler: function () {
        this.$emit('change', {index: this.index, change: !isEqual(this.rule, this.original)});
      },
      deep: true
    },
    fields: {
      handler: function () {
        let fields = intersectionBy(this.selectedFields, this.fields, 'order').map((field) => field.name);
        if (!isEqual(fields.sort(), this.rule.fields.sort())) {
          this.rule.fields = fields;
        }
      },
      deep: true
    }
  },
  created () {
    this.selectedFields = filter(this.fields, (field) => this.rule.fields.includes(field.name));
    this.validate();
  },
  methods: {
    validate() {
      let errors = {
        value: (this.rule.requiresValue && !this.rule.value),
        fields: this.rule.fields.length == 0
      };
      this.$set(this.errors, 'rule', errors);
    },
    addCondition() {
      this.rule.conditions.push(this._newCondition());
    },
    addGroup() {
      this.rule.conditionGroups.push({
        negate: false, 
        operator: 'and', 
        conditions: [this._newCondition()]
      });
    },
    deleteCondition(index) {
      this.$delete(this.rule.conditions, index);
      this.$delete(this.errors, 'conditions', index);
    },
    deleteGroup(index) {
      this.$delete(this.rule.conditionGroups, index);
      this.$delete(this.errors, 'groups', index);
    },
    _newCondition() {
      return {
        type: 'present', 
        negate: false, 
        value: '', operator: 
        'and', field: 
        this.fields[0].name ?? ''
      };
    },
    onConditionError(data) {
      this.$set(this.errors.conditions, data.index, data.error);
    },
    onGroupError(data) {
      this.$set(this.errors.groups, data.index, data.error);
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
  .multiselect {
    border-color: $red400 !important;
  }
}

.rule-value {
  margin-top: 15px;
}
</style>