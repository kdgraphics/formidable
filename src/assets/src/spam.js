import Vue from 'vue';

import FormsSpam from './components/FormsSpam.vue';
import FormsSpamToolbar from './components/FormsSpamToolbar.vue';
import SpamStore from './store/SpamStore';
import VueConfirmDialog from '@vue-confirm-dialog';

Vue.mixin({
  methods: {
    t: (str, params) => {
      return Craft.t('formidable', str, params);
    }
  }
});

Vue.use(VueConfirmDialog);

new Vue({
  el: '#main',
  store: SpamStore,
  components: {
    FormsSpam,
    FormsSpamToolbar
  }
});
