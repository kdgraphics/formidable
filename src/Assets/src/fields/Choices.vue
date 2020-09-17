<template>
  <div>
    <div class="heading">
      <label>{{ t('Choices') }}<a class="add-choice" href="#" @click.prevent="addChoice('', '')">{{ t('Add') }}</a></label>
    </div>
    <div class="list text">
      <ul>
        <li v-for="(choice, index) in field.choices" :key="index" class="choice">
          <div class="move-choice">
            <div class="up" @click="moveChoice(index,index-1)" v-if="index!=0"></div>
            <div class="down" @click="moveChoice(index,index+1)" v-if="index!=(field.choices.length-1)"></div>
          </div>
          <input class="value choice-elem" :placeholder="t('Value')" :required="field.choices.length == 1" v-model="choice.value">
          <input class="label choice-elem" :placeholder="t('Label')" :required="field.choices.length == 1" v-model="choice.label">
          <div class="delete-container">
            <a class="delete-choice" @click.prevent="deleteChoice(index)">{{ t('Delete') }}</a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import { isEqual } from 'lodash';

export default {
  props: {
    field: Object,
    default: String
  },
  created() {
    if (!this.field.choices) {
      this.field.choices = [];
      this.addChoice('', this.default);
    }
  },
  methods: {
    addChoice(value = '', label = '') {
      this.field.choices.push({value:value, label:label});
    },
    deleteChoice(id) {
      if (this.field.choices.length == 1) {
        return;
      }
      this.$delete(this.field.choices, id);
    },
    moveChoice(from, to) {
      this.field.choices.splice(to, 0, this.field.choices.splice(from, 1)[0]);
    }
  }
};
</script>
<style lang="scss">
.inspirefield-container {
  .choices {
    width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    input {
      border: 1px solid #e4edf6;
      padding:5px;
      width: calc(50% - 45px);
    }
    .add-choice {
      display: inline-block;
      margin-left: 10px;
      font-weight: normal;
    }
    .list {
      li:nth-child(1) {
        .delete-choice {
          display:none;
        }
      }
    }
    .delete-container {
      padding-left: 10px;
      width: 50px;
      line-height: 30px;
    }
    .choice {
      display: flex;
    }
    .move-choice {
      padding-top: 10px;
      width: 40px;
      .up,.down {
        width: 0;
        height: 0;
        float:left;
        cursor: pointer;
        transition: all 0.3s;
      }
      .up {
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 12px solid #e4edf6;
        &:hover {
          border-bottom-color: darken(#e4edf6, 10%);
        }
      }
      .down {
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 12px solid #e4edf6;
        &:hover {
          border-top-color: darken(#e4edf6, 10%);
        }
      }
    }
  }
}
</style>