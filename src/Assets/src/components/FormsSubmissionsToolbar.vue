<template>
  <div class="formidable-submissions-toolbar flex flex-no-wrap" ref="container">
    <div>
      <button type="button" class="btn menubtn statusmenubtn">
        <span v-if="!filters.trashed" class="status"></span>
        <span v-if="filters.trashed" class="icon" data-icon="trash"></span>
        {{ t(filters.trashed === null ? t("All") : (filters.trashed === false ? t("Not trashed") : t('Trashed'))) }}
      </button>
      <div class="menu">
        <ul class="padded">
          <li><a href="#" :class="filters.trashed === null ? 'sel' : ''" v-on:click="setTrashed(null)"><span class="status"></span>{{ t('All') }}</a></li>
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
          <li><a href="#" :class="filters.read === null ? 'sel' : ''" v-on:click="setRead(null)">{{ t('All') }}</a></li>
          <li><a href="#" :class="filters.read === true ? 'sel' : ''" v-on:click="setRead(true)">{{ t('Read') }}</a></li>
          <li><a href="#" :class="filters.read === false ? 'sel' : ''" v-on:click="setRead(false)">{{ t('Not read') }}</a></li>
        </ul>
      </div>
    </div>
    <div>
      <button type="button" class="btn menubtn statusmenubtn">
        {{ t("Select form(s)") }}
      </button>
      <div class="menu">
        <ul class="padded">
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
    <button class="btn" @click="setIsChartView(!isChartView)">{{ isChartView ? t('Table view') : t('Chart view')}}</button>
    <div v-show="selected.length">
      <button type="button" class="btn menubtn" data-icon="settings">
      </button>
      <div class="menu">
        <ul class="padded">
          <li><a href="#" v-on:click="markAsRead">{{ t('Mark as read') }}</a></li>
          <li><a href="#" v-on:click="deleteSelection">{{ t('Delete') }}</a></li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import axios from 'axios';
import { keys, remove } from 'lodash';

export default {
  props: {
    allForms: Object,
    timezone: String
  },
  created: function(){
    this.setForms(this.allForms);
    this.setTimezone(this.timezone);
    if (this.allForms) {
      this.addForm(keys(this.allForms)[0]);
    }
  },
  computed: {
    ...mapState(['sort', 'filters', 'selected', 'isChartView', 'submissions'])
  },
  mounted () {
    Craft.initUiElements(this.$refs.container);
    let _this = this;
    $('.formidable-submissions-toolbar .datepicker')
      .datepicker(Craft.datepickerOptions)
      .on('change', function(e) {
        let input = this;
        $(this).parent().parent().find('.clear-btn').on('click', function(){
          _this.name == 'dateFrom' ? _this.setDateFrom(null) : _this.setDateTo(null);
        });
        _this.name == 'dateFrom' ? _this.setDateFrom(this.value) : _this.setDateTo(this.value);
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
    markAsRead () {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.ids = this.selected;
      axios.post('/admin/formidable/submissions/set-as-read', data)
        .then((response) => {
          Craft.cp.displayNotice(response.data.message);
          this.markSelectedAsRead();
        })
        .catch((error) => {
          console.log(error);
        })
    },
    deleteSelection () {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.ids = this.selected;
      axios.post('/admin/formidable/submissions/delete-many', data)
        .then((response) => {
          Craft.cp.displayNotice(response.data.message);
          this.deleteSelected();
        })
        .catch((error) => {
          console.log(error);
        })
    },
    ...mapMutations(['setTimezone', 'setForms', 'setPager', 'setTrashed', 'setRead', 'setDateTo', 'setDateFrom', 'addForm', 'removeForm', 'setIsChartView', 'markSelectedAsRead', 'deleteSelected'])
  }
};

</script>