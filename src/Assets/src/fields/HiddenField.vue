<template>
  <forms-field
    v-bind="$attrs"
    :has-instructions="false"
    :has-tooltip="false"
    :has-label="false"
    :has-placeholder="false"
    :has-container-class="false"
  >
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
import slugify from 'slugify';
import FormsField from '../components/FormsField.vue';

export default {
  components: {FormsField},
  computed: {
    ...mapState(['fields', 'selectedField'])
  }
};
</script>