<template>
  <div id="forms-toolbar" class="flex flex-grow">
    <a href="#" class="btn submit add icon" @click.prevent="add()">{{ t('New') }}</a>
    <button v-if="showSaveDelete.includes(selectedTab)" href="#" class="btn delete icon" @click.prevent="deleteForm()">{{ t('Delete') }}</button>
    <button v-if="showSaveDelete.includes(selectedTab)" :disabled="!hasUnsavedChanges" href="#" class="btn save submit" @click.prevent="save()">{{ t('Save') }}</button>
  </div>
</template>

<script>
import { EventBus } from './../EventBus.js';
import { mapMutations, mapState } from 'vuex';

export default {
	name: 'toolbar',
  data: function () {
    return {
      showSaveDelete: ['config', 'fields', 'rules']
    }
  },
  computed: {
  	...mapState({
  		selectedTab: state => state.selectedTab,
  		hasUnsavedChanges: state => state.hasUnsavedChanges,
  		form: state => state.form,
  		isAdding: state => state.isAdding,
  	})
  },
  methods: {
    add() {
      EventBus.$emit('add-form-click');
    },
    save(e) {
      e.preventDefault();
      let data = {data: this.form};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      this.setErrors({});
      axios.post('/admin/inspireforms/forms/save', data)
        .then((response) => {
          if (response.data.success) {
            this.replaceForm(response.data.form);
            Craft.cp.displayNotice(response.data.message);
            EventBus.$emit('form-saved', {form: response.data.form});
            if (this.isAdding) {
              this.$forms.doEdit(response.data.form, false);
            }
          } else {
            Craft.cp.displayError(response.data.message);
            this.setErrors(response.data.errors);
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    deleteForm() {
      EventBus.$emit('delete-form-click');
    }
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