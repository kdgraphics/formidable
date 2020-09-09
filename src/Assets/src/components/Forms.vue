<template>
	
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { EventBus } from './../EventBus.js';
import axios from 'axios';

export default {
  name: 'forms',
  props: {
    allForms: Object,
    allNotifications: Object,
    allCaptchas: Object,
    allVolumes: Array,
    formId: Number
  },
  computed: {
    ...mapState({
      forms: state => state.forms,
      form: state => state.form,
      hasUnsavedChanges: state => state.hasUnsavedChanges
    })
  },
  created() {
    this.setForms(this.allForms);
    this.setNotifications(this.allNotifications);
    this.setCaptchas(this.allCaptchas);
    this.setVolumes(this.allVolumes);
    if (this.formId) {
      this.doEdit(this.formId, false);
    }
    EventBus.$on('edit-form-click', (data) => {
      if (data.id != this.form.id) {
        this.edit(data.id);
      }
    });
  },
  methods: {
    edit(id) {
      if (this.hasUnsavedChanges) {
        this.$confirm({
          message: this.t('You have unsaved changes, continue anyway ?'),
          button: {
            no: this.t('No'),
            yes: this.t('Yes')
          },
          callback: confirm => {
            if (confirm) {
              this.doEdit(id);
            }
          }
        });
      } else {
        this.doEdit(id);
      }
    },
    doEdit(id, pushState = true) {
      if (pushState) {
        window.history.pushState({}, '', '/admin/inspireforms/forms/edit/'+id);
      }
      this.setForm(id);
    },
    ...mapMutations({
      setForm: 'setForm',
      setForms: 'setForms',
      setNotifications: 'setNotifications',
      setSelectedTab: 'setSelectedTab',
      setCaptchas: 'setCaptchas',
      setVolumes: 'setVolumes',
    })
  }
};

</script>