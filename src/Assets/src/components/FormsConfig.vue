<template>
	<div id="forms-config" v-show="activeMenu == 'config'">
    <form method="post" accept-charset="UTF-8" ref="form" id="forms-form">
      <div class="field first">
        <div class="heading">
          <label class="required" for="form-name">{{ t('Name') }}</label>
        </div>
        <div class="input ltr">
          <input id="form-name" v-model="form.name" :class="'text fullwidth' + (errors.name ? ' error' : '')">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required" for="form-handle">{{ t('Handle') }}</label>
        </div>
        <div class="input ltr">
          <input id="form-handle" v-model="form.handle" :class="'text fullwidth' + (errors.handle ? ' error' : '')">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-captcha">{{ t('Captcha') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="form.captcha" class="text fullwidth" id="form-captcha">
              <option :value="null">{{ t('No captcha') }}</option>
              <option v-for="captcha in captchas" :value="captcha.handle" :key="captcha.handle">{{ captcha.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div class="field" aria-describedby="redirect-field-instructions">
        <div class="heading">
          <label for="form-redirect">{{ t('Redirect') }}</label>                                    
        </div>
        <div id="redirect-field-instructions" class="instructions">
          <p>{{ t('Url to redirect users after submission') }}</p>
        </div>
        <div class="input ltr">
          <input type="text" id="form-redirect" class="text fullwidth" v-model="form.redirect">
        </div>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label for="form-honeypot">{{ t('Honeypot') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-honeypot" class="lightswitch on" tabindex="0" role="switch" :aria-checked="form.honeypot">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" v-model="form.honeypot">
          </div>
        </div>
      </div>
      <div v-if="form.honeypot" class="field" aria-describedby="honeypotField-field-instructions">
        <div class="heading">
          <label class="required" for="form-honeypotField">{{ t('Honeypot field name') }}</label>
        </div>
        <div id="honeypotField-field-instructions" class="instructions">
          <p>{{ t('A name that is not an existing field, and has a quite sensitive name') }}</p>
        </div>
        <div class="input ltr">
          <input type="text" id="form-honeypotField" :class="'text fullwidth' + (errors.honeypotField ? ' error' : '')" v-model="form.honeypotField">
        </div>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label for="form-saveSubmissions">{{ t('Save submissions') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-saveSubmissions" class="lightswitch on" tabindex="0" role="switch" :aria-checked="form.saveSubmissions">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" v-model="form.saveSubmissions">
          </div>
        </div>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label for="form-saveSpam">{{ t('Save spam') }}</label>
        </div>
        <div class="input ltr">
          <div id="form-saveSpam" ref="lightswitch" :class="{'lightswitch': true, 'on':form.saveSpam == 1}" tabindex="0" role="switch" :aria-checked="form.saveSpam">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" v-model="form.saveSpam">
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-notify_id">{{ t('User notification') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="form.notify_id" class="text fullwidth" id="form.notify_id">
              <option :value="null">{{ t('Don\'t notify') }}</option>
              <option v-for="notif in notifications" :value="notif.id" :key="notif.id">{{ notif.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div v-if="form.notify_id" class="field">
        <div class="heading">
          <label class="required" for="form.notifyField">{{ t('User email field') }}</label>
        </div>
        <div class="input ltr">
          <input id="form-notifyField" :class="'text fullwidth' + (errors.notifyField ? ' error' : '')" v-model="form.notifyField">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-notifyAdmin_id">{{ t('Admin notification') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="form.notifyAdmin_id" id="form-notifyAdmin_id" class="text fullwidth">
              <option :value="null">{{ t('Don\'t notify') }}</option>
              <option v-for="notif in notifications" :value="notif.id" :key="notif.id">{{ notif.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div v-if="form.notifyAdmin_id" class="field" aria-describedby="emails-field-instructions">
        <div class="heading">
          <label class="required" for="form-notifyEmails">{{ t('Admin emails') }}</label>
        </div>
        <div id="emails-field-instructions" class="instructions">
          <p>{{ t('Comma separated emails') }}</p>
        </div>
        <div class="input ltr">
          <textarea id="form-notifyEmails" :class="'text fullwidth' + (errors.notifyEmails ? ' error' : '')" v-model="form.notifyEmails"></textarea>
        </div>
      </div>

      <div class="advanced">
        <div class="header" @click="showAdvanced = !showAdvanced" :class="{'closed': !showAdvanced}">{{ t('Advanced') }}</div>
        <div class="content" v-show="showAdvanced">
          <div class="hidden"></div>
          <div id="method-field" class="field first">
            <div class="heading">
              <label for="form-method">{{ t('Form method') }}</label>                                    
            </div>
            <div class="input ltr">
              <div class="select">
                <select id="form-method" v-model="form.method">
                  <option value="POST">POST</option>
                  <option value="GET">GET</option>
                </select>
              </div>
            </div>
          </div>                        
          <div class="field">
            <div class="heading">
              <label for="form-class">{{ t('Class') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" id="form-class" class="text fullwidth" v-model="form.class">
            </div>
          </div>
          <div class="field">
            <div class="heading">
              <label class="required" for="form-submitLabel">{{ t('Submit label') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" id="form-submitLabel" :class="'text fullwidth' + (errors.submitLabel ? ' error' : '')" v-model="form.submitLabel">
            </div>
          </div>
          <div id="color-field" class="field">
            <div class="heading">
              <label for="form-color">{{ t('Chart color') }}</label>
            </div>
            <div class="input ltr">
              <div id="color-container" ref="colorInput" class="flex color-container">
                <div class="color">
                  <div class="color-preview" :style="{'background-color': form.color}">
                  </div>
                </div>
                <input type="text" id="form-color" class="color-input text" v-model="form.color" size="10">
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import axios from 'axios';
import Vue from 'vue';
import { isEqual, cloneDeep, reduce, trim } from 'lodash';

export default {
  props: {
    allNotifications: Object,
    allCaptchas: Object,
    allVolumes: Array,
    formEdited: Object,
  },
  computed: {
    ...mapState(['activeMenu', 'form', 'fields', 'fieldNames']),
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    }
  },
  data: function () {
    return {
      showAdvanced: false,
      captchas: this.allCaptchas,
      notifications: this.allNotifications,
      volumes: this.allVolumes,
      original: {},
      errors: {},
    }
  },
  watch: {
    form: {
      handler: function () {
        this.configHasChanged(!isEqual(this.form, this.original));
        this.validate();
      },
      deep: true
    },
    fieldNames: function () {
      this.validate();
    },
    hasErrors: function () {
      this.configHasErrors(this.hasErrors);
    }
  },
  created() {
    this.original = cloneDeep(this.formEdited);
    this.setIsAdding(this.formEdited.id == null);
    this.setForm(this.formEdited);
  },
  mounted() {
    this.$nextTick(() => {
      Craft.initUiElements(this.$refs.form);
      new Craft.ColorInput(this.$refs.colorInput);
      new Craft.HandleGenerator('#form-name', '#form-handle');
      $(this.$refs.colorInput).on('change', function (e) {
        _this.form.color = $(e.target).val();
      });
      let _this = this;
      $(this.$el).find('.lightswitch').on('change', function(e){
        let input = $(e.target).find('input');
        let value = (input.val() == 1);
        _this.form[input.attr('name')] = value;
      });
    });
  },
  methods: {
    validate() {
      this.errors = {
        name: trim(this.form.name) == '',
        handle: trim(this.form.handle) == '',
        notifyEmails: (this.form.notifyAdmin_id && trim(this.form.notifyEmails) == ''),
        submitLabel: trim(this.form.submitLabel) == '',
        notifyField: this.validateNotifyField(),
        honeypotField: (this.form.honeypotField == '' || this.fieldNames.indexOf(this.form.honeypotField) != -1)
      };
    },
    validateNotifyField () {
      if (!this.form.notify_id) {
        return false;
      }
      let _this = this;
      let fields = this.fields.filter(function (field) {
        return (field.type == 'email' && field.name == _this.form.notifyField);
      });
      return (fields.length == 0);
    },
    ...mapMutations({
      setForm: 'setForm',
      setIsAdding: 'setIsAdding'
    }),
    ...mapActions({
      configHasChanged: 'configHasChanged',
      configHasErrors: 'configHasErrors',
    })
  }
};
</script>

<style lang="scss" scoped>
@import "~craftcms-sass/mixins";

#forms-form {
  display:flex;
  flex-wrap: wrap;
  .field {
    width: 100%;
    margin-top: 0;
  }
  .advanced {
    width: 100%;
    .header{
      box-shadow: none;
      border-radius: 0;
      padding: 5px 10px;
      cursor: pointer;
      border-top-right-radius: 5px;
      border-top-left-radius: 5px;
      &.closed {
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
      }
    }
    .content {
      padding: 10px;
      padding-bottom: 0;
      padding-top: 20px;
      border: 1px solid #e4edf6;
      border-top: none;
      border-bottom-right-radius: 5px;
      border-bottom-left-radius: 5px;
      display:flex;
      flex-wrap: wrap;
    }
  }
}

@media only screen and (min-width: 768px){
  #forms-form {
    .field {
      width: calc(50% - 10px);
      &:nth-child(2n) {
        padding-right: 5px;
      }
      &:nth-child(2n+1) {
        padding-left: 5px;
      }
      &:nth-child(3) {
        margin-top: 0
      }
    }
  }
}
</style>