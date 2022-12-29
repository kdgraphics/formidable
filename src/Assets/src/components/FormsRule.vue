<template>
  <div>
    <div class="rule-fields">
      <div class="field">
        <div class="heading">
          <label>{{ t('Title') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedRule.title"
            class="text fullwidth"
          >
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Fields') }}</label>
        </div>
        <div class="instructions">
          {{ t('Fields to which this rule apply') }}
        </div>
        <div class="input ltr">
          <multiselect
            v-model="selectedFields"
            :options="validFields"
            track-by="handle"
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
        <ul class="errors">
          <li
            v-for="error, index in errors.fields"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
    </div>
    <div
      v-if="selectedRule.requiresValue"
      class="rule-value"
    >
      <div class="field">
        <div class="heading">
          <label>{{ t('Value') }}</label>
        </div>
        <div class="instructions">
          {{ t('Value to compare against') }}
        </div>
        <div class="input ltr">
          <input
            v-model="selectedRule.value"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.value"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
    </div>
    <div
      v-if="selectedRule.requiresField"
      class="rule-value"
    >
      <div class="field">
        <div class="heading">
          <label>{{ t('Field') }}</label>
        </div>
        <div class="instructions">
          {{ t('Field to compare against') }}
        </div>
        <div class="select">
          <select
            v-model="selectedRule.value"
            class="'text fullwidth"
          >
            <option
              v-for="field, index in allFields"
              :key="index"
              :value="field.handle"
            >
              {{ field.name }}
            </option>
          </select>
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.value"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
    </div>
    <div class="rule-conditions">
      <div class="field">
        <div class="heading">
          <label>{{ t('Conditions') }}</label>
          <div class="actions">
            <a
              href="#"
              @click.prevent="addCondition"
            >{{ t('Add condition') }}</a>&nbsp;
            <a
              href="#"
              @click.prevent="addGroup"
            >{{ t('Add group') }}</a>
          </div>
        </div>
        <div class="instructions">
          {{ t('Conditions for this rule to apply') }}
        </div>
      </div>
      <div class="conditions-list">
        <rule-condition
          v-for="(condition, index) in selectedRule.conditions"
          :key="index"
          :uid="condition.uid"
          @delete="deleteCondition(condition.uid)"
        />
        <rule-condition-group
          v-for="(group, index) in selectedRule.conditionGroups"
          :key="index"
          :uid="group.uid"
          @delete="deleteGroup(group.uid)"
        />
        <p v-if="selectedRule.conditions.length == 0 && selectedRule.conditionGroups.length == 0">
          {{ t('No conditions') }}
        </p>
      </div>
    </div>
    <div class="rule-message field">
      <div class="heading">
        <label>{{ t('Error message') }}</label>
      </div>
      <div class="instructions">
        {{ t('Messages to display when this rule doesn\'t pass. Every field parameters are accessible with the shortcut {field.parameter}. Use {value} for the posted value.') }}<br>{{ t('Default message') }}
      </div>
      <div class="input ltr">
        <input
          v-model="selectedRule.message"
          class="text fullwidth"
          :placeholder="selectedRule.defaultMessage"
        >
      </div>
    </div>
    <div
      v-for="field in selectedFields"
      :key="field.handle"
      class="rule-messages field"
    >
      <div class="instructions">
        {{ t('Message for {field}', {field: field.name}) }}
      </div>
      <div class="input ltr">
        <input
          class="text fullwidth"
          :placeholder="selectedRule.message"
          :value="selectedRule.messages[field.handle] ?? ''"
          @keyup="changeMessage(field.handle, $event)"
        >
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import Vue from 'vue';
import { isEqual, cloneDeep, reduce, filter, intersection } from 'lodash';
import { v4 as uuidv4 } from 'uuid';
import Multiselect from 'vue-multiselect';
import RuleCondition from './RuleCondition.vue';
import RuleConditionGroup from './RuleConditionGroup.vue';

export default {
  components: {Multiselect, RuleCondition, RuleConditionGroup},
  computed: {
    errors: function () {
      if (!this.selectedRule.errors) {
        return {};
      }
      return this.selectedRule.errors;
    },
    selectedFields: {
      get: function () {
        return filter(this.allFields, (field) => this.selectedRule.fields.includes(field.handle));
      },
      set: function (value) {
        this.selectedRule.fields = value.map((field) => field.handle);
      }
    },
    allFields: function () {
      let fields = [];
      for(let field of this.fields) {
        if (field.type == 'group') {
          for(let subfield of field.fields) {
            fields.push(subfield);
          }
        } else {
          fields.push(field);
        }
      }
      return fields;
    },
    validFields: function () {
      return this.allFields.filter((field) => field.validRules.includes(this.selectedRule.type));
    },
    ...mapState(['fields', 'selectedRule']),
  },
  methods: {
    changeMessage(field, event) {
      if (Array.isArray(this.selectedRule.messages)) {
        this.selectedRule.messages = {};
      }
      this.selectedRule.messages[field] = event.target.value;
    },
    addCondition() {
      this.selectedRule.conditions.push(this._newCondition());
    },
    addGroup() {
      this.selectedRule.conditionGroups.push({
        negate: false, 
        operator: 'and', 
        uid: uuidv4(),
        conditions: [this._newCondition()]
      });
    },
    deleteCondition(uid) {
      this.selectedRule.conditions = this.selectedRule.conditions.filter(r => r.uid != uid);
    },
    deleteGroup(uid) {
      this.selectedRule.conditionGroups = this.selectedRule.conditionGroups.filter(r => r.uid != uid);
    },
    _newCondition() {
      return {
        type: 'required', 
        negate: false, 
        uid: uuidv4(),
        value: '',
        operator: 'and', 
        field: this.allFields.length ? this.allFields[0].handle : ''
      };
    }
  }
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>

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
    padding: 5px;
    border-radius: 3px;
  }
}

.rule-messages {
  margin: 5px 0;
  &:last-child {
    margin-bottom: 10px !important;
  }
}

.rule-value {
  margin-top: 15px;
}
</style>