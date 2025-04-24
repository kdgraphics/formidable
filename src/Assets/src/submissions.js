import Vue from 'vue';

import FormsSubmissions from './components/FormsSubmissions.vue';
import FormsSubmissionsToolbar from './components/FormsSubmissionsToolbar.vue';
import SubmissionsStore from './store/SubmissionsStore';
import VueConfirmDialog from 'vue-confirm-dialog';

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
  store: SubmissionsStore,
  components: {
    FormsSubmissions,
    FormsSubmissionsToolbar
  }
});
