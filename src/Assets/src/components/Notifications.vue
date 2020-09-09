<template>
  <div id="notifications-content" :class="{'busy': isLoading}">
    <form method="post" accept-charset="UTF-8" id="notification-form" v-if="isEditing || isAdding" @submit="save">
      <input v-if="isEditing" type="hidden" name="id" id="field-id" v-model="notification.id">
      <div class="field first">
        <div class="heading">
          <label class="required">{{ t('Name') }}</label>
        </div>
        <div class="input ltr">
          <input name="name" v-model="notification.name" class="text fullwidth" required="required">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required">{{ t('Method') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="notification.method" name="method" class="text fullwidth" required="required">
              <option value="html">{{ t('Html') }}</option>
              <option value="template">{{ t('Template') }}</option>
            </select>
          </div>
        </div>
      </div>
      <div v-if="notification.method == 'html'" class="field" aria-describedby="html-field-instructions">
        <div class="heading">
          <label class="required">{{ t('Html') }}</label>
        </div>
        <div id="html-field-instructions" class="instructions">
          <p>{{ t('Any tag accepted, twig accepted') }}</p>
        </div>
        <div class="input ltr">
          <textarea name="html" class="text fullwidth" rows="5" required="required" v-model="notification.html"></textarea>
        </div>
      </div>
      <div class="field" v-if="notification.method == 'template'">
        <div class="heading">
          <label id="template-field-label" class="required" for="template">{{ t('Template') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" name="template" v-model="notification.template" required="required">
        </div>
      </div>
      <div class="field" aria-describedby="subject-field-instructions">
        <div class="heading">
          <label id="subject-field-label" class="required" for="subject">{{ t('Subject') }}</label>
        </div>
        <div id="subject-field-instructions" class="instructions">
          <p>{{ t('Subject for the email') }}</p>
        </div>
        <div class="input ltr">
          <input type="text" required="required" class="text fullwidth" name="subject" v-model="notification.subject">
        </div>
      </div>
      <div id="sentFrom-field" class="field" aria-describedby="sentFrom-field-instructions">
        <div class="heading">
          <label id="sentFrom-field-label" class="required" for="sentFrom">{{ t('Sent from') }}</label>
        </div>
        <div id="sentFrom-field-instructions" class="instructions">
          <p>{{ t('Email address sent from') }}</p>
        </div>
        <div class="input ltr">
          <input type="email" required="required" v-on:change="changes" class="text fullwidth" name="sentFrom" v-model="notification.sentFrom">
        </div>
        <ul v-if="errors.sentFrom" class="errors">
          <li v-for="error in errors.sentFrom" :key="error">{{ error }}</li>
        </ul>
      </div>
      <div id="actions">
        <button v-if="isEditing" class="btn" @click.prevent="deleteNotif" :disabled="isDeleting">{{ t('Delete') }}</button>
        <button :disabled="!hasUnsavedChanges" class="btn submit">{{ t('Save') }}</button>
      </div>
    </form>
  </div>
</template>

<script>
import Vue from 'vue';
import { mapMutations, mapState } from 'vuex';
import { EventBus } from './../EventBus.js';
import axios from 'axios';

export default {
  props: {
    allNotifications: Object,
    currentNotification: Object
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      isAdding: state => state.isAdding,
      isEditing: state => state.isEditing,
      isDeleting: state => state.isDeleting,
      notification: state => state.notification,
      notifications: state => state.notifications,
      original: state => state.original,
      hasUnsavedChanges: state => state.hasUnsavedChanges,
      errors: state => state.errors
    })
  },
  mounted () {
    window.addEventListener('popstate', () => {
      const pathSplit = document.location.pathname.split('/');
      if (pathSplit.length == 6 && pathSplit[4] === 'edit') {
        this.edit(pathSplit[5], false);
      } else if (pathSplit.length == 5 && pathSplit[4] === 'add') {
        this.add(false);
      }
    });
  },
  created() {
    EventBus.$on('add-notification-click', () => {
      this.add();
    });
    EventBus.$on('edit-notification-click', (data) => {
      if (data.id != this.notification.id) {
        this.edit(data.id);
      }
    });
    EventBus.$on('notification-saved', (data) => {
      this.fixSideBar();
    });
    EventBus.$on('notification-deleted', (data) => {
      this.fixSideBar();
    });

    this.setNotifications(this.allNotifications);
    if (this.currentNotification) {
      this.editMode(this.currentNotification);
    } else {
      this.doAdd();
    }
  },
  mounted() {
    this.fixSideBar();
  },
  watch: {
    notification: {
      handler: function (notification) {
        for (const key in notification) {
          if (notification[key] == '' && typeof this.original[key] == 'undefined') {
            continue;
          }
          if (notification[key] != this.original[key]) {
            this.setHasUnsavedChanges(true);
            return;
          }
        }
        this.setHasUnsavedChanges(false);
      },
      deep: true
    }
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
    changes (e) {
      this.setHasUnsavedChanges(true);
    },
    deleteNotif() {
      this.$confirm({
        message: this.t('Do you want to delete this notification ?'),
        button: {
          no: this.t('No'),
          yes: this.t('Yes')
        },
        callback: confirm => {
          if (confirm) {
            this.doDelete();
          }
        }
      });
    },
    doDelete() {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      this.setIsDeleting(true);
      axios.post('/admin/inspireforms/notifications/delete/'+this.notification.id, data)
        .then((response) => {
          if (response.data.success) {
            Craft.cp.displayNotice(response.data.message);
            this.removeNotification(this.notification.id);
            EventBus.$emit('notification-deleted', {notification: response.data.notification});
            this.doAdd();
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.setIsDeleting(false);
        });
    },
    save(e) {
      e.preventDefault();
      let data = {data: this.notification};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      this.setErrors({});
      axios.post('/admin/inspireforms/notifications/save', data)
        .then((response) => {
          if (response.data.success) {
            this.replaceNotification(response.data.notification);
            this.doEdit(response.data.notification);
            Craft.cp.displayNotice(response.data.message);
            EventBus.$emit('notification-saved', {notification: response.data.notification});
          } else {
            this.setErrors(response.data.errors);
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    add() {
      if (this.hasUnsavedChanges) {
        this.$confirm({
          message: this.t('You have unsaved changes, continue anyway ?'),
          button: {
            no: this.t('No'),
            yes: this.t('Yes')
          },
          callback: confirm => {
            if (confirm) {
              this.doAdd();
            }
          }
        });
      } else {
        this.doAdd();
      }
    },
    doAdd(pushState = true) {
      this.addMode();
      if (pushState) {
        window.history.pushState({}, '', '/admin/inspireforms/notifications/add');
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
              this.retrieve(id);
            }
          }
        });
      } else {
        this.retrieve(id);
      }
    },
    retrieve(id) {
      this.setIsLoading(true);
      const formData = new FormData();
      formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);
      axios
        .post('/admin/inspireforms/notifications/get/'+id, formData)
        .then((response) => {
          if (response.data.success) {
            this.doEdit(response.data.notification);
          } else {
            Craft.cp.displayError(response.data.error);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.setIsLoading(false);
        });
    },
    doEdit(notification, pushState = true) {
      if (pushState) {
        window.history.pushState({}, '', '/admin/inspireforms/notifications/edit/'+notification.id);
      }
      this.editMode(notification);
    },
    ...mapMutations({
      setHasUnsavedChanges: 'setHasUnsavedChanges',
      replaceNotification: 'replaceNotification',
      removeNotification: 'removeNotification',
      resetNotification: 'resetNotification',
      setNotifications: 'setNotifications',
      setIsLoading: 'setIsLoading',
      setIsDeleting: 'setIsDeleting',
      setErrors: 'setErrors',
      editMode: 'editMode',
      addMode: 'addMode'
    })
  },
};
</script>

<style lang="scss" scoped>
@import "~craftcms-sass/mixins";

#actions {
    margin-top: 24px;
    text-align: right;
    :disabled {
      opacity: 0.5;
    }
}

</style>