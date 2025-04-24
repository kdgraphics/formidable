<template>
  <div class="form-condition-group">
    <div class="left-box">
      <div class="top-box">
        <a href="#" class="delete icon" @click.prevent="$emit('delete')" :title="t('Delete')"></a>
        <div class="operator">
          <a href="#" v-if="hasConditions || index > 0" @click="changeOperator">{{ capitalize(group.operator) }}</a>
        </div>
        <div class="negate" v-if="group.negate">
          <a href="#" @click.prevent="negate">{{ t('not') }}</a>
        </div>
      </div>
      <a class="icon-negate" href="#" v-if="!group.negate" @click.prevent="negate"></a>
    </div>
    <div class="group-condition-list">
      <condition v-for="(condition, index) in group.conditions" v-on:delete="deleteCondition(index)" v-on:error="onError"
        :key="index" 
        :condition="condition" 
        :index="index"
      />
      <a href="#" @click.prevent="addCondition">{{ t('Add condition') }}</a>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { reduce } from 'lodash';

export default {
  props: {
    group: Object,
    index: Number,
    hasConditions: Boolean,
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
    ...mapState(['fields'])
  },
  data() {
    return {
      errors: {},
    }
  },
  watch: {
    hasErrors: function () {
      this.$emit('error', {index: this.index, error: this.hasErrors});
    }
  },
  methods: {
    addCondition() {
      this.group.conditions.push({
        type: 'present', 
        negate: false, 
        value: '', 
        operator: 'and', 
        field: this.fields[0].name ?? ''
      });
    },
    changeOperator(){
      this.group.operator = this.group.operator == 'and' ? 'or' : 'and';
    },
    negate() {
      this.group.negate = !this.group.negate;
    },
    deleteCondition(index) {
      this.$delete(this.group.conditions, index);
      this.$delete(this.errors, index);
    },
    onError(data) {
      this.$set(this.errors, data.index, data.error);
    }
  }
};
</script>

<style lang="scss" scoped="true">
@import "~craftcms-sass/mixins";

.form-condition-group {
  .group-condition-list {
    border-left: 1px solid $grey200;
    padding-left: 5px;
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
    .icon-negate {
      margin-left: 0;
    }
  }
}
</style>