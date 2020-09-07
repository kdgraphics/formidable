import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    isLoading: false,
    isAdding: false,
    isDeleting: false,
    isEditing: false,
    notification: {method: 'html'},
    original: {},
    notifications: [],
    hasUnsavedChanges: false,
    errors: {}
  },
  mutations: {
    setIsLoading (state, value) {
      state.isLoading = value;
    },
    setIsAdding (state, value) {
      state.isAdding = value;
    },
    setIsEditing (state, value) {
      state.isEditing = value;
    },
    setIsDeleting (state, value) {
      state.isDeleting = value;
    },
    setNotifications (state, value) {
      state.notifications = value;
    },
    setNotification (state, value) {
      state.original = {...value};
      state.notification = value;
    },
    setHasUnsavedChanges (state, value) {
      state.hasUnsavedChanges = value;
    },
    setErrors (state, value) {
      state.errors = value
    },
    replaceNotification (state, value) {
      state.notifications[value.id] = value;
    },
    removeNotification (state, value) {
      delete state.notifications[value];
    },
    addMode (state, value) {
      state.hasUnsavedChanges = false;
      state.errors = {};
      state.notification = {method: 'html'};
      state.original = {method: 'html'};
      state.isEditing = false;
      state.isAdding = true;
    },
    editMode (state, value) {
      state.hasUnsavedChanges = false;
      state.errors = {};
      state.notification = value;
      state.original = {...value};
      state.isEditing = true;
      state.isAdding = false;
    },
  }
});
