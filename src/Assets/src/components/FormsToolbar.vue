<template>
  <div
    id="forms-toolbar"
    class="flex flex-grow"
  >
    <button
      v-if="!isAdding"
      :disabled="isLoading"
      href="#"
      class="btn icon add"
      @click.prevent="_new"
    >
      {{ t('New') }}
    </button>
    <button
      v-if="!isAdding"
      href="#"
      class="btn delete icon"
      :disabled="isLoading"
      @click.prevent="_delete"
    >
      {{ t('Delete') }}
    </button>
    <button
      v-if="!isAdding"
      :disabled="isLoading"
      href="#"
      class="btn save"
      @click.prevent="_duplicate"
    >
      {{ t('Duplicate') }}
    </button>
    <button
      :disabled="isLoading"
      href="#"
      class="btn save submit"
      @click.prevent="saveForm"
    >
      {{ t('Save') }}
    </button>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState(['isAdding', 'isLoading']),
  },
  created() {
    let _this = this;
    window.addEventListener('keydown', (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key === 's') {
        if (!this.isLoading) {
          event.preventDefault();
          _this.saveForm(event);
        }
      }
    });
  },
  methods: {
    _delete() {
      if (confirm(this.t('This will also delete all submissions for this form, Confirm deletion ?'))) {
        this.deleteForm();
      }
    },
    _new() {
      if (confirm(this.t('You will loose unsaved changes, continue anyway ?'))) {
        this.newForm();
      }
    },
    _duplicate() {
      if (confirm(this.t('You will loose unsaved changes, continue anyway ?'))) {
        this.duplicateForm();
      }
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
  .btn.delete:before {
    margin-top: -5px;
  }
}
</style>