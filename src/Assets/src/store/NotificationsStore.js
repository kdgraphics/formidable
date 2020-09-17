import Vuex from 'vuex';
import Vue from 'vue';
import {cloneDeep} from 'lodash';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    isLoading: false,
    isAdding: true,
    notification: {},
    notifications: [],
    hasUnsavedChanges: false,
    hasErrors: false,
    loadedTime: 0,
    counter: 0,
    original: {}
  },
  mutations: {
    setIsLoading (state, value) {
      state.isLoading = value;
    },
    setNotifications (state, value) {
      state.notifications = value;
    },
    setHasUnsavedChanges (state, value) {
      state.hasUnsavedChanges = value;
    },
    setNotification(state, value) {
      if (value) {
        state.isAdding = false;
        state.notification = state.notifications[value];
      } else {
        state.isAdding = true;
        state.notification = {
          name: '',
          method: 'html',
          html: '',
          template: '',
          subject: '',
          sentFrom: ''
        }
      }
      state.original = cloneDeep(state.notification);
      state.counter = state.counter + 1;
    },
    setHasErrors(state, value) {
      state.hasErrors = value;
    },
    setLoadedTime(state, value) {
      state.loadedTime = value;
    }
  }
});
