import { createStore } from 'vuex';

export const store = createStore({
  state: {
    selected: [],
    isChartView: false,
    isFetching: false,
    forms: {},
    spams: [],
    chartData: null,
    filters: {
      forms: [],
      trashed: false,
      ip: null,
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
    setIP(state, value) {
      state.filters.ip = value;
    },
    addForm(state, value) {
      state.filters.forms.push(value);
    },
    removeForm(state, value) {
      state.filters.forms = state.filters.forms.filter(f => f != value);
    },
    setSpams(state, value) {
      state.spams = value;
    },
    deleteMany(state, ids) {
      state.spams = state.spams.filter(s => !ids.includes(s.id));
      state.selected = [];
    }
  },
  actions: {
    deleteMany({commit, state, dispatch}, ids) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.ids = ids;
      return new Promise((resolve, reject) => {
        axios.post(Craft.getActionUrl('formidable/cp-spam/delete-many'), data)
          .then((response) => {
            commit('deleteMany', ids);
            dispatch('fetch');
            Craft.cp.displayNotice(response.data.message);
            resolve(response);
          })
          .catch((error) => {
            console.log(error);
            reject(error);
          })
      });
    },
    fetch({commit, state}) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.filters = state.filters;
      data.sort = state.sort;
      data.page = state.pager.page;
      commit('setIsFetching', true);
      axios.post(Craft.getActionUrl('formidable/cp-spam/load'), data)
        .then((response) => {
          commit('setSpams', response.data.spams);
          commit('setPager', {
            count: response.data.count,
            totalCount: response.data.totalCount,
            page: response.data.page,
            pageCount: response.data.pageCount,
          });
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          commit('setIsFetching', false);
        });
    },
    fetchAll({commit, state, dispatch}) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.filters = state.filters;
      commit('setIsFetching', true);
      axios.post(Craft.getActionUrl('formidable/cp-spam/load-all'), data)
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
    deleteSelection({commit, state, dispatch}) {
      dispatch('deleteMany', state.selected);
    }
  }
});
