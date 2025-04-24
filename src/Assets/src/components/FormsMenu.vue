<template>
  <nav id="forms-nav">
    <ul>
      <li v-for="label, id in allForms">
        <a :href="getAdminUrl('formidable/forms/' + id)" :class="(id == form.id) ? 'sel' : ''" @click.prevent="load(id)">{{ label }}</a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  props: {
    allForms: Object
  },
  computed:{
    ...mapState(['form'])
  },
  created() {
    this.setAllForms(this.allForms);
  },
  methods: {
    load (id) {
      this.$confirm({
        message: this.t('You will loose unsaved changes, continue anyway ?'),
        button: {
          no: this.t('No'),
          yes: this.t('Yes')
        },
        callback: confirm => {
          if (confirm) {
            this.loadForm(id);
          }
        }
      });
    },
    getAdminUrl(url) {
      return Craft.getUrl(url);
    },
    ...mapMutations(['setIsAdding', 'setAllForms']),
    ...mapActions(['loadForm'])
  }
};
</script>
