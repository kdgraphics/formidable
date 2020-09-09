<template>
	<nav id="tabs" v-if="selectedTab != null">
    <ul>
      <li>
        <a href="#" :class="{'sel': selectedTab == 'config'}" @click.prevent="selectTab('config')">
          {{ t('Config') }}<span v-if="configHasChanged" class="unsaved"> *</span>
        </a>
      </li>
      <li>
        <a href="#" :class="{'sel': selectedTab == 'fields'}" @click.prevent="selectTab('fields')">
          {{ t('Fields') }}<span v-if="fieldsHaveChanged" class="unsaved"> *</span>
        </a>
      </li>
      <li>
        <a href="#" :class="{'sel': selectedTab == 'rules'}" @click.prevent="selectTab('rules')">
          {{ t('Rules') }}<span v-if="rulesHaveChanged" class="unsaved"> *</span>
        </a>
      </li>
      <li>
        <a href="#" :class="{'sel': selectedTab == 'submissions'}" @click.prevent="selectTab('submissions')">{{ t('Submissions') }}</a>
      </li>
      <li>
        <a href="#" :class="{'sel': selectedTab == 'spam'}" @click.prevent="selectTab('spam')">{{ t('Spam') }}</a>
      </li>
    </ul>
    <button type="button" id="overflow-tab-btn" data-icon="ellipsis" class="btn menubtn hidden"></button>
    <div id="overflow-tab-menu" class="menu">
      <ul role="listbox"></ul>
    </div>
  </nav>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { EventBus } from './../EventBus.js';

export default {
  computed: {
    ...mapState({
      selectedTab: state => state.selectedTab,
      configHasChanged: state => state.configHasChanged,
      fieldsHaveChanged: state => state.fieldsHaveChanged,
      rulesHaveChanged: state => state.rulesHaveChanged,
    })
  },
  methods: {
    selectTab(name) {
      if (name != this.selectedTab) {
        this.setSelectedTab(name);
      }
    },
    ...mapMutations({
      setSelectedTab: 'setSelectedTab'
    })
  }
};

</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

#content-container .unsaved {
  color: $red400;
}
</style>