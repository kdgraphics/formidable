import Vue from 'vue';

import NotificationsMenu from './components/NotificationsMenu.vue';
import Notifications from './components/Notifications.vue';
import ConfirmDialog from './components/ConfirmDialog.vue';
import NotificationsToolbar from './components/NotificationsToolbar.vue';
import NotificationsStore from './store/NotificationsStore.js';
import VueConfirmDialog from 'vue-confirm-dialog';

Vue.use(VueConfirmDialog);

Vue.mixin({
  methods: {
    t: (str, params) => {
      return Craft.t('inspireforms', str, params);
    }
  }
});

new Vue({
  el: '#main',
  store: NotificationsStore,
  components: {
    NotificationsMenu,
    NotificationsToolbar,
    Notifications,
    ConfirmDialog
  }
});
