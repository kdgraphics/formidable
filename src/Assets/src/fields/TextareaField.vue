<template>
  <forms-field>
    <template #extraFields>
      <div class="field">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <textarea
            v-model="selectedField.default"
            class="text fullwidth"
          />
        </div>
      </div>
    </template>
    <template #extraAdvanced>
      <div class="field">
        <div class="heading">
          <label>{{ t('Rows') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.rows"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.rows"
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

export default {
  components: {FormsField},
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