import { createStore } from 'vuex';

export const store = createStore({
  state: {
    selected: [],
    isChartView: false,
    isFetching: false,
    forms: {},
    submissions: [],
    chartData: null,
    filters: {
      forms: [],
      trashed: false,
      read: -1,
      search: null,
      dateFrom: {
        date: null,
        timezone: null
      },
      dateTo: {
        date: null,
        timezone: null
      }
    },
    sort: {
      field: 'dateCreated',
      side: 'desc'
    },
    pager: {}
  },
  mutations: {
    setForms (state, value) {
      state.forms = value;
    },
    setIsFetching (state, value) {
      state.isFetching = value;
    },
    setPager (state, value) {
      state.pager = value;
    },
    setChartData (state, value) {
      state.chartData = value;
    },
    setTimezone(state, value) {
      state.filters.dateFrom.timezone = value;
      state.filters.dateTo.timezone = value;
    },
    setSelected (state, value) {
      state.selected = value;
    },
    setIsChartView (state, value) {
      state.isChartView = value;
    },
    setDateTo(state, value) {
      state.filters.dateTo.date = value;
    },
    setDateFrom(state, value) {
      state.filters.dateFrom.date = value;
    },
    setTrashed(state, value) {
      state.filters.trashed = value;
    },
    setRead(state, value) {
      state.filters.read = value;
    },
    setSearch(state, value) {
      state.filters.search = value;
    },
    addForm(state, value) {
      state.filters.forms.push(value);
    },
    removeForm(state, value) {
      state.filters.forms = state.filters.forms.filter(f => f != value);
    },
    setSubmissions(state, value) {
      state.submissions = value;
    },
    markAsRead(state, ids) {
      let sub;
      for (let index in state.submissions) {
        sub = state.submissions[index];
        if (ids.includes(sub.id)) {
          state.submissions[index].read = 1;
        }
      }
    },
    deleteMany(state, ids) {
      state.submissions = state.submissions.filter(s => !ids.includes(s.id));
      state.selected = [];
    }
  },
  actions: {
    deleteMany({commit, state}, ids) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.ids = ids;
      return new Promise((resolve, reject) => {
        axios.post(Craft.getActionUrl('formidable/cp-submissions/delete-many'), data)
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
        axios.post(Craft.getActionUrl('formidable/cp-submissions/set-as-read'), data)
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
    fetch ({commit, state}) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.filters = state.filters;
      data.sort = state.sort;
      data.page = state.pager.page;
      commit('setIsFetching', true);
      axios.post(Craft.getActionUrl('formidable/cp-submissions/load'), data)
        .then((response) => {
          commit('setSubmissions', response.data.submissions);
          commit('setPager', {
            count: response.data.count,
            totalCount: response.data.totalCount,
            page: response.data.page,
            pageCount: response.data.pageCount,
          });
        })
        .catch((error) => {
          Craft.cp.displayError('There\'s been a problem loading the submissions');
          console.log(error);
        })
        .finally(() => {
          commit('setIsFetching', false);
        });
    },
    fetchAll({commit, state}) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.filters = state.filters;
      commit('setIsFetching', true);
      axios.post(Craft.getActionUrl('formidable/cp-submissions/load-all'), data)
        .then((response) => {
          commit('setChartData', response.data.data);
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          commit('setIsFetching', false);
        });
    },
  }
});
