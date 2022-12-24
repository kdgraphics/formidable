<template>
  <forms-field>
    <template #extraFields>
      <div class="field">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.default"
            type="text"
            class="text fullwidth"
          >
        </div>
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
    ...mapState(['selectedField']),
  }
};
</script>