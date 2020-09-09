<template>
	<div id="forms-config" v-show="selectedTab == 'config'">
    <form method="post" accept-charset="UTF-8" ref="form" id="forms-form">
      <input type="hidden" name="id" v-model="form.id">
      <div class="field first">
        <div class="heading">
          <label class="required">{{ t('Name') }}</label>
        </div>
        <div class="input ltr">
          <input name="name" id="form-name" v-model="form.name" class="text fullwidth" required="required">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label class="required">{{ t('Handle') }}</label>
        </div>
        <div class="input ltr">
          <input id="form-handle" v-model="form.handle" name="handle" class="text fullwidth" required="required" :disabled="isEditing">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Captcha') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="form.captcha" name="captcha" class="text fullwidth">
              <option :value="null">{{ t('No captcha') }}</option>
              <option v-for="captcha in captchas" :value="captcha.handle" :key="captcha.handle">{{ captcha.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div id="redirect-field" class="field" aria-describedby="redirect-field-instructions">
        <div class="heading">
          <label id="redirect-field-label" for="redirect">{{ t('Redirect') }}</label>                                    
        </div>
        <div id="redirect-field-instructions" class="instructions">
          <p>{{ t('Url to redirect users after submission') }}</p>
        </div>
        <div class="input ltr">
          <input type="text" id="redirect" class="text fullwidth" name="redirect" v-model="form.redirect">
        </div>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label for="honeypot">{{ t('Honeypot') }}</label>
        </div>
        <div class="input ltr">
          <div id="honeypot" class="lightswitch on" tabindex="0" role="switch" :aria-checked="form.honeypot">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="honeypot" v-model="form.honeypot">
          </div>
        </div>
      </div>
      <div v-if="form.honeypot" id="honeypotField-field" class="field" aria-describedby="honeypotField-field-instructions">
        <div class="heading">
          <label id="honeypotField-field-label" class="required" for="honeypotField">{{ t('Honeypot field name') }}</label>
        </div>
        <div id="honeypotField-field-instructions" class="instructions">
          <p>{{ t('A name that is not an existing field, and has a quite sensitive name') }}</p>
        </div>
        <div class="input ltr">
          <input type="text" id="honeypotField" class="text fullwidth" name="honeypotField" v-model="form.honeypotField" required="required">
        </div>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label for="saveSubmissions">{{ t('Save submissions') }}</label>
        </div>
        <div class="input ltr">
          <div id="saveSubmissions" class="lightswitch on" tabindex="0" role="switch" :aria-checked="form.saveSubmissions">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="saveSubmissions" v-model="form.saveSubmissions">
          </div>
        </div>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label for="saveSpam">{{ t('Save spam') }}</label>
        </div>
        <div class="input ltr">
          <div id="saveSpam" ref="lightswitch" :class="{'lightswitch': true, 'on':form.saveSpam == 1}" tabindex="0" role="switch" :aria-checked="form.saveSpam">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="saveSpam" v-model="form.saveSpam">
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('User notification') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="form.notify_id" name="notify_id" class="text fullwidth">
              <option :value="null">{{ t('Don\'t notify') }}</option>
              <option v-for="notif in notifications" :value="notif.id" :key="notif.id">{{ notif.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div v-if="form.notify_id" class="field">
        <div class="heading">
          <label class="required">{{ t('User email field') }}</label>
        </div>
        <div class="input ltr">
          <input name="notifyField" class="text fullwidth" v-model="form.notifyField">
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Admin notification') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="form.notifyAdmin_id" name="notifyAdmin_id" class="text fullwidth">
              <option :value="null">{{ t('Don\'t notify') }}</option>
              <option v-for="notif in notifications" :value="notif.id" :key="notif.id">{{ notif.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div v-if="form.notifyAdmin_id" class="field" aria-describedby="emails-field-instructions">
        <div class="heading">
          <label class="required">{{ t('Admin emails') }}</label>
        </div>
        <div id="emails-field-instructions" class="instructions">
          <p>{{ t('Comma separated emails') }}</p>
        </div>
        <div class="input ltr">
          <textarea name="notifyEmails" class="text fullwidth" v-model="form.notifyEmails"></textarea>
        </div>
      </div>

      <div class="advanced">
        <div class="header" @click="showAdvanced = !showAdvanced" :class="{'closed': !showAdvanced}">{{ t('Advanced') }}</div>
        <div class="content" v-show="showAdvanced">
          <div class="hidden"></div>
          <div id="method-field" class="field first">
            <div class="heading">
              <label id="method-field-label" for="method">{{ t('Form method') }}</label>                                    
            </div>
            <div class="input ltr">
              <div class="select">
                <select id="method" name="method" v-model="form.method">
                  <option value="POST">POST</option>
                  <option value="GET">GET</option>
                </select>
              </div>
            </div>
          </div>                        
          <div id="class-field" class="field">
            <div class="heading">
              <label id="class-field-label" for="class">{{ t('Class') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" id="class" class="text fullwidth" name="class" v-model="form.class">
            </div>
          </div>
          <div id="submitLabel-field" class="field">
            <div class="heading">
              <label id="submitLabel-field-label" class="required" for="submitLabel">{{ t('Submit label') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" id="submitLabel" class="text fullwidth" name="submitLabel" v-model="form.submitLabel">
            </div>
          </div>
          <div id="color-field" class="field">
            <div class="heading">
              <label id="color-field-label" for="color">{{ t('Chart color') }}</label>
            </div>
            <div class="input ltr">
              <div id="color-container" ref="colorInput" class="flex color-container">
                <div class="color">
                  <div class="color-preview" :style="{'background-color': form.color}">
                  </div>
                </div>
                <input type="text" id="color" class="color-input text" name="color" v-model="form.color" size="10">
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { EventBus } from './../EventBus.js';
import axios from 'axios';
import Vue from 'vue';

export default {
  computed: {
    ...mapState({
      form: state => state.form,
      notifications: state => state.notifications,
      captchas: state => state.captchas,
      isEditing: state => state.isEditing,
      originalForm: state => state.originalForm,
      selectedTab: state => state.selectedTab,
    })
  },
  data: function () {
    return {
      showAdvanced: false
    }
  },
  watch: {
    form: {
      handler: function (form) {
        for (const key in form) {
          if (form[key] == '' && typeof this.originalForm[key] == 'undefined') {
            continue;
          }
          if (form[key] != this.originalForm[key]) {
            this.setConfigHasChanged(true);
            return;
          }
        }
        this.setConfigHasChanged(false);
      },
      deep: true
    }
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
    ...mapMutations({
      setConfigHasChanged: 'setConfigHasChanged'
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