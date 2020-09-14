<template>
  <div id="forms-toolbar" class="flex flex-grow">
    <button href="#" class="btn delete icon" v-if="!isAdding" :disabled="isDeleting" @click.prevent="deleteForm()">{{ t('Delete') }}</button>
    <button :disabled="!hasUnsavedChanges || isSaving || hasErrors" href="#" class="btn save submit" @click.prevent="save">{{ t('Save') }}</button>
  </div>
</template>

<script>
import { EventBus } from './../EventBus.js';
import { mapMutations, mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
	name: 'toolbar',
  data: function () {
    return {
      showSaveDelete: ['config', 'fields', 'rules']
    }
  },
  computed: {
  	...mapState(['hasUnsavedChanges', 'hasErrors', 'form', 'fields', 'rules', 'isAdding', 'isSaving', 'isDeleting']),
  },
  created() {
    window.addEventListener('keydown', (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key === 's') {
        if (this.hasUnsavedChanges && !this.isSaving) {
          event.preventDefault();
          this.save(event);
        }
      }
    });
  },
  methods: {
    save() {
      let data = {form: this.form, fields: this.fields, rules: this.rules};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      this.setIsSaving(true);
      axios.post('/admin/inspireforms/forms/save', data)
        .then((response) => {
          if (response.data.success) {
            Craft.cp.displayNotice(response.data.message);
            this.resetChanges();
            if (this.isAdding) {
              this.setIsAdding(false);
            }
          } else {
            Craft.cp.displayError(response.data.message);
            this.setErrors(response.data.errors);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.setIsSaving(false);
        });
    },
    deleteForm() {
      
    },
    ...mapMutations({
      setIsSaving: 'setIsSaving',
      setIsAdding: 'setIsAdding',
    }),
    ...mapActions({
      resetChanges: 'resetChanges'
    })
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