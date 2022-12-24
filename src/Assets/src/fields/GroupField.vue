<template>
  <forms-field
    v-bind="$attrs"
    :has-placeholder="false"
    :has-field-class="false"
    :has-autocomplete="false"
  >
    <template #extraAdvanced>
      <div class="field">
        <div class="heading">
          <label>{{ t('Fields class') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.fieldClass"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
    </template>
  </forms-field>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { reduce, isEqual } from 'lodash';
import slugify from 'slugify';
import FormsField from '../components/FormsField.vue';

export default {
  components: {FormsField},
  computed: {
    ...mapState(['selectedField'])
  }
};
</script>