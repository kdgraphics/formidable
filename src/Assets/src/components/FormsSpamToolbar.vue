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
          v-if="filters.trashed === -1"
          class="status"
        />
        <span
          v-if="filters.trashed === true"
          class="icon"
          data-icon="trash"
        />
        <span
          v-if="filters.trashed === false"
          class="status live"
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
    <div class="field">
      <div class="input ltr">
        <input
          class="text"
          :placeholder="t('IP')"
          @change="setIP"
        >
      </div>
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
              @click="deleteSelection"
            >{{ t('Delete') }}</a>
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
    ...mapState(['sort', 'filters', 'selected', 'isChartView'])
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
        $(this).parent().parent().find('.clear-btn').on('click', function(){
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
    selectAllForms: function (e) {
      e.preventDefault();
      for (let i in this.allForms) {
        if (!this.filters.forms.includes(i)) {
          this.addForm(i);
        }
      }
    },
    ...mapMutations(['setTimezone', 'setForms', 'setPager', 'setIP', 'setTrashed', 'setDateTo', 'setDateFrom', 'addForm', 'removeForm', 'setIsChartView', 'deleteSelected']),
    ...mapActions(['deleteSelection'])
  }
};

</script>