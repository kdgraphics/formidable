<template>
  <div class="formidable-submissions">
    <div class="tableview tablepane" v-show="!isChartView">
      <table class="data fullwidth">
        <thead>
          <tr>
            <th class="checkbox-cell selectallcontainer orderable" role="checkbox" tabindex="0" aria-checked="false"><div class="checkbox" @click="toggleSelectAll"></div></th>
            <th scope="col" data-attribute="form" :class="sort.field == 'form.name' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('form.name')">{{ t('Form') }}</th>
            <th scope="col" data-attribute="submitted" :class="sort.field == 'dateCreated' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('dateCreated')">{{ t('Submitted') }}</th>
            <th scope="col" data-attribute="read" :class="sort.field == 'read' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('read')">{{ t('Read') }}</th>
            <th scope="col" data-attribute="notified" :class="sort.field == 'notified' ? ('ordered ' + sort.side) : 'orderable'" v-on:click="setOrdered('notified')">{{ t('Notified') }}</th>
            <th scope="col" data-attribute="open">{{ t('View') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission,index in submissions" :class="{'sel': selected.includes(submission.id), 'unread': !submission.read, trashed: submission.dateDeleted }">
            <td class="checkbox-cell"><div class="checkbox" title="Select" v-on:click="toggleSelect(submission.id)"></div></td>
            <td data-title="Form" data-attr="form">{{ submission.form.name }}</td>
            <td data-title="Submitted" data-attr="submitted">{{ submission.dateCreated }}</td>
            <td data-title="Read" class="read" data-attr="read">{{ submission.read ? t('Yes') : t('No') }}</td>
            <td data-title="Notified" data-attr="notified">{{ submission.notified ? t('Yes') : t('No') }}</td>
            <td data-title="Open" data-attr="open">
              <a v-on:click.prevent="open(index)" class="icon icon-mask">
                open
              </a>
            </td>
          </tr>
          <tr v-if="submissions.length == 0 && !isFetching">
            <td colspan="6">{{ t('No submissions found') }}</td>
          </tr>
        </tbody>
      </table>
      <div class="loader" v-if="isFetching">
        <img :src="loaderPath">
      </div>
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
              <li>{{ t('Created') }}: {{ viewed ? viewed.dateCreated : '' }}</li>
              <li>{{ t('Notified') }}: {{ viewed ? (viewed.notified ? t('Yes') : t('No')) : '' }}</li>
              <li>{{ t('Admin Notified') }}: {{ viewed ? (viewed.adminNotified ? t('Yes') : t('No')) : '' }}</li>
              <li>{{ t('Form') }}: {{ viewed ? viewed.form.name : ''}}</li>
            </ul>
          </div>
          <div class="main">
            <div class="heading"><span>{{ t('Submission') }}</span></div>
            <ul class="submission">
              <li :class="{field: true, padded: (value.field ? value.field.group_id != null : false), bold: (value.field ? value.field.type == 'group' : false)}" v-if="viewed" v-for="value in viewed.values">{{ value.field ? value.field.name : '*Field deleted*' }}: <span v-html="value.htmlValue"></span></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="footer">
        <div class="buttons">
          <button type="button" class="btn submit" @click="deleteMany([viewed.id]);popup.hide()">{{ t('Delete') }}</button>
          <button type="button" class="btn" @click="popup.hide()">{{ t('Close') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import axios from 'axios';
import { pull, range } from 'lodash';
import Chart from 'chart.js/dist/Chart';

export default {
  computed: {
    ...mapState(['filters', 'sort', 'isChartView', 'submissions', 'selected', 'isFetching'])
  },
  props: {
    loaderPath: String
  },
  data: function () {
    return {
      chartData: {},
      chart: null,
      chartData: false,
      pager: {},
      pager: {},
      viewed: {
        form: {},
        values: {}
      },
      popup: null,
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
    toggleSelectAll() {
      if (this.selected.length) {
        this.setSelected([]);
      } else {
        this.setSelected(this.submissions.map((sub) => sub.id));
      }
    },
    open: function (id) {
      this.viewed = this.submissions[id];
      if (this.popup) {
        this.popup.show();
      } else {
        this.popup = new Garnish.Modal(this.$refs.modal);
      }
      if (!this.viewed.read) {
        this.markAsRead([this.viewed.id]);
      }
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
      this.setIsFetching(true);
      axios.post(Craft.getCpUrl('formidable/submissions/load'), data)
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
          Craft.cp.displayError('There\'s been a problem loading the submissions');
          console.log(error);
        })
        .finally(() => {
          this.setIsFetching(false);
        });
    },
    fetchAll: function() {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      data.filters = this.filters;
      this.setIsFetching(true);
      axios.post(Craft.getCpUrl('formidable/submissions/loadAll'), data)
        .then((response) => {
          this.chartData = true;
          this.updateChart(response.data.data);
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.setIsFetching(false);
        });
    },
    ...mapMutations(['setSelected', 'setSort', 'setSubmissions', 'setIsFetching']),
    ...mapActions(['markAsRead', 'deleteMany'])
  }
};

</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

.formidable-submissions {
  .tableview {
    position: relative;
    min-height: 100px;
  }
  .icon.icon-mask {
    cursor: pointer;
  }
  .loader {
    position: absolute;
    background: rgba(0, 0, 0, 0.1);
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    img {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 100px;
    }
  }
  tr.unread td.read {
    font-weight: bold;
  }
  tr.trashed * {
    opacity: 0.7
  }
  #footer {
    justify-content: space-between;
    .pagination .page-number.active{
      background: $grey200;
    }
  }
}
#submission-modal {
  .heading span {
    font-size: 13px !important;
  }
  li {
    margin: 5px 0;
  }
  .sidebar-content {
    padding: 20px;
    padding-top: 10px;
  }
  .submission {
    margin-top: 20px;
  }
  .main {
    .heading {
      display: inline-block;
      padding: 0 5px;
      margin: 0 -5px;
      text-transform: uppercase;
      color: #9aa5b1;
      font-weight: bold;
    }
  }
  .buttons{
    justify-content: space-between;
  }
  .bold {
    font-weight: bold;
  }
  .padded {
    padding-left: 10px;
  }
}
</style>