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
          <label>{{ t('Category') }}</label>
        </div>
        <div class="select fullwidth">
          <select
            v-model="selectedField.groups"
            multiple
          >
            <option
              v-for="name, id in categories"
              :key="id"
              :value="id"
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
      categories: []
    }
  },
  computed: {
    errors: function () {
      if (!this.selectedField.errors) {
        return {};
      }
      return this.selectedField.errors;
    },
    fields: function () {
      if (!this.selectedField.category) {
        return {};
      }
      return this.allFields[this.selectedField.category];
    },
    ...mapState(['selectedField'])
  },
  created() {
    let _this = this;
    if (typeof window.selectCategoryFieldCategories == 'undefined') {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      window.selectCategoryFieldCategories = axios.post(Craft.getActionUrl('formidable/cp-utilities/category-groups'), data)
      .catch((error) => {
        console.log(error);
      })
    }
    window.selectCategoryFieldCategories.then(function (res) {
      _this.categories = res.data.groups;
    })
  }
};
</script>