<template>
  <div
    ref="container"
    class="formidable-submissions-toolbar flex flex-no-wrap"
  >
    <div>
      <button
        type="button"
        class="btn menubtn statusmenubtn"
      >
        <span
          v-if="filters.trashed === false"
          class="status live"
        />
        <span
          v-if="filters.trashed === true"
          class="icon"
          data-icon="trash"
        />
        <span
          v-if="filters.trashed === -1"
          class="status"
        />
        {{ t(filters.trashed === -1 ? t("All") : (filters.trashed === false ? t("Not trashed") : t('Trashed'))) }}
      </button>
      <div class="menu">
        <ul class="padded">
          <li>
            <a
              href="#"
              :class="filters.trashed === -1 ? 'sel' : ''"
              @click="setTrashed(-1)"
            ><span class="status" />{{ t('All') }}</a>
          </li>
          <li>
            <a
              href="#"
              :class="filters.trashed === false ? 'sel' : ''"
              @click="setTrashed(false)"
            ><span class="status live" />{{ t('Not trashed') }}</a>
          </li>
          <li>
            <a
              href="#"
              :class="filters.trashed === true ? 'sel' : ''"
              @click="setTrashed(true)"
            ><span
              class="icon"
              data-icon="trash"
            />{{ t('Trashed') }}</a>
          </li>
        </ul>
      </div>
    </div>
    <div>
      <button
        type="button"
        class="btn menubtn statusmenubtn"
      >
        <span
          v-if="filters.read === -1"
          class="status"
        />
        <span
          v-if="filters.read === true"
          class="status live"
        />
        <span
          v-if="filters.read === false"
          class="status suspended"
        />
        {{ filters.read === -1 ? t("All") : (filters.read === true ? t("Read") : t("Not read")) }}
      </button>
      <div class="menu">
        <ul class="padded">
          <li>
            <a
              href="#"
              :class="filters.read === -1 ? 'sel' : ''"
              @click="setRead(-1)"
            ><span class="status" />{{ t('All') }}</a>
          </li>
          <li>
            <a
              href="#"
              :class="filters.read === true ? 'sel' : ''"
              @click="setRead(true)"
            ><span class="status live" />{{ t('Read') }}</a>
          </li>
          <li>
            <a
              href="#"
              :class="filters.read === false ? 'sel' : ''"
              @click="setRead(false)"
            ><span class="status suspended" />{{ t('Not read') }}</a>
          </li>
        </ul>
      </div>
    </div>
    <div>
      <button
        type="button"
        class="btn menubtn statusmenubtn"
      >
        {{ t("Select form(s)") }}
      </button>
      <div class="menu">
        <ul class="padded">
          <li>
            <a
              href="#"
              @click="selectAllForms"
            >{{ t('All') }}</a>
          </li>
          <li
            v-for="title, id in allForms"
            :key="id"
          >
            <a
              href="#"
              :class="filters.forms.includes(id) ? 'sel' : ''"
              @click="toggleForm(id)"
            >{{ title }}</a>
          </li>
        </ul>
      </div>
    </div>
    <div>
      <div class="datetimewrapper">
        <div class="datewrapper">
          <input
            class="text datepicker"
            name="dateFrom"
            :placeholder="'      '+t('Date from')"
            autocomplete="off"
          >
          <div data-icon="date" />
        </div>
      </div>
    </div>
    <div>
      <div class="datetimewrapper">
        <div class="datewrapper"> 
          <input
            class="text datepicker"
            name="dateTo"
            :placeholder="'       '+t('Date to')"
            autocomplete="off"
          >
          <div data-icon="date" />
        </div>
      </div>
    </div>
    <div class="input">
      <input
        class="text"
        name="search"
        placeholder="Search"
        size="10"
        autocomplete="off"
        :disabled="isFetching"
        @keyup="search"
      >
    </div>
    <button
      class="btn"
      @click="setIsChartView(!isChartView)"
    >
      {{ isChartView ? t('Table view') : t('Chart view') }}
    </button>
    <div v-show="selected.length">
      <button
        type="button"
        class="btn menubtn"
        data-icon="settings"
      />
      <div class="menu">
        <ul class="padded">
          <li>
            <a
              href="#"
              @click="markSelectedAsRead"
            >{{ t('Mark as read') }}</a>
          </li>
          <li>
            <a
              href="#"
              @click="deleteSelection"
            >{{ t('Delete') }}</a>
          </li>
          <li>
            <a
              v-if="filters.forms.length == 1"
              href="#"
              @click="exportToCSV"
            >{{ t('Export') }}</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { keys, remove } from 'lodash';

export default {
  props: {
    allForms: {
      type: Object,
      default: () => {return {};}
    },
    timezone: {
      type: String,
      default: ''
    }
  },
  computed: {
    ...mapState(['sort', 'filters', 'selected', 'isChartView', 'submissions', 'isFetching'])
  },
  created: function(){
    this.setForms(this.allForms);
    this.setTimezone(this.timezone);
    if (this.allForms) {
      this.addForm(keys(this.allForms)[0]);
    }
  },
  mounted () {
    Craft.initUiElements(this.$refs.container);
    let _this = this;
    $('.formidable-submissions-toolbar .datepicker')
      .datepicker(Craft.datepickerOptions)
      .on('change', function(e) {
        let input = this;
        $(this).parent().parent().find('.clear-btn').on('click', () => {
          this.name == 'dateFrom' ? _this.setDateFrom(null) : _this.setDateTo(null);
        });
        this.name == 'dateFrom' ? _this.setDateFrom(this.value) : _this.setDateTo(this.value);
      });
  },
  methods: {
    toggleForm: function (id) {
      if (this.filters.forms.includes(id)) {
        this.removeForm(id);
      } else {
        this.addForm(id);
      }
    },
    selectAllForms: function () {
      for (let i in this.allForms) {
        if (!this.filters.forms.includes(i)) {
          this.addForm(i);
        }
      }
    },
    search: function (e) {
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        if (!this.isFetching) {
          this.setSearch(e.target.value);
        }
      }, 800);
    },
    formatFilter: function (filters) {
      let out = {};
      for (let i in filters) {
        if (typeof filters[i] == 'object') {
          out[i] = this.formatFilter(filters[i]);
        } else {
          out[i] = filters[i];
        }
      }
      return out; 
    },
    exportToCSV: function () {
      window.open(Craft.getCpUrl('formidable/submissions/export?ids='+this.selected.join(',')));
    },
    ...mapMutations(['setTimezone', 'setForms', 'setPager', 'setTrashed', 'setRead', 'setDateTo', 'setDateFrom', 'addForm', 'removeForm', 'setIsChartView', 'deleteSelected', 'setSearch']),
    ...mapActions(['markSelectedAsRead', 'deleteSelection'])
  }
};

</script>