<template>
  <nav id="notification-nav">
    <ul>
      <li v-for="notif in notifications" v-bind:key="notif.id">
        <a href="#" :class="{'sel':  notif.id === notification.id}"
           @click.prevent="edit(notif.id)">
          <div class="top">
            <div class="name">{{ notif.name }}</div>
            <span v-if="notification.id == notif.id && hasUnsavedChanges && !hasErrors" class="red-span">*</span>
            <span v-if="notification.id == notif.id && hasErrors" class="red-span">!</span>
          </div>
          <div class="small">{{ t('From') + ': ' + notif.sentFrom }}</div>
          <div class="small">{{ t('Subject') + ': ' + notif.subject }}</div>
        </a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState(['notifications', 'notification', 'hasUnsavedChanges', 'hasErrors'])
  },
  mounted () {
    this.fixSideBar();
    window.addEventListener('popstate', () => {
      let id = null;
      const pathSplit = document.location.pathname.split('/');
      if (pathSplit.length == 6 && pathSplit[4] === 'edit') {
        id = pathSplit[5];
      }
      this.setNotification(id);
    });
  },
  methods: {
    fixSideBar() {
      let size = Object.keys(this.notifications).length;
      let elem = document.querySelector('#main-content');
      let sidebar = document.querySelector('#sidebar-container');
      if (size > 0) {
        elem.classList.add('has-sidebar');
        sidebar.style.display = 'block';
      } else {
        elem.classList.remove('has-sidebar');
        sidebar.style.display = 'none';
      }
    },
    edit (id) {
      if (this.hasUnsavedChanges) {
        this.$confirm({
          message: this.t('You have unsaved changes, continue anyway ?'),
          button: {
            no: this.t('No'),
            yes: this.t('Yes')
          },
          callback: confirm => {
            if (confirm) {
              this.setNotification(id);
            }
          }
        });
      } else {
        this.setNotification(id);
      }
    },
    ...mapMutations(['setNotification'])
  }
};
</script>


<style lang="scss" scoped>
@import "~craftcms-sass/mixins";

#notification-nav {
    font-size: 14px;
    .top {
      display: flex;
      justify-content: space-between;
      width: 100%;
      .name {
        flex-grow: grow;
      }
    }
    a {
      flex-direction: column;
      align-items: baseline;
    }
    .small {
      font-size: 12px;
      text-align: left;
      padding-left: 5px;
    }
    .red-span {
      color: $red400;
      padding-left:5px;
    }
}

</style>
