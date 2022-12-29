<template>
  <div
    v-show="activeTab == 'config'"
    id="forms-config"
  >
    <form
      id="forms-form"
      ref="form"
      method="post"
      accept-charset="UTF-8"
    >
      <div class="half">
        <div class="field first">
          <div class="heading">
            <label
              class="required"
              for="form-name"
            >{{ t('Name') }}</label>
          </div>
          <div class="input ltr">
            <input
              id="form-name"
              v-model="form.name"
              class="text fullwidth"
            >
          </div>
          <ul class="errors">
            <li
              v-for="error, index in errors.name"
              :key="index"
              class="error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="field">
          <div class="heading">
            <label for="form-captcha">{{ t('Captcha') }}</label>
          </div>
          <div class="input ltr">
            <div class="select">
              <select
                id="form-captcha"
                v-model="form.captcha"
                class="text fullwidth"
              >
                <option :value="null">
                  {{ t('No captcha') }}
                </option>
                <option
                  v-for="captcha in captchas"
                  :key="captcha.handle"
                  :value="captcha.handle"
                >
                  {{ captcha.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <div class="heading">
            <label for="form-spamCheck">{{ t('Check if user email is a known spammer') }}</label>
          </div>
          <div class="input ltr">
            <div class="select">
              <select
                id="form-spamCheck"
                v-model="form.spamCheck"
              >
                <option value="">
                  {{ t('Don\'t check') }}
                </option>
                <option
                  v-for="field in emailFields"
                  :key="field.handle"
                  :value="field.handle"
                >
                  Field : {{ field.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <div class="heading">
            <label for="form-obfuscateFieldNames">{{ t('Obfuscate field names') }}</label>
          </div>
          <div class="instructions">
            {{ t('Make all field names impossible to read, helps against spam') }}
          </div>
          <div class="input ltr">
            <div
              id="form-obfuscateFieldNames"
              :class="{lightswitch:true, on:form.obfuscateFieldNames}"
              tabindex="0"
              role="switch"
              :aria-checked="form.obfuscateFieldNames"
            >
              <div class="lightswitch-container">
                <div class="handle" />
              </div>
              <input
                v-model="form.obfuscateFieldNames"
                type="hidden"
                name="obfuscateFieldNames"
              >
            </div>
          </div>
        </div>
        <div class="lightswitch-field field">
          <div class="heading">
            <label for="form-saveSubmissions">{{ t('Save submissions') }}</label>
          </div>
          <div class="input ltr">
            <div
              id="form-saveSubmissions"
              :class="{lightswitch:true, on:form.saveSubmissions}"
              tabindex="0"
              role="switch"
              :aria-checked="form.saveSubmissions"
            >
              <div class="lightswitch-container">
                <div class="handle" />
              </div>
              <input
                v-model="form.saveSubmissions"
                type="hidden"
                name="saveSubmissions"
              >
            </div>
          </div>
        </div>
        <div class="lightswitch-field field">
          <div class="heading">
            <label for="form-saveSpam">{{ t('Save spam') }}</label>
          </div>
          <div class="input ltr">
            <div
              id="form-saveSpam"
              ref="lightswitch"
              :class="{'lightswitch': true, 'on':form.saveSpam == 1}"
              tabindex="0"
              role="switch"
              :aria-checked="form.saveSpam"
            >
              <div class="lightswitch-container">
                <div class="handle" />
              </div>
              <input
                v-model="form.saveSpam"
                type="hidden"
                name="saveSpam"
              >
            </div>
          </div>
        </div>
        <div class="lightswitch-field field">
          <div class="heading">
            <label for="form-honeypot">{{ t('Honeypot') }}</label>
          </div>
          <div class="input ltr">
            <div
              id="form-honeypot"
              :class="{lightswitch:true, on:form.honeypot}"
              tabindex="0"
              role="switch"
              :aria-checked="form.honeypot"
            >
              <div class="lightswitch-container">
                <div class="handle" />
              </div>
              <input
                v-model="form.honeypot"
                type="hidden"
                name="honeypot"
              >
            </div>
          </div>
        </div>
        <div
          v-if="form.honeypot"
          class="field"
          aria-describedby="honeypotField-field-instructions"
        >
          <div class="heading">
            <label
              class="required"
              for="form-honeypotField"
            >{{ t('Honeypot field name') }}</label>
          </div>
          <div
            id="honeypotField-field-instructions"
            class="instructions"
          >
            <p>{{ t('A name that is not an existing field, and has a quite sensitive name') }}</p>
          </div>
          <div class="input ltr">
            <input
              id="form-honeypotField"
              v-model="form.honeypotField"
              type="text"
              class="text fullwidth"
            >
          </div>
          <ul class="errors">
            <li
              v-for="error, index in errors.honeypotField"
              :key="index"
              class="error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
      </div>

      <div class="half">
        <div class="field">
          <div class="heading">
            <label
              class="required"
              for="form-handle"
            >{{ t('Handle') }}</label>
          </div>
          <div class="input ltr">
            <input
              id="form-handle"
              v-model="form.handle"
              class="text fullwidth"
            >
          </div>
          <ul class="errors">
            <li
              v-for="error, index in errors.handle"
              :key="index"
              class="error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div
          class="field"
          aria-describedby="redirect-field-instructions"
        >
          <div class="heading">
            <label for="form-redirect">{{ t('Redirect') }}</label>                                    
          </div>
          <div
            id="redirect-field-instructions"
            class="instructions"
          >
            <p>{{ t('Url to redirect users after submission') }}</p>
          </div>
          <div class="input ltr">
            <input
              id="form-redirect"
              v-model="form.redirect"
              type="text"
              class="text fullwidth"
            >
          </div>
        </div>
        <div class="field">
          <div class="heading">
            <label for="form-notifySpam_key">{{ t('Spam email') }}</label>
          </div>
          <div class="input ltr">
            <div class="select">
              <select
                id="form.notifySpam_key"
                v-model="form.notifySpam_key"
                class="text fullwidth"
              >
                <option :value="null">
                  {{ t('Don\'t notify') }}
                </option>
                <option
                  v-for="email in emails"
                  :key="email.key"
                  :value="email.key"
                >
                  {{ email.heading }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div
          v-if="form.notifySpam_key"
          class="field"
          aria-describedby="spamemails-field-instructions"
        >
          <div class="heading">
            <label
              class="required"
              for="form-notifySpamEmails"
            >{{ t('Spam email recipients (env variables supported)') }}</label>
          </div>
          <div
            id="spamemails-field-instructions"
            class="instructions"
          >
            <p>{{ t('Comma separated emails') }}</p>
          </div>
          <div class="input ltr">
            <textarea
              id="form-notifySpamEmails"
              v-model="form.notifySpamEmails"
              class="text fullwidth"
            />
          </div>
          <ul class="errors">
            <li
              v-for="error, index in errors.notifySpamEmails"
              :key="index"
              class="error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="field">
          <div class="heading">
            <label for="form-notifyAdmin_key">{{ t('Admin email') }}</label>
          </div>
          <div class="input ltr">
            <div class="select">
              <select
                id="form-notifyAdmin_key"
                v-model="form.notifyAdmin_key"
                class="text fullwidth"
              >
                <option :value="null">
                  {{ t('Don\'t notify') }}
                </option>
                <option
                  v-for="email in emails"
                  :key="email.key"
                  :value="email.key"
                >
                  {{ email.heading }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div
          v-if="form.notifyAdmin_key"
          class="field"
          aria-describedby="emails-field-instructions"
        >
          <div class="heading">
            <label
              class="required"
              for="form-notifyEmails"
            >{{ t('Admin email recipients (env variables supported)') }}</label>
          </div>
          <div
            id="emails-field-instructions"
            class="instructions"
          >
            <p>{{ t('Comma separated emails') }}</p>
          </div>
          <div class="input ltr">
            <textarea
              id="form-notifyEmails"
              v-model="form.notifyEmails"
              class="text fullwidth"
            />
          </div>
          <ul class="errors">
            <li
              v-for="error, index in errors.notifyEmails"
              :key="index"
              class="error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="field">
          <div class="heading">
            <label for="form-notify_key">{{ t('User email') }}</label>
          </div>
          <div class="input ltr">
            <div class="select">
              <select
                id="form.notify_key"
                v-model="form.notify_key"
                class="text fullwidth"
              >
                <option :value="null">
                  {{ t('Don\'t notify') }}
                </option>
                <option
                  v-for="email in emails"
                  :key="email.key"
                  :value="email.key"
                >
                  {{ email.heading }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div
          v-if="form.notify_key"
          class="field"
        >
          <div class="heading">
            <label
              class="required"
              for="form.notifyField"
            >{{ t('User email field') }}</label>
          </div>
          <div class="input ltr">
            <div class="select">
              <select
                id="form.notifyField"
                v-model="form.notifyField"
              >
                <option
                  v-for="field in emailFields"
                  :key="field.handle"
                  :value="field.handle"
                >
                  {{ field.name }}
                </option>
              </select>
            </div>
          </div>
          <ul class="errors">
            <li
              v-for="error, index in errors.notifyField"
              :key="index"
              class="error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
      </div>
      <div class="advanced">
        <div class="header">
          {{ t('Advanced') }}
        </div>
        <div class="content">
          <div class="hidden" />
          <div
            id="method-field"
            class="field first"
          >
            <div class="heading">
              <label for="form-method">{{ t('Form method') }}</label>                                    
            </div>
            <div class="input ltr">
              <div class="select">
                <select
                  id="form-method"
                  v-model="form.method"
                >
                  <option value="POST">
                    POST
                  </option>
                  <option value="GET">
                    GET
                  </option>
                </select>
              </div>
            </div>
          </div>
          <div class="field">
            <div class="heading">
              <label for="form-autocomplete">{{ t('Autocomplete') }}</label>
            </div>
            <div class="input ltr">
              <div
                id="form-autocomplete"
                :class="{lightswitch:true, on:form.autocomplete}"
                tabindex="0"
                role="switch"
                :aria-checked="form.autocomplete"
              >
                <div class="lightswitch-container">
                  <div class="handle" />
                </div>
                <input
                  v-model="form.autocomplete"
                  type="hidden"
                  name="autocomplete"
                >
              </div>
            </div>
          </div>                   
          <div class="field">
            <div class="heading">
              <label for="form-class">{{ t('Class') }}</label>
            </div>
            <div class="input ltr">
              <input
                id="form-class"
                v-model="form.class"
                type="text"
                class="text fullwidth"
              >
            </div>
            <ul class="errors">
              <li
                v-for="error, index in errors.class"
                :key="index"
                class="error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="field">
            <div class="heading">
              <label for="form-htmlId">{{ t('Html id') }}</label>
            </div>
            <div class="input ltr">
              <input
                id="form-htmlId"
                v-model="form.htmlId"
                type="text"
                class="text fullwidth"
              >
            </div>
            <ul class="errors">
              <li
                v-for="error, index in errors.htmlId"
                :key="index"
                class="error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="field">
            <div class="heading">
              <label
                class="required"
                for="form-submitLabel"
              >{{ t('Submit label') }}</label>
            </div>
            <div class="input ltr">
              <input
                id="form-submitLabel"
                v-model="form.submitLabel"
                type="text"
                class="text fullwidth"
              >
            </div>
            <ul class="errors">
              <li
                v-for="error, index in errors.submitLabel"
                :key="index"
                class="error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="field">
            <div class="heading">
              <label for="form-submitClass">{{ t('Submit class') }}</label>
            </div>
            <div class="input ltr">
              <input
                id="form-submitClass"
                v-model="form.submitClass"
                type="text"
                class="text fullwidth"
              >
            </div>
            <ul class="errors">
              <li
                v-for="error, index in errors.submitClass"
                :key="index"
                class="error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div
            id="color-field"
            class="field"
          >
            <div class="heading">
              <label for="form-color">{{ t('Chart color') }}</label>
            </div>
            <div class="input ltr">
              <div
                id="color-container"
                ref="colorInput"
                class="flex color-container"
              >
                <div class="color">
                  <div
                    class="color-preview"
                    :style="{'background-color': form.color}"
                  />
                </div>
                <input
                  id="form-color"
                  v-model="form.color"
                  type="text"
                  class="color-input text"
                  size="10"
                >
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
    allEmails: {
      type: Array,
      default: () => {return [];}
    },
    allCaptchas: {
      type: Object,
      default: () => {return {};}
    },
    formEdited: {
      type: Number,
      default: null
    }
  },
  data: function () {
    return {
      captchas: this.allCaptchas,
      emails: this.allEmails,
    }
  },
  computed: {
    errors: function () {
      if (!this.form.errors) {
        return {};
      }
      return this.form.errors;
    },
    fieldNames: function () {
      return this.allFields.map((field) => field.name);
    },
    emailFields: function () {
      return this.allFields.filter((field) => field.active && field.type == 'email');
    },
    allFields: function () {
      let fields = [];
      for(let field of this.fields) {
        if (field.type == 'group') {
          for(let subfield of field.fields) {
            fields.push(subfield);
          }
        } else {
          fields.push(field);
        }
      }
      return fields;
    },
    ...mapState(['activeTab', 'form', 'fields', 'isAdding', 'loadedTime']),
  },
  watch: {
    loadedTime: function() {
      this.init();
    },
    'form.name': function() {
      this.slugifyHandle();
    }
  },
  created() {
    if (this.formEdited) {
      this.loadForm(this.formEdited);
    } else {
      this.newForm();
    }
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
      if (this.isAdding) {
        this.changePageTitle(this.t('Add new form'));
      } else {
        this.changePageTitle(this.t('{form} form', {form: this.form.name}));
      }
    },
    slugifyHandle(){
      if (this.isAdding) {
        this.form.handle = slugify(this.form.name ?? '', {lower: true});
      }
    },
    ...mapMutations(['setForm', 'setLoadedTime', 'setIsAdding', 'setVolumes']),
    ...mapActions(['loadForm', 'newForm'])
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
      border-top-right-radius: 5px;
      border-top-left-radius: 5px;
    }
    .content {
      padding: 10px;
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
      width: 50%;
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