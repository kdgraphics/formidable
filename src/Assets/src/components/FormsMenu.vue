<template>
  <nav id="forms-nav">
    <ul>
      <li
        v-for="label, id in allForms"
        :key="id"
      >
        <a
          :href="getAdminUrl('formidable/forms/' + id)"
          :class="(id == form.id) ? 'sel' : ''"
          @click.prevent="load(id)"
        >{{ label }}</a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { isEqual } from 'lodash';

export default {
  props: {
    allForms: {
      type: Object,
      default: () => {return {};}
    }
  },
  computed:{
    hasChanges: function () {
      return !isEqual(this.form, this.originalForm) || !isEqual(this.fields, this.originalFields) || !isEqual(this.rules, this.originalRules);
    },
    ...mapState(['form', 'originalForm', 'rules', 'originalRules', 'fields', 'originalFields'])
  },
  created() {
    this.setAllForms(this.allForms);
  },
  methods: {
    load (id) {
      if (!this.hasChanges) {
        this.loadForm(id);
      } else if (confirm(this.t('You will loose unsaved changes, continue anyway ?'))) {
        this.loadForm(id);
      }
    },
    getAdminUrl(url) {
      return Craft.getUrl(url);
    },
    ...mapMutations(['setIsAdding', 'setAllForms']),
    ...mapActions(['loadForm'])
  }
};
</script>
