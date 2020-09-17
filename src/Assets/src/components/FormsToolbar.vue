<template>
  <div id="forms-toolbar" class="flex flex-grow">
    <button v-if="!isAdding" :disabled="isLoading" href="#" class="btn icon add" @click.prevent="_new">{{ t('New') }}</button>
    <button href="#" class="btn delete icon" v-if="!isAdding" :disabled="isLoading" @click.prevent="_delete">{{ t('Delete') }}</button>
    <button :disabled="!hasUnsavedChanges || isLoading || hasErrors" href="#" class="btn save submit" @click.prevent="saveForm">{{ t('Save') }}</button>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  computed: {
  	...mapState(['hasUnsavedChanges', 'hasErrors', 'isAdding', 'isLoading']),
  },
  created() {
    window.addEventListener('keydown', (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key === 's') {
        if (this.hasUnsavedChanges && !this.isLoading && !this.hasErrors) {
          event.preventDefault();
          this.save(event);
        }
      }
    });
  },
  methods: {
    _delete() {
      this.$confirm({
        message: this.t('Confirm deletion ?'),
        button: {
          no: this.t('No'),
          yes: this.t('Yes')
        },
        callback: confirm => {
          if (confirm) {
            this.deleteForm();
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
              this.newForm();
            }
          }
        })
      } else {
        this.newForm();
      }
    },
    ...mapActions(['saveForm', 'newForm', 'deleteForm'])
  }
};
</script>

<style lang="scss" scoped>
#forms-toolbar {
   justify-content: end;
  :disabled {
    opacity: 0.5
  }
}
</style>