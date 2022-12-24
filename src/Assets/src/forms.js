import { createApp } from 'vue';

import FormsTabs from './components/FormsTabs.vue';
import FormsMenu from './components/FormsMenu.vue';
import FormsConfig from './components/FormsConfig.vue';
import FormsFields from './components/FormsFields.vue';
import FormsSpam from './components/FormsSpam.vue';
import FormsRules from './components/FormsRules.vue';
import FormsToolbar from './components/FormsToolbar.vue';
import { store } from './store/FormsStore';
import { capitalize } from 'lodash';
import { Functions } from './functions';

const app = createApp();

app.use(Functions);
app.use(store);

app.component('forms-rules', FormsRules);
app.component('forms-toolbar', FormsToolbar);
app.component('forms-menu', FormsMenu);
app.component('forms-tabs', FormsTabs);
app.component('forms-config', FormsConfig);
app.component('forms-fields', FormsFields);

app.mount('#main');