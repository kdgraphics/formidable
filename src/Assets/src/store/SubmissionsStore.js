import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    selected: [],
    isChartView: false,
    isFetching: false,
    forms: {},
    submissions: [],
    sort: {
      field: 'dateCreated',
      side: 'desc'
    },
    filters: {
      forms: [],
      trashed: false,
      read: null,
      search: null,
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
    setIsFetching (state, value) {
      state.isFetching = value;
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
    setSearch(state, value) {
      Vue.set(state.filters, 'search', value);
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
    markAsRead(state, ids) {
      let sub;
      for (let index in state.submissions) {
        sub = state.submissions[index];
        if (ids.includes(sub.id)) {
          Vue.set(state.submissions[index], 'read', 1);
        }
      }
    },
    deleteMany(state, ids) {
      let sub;
      for (let index in state.submissions) {
        sub = state.submissions[index];
        if (ids.includes(sub.id)) {
          Vue.delete(state.submissions, index);
        }
      }
      state.selected = [];
    }
  },
  actions: {
    deleteMany({commit, state}, ids) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.ids = ids;
      return new Promise((resolve, reject) => {
        axios.post(Craft.getCpUrl('formidable/submissions/delete-many'), data)
          .then((response) => {
            commit('deleteMany', ids);
            Craft.cp.displayNotice(response.data.message);
            resolve(response);
          })
          .catch((error) => {
            console.log(error);
            reject(error);
          })
      });
    },
    deleteSelection({commit, state, dispatch}) {
      dispatch('deleteMany', state.selected);
    },
    markAsRead({commit, state}, ids) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.ids = ids;
      return new Promise((resolve, reject) => {
        axios.post(Craft.getCpUrl('formidable/submissions/set-as-read'), data)
          .then((response) => {
            commit('markAsRead', ids);
            resolve(response);
          })
          .catch((error) => {
            console.log(error);
            reject(error);
          })
      });
    },
    markSelectedAsRead ({commit, state, dispatch}) {
      dispatch('markAsRead', state.selected)
        .then((response) => {
          Craft.cp.displayNotice(response.data.message);
        })
    },
  }
});
