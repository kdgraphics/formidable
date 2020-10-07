<template>
	<div id="forms-config" v-show="activeTab == 'config'">
    <form method="post" accept-charset="UTF-8" ref="form" id="forms-form">
      <div class="half">
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
        <div class="lightswitch-field field">
          <div class="heading">
            <label for="form-frontEndValidation">{{ t('Enable front-end validation') }}</label>
          </div>
          <div class="input ltr">
            <div id="form-frontEndValidation" :class="{lightswitch:true, on:form.frontEndValidation}" tabindex="0" role="switch" :aria-checked="form.frontEndValidation">
              <div class="lightswitch-container">
                <div class="handle"></div>
              </div>
              <input type="hidden" name="frontEndValidation" v-model="form.frontEndValidation">
            </div>
          </div>
        </div>
        <div class="lightswitch-field field">
          <div class="heading">
            <label for="form-saveSubmissions">{{ t('Save submissions') }}</label>
          </div>
          <div class="input ltr">
            <div id="form-saveSubmissions" :class="{lightswitch:true, on:form.saveSubmissions}" tabindex="0" role="switch" :aria-checked="form.saveSubmissions">
              <div class="lightswitch-container">
                <div class="handle"></div>
              </div>
              <input type="hidden" name="saveSubmissions" v-model="form.saveSubmissions">
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
              <input type="hidden" name="saveSpam" v-model="form.saveSpam">
            </div>
          </div>
        </div>
        <div class="lightswitch-field field">
          <div class="heading">
            <label for="form-honeypot">{{ t('Honeypot') }}</label>
          </div>
          <div class="input ltr">
            <div id="form-honeypot" :class="{lightswitch:true, on:form.honeypot}" tabindex="0" role="switch" :aria-checked="form.honeypot">
              <div class="lightswitch-container">
                <div class="handle"></div>
              </div>
              <input type="hidden" name="honeypot" v-model="form.honeypot">
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
            <div class="select" :class="{'error' : errors.notifyField}">
              <select v-model="notifyField" id="form.notifyField">
                <option v-for="field in emailFields" :key="field.name" :value="field">{{ field.name }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="half">
        <div class="field">
          <div class="heading">
            <label class="required" for="form-handle">{{ t('Handle') }}</label>
          </div>
          <div class="input ltr">
            <input id="form-handle" v-model="form.handle" :disabled="!isAdding" :class="'text fullwidth' + (errors.handle ? ' error' : '')">
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
        <div class="field">
          <div class="heading">
            <label for="form-notifySpam_id">{{ t('Spam notification') }}</label>
          </div>
          <div class="input ltr">
            <div class="select">
              <select v-model="form.notifySpam_id" class="text fullwidth" id="form.notifySpam_id">
                <option :value="null">{{ t('Don\'t notify') }}</option>
                <option v-for="notif in notifications" :value="notif.id" :key="notif.id">{{ notif.name }}</option>
              </select>
            </div>
          </div>
        </div>
        <div v-if="form.notifySpam_id" class="field" aria-describedby="spamemails-field-instructions">
          <div class="heading">
            <label class="required" for="form-notifySpamEmails">{{ t('Spam notification recipients') }}</label>
          </div>
          <div id="spamemails-field-instructions" class="instructions">
            <p>{{ t('Comma separated emails') }}</p>
          </div>
          <div class="input ltr">
            <textarea id="form-notifySpamEmails" :class="'text fullwidth' + (errors.notifySpamEmails ? ' error' : '')" v-model="form.notifySpamEmails"></textarea>
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
            <label class="required" for="form-notifyEmails">{{ t('Admin notification recipients') }}</label>
          </div>
          <div id="emails-field-instructions" class="instructions">
            <p>{{ t('Comma separated emails') }}</p>
          </div>
          <div class="input ltr">
            <textarea id="form-notifyEmails" :class="'text fullwidth' + (errors.notifyEmails ? ' error' : '')" v-model="form.notifyEmails"></textarea>
          </div>
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
          <div class="field">
            <div class="heading">
              <label for="form-submitClass">{{ t('Submit class') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" id="form-submitClass" :class="'text fullwidth' + (errors.submitClass ? ' error' : '')" v-model="form.submitClass">
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
import { cloneDeep, reduce, trim, isEqual } from 'lodash';
import slugify from 'slugify';

export default {
  props: {
    allNotifications: Object,
    allCaptchas: Object,
    allVolumes: Array,
    formEdited: Object,
    loaded: Number
  },
  computed: {
    ...mapState(['activeTab', 'form', 'fields', 'isAdding', 'loadedTime']),
    /**
     * @return {Boolean} global error state
     */
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    /**
     * @return {Array} list of field names
     */
    fieldNames: function () {
      return this.fields.map((field) => field.name);
    },
    emailFields: function () {
      return this.fields.filter((field) => field.type == 'email');
    },
    notifyField: {
      get: function() {
        return this.notifyFieldObject;
      },
      set: function(field) {
        this.notifyFieldObject = field;
        this.$set(this.form, 'notifyField', field.name);
      }
    }
  },
  data: function () {
    return {
      showAdvanced: false,
      captchas: this.allCaptchas,
      notifications: this.allNotifications,
      original: {},
      errors: {},
      notifyFieldObject: null
    }
  },
  watch: {
    loadedTime: function() {
      this.init();
    },
    'form.name': function() {
      this.slugifyHandle();
    },
    form: {
      handler: function() {
        this.configHasChanged(!isEqual(this.form, this.original));
        this.validate();
      },
      deep: true
    },
    fieldNames: function() {
      this.validate();
    },
    hasErrors: function() {
      this.configHasErrors(this.hasErrors);
    }
  },
  created() {
    this.setIsAdding(this.formEdited.id == null);
    this.setForm(this.formEdited);
    this.setLoadedTime(this.loaded);
    this.setVolumes(this.allVolumes);
    this.init();
  },
  mounted() {
    this.$nextTick(() => {
      /**
       * Initialize Craft elements,
       * fixes color input on the form object
       * fixes lightswitch inputs on the form object
       */
      Craft.initUiElements(this.$refs.form);
      new Craft.ColorInput(this.$refs.colorInput);
      $(this.$refs.colorInput).on('change', function (e) {
        _this.form.color = $(e.target).val();
      });
      let _this = this;
      $(this.$el).find('.lightswitch').on('change', function(e){
        let input = $(e.target).find('input');
        let value = (input.val() == 1 ? 1 : 0);
        _this.form[input.attr('name')] = value;
      });
    });
  },
  methods: {
    init() {
      this.errors = {};
      this.original = cloneDeep(this.form);
      this.notifyFieldObject = this.fields.filter((field) => field.name == this.form.notifyField)[0];
      if (this.isAdding) {
        this.changePageTitle(this.t('Add new form'));
      } else {
        this.changePageTitle(this.t('{form} form', {form: this.form.name}));
      }
    },
    slugifyHandle(){
      if (this.isAdding && this.form.name) {
        this.form.handle = slugify(this.form.name, {lower: true});
      }
    },
    validate() {
      this.errors = {
        name: trim(this.form.name) == '',
        handle: trim(this.form.handle) == '',
        notifyEmails: (this.form.notifyAdmin_id && trim(this.form.notifyEmails) == ''),
        notifySpamEmails: (this.form.notifySpam_id && trim(this.form.notifySpamEmails) == ''),
        submitLabel: trim(this.form.submitLabel) == '',
        notifyField: this.validateNotifyField(),
        honeypotField: this.validateHoneypotField()
      };
    },
    validateHoneypotField() {
      if (!this.form.honeypot) {
        return false;
      }
      return (!this.form.honeypotField || this.fieldNames.indexOf(this.form.honeypotField) != -1);
    },
    validateNotifyField () {
      if (!this.form.notify_id) {
        return false;
      }
      return (this.form.notifyField == null || this.form.notifyField == '');
    },
    ...mapMutations(['setForm', 'setLoadedTime', 'setIsAdding', 'setVolumes']),
    ...mapActions(['configHasChanged', 'configHasErrors'])
  }
};
</script>

<style lang="scss" scoped>
@import "~craftcms-sass/mixins";

#forms-form {
  display:flex;
  flex-wrap: wrap;
  .half {
    width: 100%;
  }
  .field {
    width: 100%;
    margin-top: 0;
  }
  .select {
    &.error {
      border: 1px solid $red400;
    }
  }
  .advanced {
    width: 100%;
    margin-top: 20px;
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
    .half {
      width: calc(50% - 10px);
      &:first-child {
        padding-right: 10px;
      }
      &:nth-child(2) {
        padding-left: 10px;
      }
    }
    .advanced .field {
      width: calc(50% - 10px);
      &:nth-child(2n) {
        padding-right: 10px;
      }
      &:nth-child(2n+1) {
        padding-left: 10px;
      }
    }
  }
}
</style>