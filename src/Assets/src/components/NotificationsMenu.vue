<template>
  <nav id="notification-nav">
    <ul>
      <li v-for="notif in notifications" v-bind:key="notif.id">
        <a :href="'/admin/inspireforms/notifications/' + notif.id + '/edit'" :class="{'sel': notification && notif.id === notification.id}"
           @click.prevent="edit(notif.id)">
          <div class="name">{{ notif.name }}</div>
          <div class="small">{{ t('From') + ': ' + notif.sentFrom }}</div>
          <div class="small">{{ t('Subject') + ': ' + notif.subject }}</div>
        </a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { EventBus } from './../EventBus.js';
import axios from 'axios';

export default {
  computed: {
    ...mapState({
      notifications: state => state.notifications,
      notification: state => state.notification
    })
  },
  created() {
    EventBus.$on('notification-saved', (data) => {
      this.rerender();
    });
  },
  methods: {
    edit(id) {
      EventBus.$emit('edit-notification-click', {id: id});
    },
    rerender() {
      this.$forceUpdate();
    }
  }
};
</script>


<style lang="scss" scoped>

#notification-nav {
    font-size: 14px;
    a {
      flex-direction: column;
      align-items: baseline;
    }
    .small {
      font-size: 12px;
      text-align: right;
      padding-left: 5px;
    }
}

</style>
