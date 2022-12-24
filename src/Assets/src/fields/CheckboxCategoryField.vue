<template>
  <forms-field :has-placeholder="false">
    <template #extraFields>
      <div class="field">
        <div class="heading">
          <label>{{ t('Category groups') }}</label>
        </div>
        <div class="select fullwidth">
          <select
            v-model="selectedField.groups"
            multiple
          >
            <option
              v-for="name, uid in categories"
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
    ...mapState(['selectedField']),
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