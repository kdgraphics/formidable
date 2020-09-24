<template>
  <div class="formidable-submissions">
    <div class="tableview tablepane" v-show="!chartView">
      <table class="data fullwidth">
        <thead>
          <tr>
            <th class="checkbox-cell selectallcontainer orderable" role="checkbox" tabindex="0" aria-checked="false"><div class="checkbox"></div></th>
            <th scope="col" data-attribute="form" :class="sort.field == 'form.name' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('form.name')">{{ t('Form') }}</th>
            <th scope="col" data-attribute="submitted" :class="sort.field == 'dateCreated' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('dateCreated')">{{ t('Submitted') }}</th>
            <th scope="col" data-attribute="read" :class="sort.field == 'read' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('read')">{{ t('Read') }}</th>
            <th scope="col" data-attribute="notified" :class="sort.field == 'notified' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('notified')">{{ t('Notified') }}</th>
            <th scope="col" data-attribute="open">{{ t('View') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in submissions" :class="selected.includes(submission.id) ? 'sel' : ''">
            <td class="checkbox-cell"><div class="checkbox" title="Select" v-on:click="toggleSelect(submission.id)"></div></td>
            <td data-title="Form" data-attr="form"><a target="_blank" :href="'/admin/formidable/forms/'+submission.form.id">{{ submission.form.name }}</a></td>
            <td data-title="Submitted" data-attr="submitted">{{ submission.dateCreated }}</td>
            <td data-title="Read" data-attr="read">{{ submission.read ? t('Yes') : t('No') }}</td>
            <td data-title="Notified" data-attr="notified">{{ submission.notified ? t('Yes') : t('No') }}</td>
            <td data-title="Open" data-attr="open" v-on:click="open(submission.id)"><span class="icon icon-mask categories"><span data-icon="categories"></span></span></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="chart-view" v-show="chartView">
      <canvas id="formidable-chart-container"></canvas>
    </div>
    <div id="footer" class="flex">
      <button class="btn" @click="chartView = !chartView">{{ chartView ? t('Table view') : t('Chart view')}}</button>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import axios from 'axios';
import { pull } from 'lodash';
import Chart from 'chart.js/dist/Chart';

export default {
  computed: {
    ...mapState(['filters'])
  },
  data: function () {
    return {
      selected: [],
      chartView: false,
      chartData: {},
      chart: null,
      chartSubmissions: [],
      submissions: [],
      sort: {
        field: 'dateCreated',
        side: 'desc'
      }
    }
  },
  watch: {
    chartSubmissions: function() {
      this.normalizeChartData();
      if (this.chartView) {
        this.updateChart();
      }
    },
    chartView: function () {
      this.setIsChartView(this.chartView);
      if (this.chartView) {
        this.fetchAll();
      }
    },
    sort: {
      handler: function() {
        if (!this.chartView) {
          this.fetch();
        }
      },
      deep: true
    },
    filters: {
      handler: function() {
        if (this.chartView) {
          this.fetchAll();
        } else {
          this.fetch();
        }
      },
      deep: true
    }
  },
  created() {
    this.fetch();
  },
  mounted() {
    let context = document.getElementById('formidable-chart-container').getContext('2d');
    this.chart = new Chart(context, {
      type: 'line',
    });
  },
  methods: {
    open: function (id) {

    },
    updateChart: function() {
      this.chart.data.labels = this.chartData.labels;
      this.chart.data.datasets = this.chartData.data;
      this.chart.update();
    },
    toggleSelect: function (id) {
      if (this.selected.includes(id)) {
        let index = this.selected.indexOf(id);
        this.$delete(this.selected, index);
      } else {
        this.selected.push(id);
      }
      this.setSelected(this.selected);
    },
    setOrdered: function (field) {
      this.$set(this.sort, 'field', field);
      this.$set(this.sort, 'side', this.sort.side == 'desc' ? 'asc' : 'desc');
    },
    fetch: function() {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.filters = this.filters;
      data.sort = this.sort;
      data.paginate = !this.isChartView;
      this.isFetching = true;
      axios.post('/admin/formidable/submissions/load', data)
        .then((response) => {
          this.submissions = response.data.submissions;
          this.pager = {
            count: response.data.count,
            totalCount: response.data.totalCount,
            page: response.data.page,
            pageCount: response.data.pageCount,
          };
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.isFetching = false;
        });
    },
    fetchAll: function() {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.filters = this.filters;
      this.isFetching = true;
      axios.post('/admin/formidable/submissions/loadAll', data)
        .then((response) => {
          this.chartSubmissions = response.data.submissions;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.isFetching = false;
        });
    },
    normalizeChartData()
    {
      let forms = {};
      let labels = [];
      this.chartSubmissions.forEach(sub => {
        if (!labels.includes(sub.dateCreated)) {
          labels.push(sub.dateCreated);
        }
        if (!(sub.form_id in forms)) {
          forms[sub.form_id] = {
            label: sub.form.name,
            fill: false,
            borderColor: sub.form.color
          };
        }
      });
      Object.keys(forms).forEach(form_id => {
        forms[form_id].data = labels.map(x => 0);
      });
      this.chartSubmissions.forEach(sub => {
        forms[sub.form_id].data[labels.indexOf(sub.dateCreated)] ++;
      });

      this.chartData = {
        labels: labels,
        data: Object.values(forms)
      };
    },
    ...mapMutations(['setSelected', 'setSort', 'setIsChartView'])
  }
};

</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

.formidable-submissions {
  .icon.icon-mask {
    cursor: pointer;
  }
}
</style>