<template>
  <div
    v-show="activeTab == 'rules'"
    id="formidable-rules"
    class="flex"
  >
    <div
      id="formidablerules-sidebar"
      class="flex sidebar"
    >
      <div
        id="registered-rules-btn"
        class="btn menubtn add icon"
      >
        {{ t('Add rule') }}
      </div>
      <div class="menu">
        <ul>
          <li>
            <a
              v-for="registered in registeredRules"
              :key="registered.type"
              href="#"
              @click.prevent="_addRule(registered.type)"
            >{{ registered.name }}</a>
          </li>
        </ul>
      </div>
      <nav class="rules-nav">
        <ul>
          <li
            v-for="(rule, index) in rules"
            :key="index"
            :class="{'sel': selectedRule && rule.uid == selectedRule.uid}"
          >
            <a
              href="#"
              class="rule-link"
              @click.prevent="setSelectedRule(rule)"
            >
              <div class="top">
                <div class="name">{{ getRuleName(rule) }}</div>
              </div>
              <div class="fields">{{ getRuleSelectedFields(rule.fields) }}</div>
              <div
                v-if="countConditions(index) > 0"
                class="conditions"
              >{{ countConditions(index) }}{{ ' '+t('condition' + (countConditions(index) > 1 ? 's' : '')) }}</div>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div id="formidablerules-container">
      <forms-rule
        v-if="selectedRule"
        :class="selectedRule.type+'-rule form-rule'"
      />
      <div
        v-if="rules.length == 0"
        class="no-rules"
      >
        {{ t('No rules found') }}
      </div>
      <div
        v-show="selectedRule != null"
        class="actions"
      >
        <a
          class="delete"
          @click.prevent="_deleteRule()"
        >{{ t('Delete') }}</a>&nbsp;
        <a @click.prevent="duplicateRule()">{{ t('Duplicate') }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { isEqual, cloneDeep, reduce, mapValues, merge } from 'lodash';
import { v4 as uuidv4 } from 'uuid';
import FormsRule from './FormsRule.vue';

export default {
  components: { FormsRule },
	props: {
		registeredRules: {
      type: Object,
      default: () => {return {};}
    }
	},
  data: function () {
    return {
      changes: {}
    }
  },
  computed: {
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
    ...mapState(['activeTab', 'rules', 'form', 'loadedTime', 'fields', 'selectedRule'])
  },
  created() {
    this.setRegisteredRules(this.registeredRules);
  },
  methods: {
    getRuleName(rule) {
      if (rule.title) {
        return rule.title + ' (' + rule.name + ')';
      }
      return rule.name;
    },
    getRuleSelectedFields(fields) {
      if (!fields) {
        return [];
      }
      return this.allFields.filter((field) => fields.includes(field.handle)).map((field) => field.name).join(', ');
    },
    countConditions(index) {
      let count = this.rules[index].conditions.length;
      for (let key in this.rules[index].conditionGroups) {
        let group = this.rules[index].conditionGroups[key];
        count = count + group.conditions.length;
      }
      return count;
    },
    _addRule(type) {
      let rule = merge({}, this.registeredRules[type]);
      rule.id = null;
      rule.uid = uuidv4();
      this.addRule(rule);
      this.setSelectedRule(rule);
    },
    duplicateRule() {
      let rule = merge({}, this.selectedRule);
      rule.uid = uuidv4();
      rule.conditions = [];
      for (let i in this.selectedRule.conditions) {
        let condition = merge({}, this.selectedRule.conditions[i]);
        rule.conditions.push(condition);
      }
      this.addRule(rule);
      this.setSelectedRule(rule);
    },
  	_deleteRule() {
      this.deleteRule(this.selectedRule);
      if (this.rules.length) {
        this.setSelectedRule(this.rules[0]);
      } else {
        this.setSelectedRule(null);
      }
  	},
    ...mapMutations(['setRules', 'setConditionTypes','setRegisteredRules', 'setSelectedRule', 'addRule', 'deleteRule'])
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
    max-height: calc(100vh - 300px);
    overflow-y: auto;
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
  .form-rule {
    width: calc(100% - 2px);
  }
}

#formidablerules-container {
  width: 70%;
  max-height: calc(100vh - 260px);
  padding: 0 3px;
  margin-bottom: 0 !important;
  overflow-y: auto;
  overflow-x: visible;
  .multiselect {
    min-height: unset;
    border: 1px solid $grey200;
    border-radius: 3px;
    &.multiselect--active {
      box-shadow: var(--focus-ring);
    }
  }
  .multiselect__placeholder {
    margin-bottom: 5px;
    margin-top: 5px;
  }

  .multiselect__content-wrapper {
    top: 38px;
  }
  .multiselect__tags {
    padding-top: 4px;
    padding-bottom: 0px;
    min-height: 34px;
    border: none;
    background-color: unset;
  }
  .multiselect__content {
    font-size: 14px;
  }
  .multiselect__select {
    height: 34px;
  }
  .multiselect__tag {
    background-color: $grey200;
    color: $textColor;
    padding-top: 6px;
    padding-bottom: 6px;
    margin-bottom: 0;
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