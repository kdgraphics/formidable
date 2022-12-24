import { createApp } from 'vue';

import FormsSpam from './components/FormsSpam.vue';
import FormsSpamToolbar from './components/FormsSpamToolbar.vue';
import { store } from './store/SpamStore';
import { Functions } from './functions';

const app = createApp();

app.use(Functions);
app.use(store);
app.component('forms-spam', FormsSpam);
app.component('forms-spam-toolbar', FormsSpamToolbar);

app.mount('#main');