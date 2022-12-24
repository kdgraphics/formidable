<template>
  <div class="field choices">
    <div class="heading">
      <label>{{ t('Choices') }}<a
        class="add-choice"
        href="#"
        @click.prevent="addChoice('', '')"
      >{{ t('Add') }}</a></label>
    </div>
    <div class="list text">
      <ul>
        <li
          v-for="(choice, index) in selectedField.choices"
          :key="index"
          class="choice"
        >
          <div class="move-choice">
            <div
              v-if="index != 0"
              class="up"
              @click="moveChoice(index,index-1)"
            />
            <div
              v-if="index != (selectedField.choices.length-1)"
              class="down"
              @click="moveChoice(index,index+1)"
            />
          </div>
          <input
            v-model="selectedField.choices[index].value"
            class="value choice-elem"
            :placeholder="t('Value')"
            :required="selectedField.choices.length == 1"
            :class="(countValues(index) > 1 || selectedField.choices[index].value.trim() !== selectedField.choices[index].value) ? 'error' : ''"
            @input="validate"
          >
          <input
            v-model="selectedField.choices[index].label"
            class="label choice-elem"
            :placeholder="t('Label')"
            :required="selectedField.choices.length == 1"
            :class="selectedField.choices.length == 1 && !selectedField.choices[index].label ? 'error' : ''"
            @input="validate"
          >
          <div class="delete-container">
            <a
              class="delete-choice"
              @click.prevent="deleteChoice(index)"
            >{{ t('Delete') }}</a>
          </div>
        </li>
      </ul>
    </div>
    <ul
      v-if="selectedField.errors && selectedField.errors.choices"
      class="errors"
    >
      <li
        v-for="error, index in selectedField.errors.choices"
        :key="index"
        class="error"
      >
        {{ error }}
      </li>
    </ul>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  props: {
    default: {
      type: String,
      default: ''
    }
  },
  computed: {
    ...mapState(['selectedField']),
  },
  created() {
    if (!this.selectedField.choices) {
      this.selectedField.choices = [];
      this.addChoice('', this.default);
    }
    this.validate();
  },
  methods: {
    countValues(index) {
      let count = 0;
      let source = this.selectedField.choices[index];
      for (let choice of this.selectedField.choices) {
        if (choice.value == source.value) {
          count++;
        }
      }
      return count;
    },
    validate() {
      let error = false;
      let value;
      for (let index in this.selectedField.choices) {
        value = this.selectedField.choices[index].value;
        if (value.trim() !== value) {
          error = true;
          break;
        }
        if (this.countValues(index) > 1) {
          error = true;
          break;
        }
      }
    },
    addChoice(value = '', label = '') {
      this.selectedField.choices.push({value:value, label:label});
      this.validate();
    },
    deleteChoice(id) {
      if (this.selectedField.choices.length == 1) {
        return;
      }
      this.selectedField.choices = this.selectedField.choices.filter((choice, id2) => {
        return id != id2;
      });
    },
    moveChoice(from, to) {
      this.selectedField.choices.splice(to, 0, this.selectedField.choices.splice(from, 1)[0]);
    }
  }
};
</script>
<style lang="scss">
@import "~craftcms-sass/mixins";

.formidablefield-container {
  .choices {
    width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    input {
      border: 1px solid #e4edf6;
      padding:5px;
      width: calc(50% - 50px);
      &.error {
        border: 1px solid $red400;
      }
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
      width: 60px;
      line-height: 30px;
    }
    .choice {
      display: flex;
    }
    .move-choice {
      padding-top: 10px;
      width: 25px;
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