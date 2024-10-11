<template>
  <div class="formidable-submissions-toolbar flex flex-no-wrap" ref="container">
    <div>
      <button type="button" class="btn menubtn statusmenubtn">
        <span v-if="!filters.trashed" class="status"></span>
        <span v-if="filters.trashed" class="icon" data-icon="trash"></span>
        {{ t(filters.trashed === -1 ? t("All") : (filters.trashed === false ? t("Not trashed") : t('Trashed'))) }}
      </button>
      <div class="menu">
        <ul class="padded">
          <li><a href="#" :class="filters.trashed === -1 ? 'sel' : ''" v-on:click="setTrashed(-1)"><span class="status"></span>{{ t('All') }}</a></li>
          <li><a href="#" :class="filters.trashed === false ? 'sel' : ''" v-on:click="setTrashed(false)">{{ t('Not trashed') }}</a></li>
          <li><a href="#" :class="filters.trashed === true ? 'sel' : ''" v-on:click="setTrashed(true)"><span class="icon" data-icon="trash"></span>{{ t('Trashed') }}</a></li>
        </ul>
      </div>
    </div>
    <div>
      <button type="button" class="btn menubtn statusmenubtn">
        <span v-if="!filters.read" class="status"></span>
        <span v-if="filters.read" class="icon"></span>
        {{ filters.read === null ? t("All") : (filters.read === true ? t("Read") : t("Not read")) }}
      </button>
      <div class="menu">
        <ul class="padded">
          <li><a href="#" :class="filters.read === -1 ? 'sel' : ''" v-on:click="setRead(-1)">{{ t('All') }}</a></li>
          <li><a href="#" :class="filters.read === true ? 'sel' : ''" v-on:click="setRead(true)">{{ t('Read') }}</a></li>
          <li><a href="#" :class="filters.read === false ? 'sel' : ''" v-on:click="setRead(false)">{{ t('Not read') }}</a></li>
        </ul>
      </div>
    </div>
    <div v-if="isMultiSiteBoolean">
      <button type="button" class="btn menubtn statusmenubtn">
        {{ selectedSite ? selectedSite : t("Select site") }}
      </button>
      <div class="menu">
        <ul id="siteFilter" class="padded">
          <template v-if="isGrouped">
            <li :class="selectedSite === '' ? 'selected' : ''">
              <a href="#" @click="handleSiteClick(undefined, '')">All</a>
            </li>
            <li v-for="(group, groupIndex) in allSites" :key="groupIndex" class="group">
              <div class="group-label">
                <strong>{{ groupIndex }}</strong>
              </div>
              <ul>
                <li v-for="(value, id) in group" :key="id" :class="filters.siteId == id ? 'selected' : ''">
                  <a href="#" @click="handleSiteClick(id, value)">{{ value }}</a>
                </li>
              </ul>
            </li>
          </template>
          <template v-else>
            <li :class="selectedSite === '' ? 'selected' : ''">
              <a href="#" @click="handleSiteClick(undefined, '')">All</a>
            </li>
            <li v-for="(value, id) in allSites" :key="id" :class="filters.siteId == id ? 'selected' : ''">
              <a href="#" @click="handleSiteClick(id, value)">{{ value }}</a>
            </li>
          </template>
        </ul>
      </div>
    </div>
    <div>
      <button type="button" class="btn menubtn statusmenubtn">
        {{ t("Select form(s)") }}
      </button>
      <div class="menu">
        <ul class="padded">
          <li><a href="#" v-on:click.prevent="selectAllForms"><i>{{ t('All') }}</i></a></li>
          <li><a href="#" v-on:click.prevent="unselectAllForms"><i>{{ t('Unselect all') }}</i></a></li>
          <li v-for="title, id in allForms"><a href="#" :class="filters.forms.includes(id) ? 'sel' : ''" v-on:click="toggleForm(id)">{{ title }}</a></li>
        </ul>
      </div>
    </div>
    <div>
      <div class="datetimewrapper">
        <div class="datewrapper">
          <input class="text datepicker" name="dateFrom" :placeholder="'      '+t('Date from')" autocomplete="off">
          <div data-icon="date"></div>
        </div>
      </div>
    </div>
    <div>
      <div class="datetimewrapper">
        <div class="datewrapper"> 
          <input class="text datepicker" name="dateTo" :placeholder="'       '+t('Date to')" autocomplete="off">
          <div data-icon="date"></div>
        </div>
      </div>
    </div>
    <div class="input">
      <input class="text" name="search" placeholder="Search" size="10" autocomplete="off" :disabled="isFetching" v-on:keyup="search">
    </div>
    <button class="btn" @click="setIsChartView(!isChartView)">{{ isChartView ? t('Table view') : t('Chart view')}}</button>
    <button class="btn" @click="exportAll()">{{ t('Export all')}}</button>
    <div v-show="selected.length">
      <button type="button" class="btn menubtn" data-icon="settings">
      </button>
      <div class="menu">
        <ul class="padded">
          <li><a href="#" v-on:click="markSelectedAsRead">{{ t('Mark as read') }}</a></li>
          <li><a href="#" v-on:click="deleteSelection">{{ t('Delete') }}</a></li>
          <li><a href="#" v-if="filters.forms.length == 1 || selected.length == 1" v-on:click="exportToCSV">{{ t('Export') }}</a></li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import axios from 'axios';
import { keys, remove } from 'lodash';

export default {
  props: {
    allForms: Object,
    timezone: String,
    isMultiSite: {
      type: [Boolean, String],
      default: false,
    },
    allSites: {
      type: Object,
      default: {}
    },
  },
  data: function () {
    return {
      selectedSite: ''
    }
  },
  created: function(){
    this.setForms(this.allForms);
    this.setTimezone(this.timezone);
    if (this.allForms) {
      this.selectAllForms();
    }
  },
  computed: {
    ...mapState(['sort', 'filters', 'selected', 'isChartView', 'submissions', 'isFetching']),
    isMultiSiteBoolean: {
      get() {
        return this.isMultiSite === '1' || this.isMultiSite === true;
      },
      set(value) {
        this.$emit('update:isMultiSite', value ? '1' : '0');
      }
    },
    isGrouped() {
      return Object.values(this.allSites).some(value => typeof value === 'object')
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
    unselectAllForms: function () {
      this.filters.forms = [];
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
    exportAll: function () {
      console.log(this.sort);
      window.open(Craft.getCpUrl('formidable/submissions/export-all?filters='+JSON.stringify(this.filters)+'&sort='+JSON.stringify(this.sort)));
    },
    handleSiteClick(id, name) {
      this.setSelectedSite(id)
      this.selectedSite = name
    },
    ...mapMutations(['setTimezone', 'setForms', 'setPager', 'setTrashed', 'setRead', 'setDateTo', 'setDateFrom', 'addForm', 'removeForm', 'setIsChartView', 'deleteSelected', 'setSearch', 'setSelectedSite']),
    ...mapActions(['markSelectedAsRead', 'deleteSelection'])
  }
};
</script>

<style scoped>
  #siteFilter li .group-label {
    padding: 5px 0px;
  }

  #siteFilter li.selected a {
    cursor: not-allowed;
    background-color: #606d7b;
    color: #fff;
  }
</style>