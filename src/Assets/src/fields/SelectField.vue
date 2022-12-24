<template>
  <forms-field
    :has-placeholder="false"
  >
    <template #extraFields>
      <div class="lightswitch-field field">
        <div class="heading">
          <label>{{ t('Multiple') }}</label>
        </div>
        <div class="input ltr">
          <div
            :class="'lightswitch ' + (selectedField.multiple ? 'on' : '')"
            tabindex="0"
            role="switch"
            :aria-checked="selectedField.multiple"
          >
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.multiple"
              type="hidden"
              name="multiple"
            >
          </div>
        </div>
      </div>
      <choices
        :default="t('Select')"
      />
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
    ...mapState(['selectedField']),
  }
};
</script>