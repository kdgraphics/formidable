<template>
	<div id="formidable-rules" v-show="activeTab == 'rules'" class="flex">
    <div class="flex sidebar" id="formidablerules-sidebar">
      <div id="registered-rules-btn" class="btn menubtn add icon">{{ t('Add rule') }}</div>
      <div class="menu">
        <ul>
          <li><a v-for="registered in registeredRules" :key="registered.type" @click.prevent="addRule(registered.type)" href="#">{{ registered.name }}</a></li>
        </ul>
      </div>
      <nav>
        <ul>
          <li v-for="(rule, index) in rules" :key="'' + form.id + index" :class="{'sel': index == selectedRule}" :data-index="index">
            <a href="#" @click.prevent="setSelectedRule(index)" class="rule-link">
              <div class="top">
                <div class="name">{{ rule.name }}</div>
                <div v-show="changes[index] && !errors[index]" class="red-span">*</div>
                <div v-show="errors[index]" class="red-span">!</div>
              </div>
              <div class="fields">{{ rule.fields.join(', ') }}</div>
              <div class="conditions" v-if="countConditions(index) > 0">{{ countConditions(index) }}{{ ' '+t('condition' + (countConditions(index) > 1 ? 's' : '')) }}</div>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div id="formidablerules-container">
      <forms-rule v-for="(rule, index) in rules" v-show="index == selectedRule" v-on:error="onError" v-on:change="onChange"
        :class="rule.type+'-rule form-rule'" 
        :key="'' + form.id + index"
        :index="index"
        :rule="rule"
        :original="originals[index]"
        >
      </forms-rule>
      <div class="no-rules" v-if="rules.length == 0">{{ t('No rules found') }}</div>
      <div class="actions" v-show="selectedRule != null">
        <a class="delete" @click.prevent="deleteRule()">{{ t('Delete') }}</a>
        <a @click.prevent="duplicateRule()">{{ t('Duplicate') }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { isEqual, cloneDeep, reduce, mapValues } from 'lodash';
import { EventBus } from '../EventBus';

export default {
	props: {
		allRules: Array,
		registeredRules: Object
	},
  computed: {
    /**
     * @return {Boolean} global change status
     */
    hasChanges: function () {
      return (reduce(this.changes, function(result, value, key) {
        return result || value
      }, false) || this.deletedRules > 0 || this.addedRules > 0);
    },
    /**
     * @return {Boolean} global error status
     */
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    ...mapState(['activeTab', 'rules', 'form', 'loadedTime'])
  },
  data: function () {
    return {
      /**
       * @type {Integer}
       */
      selectedRule: null,
      deletedRules: 0,
      addedRules: 0,
      /**
       * @type {Object} Keyed by rule index
       */
      errors: {},
      /**
       * @type {Object} Keyed by rule index
       */
      changes: {},
      /**
       * @type {Object} Keyed by rule index
       */
      originals: {}
    }
  },
  created() {
    this.setRules(this.allRules);
    this.setRegisteredRules(this.registeredRules);
    this.init();
  },
  watch: {
    loadedTime: function() {
      this.init();
    },
    hasErrors: function () {
      this.rulesHaveErrors(this.hasErrors);
    },
    hasChanges: function () {
      this.handleRulesChanged();
    }
  },
  methods: {
    /**
     * (re)initialise the rules
     */
    init () {
      this.errors = {};
      this.changes = {};
      this.originals = cloneDeep(this.rules);
      this.deletedRules = 0;
      this.addedRules = 0;
      this.selectedRule = this.rules.length ? 0 : null;
    },
    countConditions(index) {
      let count = this.rules[index].conditions.length;
      for (let key in this.rules[index].conditionGroups) {
        let group = this.rules[index].conditionGroups[key];
        count = count + group.conditions.length;
      }
      return count;
    },
    addRule(type) {
      this._addRule(cloneDeep(this.registeredRules[type]));
    },
    duplicateRule() {
      this._addRule(cloneDeep(this.rules[this.selectedRule]));
    },
  	_addRule(rule) {
      rule.id = null;
      let index = this.rules.length;
      this.rules.push(rule);
      this.addedRules++;
      this.selectedRule = index;
  	},
  	deleteRule(index) {
      if (this.rules[this.selectedRule].id) {
        this.deletedRules++;
      } else {
        this.addedRules--;
      }
  		this.$delete(this.rules, this.selectedRule);
      this.$delete(this.errors, this.selectedRule);
      this.$delete(this.changes, this.selectedRule);
      this.selectedRule = this.rules.length ? 0 : null;
  	},
    setSelectedFields(data) {
      this.selectedFields[data.index] = data.fields.map((field) => field.map).join(', ');
    },
    setSelectedRule(index) {
      this.selectedRule = index;
    },
    handleRulesChanged() {
      let change = (this.deletedRules > 0 || this.addedRules > 0 || this.hasChanges);
      this.rulesHaveChanged(change);
    },
    onError(data) {
      this.$set(this.errors, data.index, data.error);
    },
    onChange(data) {
      this.$set(this.changes, data.index, data.change);
    },
    ...mapMutations({
      setRules: 'setRules',
      setConditionTypes: 'setConditionTypes',
      setRegisteredRules: 'setRegisteredRules',
    }),
  	...mapActions({
      rulesHaveChanged: 'rulesHaveChanged',
      rulesHaveErrors: 'rulesHaveErrors'
  	})
  }
};
</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

#formidablerules-sidebar {
  width: calc(30% - 10px);
  background-color: white;
  padding: 0;
  margin-bottom: 0;
  font-size: 14px;
  nav {
    padding-left: 0;
    width: 100%;
    margin-bottom: 0;
  }
  li {
    border: 1px solid $grey200;
    box-shadow: inset 0 1px 4px -1px $grey200;
    background-color: #fbfcfe;
    border-radius: 3px;
    margin-bottom: 5px;
    &:last-child {
      margin:0;
    }
    &.sel {
      background-color: #e4edf6
    }
  }
  .rule-link {
    display: flex;
    flex-wrap: wrap;
    padding: 7px 10px;
    .top {
      display: flex;
    }
    &>div {
      width: 100%;
    }
    .fields {
      margin-bottom: 5px;
    }
    .fields, .conditions {
      font-size: 13px;
      line-height: 1;
    }
    .name {
      flex-grow: 1
    }
  }
  .name {
    font-weight: bold;
  }
}

