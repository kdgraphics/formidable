import { createStore } from 'vuex'
import { unset, keys, filter, cloneDeep } from 'lodash';
import { v4 as uuidv4 } from 'uuid';

function duplicateField(field) {
  let newField = {...field};
  newField.id = null;
  newField.form_id = null;
  newField.group_id = null;
  newField.uid = uuidv4();
  if (newField.type == 'group') {
    let groupFields = [];
    for (let i in field.fields) {
      groupFields.push(duplicateField(field.fields[i]));
    }
    newField.fields = groupFields;
  }
  return newField;
}

function duplicateRule(rule) {
  let newRule = {...rule};
  newRule.id = null;
  newRule.form_id = null;
  newRule.uid = uuidv4();
  return newRule;
}

export const store = createStore({
  state: {
    isAdding: false,
    isLoading: false,
    form: {},
    originalForm: {},
    fields: [],
    originalFields: [],
    selectedField: null,
    rules: [],
    originalRules: [],
    registeredRules: [],
    selectedRule: null,
    activeTab: null,
    allForms: {},
    loadedTime: 0
  },
  mutations: {
    setIsLoading(state, value) {
      state.isSaving = value;
    },
    setIsAdding (state, value) {
      state.isAdding = value;
    },
    setFields(state, value) {
      state.fields = value;
    },
    setOriginalFields(state, value) {
      state.originalFields = cloneDeep(value);
    },
    setRules(state, value) {
      state.rules = value;
    },
    setOriginalRules(state, value) {
      state.originalRules = cloneDeep(value);
    },
    setRegisteredRules(state, value) {
      state.registeredRules = value;
    },
    setSelectedRule(state, value) {
      state.selectedRule = value;
    },
    addRule(state, rule) {
      state.rules.push(rule);
    },
    deleteRule(state, rule) {
      state.rules = state.rules.filter((r) => r.uid != rule.uid);
    },
    setForm(state, value) {
      state.form = value;
    },
    setOriginalForm(state, value) {
      state.originalForm = cloneDeep(value);
    },
    setActiveTab(state, value) {
      state.activeTab = value;
    },
    setAllForms(state, value) {
      state.allForms = value;
    },
    setLoadedTime(state, value) {
      state.loadedTime = value;
    },
    setSelectedField(state, field) {
      state.selectedField = field;
    },
    deleteField(state, field) {
      state.fields = state.fields.filter(f => f.uid != field.uid);
      for (let i in state.fields) {
        if (state.fields[i].type == 'group') {
          state.fields[i].fields = state.fields[i].fields.filter(f => f.uid != field.uid);
        }
      }
      let rule;
      for (let i in state.rules) {
        rule = state.rules[i];
        rule.fields = rule.fields.filter(f => f != field.handle);
        if (rule.requiresField && rule.value == field.handle) {
          rule.value = '';
        }
        for (let j in rule.conditions) {
          if (rule.conditions[j].field == field.handle) {
            rule.conditions[j].field = '';
          }
        }
        for (let j in rule.conditionGroups) {
          for (let k in rule.conditionGroups[j].conditions) {
            if (rule.conditionGroups[j].conditions[k].field == field.handle) {
              rule.conditionGroups[j].conditions[k].field = '';
            }
          }
        }
        if (state.form.spamCheck == field.handle) {
          state.form.spamCheck = '';
        }
        if (state.form.notifyField == field.handle) {
          state.form.notifyField = '';
        }
      }
    },
    addField(state, field) {
      state.fields.push(field);
    }
  },
  actions: {
    loadForm({commit, state, dispatch}, id) {
      let data = {
        id: id
      };
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      commit('setIsLoading', false);
      axios.post(Craft.getActionUrl('formidable/cp-forms/get'), data)
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
      axios.post(Craft.getActionUrl('formidable/cp-forms/save'), data)
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
            if (response.data.form) {
              response.data.resetOriginals = false;
              dispatch('resetForm', response.data);
            }
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
      axios.post(Craft.getActionUrl('formidable/cp-forms/new'), data)
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
    duplicateForm({commit, state, dispatch}) {
      let newForm = { ...state.form };
      newForm.uid = uuidv4();
      newForm.id = null;
      let newFields = [];
      for (let i in state.fields) {
        newFields.push(duplicateField(state.fields[i]));
      }
      let newRules = [];
      for (let i in state.rules) {
        newRules.push(duplicateRule(state.rules[i]));
      }
      commit('setIsAdding', true);
      dispatch('resetForm', {
        form: newForm,
        fields: newFields,
        rules: newRules,
        loaded: Math.floor(Date.now() / 1000)
      });
      Craft.cp.displayNotice('Form is duplicated, check your changes and save');
    },
    deleteForm({commit, state, dispatch}) {
      let data = {
        id: state.form.id
      };
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      commit('setIsLoading', true);
      axios.post(Craft.getActionUrl('formidable/cp-forms/delete'), data)
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
      commit('setForm', data.form);
      commit('setFields', data.fields);
      commit('setRules', data.rules);
      commit('setLoadedTime', data.loaded);
      if (data.resetOriginals ?? true) {
        commit('setOriginalForm', state.form);
        commit('setOriginalFields', state.fields);
        commit('setOriginalRules', state.rules);
      }
      if (state.selectedField) {
        let field;
        let found;
        outerloop:
        for (let i in data.fields) {
          field = data.fields[i];
          if (field.uid == state.selectedField.uid) {
            found = field;
            break;
          }
          if (field.type == 'group') {
            for (let j in field.fields) {
              if (field.fields[j].uid == state.selectedField.uid) {
                found = field.fields[j];
                break outerloop;
              }
            }
          }
        }
        if (found) {
          commit('setSelectedField', found);
        } else {
          commit('setSelectedField', null);
        }
      }
      if (state.selectedRule) {
        let rules = data.rules.filter(f => f.uid == state.selectedRule.uid);
        if (rules.length) {
          commit('setSelectedRule', rules[0]);
        } else {
          commit('setSelectedRule', null);
        }
      }
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
