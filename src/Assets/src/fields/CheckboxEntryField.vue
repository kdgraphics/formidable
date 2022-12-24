<template>
  <forms-field
    :has-placeholder="false"
  >
    <template #extraFields>
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