<template>
    <component :is="'field-'+field.type" :field="field" :index="index">
      <template v-slot:name>
        <div class="field first">
          <div class="heading">
            <label class="required" :for="'field-'+field.index+'-name'">{{ t('Name') }}</label>
          </div>
          <div class="input ltr">
            <input type="text" :id="'field-'+field.index+'-name'" class="text fullwidth" v-model="field.name" required="required">
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

export default {
  props: {
    index: Number,
    field: Object
  },
  data: function () {
    return {
      showAdvanced: false,
      originalField: {...this.field},
      isNew: function () {
        return (typeof this.field.id != 'number');
      }
    }
  },
  watch: {
    field: {
      handler: function (field) {
        if (this.isNew()) {
          return;
        }
        for (const key in field) {
          if (field[key] == '' && typeof this.originalField[key] == 'undefined') {
            continue;
          }
          if (field[key] != this.originalField[key]) {
            this.changeFieldStatus({index: this.index, status: true});
            return;
          }
        }
        this.changeFieldStatus({index: this.index, status: false});
      },
      deep: true
    }
  },
  methods: {
    ...mapActions({
      changeFieldStatus: 'changeFieldStatus'
    })
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