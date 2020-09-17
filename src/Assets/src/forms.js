import Vue from 'vue';

import FormsTabs from './components/FormsTabs.vue';
import FormsMenu from './components/FormsMenu.vue';
import FormsConfig from './components/FormsConfig.vue';
import FormsFields from './components/FormsFields.vue';
import FormsRules from './components/FormsRules.vue';
import FormsRule from './components/FormsRule.vue';
import FormsSpam from './components/FormsSpam.vue';
import FormsSubmissions from './components/FormsSubmissions.vue';
import FormsToolbar from './components/FormsToolbar.vue';
import ConfirmDialog from './components/ConfirmDialog.vue';
import RuleCondition from './components/RuleCondition.vue';
import RuleConditionGroup from './components/RuleConditionGroup.vue';
import Choices from './fields/Choices.vue';
import FormsStore from './store/FormsStore';
import VueConfirmDialog from 'vue-confirm-dialog';
import Multiselect from 'vue-multiselect';
import { capitalize, isEqual } from 'lodash';

Vue.mixin({
  methods: {
    t: (str, params) => {
      return Craft.t('inspireforms', str, params);
    },
    capitalize: (str) => {
      return capitalize(str);
    },
    changePageTitle: (str) => {
      $('#page-title h1').html(str);
    }
  }
});

for (const [type, component] of Object.entries(window.formFields)) {
  Vue.component('field-' + type, component);
}

Vue.use(VueConfirmDialog);
Vue.component('forms-rule', FormsRule);
Vue.component('field-choices', Choices);
Vue.component('multiselect', Multiselect);
Vue.component('condition', RuleCondition);
Vue.component('condition-group', RuleConditionGroup);

new Vue({
  el: '#main',
  store: FormsStore,
  components: {
    FormsTabs,
    FormsMenu,
    FormsConfig,
    FormsFields,
    FormsRules,
    FormsSpam,
    FormsSubmissions,
    FormsToolbar,
    ConfirmDialog,
  }
});
