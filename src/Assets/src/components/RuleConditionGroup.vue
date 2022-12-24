<template>
  <div class="form-condition-group">
    <div class="left-box">
      <div class="top-box">
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
          >{{ capitalize(group.operator) }}</a>
        </div>
        <div
          v-if="group.negate"
          class="negate"
        >
          <a
            href="#"
            @click.prevent="negate"
          >{{ t('not') }}</a>
        </div>
      </div>
      <a
        v-if="!group.negate"
        class="icon-negate"
        href="#"
        @click.prevent="negate"
      />
    </div>
    <div class="group-condition-list">
      <rule-condition
        v-for="(condition, index) in group.conditions"
        :key="index"
        :group="group"
        :uid="condition.uid"
        @delete="deleteCondition(condition.uid)"
      />
      <a
        href="#"
        @click.prevent="addCondition"
      >{{ t('Add condition') }}</a>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { capitalize } from 'lodash';
import { v4 as uuidv4 } from 'uuid';
import RuleCondition from './RuleCondition.vue';

export default {
  components: {RuleCondition},
  props: {
    uid: {
      type: String,
      default: ''
    }
  },
  emits: ['delete'],
  computed: {
    isFirst: function () {
      if (this.selectedRule.conditions.length) {
        return false;
      }
      let group = this.selectedRule.conditionGroups[0];
      return group.uid == this.group.uid;
    },
    group: function () {
      return this.selectedRule.conditionGroups.filter(c => c.uid == this.uid)[0];
    },
    ...mapState(['fields', 'selectedField', 'selectedRule'])
  },
  methods: {
    addCondition() {
      this.group.conditions.push({
        type: 'required', 
        negate: false, 
        value: '', 
        operator: 'and', 
        field: this.fields.length ? this.fields[0].handle : '',
        uid: uuidv4()
      });
    },
    changeOperator(){
      this.group.operator = this.group.operator == 'and' ? 'or' : 'and';
    },
    negate() {
      this.group.negate = !this.group.negate;
    },
    deleteCondition(uid) {
      this.group.conditions = this.group.conditions.filter(r => r.uid != uid);
    },
    capitalize(str) {
      return capitalize(str);
    }
  }
};
</script>

<style lang="scss" scoped="true">
@import "~craftcms-sass/mixins";

.form-condition-group {
  display: flex;
  margin-bottom: 10px;
  &:last-child {
    margin-bottom: 0;
  }
  .group-condition-list {
    border-left: 1px solid $grey200;
    padding-left: 5px;
    margin-left: 5px;
    flex-grow: 1;
  }
  .negate, .operator, .icon-negate, .type, .select, .value {
    line-height: 24px;
    margin-left: 5px;
  }
  .actions {
    display: flex;
    flex-wrap: wrap;
    font-size: 13px;
  }
  .top-box {
    display: flex;
    width: 67px;
  }
  .left-box{
    display: flex;
    flex-direction: column;
    width: 41px;
    .icon-negate {
      margin-left: 0;
    }
  }
}
</style>