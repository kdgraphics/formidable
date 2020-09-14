import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    isAdding: false,
    isDeleting: false,
    isSaving: false,
    form: {},
    fields: {},
    rules: {},
    fieldNames: [],
    configHasChanged: false,
    fieldsHaveChanged: false,
    rulesHaveChanged: false,
    hasUnsavedChanges: false,
    activeMenu: null,
    conditionTypes: {},
    fieldsHaveErrors: false,
    rulesHaveErrors: false,
    configHasErrors: false,
    hasErrors: false
  },
  mutations: {
    setIsSaving(state, value) {
      if (value) {
        state.errors = {};
      }
      state.isSaving = value;
    },
    setHasUnsavedChanges(state, value) {
      state.hasUnsavedChanges = value;
    },
    setIsAdding (state, value) {
      state.isAdding = value;
    },
    setIsDeleting (state, value) {
      state.isDeleting = value;
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
    setForm(state, value) {
      state.form = value;
    },
    setActiveMenu(state, value) {
      state.activeMenu = value;
    },
    setConditionTypes(state, value) {
      state.conditionTypes = value;
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
    setFieldNames(state, value) {
      state.fieldNames = value;
    },
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
    resetChanges({dispatch, state, commit}) {
      commit('setConfigHasChanged', false);
      commit('setRulesHaveChanged', false);
      commit('setFieldsHaveChanged', false);
      commit('setHasUnsavedChanges', false);
    }
  }
});
