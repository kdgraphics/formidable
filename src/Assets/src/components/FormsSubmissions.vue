<template>
  <div class="formidable-submissions">
    <div class="tableview tablepane" v-show="!isChartView">
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
          <tr v-for="submission in submissions" :class="{'sel': selected.includes(submission.id), 'unread': !submission.read }">
            <td class="checkbox-cell"><div class="checkbox" title="Select" v-on:click="toggleSelect(submission.id)"></div></td>
            <td data-title="Form" data-attr="form"><a target="_blank" :href="'/admin/formidable/forms/'+submission.form.id">{{ submission.form.name }}</a></td>
            <td data-title="Submitted" data-attr="submitted">{{ submission.dateCreated }}</td>
            <td data-title="Read" class="read" data-attr="read">{{ submission.read ? t('Yes') : t('No') }}</td>
            <td data-title="Notified" data-attr="notified">{{ submission.notified ? t('Yes') : t('No') }}</td>
            <td data-title="Open" data-attr="open" v-on:click="open(submission.id)"><span class="icon icon-mask categories"><span data-icon="categories"></span></span></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="chart-view" v-show="isChartView">
      <canvas id="formidable-chart-container"></canvas>
    </div>
    <div id="footer" class="flex">
        <div class="pagination flex" v-if="!isChartView">
          <div :title="t('First Page')" :class="'page-link page-first ' + (pager.pageCount > 0 && pager.page > 0 ? '' : 'disabled')" @click="pager.pageCount > 0 && pager.page > 0 ? changePage(0) : null">«</div>
          <div data-icon="leftangle" :title="t('Previous Page')" :class="'page-link ' + (pager.pageCount > 0 && pager.page > 0 ? '' : 'disabled')" @click="pager.pageCount > 0 && pager.page > 0 ? changePage(pager.page - 1) : null"></div>
          <div v-for="i in pagerRange()" :class="'page-link page-number ' + (pager.page == i ? 'active' : '')" @click="pager.page != i ? changePage(i) : null">{{ i+1 }}</div>
          <div data-icon="rightangle" :title="t('Next Page')" :class="'page-link ' + (pager.pageCount > 0 && pager.page < pager.pageCount -1 ? '' : 'disabled')" @click="pager.pageCount > 0 && pager.page < pager.pageCount -1 ? changePage(pager.page+1) : null"></div>
          <div :title="t('Last Page')" :class="'page-link page-last ' + (pager.pageCount > 0 && pager.page < pager.pageCount -1 ? '' : 'disabled')" @click="pager.pageCount > 0 && pager.page < pager.pageCount -1 ? changePage(pager.pageCount-1) : null">»</div>
        </div>
        <div class="totals">{{ pager.totalCount + ' ' + t('submissions') }}</div>
    </div>
    <div class="modal elementselectormodal" id="submission-modal" style="display:none" ref="modal">
      <div class="body has-sidebar">
        <div class="content has-sidebar">
          <div class="sidebar">
            <div class="heading"><span>{{ t('Details') }}</span></div>
            <ul class="sidebar-content">
              <li>{{ t('Created') }}: {{ viewed.dateCreated }}</li>
            </ul>
          </div>
          <div class="main">
            <p>hello</p>
          </div>
        </div>
      </div>
      <div class="footer">
        <div class="buttons right">
          <button type="button" class="btn" @click="closeModal">{{ t('Close') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import axios from 'axios';
import { pull, range } from 'lodash';
import Chart from 'chart.js/dist/Chart';

export default {
  computed: {
    ...mapState(['filters', 'isChartView', 'submissions'])
  },
  data: function () {
    return {
      selected: [],
      chartData: {},
      chart: null,
      chartData: false,
      pager: {},
      sort: {
        field: 'dateCreated',
        side: 'desc'
      },
      pager: {},
      viewed: {}
    }
  },
  watch: {
    isChartView: function () {
      if (this.isChartView && !this.chartData) {
        this.fetchAll();
      } else if (!this.isChartView && !this.submissions.length) {
        this.fetch();
      }
    },
    sort: {
      handler: function() {
        if (!this.isChartView) {
          this.fetch();
        }
      },
      deep: true
    },
    filters: {
      handler: function() {
        this.chartData = false;
        this.setSubmissions = [];
        if (this.isChartView) {
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
      options: {
        responsive: true,
        scales: {
          yAxes: [{
            ticks: {
              stepSize: 1
            }
          }]
        }
      }
    });
  },
  methods: {
    open: function (id) {
      this.viewed = this.submissions[id];
      new Garnish.Modal(this.$refs.modal);
    },
    changePage(page) {
      this.$set(this.pager, 'page', page);
      this.fetch();
    },
    pagerRange() {
      return range(this.pager.pageCount);
    },
    updateChart: function(data) {
      this.chart.data.labels = data.labels;
      this.chart.data.datasets = data.data;
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
      data.page = this.pager.page;
      this.isFetching = true;
      axios.post('/admin/formidable/submissions/load', data)
        .then((response) => {
          this.setSubmissions(response.data.submissions);
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
          this.chartData = true;
          this.updateChart(response.data.data);
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.isFetching = false;
        });
    },
    ...mapMutations(['setSelected', 'setSort', 'setSubmissions'])
  }
};

</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

.formidable-submissions {
  .icon.icon-mask {
    cursor: pointer;
  }
  tr.unread td.read {
    font-weight: bold;
  }
  #footer {
    justify-content: space-between;
    .pagination .page-number.active{
      background: $grey200;
    }
  }
}
#submission-modal {

}
</style>