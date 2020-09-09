import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    isAdding: false,
    isDeleting: false,
    isEditing: false,
    volumes: {},
    form: {},
    originalForm: {},
    fields: {},
    forms: [],
    notifications: [],
    captchas: [],
    deletedFields: 0,
    addedFields: 0,
    fieldsStatuses: {},
    configHasChanged: false,
    fieldsHaveChanged: false,
    rulesHaveChanged: false,
    errors: {},
    selectedTab: null,
    selectedField: null,
    registeredFields: {}
  },
  getter: {
    hasUnsavedChanges: state => {
      return state.rulesHaveChanged || state.fieldsHaveChanged || state.configHasChanged
    }
  },
  mutations: {
    setIsAdding (state, value) {
      state.isAdding = value;
    },
    setIsEditing (state, value) {
      state.isEditing = value;
    },
    setIsDeleting (state, value) {
      state.isDeleting = value;
    },
    setForms (state, value) {
      state.forms = value;
    },
    setNotifications (state, value) {
      state.notifications = value;
    },
    setCaptchas (state, value) {
      state.captchas = value;
    },
    setErrors (state, value) {
      state.errors = value;
    },
    setVolumes (state, value) {
      state.volumes = value;
    },
    replaceForm (state, value) {
      state.forms[value.id] = value;
    },
    removeForm (state, value) {
      delete state.forms[value];
    },
    setSelectedTab(state, value) {
      state.selectedTab = value;
    },
    replaceForm(state, value) {
      state.forms[value.id] = value;
    },
    setSelectedField(state, value) {
      state.selectedField = value;
    },
    setRegisteredFields(state, value) {
      state.registeredFields = value;
    },
    setConfigHasChanged(state, value) {
      state.configHasChanged = value;
    },
    setFieldsHaveChanged(state, value) {
      state.fieldsHaveChanged = value;
    },
    setForm(state, id) {
      state.fields = state.forms[id].fields;
      delete state.forms[id].fields;
      state.form = state.forms[id];
      state.originalForm = {...state.forms[id]};
      state.errors = {};
      state.isAdding = false;
      state.isEditing = true;
      state.selectedTab = 'config';
    },
    changeFieldStatus(state, value) {
      state.fieldsStatuses[value.index] = value.status;
    },
    addNewField(state, type) {
      let field = {...state.registeredFields[type]};
      state.fields.push(field);
      state.selectedField = state.fields.length - 1;
      state.addedFields++;
    },
    deleteField(state, value) {
      if (state.fields[state.selectedField].id) {
        state.deletedFields++;
      } else {
        state.addedFields--;
      }
      delete state.fields[state.selectedField];
      state.fields = state.fields.filter(val => val);
    },
    duplicateField(state, value) {
      let field = {...state.fields[state.selectedField]};
      state.fields.push(field);
      state.addedFields++;
    },
  },
  actions: {
    changeFieldStatus({commit, dispatch}, value) {
      commit('changeFieldStatus', value);
      dispatch('calculateFieldsChangedStatus');
    },
    addField({commit, dispatch}, type) {
      commit('addNewField', type);
      dispatch('calculateFieldsChangedStatus');
    },
    deleteField({commit, dispatch}) {
      commit('deleteField');
      dispatch('calculateFieldsChangedStatus');
    },
    duplicateField({commit, dispatch}) {
      commit('duplicateField');
      dispatch('calculateFieldsChangedStatus');
    },
    calculateFieldsChangedStatus({commit, state}) {
      if (state.addedFields || state.deletedFields) {
        commit('setFieldsHaveChanged', true);
        return;
      }
      for(let index in state.fieldsStatuses) {
        if(state.fieldsStatuses[index]) {
          commit('setFieldsHaveChanged', true);
          return;
        }
      }
      commit('setFieldsHaveChanged', false);
    }
  }
});
