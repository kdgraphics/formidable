import { createStore } from 'vuex';

export const store = createStore({
  state: {
    selected: [],
    isChartView: false,
    forms: {},
    spams: [],
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
    }
  },
  mutations: {
    setForms (state, value) {
      state.forms = value;
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
    deleteMany({commit, state}, ids) {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.ids = ids;
      return new Promise((resolve, reject) => {
        axios.post(Craft.getCpUrl('formidable/cp-spam/delete-many'), data)
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
    }
  }
});
