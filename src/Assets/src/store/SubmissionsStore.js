import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    selected: [],
    isChartView: false,
    forms: {},
    submissions: [],
    filters: {
      forms: [],
      trashed: false,
      read: null,
      dateFrom: {
        date: null,
        timezone: null
      },
      dateTo: {
        date: null,
        timezone: null
      }
    }
  },
  mutations: {
    setForms (state, value) {
      state.forms = value;
    },
    setTimezone(state, value) {
      Vue.set(state.filters.dateFrom, 'timezone', value);
      Vue.set(state.filters.dateTo, 'timezone', value);
    },
    setSelected (state, value) {
      state.selected = value;
    },
    setIsChartView (state, value) {
      state.isChartView = value;
    },
    setDateTo(state, value) {
      Vue.set(state.filters.dateTo, 'date', value);
    },
    setDateFrom(state, value) {
      Vue.set(state.filters.dateFrom, 'date', value);
    },
    setTrashed(state, value) {
      Vue.set(state.filters, 'trashed', value);
    },
    setRead(state, value) {
      Vue.set(state.filters, 'read', value);
    },
    addForm(state, value) {
      let forms = state.filters.forms;
      forms.push(value);
      Vue.set(state.filters, 'forms', forms);
    },
    removeForm(state, value) {
      let index = state.filters.forms.indexOf(value);
      Vue.delete(state.filters.forms, index);
    },
    setSubmissions(state, value) {
      state.submissions = value;
    },
    markSelectedAsRead(state, ids) {
      let sub;
      for (let index in state.submissions) {
        sub = state.submissions[index];
        if (state.selected.includes(sub.id)) {
          Vue.set(state.submissions[index], 'read', 1);
        }
      }
    },
    deleteSelected(state, ids) {
      let sub;
      for (let index in state.submissions) {
        sub = state.submissions[index];
        if (state.selected.includes(sub.id)) {
          Vue.delete(state.submissions, index);
        }
      }
      state.selected = [];
    }
  }
});
