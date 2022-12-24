<template>
  <forms-field
    v-bind="$attrs"
    :has-placeholder="false"
  >
    <template #extraFields>
      <choices
        :default="''"
      />
    </template>
    <template #extraAdvanced>
      <div class="field">
        <div class="heading">
          <label>{{ t('Choice container class') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.choiceContainerClass"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.choiceContainerClass"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Choice label class') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.choiceLabelClass"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.choiceLabelClass"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
    </template>
  </forms-field>
</template>

<script>
import { mapState } from 'vuex';
import FormsField from '../components/FormsField.vue';
import Choices from './Choices.vue';

export default {
  components: {FormsField, Choices},
  computed: {
    errors: function () {
      if (!this.selectedField.errors) {
        return {};
      }
      return this.selectedField.errors;
    },
    ...mapState(['selectedField']),
  }
};
</script>