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
      <div class="field">
        <div class="heading">
          <label>{{ t('Sections') }}</label>
        </div>
        <div class="select fullwidth">
          <select
            v-model="selectedField.sections"
            multiple
          >
            <option
              v-for="name, uid in sections"
              :key="uid"
              :value="uid"
            >
              {{ name }}
            </option>
          </select>
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.sections"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label>{{ t('Include Select option') }}</label>
        </div>
        <div class="input ltr">
          <div
            :class="'lightswitch ' + (selectedField.includeSelect ? 'on' : '')"
            tabindex="0"
            role="switch"
            :aria-checked="selectedField.includeSelect"
          >
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.includeSelect"
              type="hidden"
              name="includeSelect"
            >
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Select Label') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.selectLabel"
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
  data() {
    return {
      sections: []
    }
  },
  computed: {
    errors: function () {
      if (!this.selectedField.errors) {
        return {};
      }
      return this.selectedField.errors;
    },
    ...mapState(['selectedField']),
  },
  created() {
    let _this = this;
    if (typeof window.selectEntryFieldSections == 'undefined') {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      window.selectEntryFieldSections = axios.post(Craft.getActionUrl('formidable/cp-utilities/sections'), data)
      .catch((error) => {
        console.log(error);
      })
    }
    window.selectEntryFieldSections.then(function (res) {
      _this.sections = res.data.sections;
    })
  }
};
</script>