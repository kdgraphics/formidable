<template>
  <div class="field custom-options">
    <div class="heading">
      <label>{{ t('Custom options') }}<a
        class="add-option"
        href="#"
        @click.prevent="addOption('', '')"
      >{{ t('Add') }}</a></label>
    </div>
    <div class="list text">
      <ul>
        <li
          v-for="(option, index) in selectedField.customOptions"
          :key="index"
          class="option"
        >
          <div class="move-option">
            <div
              v-if="index != 0"
              class="up"
              @click="moveOption(index,index-1)"
            />
            <div
              v-if="index != (selectedField.customOptions.length-1)"
              class="down"
              @click="moveOption(index,index+1)"
            />
          </div>
          <input
            v-model="selectedField.customOptions[index].value"
            class="value option-elem"
            :placeholder="t('Value')"
            :required="selectedField.customOptions.length == 1"
            :class="(countOptions(index) > 1 || selectedField.customOptions[index].value.trim() !== selectedField.customOptions[index].value) ? 'error' : ''"
            @input="validate"
          >
          <input
            v-model="selectedField.customOptions[index].label"
            class="label option-elem"
            :placeholder="t('Label')"
            :required="selectedField.customOptions.length == 1"
            :class="selectedField.customOptions.length == 1 && !selectedField.customOptions[index].label ? 'error' : ''"
            @input="validate"
          >
          <div class="delete-container">
            <a
              class="delete-option"
              @click.prevent="deleteOption(index)"
            >{{ t('Delete') }}</a>
          </div>
        </li>
      </ul>
    </div>
    <ul
      v-if="selectedField.errors && selectedField.errors.customOptions"
      class="errors"
    >
      <li
        v-for="error, index in selectedField.errors.customOptions"
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
    if (!this.selectedField.customOptions.length) {
      this.addOption('', this.default);
    }
    this.validate();
  },
  methods: {
    countOptions(index) {
      let count = 0;
      let source = this.selectedField.customOptions[index];
      for (let option of this.selectedField.customOptions) {
        if (option.value == source.value) {
          count++;
        }
      }
      return count;
    },
    validate() {
      let error = false;
      let value;
      for (let index in this.selectedField.customOptions) {
        value = this.selectedField.customOptions[index].value;
        if (value.trim() !== value) {
          error = true;
          break;
        }
        if (this.countOptions(index) > 1) {
          error = true;
          break;
        }
      }
    },
    addOption(value = '', label = '') {
      this.selectedField.customOptions.push({value:value, label:label});
      this.validate();
    },
    deleteOption(id) {
      if (this.selectedField.customOptions.length == 1) {
        return;
      }
      this.selectedField.customOptions = this.selectedField.customOptions.filter((option, id2) => {
        return id != id2;
      });
    },
    moveOption(from, to) {
      this.selectedField.customOptions.splice(to, 0, this.selectedField.customOptions.splice(from, 1)[0]);
    }
  }
};
</script>
<style lang="scss">
@import "~craftcms-sass/mixins";

.formidablefield-container {
  .custom-options {
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
    .add-option {
      display: inline-block;
      margin-left: 10px;
      font-weight: normal;
    }
    .list {
      li:nth-child(1) {
        .delete-option {
          display:none;
        }
      }
    }
    .delete-container {
      padding-left: 10px;
      width: 60px;
      line-height: 30px;
    }
    .option {
      display: flex;
    }
    .move-option {
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