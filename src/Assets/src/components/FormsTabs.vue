<template>
  <nav id="tabs">
    <ul>
      <li>
        <a :class="{'sel': activeTab === 'config'}" @click.prevent="changeTab('config')">
          {{ t('Config') }}<span v-if="configHasChanged && !configHasErrors" class="red-span">*</span><span v-if="configHasErrors" class="red-span">!</span>
          </a>
      </li>
      <li>
        <a :class="{'sel': activeTab === 'fields'}" @click.prevent="changeTab('fields')">
          {{ t('Fields') }}<span v-if="fieldsHaveChanged && !fieldsHaveErrors" class="red-span">*</span><span v-if="fieldsHaveErrors" class="red-span">!</span>
        </a>
      </li>
      <li>
        <a :class="{'sel': activeTab === 'rules'}" @click.prevent="changeTab('rules')">
          {{ t('Rules') }}<span v-if="rulesHaveChanged && !rulesHaveErrors" class="red-span">*</span><span v-if="rulesHaveErrors" class="red-span">!</span>
        </a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { mapMutations, mapState } from 'vuex';

export default {
  props: {
    tab: String
  },
  computed:{
    ...mapState(['configHasChanged', 'fieldsHaveChanged', 'rulesHaveChanged', 'rulesHaveErrors', 'fieldsHaveErrors', 'configHasErrors', 'activeTab', 'isAdding'])
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
        let url = document.location.pathname.split('/')
        url[5] = tab;
        window.history.pushState({}, '', url.join('/'));
      }
    },
    ...mapMutations({
      setActiveTab: 'setActiveTab'
    })
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
