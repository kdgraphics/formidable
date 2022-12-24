import { createApp } from 'vue';

import FormsSubmissions from './components/FormsSubmissions.vue';
import FormsSubmissionsToolbar from './components/FormsSubmissionsToolbar.vue';
import { store } from './store/SubmissionsStore';
import { Functions } from './functions';

const app = createApp();

app.use(Functions);
app.use(store);
app.component('forms-submissions', FormsSubmissions);
app.component('forms-submissions-toolbar', FormsSubmissionsToolbar);

app.mount('#main');