#formidable-rules {
  align-items: flex-start;
}

#formidablerules-container {
  width: 70%;
  .multiselect {
    height: 37px;
    min-height: unset;
    border: 1px solid $grey200;
    box-shadow: inset 0 1px 4px -1px $grey200;
    background-color: #fbfcfe;
    border-radius: 3px;
  }
  .multiselect__tags {
    padding-top: 4px;
    padding-bottom: 4px;
    min-height: unset;
    height: 37px;
    background-color: unset;
  }
  .multiselect__content {
    font-size: 14px;
  }
  .multiselect__tag {
    background-color: $grey200;
    color: $textColor;
    padding-top: 6px;
    padding-bottom: 6px;
  }
  .multiselect__tag-icon:focus, .multiselect__tag-icon:hover {
    background-color: $primaryColor;
  }
  .multiselect__tag-icon::after {
    color: $primaryColor;
  }
  .multiselect__tag-icon:focus::after, .multiselect__tag-icon:hover::after {
    color: white;
  }
  .multiselect__option--selected.multiselect__option--highlight {
    background-color: $grey200;
    color: $textColor;
  }
  .multiselect__option--highlight {
    background-color: $grey200;
    color: $textColor;
  }
  .rule-message {
    margin-bottom: 10px !important;
  }
}
</style>