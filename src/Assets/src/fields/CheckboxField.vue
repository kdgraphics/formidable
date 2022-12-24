<template>
  <forms-field
    :has-placeholder="false"
  >
    <template #extraFields>
      <div class="lightswitch-field field">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <div
            class="lightswitch on"
            tabindex="0"
            role="switch"
            :aria-checked="selectedField.default"
          >
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.default"
              type="hidden"
              name="default"
            >
          </div>
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