<template>
  <div id="notifications-content">
    <form id="notification-form" @submit.prevent="save">
      <div class="field first">
        <div class="heading">
          <label class="required">{{ t('Name') }}</label>
        </div>
        <div class="input ltr">
          <input name="name" v-model="notification.name" :class="'text fullwidth' + (errors.name ? ' error' : '')" required="required">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required">{{ t('Method') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="notification.method" name="method" :class="'text fullwidth' + (errors.method ? ' error' : '')" required="required">
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
          <textarea name="html" :class="'text fullwidth' + (errors.html ? ' error' : '')" rows="5" required="required" v-model="notification.html"></textarea>
        </div>
      </div>
      <div class="field" v-if="notification.method == 'template'">
        <div class="heading">
          <label id="template-field-label" class="required" for="template">{{ t('Template') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" :class="'text fullwidth' + (errors.template ? ' error' : '')" name="template" v-model="notification.template" required="required">
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
          <input type="text" required="required" :class="'text fullwidth' + (errors.subject ? ' error' : '')" name="subject" v-model="notification.subject">
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
          <input type="email" required="required" :class="'text fullwidth' + (errors.sentFrom ? ' error' : '')" name="sentFrom" v-model="notification.sentFrom">
        </div>
        <ul v-if="errors.sentFrom" class="errors">
          <li v-for="error in errors.sentFrom" :key="error">{{ error }}</li>
        </ul>
      </div>
      <div id="sentFromName-field" class="field">
        <div class="heading">
          <label id="sentFromName-field-label" for="sentFromName">{{ t('Name sent from') }}</label>
        </div>
        <div class="input ltr">
          <input type="email" :class="'text fullwidth' + (errors.sentFromName ? ' error' : '')" name="sentFrom" v-model="notification.sentFromName">
        </div>
        <ul v-if="errors.sentFrom" class="errors">
          <li v-for="error in errors.sentFromName" :key="error">{{ error }}</li>
        </ul>
      </div>
    </form>
  </div>
</template>

<script>
import Vue from 'vue';
import { mapMutations, mapState } from 'vuex';
import { reduce, cloneDeep, isEqual } from 'lodash';
import * as EmailValidator from 'email-validator';

export default {
  props: {
    allNotifications: Object,
    id: Number,
    loaded: Number
  },
  computed: {
    hasErrors: function () {
      return reduce(this.errors, function (result, value) {
        return result || value;
      }, false);
    },
    ...mapState(['notification', 'counter', 'isAdding', 'original'])
  },
  data() {
    return {
      errors: {}
    }
  },
  created() {
    this.setNotifications(this.allNotifications);
    this.setNotification(this.id);
    this.setLoadedTime(this.loaded);
  },
  watch: {
    notification: {
      handler: function () {
        this.setHasUnsavedChanges(!isEqual(this.notification, this.original));
        this.validate();
      },
      deep: true
    },
    counter: function() {
      this.init();
      this.validate();
    },
    hasErrors: function () {
      this.setHasErrors(this.hasErrors);
    }
  },
  methods: {
    init() {
      this.errors = {};
      this.setHasUnsavedChanges(false);
      if (this.isAdding) {
        this.changePageTitle(this.t('Add new notification'));
      } else {
        this.changePageTitle(this.t('{name} notification', {name: this.notification.name}));
      }
    },
    validate() {
      let errors = {
        name: (this.notification.name == ''),
        method: (this.notification.method == null),
        html: (this.notification.method == 'html' && this.notification.html == ''),
        template: (this.notification.method == 'template' && this.notification.template == ''),
        subject: (this.notification.subject == ''),
        sentFrom: !EmailValidator.validate(this.notification.sentFrom)
      };
      this.errors = errors;
    },
    ...mapMutations(['setHasUnsavedChanges', 'setNotifications','setNotification', 'setHasErrors', 'setLoadedTime'])
  },
};
</script>