<template>
  <forms-field
    :has-placeholder="false"
  >
    <template #extraFields>
      <div class="field">
        <div class="heading">
          <label>{{ t('Options type') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select
              v-model="selectedField.optionsType"
              class="text fullwidth"
            >
              <option value="custom">
                {{ t('Custom') }}
              </option>
              <option value="entries">
                {{ t('Entries') }}
              </option>
              <option value="categories">
                {{ t('Categories') }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <custom-options
        v-if="selectedField.optionsType == 'custom'"
        :default="''"
      />
      <div
        v-if="selectedField.optionsType == 'categories'"
        class="field"
      >
        <div class="heading">
          <label>{{ t('Category groups') }}</label>
        </div>
        <div class="select fullwidth">
          <select
            v-model="selectedField.groupUids"
            multiple
          >
            <option
              v-for="name, uid in groups"
              :key="uid"
              :value="uid"
            >
              {{ name }}
            </option>
          </select>
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.groups"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div
        v-if="selectedField.optionsType == 'entries'"
        class="field"
      >
        <div class="heading">
          <label>{{ t('Sections') }}</label>
        </div>
        <div class="select fullwidth">
          <select
            v-model="selectedField.sectionUids"
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
          <label>{{ t('Option container class') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.optionContainerClass"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.optionContainerClass"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Option label class') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.optionLabelClass"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.optionLabelClass"
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
import CustomOptions from './CustomOptions.vue';

export default {
  components: {FormsField, CustomOptions},
  data() {
    return {
      sections: [],
      groups: []
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
    });
    if (typeof window.selectCategoryFieldCategories == 'undefined') {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      window.selectCategoryFieldCategories = axios.post(Craft.getActionUrl('formidable/cp-utilities/category-groups'), data)
      .catch((error) => {
        console.log(error);
      })
    }
    window.selectCategoryFieldCategories.then(function (res) {
      _this.groups = res.data.groups;
    });
  }
};
</script>