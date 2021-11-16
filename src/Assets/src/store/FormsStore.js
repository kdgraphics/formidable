import Vuex from 'vuex';
import Vue from 'vue';
import { unset, keys } from 'lodash';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    isAdding: false,
    isLoading: false,
    form: {},
    fields: [],
    rules: [],
    registeredRules: [],
    fieldNames: [],
    configHasChanged: false,
    fieldsHaveChanged: false,
    rulesHaveChanged: false,
    hasUnsavedChanges: false,
    activeTab: null,
    fieldsHaveErrors: false,
    rulesHaveErrors: false,
    configHasErrors: false,
    hasErrors: false,
    allForms: {},
    loadedTime: 0,
    volumes: {}
  },
  mutations: {
    setIsLoading(state, value) {
      state.isSaving = value;
    },
    setHasUnsavedChanges(state, value) {
      state.hasUnsavedChanges = value;
    },
    setIsAdding (state, value) {
      state.isAdding = value;
    },
    setConfigHasChanged(state, value) {
      state.configHasChanged = value;
    },
    setFieldsHaveChanged(state, value) {
      state.fieldsHaveChanged = value;
    },
    setRulesHaveChanged(state, value) {
      state.rulesHaveChanged = value;
    },
    setFields(state, value) {
      state.fields = value;
    },
    setRules(state, value) {
      state.rules = value;
    },
    setRegisteredRules(state, value) {
      state.registeredRules = value;
    },
    setForm(state, value) {
      state.form = value;
    },
    setActiveTab(state, value) {
      state.activeTab = value;
    },
    setRulesHaveErrors(state, value) {
      state.rulesHaveErrors = value;
    },
    setFieldsHaveErrors(state, value) {
      state.fieldsHaveErrors = value;
    },
    setConfigHasErrors(state, value) {
      state.configHasErrors = value;
    },
    setHasErrors(state, value) {
      state.hasErrors = value;
    },
    setAllForms(state, value) {
      state.allForms = value;
    },
    setLoadedTime(state, value) {
      state.loadedTime = value;
    },
    setVolumes(state, value) {
      state.volumes = value;
    }
  },
  actions: {
    configHasErrors({commit, dispatch, state}, value) {
      commit('setConfigHasErrors', value);
      dispatch('checkIfErrors');
    },
    fieldsHaveErrors({commit, dispatch, state}, value) {
      commit('setFieldsHaveErrors', value);
      dispatch('checkIfErrors');
    },
    rulesHaveErrors({commit, dispatch, state}, value) {
      commit('setRulesHaveErrors', value);
      dispatch('checkIfErrors');
    },
    configHasChanged({commit, dispatch, state}, value) {
      commit('setConfigHasChanged', value);
      dispatch('checkIfChanges');
    },
    fieldsHaveChanged({commit, dispatch, state}, value) {
      commit('setFieldsHaveChanged', value);
      dispatch('checkIfChanges');
    },
    rulesHaveChanged({commit, dispatch, state}, value) {
      commit('setRulesHaveChanged', value);
      dispatch('checkIfChanges');
    },
    checkIfChanges({commit, state}) {
      commit('setHasUnsavedChanges', state.rulesHaveChanged || state.fieldsHaveChanged || state.configHasChanged);
    },
    checkIfErrors({commit, state}) {
      commit('setHasErrors', state.rulesHaveErrors || state.fieldsHaveErrors || state.configHasErrors);
    },
    loadForm({commit, state, dispatch}, id) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      commit('setIsLoading', false);
      axios.post(Craft.getCpUrl('formidable/forms/get/'+id), data)
        .then((response) => {
          if (response.data.success) {
            commit('setIsAdding', false);
            dispatch('setHistory', [id]);
            dispatch('resetForm', response.data);
          } else {
            Craft.cp.displayError(response.data.message);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          commit('setIsLoading', false);
        });
    },
    saveForm({commit, state, dispatch}) {
      let data = {form: state.form, fields: state.fields, rules: state.rules, loaded: state.loadedTime};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      commit('setIsLoading', true);
      axios.post(Craft.getCpUrl('formidable/forms/save'), data)
        .then((response) => {
          if (response.data.success) {
            Craft.cp.displayNotice(response.data.message);
            if (state.isAdding) {
              commit('setIsAdding', false);
              dispatch('setHistory', [response.data.form.id]);
              let forms = state.allForms;
              forms[response.data.form.id] = response.data.form.name;
              commit('setAllForms', forms);
            }
            dispatch('resetForm', response.data);
          } else {
            Craft.cp.displayError(response.data.message);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          commit('setIsLoading', false);
        });
    },
    newForm({commit, state, dispatch}) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      commit('setIsLoading', true);
      dispatch('setHistory', ['add']);
      axios.post(Craft.getCpUrl('formidable/forms/new'), data)
        .then((response) => {
          if (response.data.success) {
            commit('setIsAdding', true);
            dispatch('resetForm', response.data);
          } else {
            Craft.cp.displayError(response.data.message);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          commit('setIsLoading', false);
        });
    },
    deleteForm({commit, state, dispatch}) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      commit('setIsLoading', true);
      axios.post(Craft.getCpUrl('formidable/forms/delete/'+state.form.id), data)
        .then((response) => {
          if (response.data.success) {
            let forms = state.allForms;
            unset(forms, state.form.id);
            commit('setAllForms', forms);
            let ids = keys(forms);
            if (ids.length) {
              dispatch('loadForm', ids[0]);
            } else {
              dispatch('newForm');
            }
            Craft.cp.displayNotice(response.data.message);

          } else {
            Craft.cp.displayError(response.data.message);
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    resetForm({commit, state}, data) {
      console.log(data);
      commit('setForm', data.form);
      commit('setFields', data.fields);
      commit('setRules', data.rules);
      commit('setLoadedTime', data.loaded);
    },
    setHistory({}, parts) {
      let url = document.location.pathname.split('/');
      url[3] = 'forms';
      url[4] = parts[0];
      if (parts[1] ?? false) {
        url[5] = parts[1];
      }
      window.history.pushState({}, '', url.join('/'));
    }
  }
});
