<template>
    <div id="notifications-toolbar" class="flex flex-grow">
        <button v-if="!isAdding" :disabled="isLoading" class="btn add icon" @click.prevent="_new">{{ t('New') }}</button>
        <button v-if="!isAdding" :disabled="isLoading" class="btn icon delete" @click.prevent="_delete">{{ t('Delete') }}</button>
        <button :disabled="!hasUnsavedChanges || isLoading || hasErrors" class="btn submit" @click.prevent="save">{{ t('Save') }}</button>
    </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState(['isAdding', 'isLoading', 'hasErrors', 'hasUnsavedChanges', 'notification', 'loadedTime'])
  },
  created() {
    window.addEventListener('keydown', (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key === 's') {
        if (this.hasUnsavedChanges && !this.isLoading && !this.hasErrors) {
          event.preventDefault();
          this.saveNotification();
        }
      }
    });
  },
  methods: {
    _delete() {
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
    _new() {
      if (this.hasUnsavedChanges) {
        this.$confirm({
          message: this.t('You have unsaved changes, continue anyway ?'),
          button: {
            no: this.t('No'),
            yes: this.t('Yes')
          },
          callback: confirm => {
            if (confirm) {
              this.setNotification(null);
            }
          }
        });
      } else {
        this.setNotification(null);
      }
    },
    doDelete() {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      this.setIsLoading(true);
      axios.post('/admin/formidable/notifications/delete/'+this.notification.id, data)
        .then((response) => {
          if (response.data.success) {
            this.setNotifications(response.data.notifications);
            this.setNotification(response.data.id);
            this.setLoadedTime(response.data.loaded);
            Craft.cp.displayNotice(response.data.message);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.setIsLoading(false);
        });
    },
    save() {
      let data = {data: this.notification, loaded: this.loadedTime};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      this.setIsLoading(true);
      axios.post('/admin/formidable/notifications/save', data)
        .then((response) => {
          if (response.data.success) {
            this.setNotifications(response.data.notifications);
            this.setNotification(response.data.id);
            this.setLoadedTime(response.data.loaded);
            Craft.cp.displayNotice(response.data.message);
          } else {
            Craft.cp.displayError(response.data.message);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.setIsLoading(false);
        });
    },
    ...mapMutations(['setNotification', 'setIsLoading', 'setLoadedTime', 'setNotifications'])
  }
};
</script>

<style lang="scss" scoped>
@import "~craftcms-sass/mixins";

#notifications-toolbar {
    justify-content: end;
    :disabled {
      opacity: 0.5;
    }
}

</style>