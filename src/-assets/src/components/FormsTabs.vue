<template>
  <header id="content-header" class="pane-header">
    <div id="tabs" class="pane-tabs">
      <div role="tablist" :aria-label="t('Forms')">
        <a role="tab" :class="{'sel': activeTab === 'config'}" @click.prevent="changeTab('config')">
          <span class="tab-label">
            {{ t('Config') }}<span v-if="configHasErrors" class="red-span">!</span>
          </span>
        </a>
        <a role="tab" :class="{'sel': activeTab === 'fields'}" @click.prevent="changeTab('fields')">
          <span class="tab-label">
            {{ t('Fields') }}<span v-if="fieldsHaveErrors" class="red-span">!</span>
          </span>
        </a>
        <a role="tab" :class="{'sel': activeTab === 'rules'}" @click.prevent="changeTab('rules')">
          <span class="tab-label">
            {{ t('Rules') }}<span v-if="rulesHaveErrors" class="red-span">!</span>
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
    tab: String
  },
  computed:{
    ...mapState(['rulesHaveErrors', 'fieldsHaveErrors', 'configHasErrors', 'activeTab', 'isAdding', 'form'])
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


<style lang="scss">
@import "~craftcms-sass/mixins";

#content-container 

#notification-nav {
    font-size: 14px;
}

.formidable #sidebar-container, .formidable #sidebar {
  width: 150px;
  flex: 0 0 150px;
}

.formidable .red-span {
  color: $red400;
  padding-left:5px;
}

.formidable #sidebar-container {
  flex: 0 0 150px;
}

.formidable #main-content.has-sidebar #content-container {
  width: calc(100% - 126px);
  max-width: calc(100% - 126px);
}

</style>
