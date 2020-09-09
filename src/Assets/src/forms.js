import Vue from 'vue';

import FormsMenu from './components/FormsMenu.vue';
import Forms from './components/Forms.vue';
import FormsConfig from './components/FormsConfig.vue';
import FormsFields from './components/FormsFields.vue';
import FormsField from './components/FormsField.vue';
import FormsRules from './components/FormsRules.vue';
import FormsSpam from './components/FormsSpam.vue';
import FormsSubmissions from './components/FormsSubmissions.vue';
import FormsToolbar from './components/FormsToolbar.vue';
import FormsTabs from './components/FormsTabs.vue';
import ConfirmDialog from './components/ConfirmDialog.vue';
import FormsStore from './store/FormsStore';
import VueConfirmDialog from 'vue-confirm-dialog';

Vue.use(VueConfirmDialog);

Vue.mixin({
  methods: {
    t: (str, params) => {
      return Craft.t('inspireforms', str, params);
    }
  }
});

for (const [type, component] of Object.entries(window.formFields)) {
  Vue.component('field-' + type, component);
}

Vue.component('forms-field', FormsField);

new Vue({
  el: '#main',
  store: FormsStore,
  components: {
    FormsMenu,
    Forms,
    FormsConfig,
    FormsFields,
    FormsRules,
    FormsSpam,
    FormsSubmissions,
    FormsToolbar,
    FormsTabs,
    ConfirmDialog,
  }
});
