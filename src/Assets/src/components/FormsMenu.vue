<template>
  <nav id="forms-nav">
    <ul>
      <li>
        <a :class="{'sel': activeMenu === 'config'}" @click.prevent="changeMenu('config')">
          {{ t('Config') }}<span v-if="configHasChanged && !configHasErrors" class="red-span">*</span><span v-if="configHasErrors" class="red-span">!</span>
          </a>
      </li>
      <li>
        <a :class="{'sel': activeMenu === 'fields'}" @click.prevent="changeMenu('fields')">
          {{ t('Fields') }}<span v-if="fieldsHaveChanged && !fieldsHaveErrors" class="red-span">*</span><span v-if="fieldsHaveErrors" class="red-span">!</span>
        </a>
      </li>
      <li>
        <a :class="{'sel': activeMenu === 'rules'}" @click.prevent="changeMenu('rules')">
          {{ t('Rules') }}<span v-if="rulesHaveChanged && !rulesHaveErrors" class="red-span">*</span><span v-if="rulesHaveErrors" class="red-span">!</span>
        </a>
      </li>
      <li v-if="!isAdding">
        <a :class="{'sel': activeMenu === 'submissions'}" @click.prevent="changeMenu('submissions')">{{ t('Submissions') }}</a>
      </li>
      <li v-if="!isAdding">
        <a :class="{'sel': activeMenu === 'spam'}" @click.prevent="changeMenu('spam')">{{ t('Spam') }}</a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { mapMutations, mapState } from 'vuex';

export default {
  props: {
    menu: String
  },
  computed:{
    ...mapState(['configHasChanged', 'fieldsHaveChanged', 'rulesHaveChanged', 'rulesHaveErrors', 'fieldsHaveErrors', 'configHasErrors', 'activeMenu', 'isAdding'])
  },
  created() {
    this.changeMenu(this.menu, false);
    window.addEventListener('popstate', () => {
      const pathSplit = document.location.pathname.split('/');
      if (pathSplit.length >= 5 && pathSplit[2] === 'inspireforms') {
        this.changeMenu(pathSplit.length >= 5 ? pathSplit[5] : 'config', false);
      }
    });
  },
  methods: {
    changeMenu (menu, pushState = true) {
      this.setActiveMenu(menu);
      if (pushState) {
        let url = document.location.pathname.split('/')
        url[5] = menu;
        window.history.pushState({}, '', url.join('/'));
      }
    },
    ...mapMutations({
      setActiveMenu: 'setActiveMenu'
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

.inspireforms #sidebar-container, .inspireforms #sidebar {
  width: 150px;
  flex: 0 0 150px;
}

.inspireforms .red-span {
  color: $red400;
  padding-left:5px;
}

.inspireforms #sidebar-container {
  flex: 0 0 150px;
}

.inspireforms #main-content.has-sidebar #content-container {
  width: calc(100% - 126px);
  max-width: calc(100% - 126px);
}

</style>
