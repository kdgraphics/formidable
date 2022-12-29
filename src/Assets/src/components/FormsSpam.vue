<template>
  <div class="formidable-spam">
    <div
      v-show="!isChartView"
      class="tableview tablepane"
    >
      <table class="data fullwidth">
        <thead>
          <tr>
            <th
              class="checkbox-cell selectallcontainer orderable"
              role="checkbox"
              tabindex="0"
              aria-checked="false"
            >
              <div
                class="checkbox"
                @click="toggleSelectAll"
              />
            </th>
            <th
              scope="col"
              data-attribute="form"
              :class="sort.field == 'form.name' ? ('ordered ' + sort.side) : 'orderable'"
              @click="setOrdered('form.name')"
            >
              {{ t('Form') }}
            </th>
            <th
              scope="col"
              data-attribute="ip"
            >
              {{ t('IP') }}
            </th>
            <th
              scope="col"
              data-attribute="submitted"
              :class="sort.field == 'dateCreated' ? ('ordered ' + sort.side) : 'orderable'"
              @click="setOrdered('dateCreated')"
            >
              {{ t('Submitted') }}
            </th>
            <th
              scope="col"
              data-attribute="open"
            >
              {{ t('View') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="spam,index in spams"
            :key="index"
            :class="{'sel': selected.includes(spam.id), 'unread': !spam.read }"
          >
            <td class="checkbox-cell">
              <div
                class="checkbox"
                title="Select"
                @click="toggleSelect(spam.id)"
              />
            </td>
            <td
              data-title="Form"
              data-attr="form"
            >
              {{ spam.form.name }}
            </td>
            <td
              data-title="IP"
              data-attr="ip"
            >
              {{ spam.ip }}
            </td>
            <td
              data-title="Submitted"
              data-attr="submitted"
            >
              {{ spam.dateCreated }}
            </td>
            <td
              data-title="Open"
              data-attr="open"
            >
              <a
                @click.prevent="open(index)"
              >
                open
              </a>
            </td>
          </tr>
          <tr v-if="spams.length == 0">
            <td colspan="6">
              {{ t('No spam found') }}
            </td>
          </tr>
        </tbody>
      </table>
      <div
        v-if="isFetching"
        class="loader"
      >
        <img :src="loaderPath">
      </div>
    </div>
    <div
      v-show="isChartView"
      class="chart-view"
    >
      <canvas id="formidable-chart-container" />
    </div>
    <div
      id="footer"
      class="flex"
    >
      <div
        v-if="!isChartView"
        class="pagination flex"
      >
        <div
          :title="t('First Page')"
          :class="'page-link page-first ' + (pager.pageCount > 0 && pager.page > 0 ? '' : 'disabled')"
          @click="pager.pageCount > 0 && pager.page > 0 ? changePage(0) : null"
        >
          «
        </div>
        <div
          data-icon="leftangle"
          :title="t('Previous Page')"
          :class="'page-link ' + (pager.pageCount > 0 && pager.page > 0 ? '' : 'disabled')"
          @click="pager.pageCount > 0 && pager.page > 0 ? changePage(pager.page - 1) : null"
        />
        <div
          v-for="i in pagerRange()"
          :key="i"
          :class="'page-link page-number ' + (pager.page == i ? 'active' : '')"
          @click="pager.page != i ? changePage(i) : null"
        >
          {{ i+1 }}
        </div>
        <div
          data-icon="rightangle"
          :title="t('Next Page')"
          :class="'page-link ' + (pager.pageCount > 0 && pager.page < pager.pageCount -1 ? '' : 'disabled')"
          @click="pager.pageCount > 0 && pager.page < pager.pageCount -1 ? changePage(pager.page+1) : null"
        />
        <div
          :title="t('Last Page')"
          :class="'page-link page-last ' + (pager.pageCount > 0 && pager.page < pager.pageCount -1 ? '' : 'disabled')"
          @click="pager.pageCount > 0 && pager.page < pager.pageCount -1 ? changePage(pager.pageCount-1) : null"
        >
          »
        </div>
      </div>
      <div
        v-if="!isChartView"
        class="totals"
      >
        {{ pager.totalCount + ' ' + t('spam') }}
      </div>
    </div>
    <div
      id="spam-modal"
      ref="modal"
      class="modal elementselectormodal"
      style="display:none"
    >
      <div class="body has-sidebar">
        <div class="content has-sidebar">
          <div class="sidebar">
            <div class="heading">
              <span>{{ t('Details') }}</span>
            </div>
            <ul class="sidebar-content">
              <li>{{ t('Created') }}: {{ viewed ? viewed.dateCreated : '' }}</li>
              <li>{{ t('IP') }}: {{ viewed.ip }}</li>
              <li>{{ t('User agent') }}: {{ viewed.userAgent }}</li>
              <li>{{ t('Form') }}: {{ viewed ? viewed.form.name : '' }}</li>
              <li>{{ t('Notified') }}: {{ viewed ? (viewed.notified ? t('Yes') : t('No') ) : '' }}</li>
              <li>{{ t('Reason') }}: {{ viewed ? viewed.reason : '' }}</li>
            </ul>
          </div>
          <div class="main">
            <div class="heading">
              <span>{{ t('Spam') }}</span>
            </div>
            <ul
              v-if="viewed"
              class="spam"
            >
              <li
                v-for="value, field in viewed.values"
                :key="field"
                class="field"
              >
                {{ value.field ? value.field.name : '*Field deleted*' }}: <span v-html="value.htmlValue" />
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="footer">
        <div class="buttons">
          <button
            type="button"
            class="btn submit"
            @click="deleteMany([viewed.id]);popup.hide()"
          >
            {{ t('Delete') }}
          </button>
          <button
            type="button"
            class="btn"
            @click="popup.hide()"
          >
            {{ t('Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { pull, range } from 'lodash';
import Chart from 'chart.js/dist/Chart';

export default {
  props: {
    loaderPath: {
      type: String,
      default: ''
    }
  },
  data: function () {
    return {
      chart: null,
      viewed: {
        form: {},
        values: {}
      },
      popup: null
    }
  },
  computed: {
    ...mapState(['filters', 'isChartView', 'spams', 'selected', 'chartData', 'sort', 'pager', 'isFetching'])
  },
  watch: {
    chartData: function () {
      this.chart.data.labels = this.chartData.labels;
      this.chart.data.datasets = this.chartData.data;
      this.chart.update();
    },
    isChartView: function () {
      if (this.isChartView && !this.chartData) {
        this.fetchAll();
      } else if (!this.isChartView && !this.spams.length) {
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
        this.setSelected(this.spams.map((sub) => sub.id));
      }
    },
    open: function (id) {
      this.viewed = this.spams[id];
      if (this.popup) {
        this.popup.show();
      } else {
        this.popup = new Garnish.Modal(this.$refs.modal);
      }
    },
    changePage(page) {
      this.pager.page = page;
      this.fetch();
    },
    pagerRange() {
      return range(this.pager.pageCount);
    },
    toggleSelect: function (id) {
      if (this.selected.includes(id)) {
        let index = this.selected.indexOf(id);
        delete this.selected[index];
      } else {
        this.selected.push(id);
      }
      this.setSelected(this.selected);
    },
    setOrdered: function (field) {
      this.sort.field = field;
      this.sort.side = this.sort.side == 'desc' ? 'asc' : 'desc';
      this.fetch();
    },
    ...mapMutations(['setSelected']),
    ...mapActions(['deleteMany', 'fetch', 'fetchAll'])
  }
};

</script>
<style lang="scss">
@import "~craftcms-sass/mixins";

#page-title.has-toolbar {
	min-width: 100px !important;
}

.formidable-spam {
  .icon.icon-mask {
    cursor: pointer;
  }
  .tableview {
    position: relative;
  }
  #footer {
    justify-content: space-between;
    .pagination .page-number.active{
      background: $grey200;
    }
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
}
#spam-modal {
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
  .spam {
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
}
</style>