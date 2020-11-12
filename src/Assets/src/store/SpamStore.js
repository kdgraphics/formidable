import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export default new Vuex.Store({
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
    setIP(state, value) {
      Vue.set(state.filters, 'ip', value.target.value);
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
    setSpams(state, value) {
      state.spams = value;
    },
    deleteMany(state, ids) {
      let sub;
      for (let index in state.spams) {
        sub = state.spams[index];
        if (ids.includes(sub.id)) {
          Vue.delete(state.spams, index);
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
        axios.post(Craft.getCpUrl('formidable/submissions/spam/delete-many'), data)
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
