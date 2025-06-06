<template>
  <div id="forms-toolbar" class="flex flex-grow">
    <button v-if="!isAdding" :disabled="isLoading" href="#" class="btn icon add" @click.prevent="_new">{{ t('New') }}</button>
    <button href="#" class="btn delete icon" v-if="!isAdding" :disabled="isLoading" @click.prevent="_delete">{{ t('Delete') }}</button>
    <button v-if="!isAdding" :disabled="isLoading" href="#" class="btn save" @click.prevent="_duplicate">{{ t('Duplicate') }}</button>
    <button :disabled="isLoading || hasErrors" href="#" class="btn save submit" @click.prevent="saveForm">{{ t('Save') }}</button>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  computed: {
  	...mapState(['hasErrors', 'isAdding', 'isLoading']),
  },
  created() {
    let _this = this;
    window.addEventListener('keydown', (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key === 's') {
        if (!this.isLoading && !this.hasErrors) {
          event.preventDefault();
          _this.saveForm(event);
        }
      }
    });
  },
  methods: {
    _delete() {
      this.$confirm({
        message: this.t('This will also delete all submissions for this form, Confirm deletion ?'),
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
      this.$confirm({
        message: this.t('You will loose unsaved changes, continue anyway ?'),
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
    },
    _duplicate() {
      this.$confirm({
        message: this.t('You will loose unsaved changes, continue anyway ?'),
        button: {
          no: this.t('No'),
          yes: this.t('Yes')
        },
        callback: confirm => {
          if (confirm) {
            this.duplicateForm();
          }
        }
      })
    },
    ...mapActions(['saveForm', 'newForm', 'deleteForm', 'duplicateForm'])
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