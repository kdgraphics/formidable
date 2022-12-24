<template>
  <header
    id="content-header"
    class="pane-header"
  >
    <div
      id="tabs"
      class="pane-tabs"
    >
      <div
        role="tablist"
        :aria-label="t('Forms')"
      >
        <a
          role="tab"
          :class="{'sel': activeTab === 'config'}"
          @click.prevent="changeTab('config')"
        >
          <span class="tab-label">
            {{ t('Config') }}
            <span
              v-if="formHasErrors"
              data-icon="alert"
            />
          </span>
        </a>
        <a
          role="tab"
          :class="{'sel': activeTab === 'fields'}"
          @click.prevent="changeTab('fields')"
        >
          <span class="tab-label">
            {{ t('Fields') }}
            <span
              v-if="fieldsHaveErrors"
              data-icon="alert"
            />
          </span>
        </a>
        <a
          role="tab"
          :class="{'sel': activeTab === 'rules'}"
          @click.prevent="changeTab('rules')"
        >
          <span class="tab-label">
            {{ t('Rules') }}
            <span 
              v-if="rulesHaveErrors"
              data-icon="alert"
            />
          </span>
        </a>
      </div>
    </div>
  </header>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  props: {
    tab: {
      type: String,
      default: null
    }
  },
  computed:{
    formHasErrors: function () {
      if (!this.form.errors) {
        return false;
      }
      return Object.keys(this.form.errors).length > 0;
    },
    fieldsHaveErrors: function () {
      for (let i in this.fields) {
        if (this.fields[i].errors && Object.keys(this.fields[i].errors).length > 0) {
          return true;
        }
      }
      return false;
    },
    rulesHaveErrors: function () {
      for (let i in this.rules) {
        if (this.rules[i].errors && Object.keys(this.rules[i].errors).length > 0) {
          return true;
        }
      }
      return false;
    },
    ...mapState(['activeTab', 'isAdding', 'form', 'fields', 'rules'])
  },
  created() {
    this.changeTab(this.tab, false);
    window.addEventListener('popstate', () => {
      const pathSplit = document.location.pathname.split('/');
      if (pathSplit.length >= 5 && pathSplit[2] === 'formidable') {
        this.changeTab(pathSplit.length >= 5 ? pathSplit[5] : 'config', false);
      }
    });
  },
  methods: {
    changeTab (tab, pushState = true) {
      this.setActiveTab(tab);
      if (pushState) {
        let parts = [
          this.isAdding ? 'add' : this.form.id,
          tab
        ];
        this.setHistory(parts);
      }
    },
    ...mapMutations(['setActiveTab']),
    ...mapActions(['setHistory'])
  }
};
</script>


<style lang="scss" scoped>

[data-icon]::before {
  margin-top: -3px
}

</style>
