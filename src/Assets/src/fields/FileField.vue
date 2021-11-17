<template>
  <forms-field v-on="$listeners"
    :field="field"
    :original="original"
    :errors="errors"
  >
    <template v-slot:extraFields>
      <div class="field field-label">
        <div class="heading">
          <label>{{ t('Label') }}</label>
        </div>
        <div class="input ltr">
          <textarea type="text" :class="'text fullwidth field-label-value'+field.uid" v-model="field.label" rows="1"></textarea>
          <a href="#" @click.prevent="field.labelHtml = !field.labelHtml">{{ field.labelHtml ? 'Plain' : 'Html' }}</a>
        </div>
      </div>
      <div class="field field-placeholder">
        <div class="heading">
          <label>{{ t('Placeholder') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.placeholder">
        </div>
      </div>
      <div class="lightswitch-field field-multiple field">
        <div class="heading">
          <label>{{ t('Multiple files') }}</label>
        </div>
        <div class="input ltr">
          <div :class="'lightswitch ' + (field.multiple ? 'on' : '')" tabindex="0" role="switch" :aria-checked="field.multiple">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="multiple" v-model="field.multiple">
          </div>
        </div>
      </div>
      <div class="field field-extensions" aria-describedby="field-extensions-instructions">
        <div class="heading">
          <label>{{ t('Extensions') }}</label>
        </div>
        <div id="field-extensions-instructions" class="instructions"><p>{{ t('Extensions allowed (no dot), comma separated. leave empty for all') }}</p>
        </div>
        <div class="input ltr">
          <input type="text" :class="'text fullwidth' + (errors.extensions ? ' error' : '')" v-model="field.extensions" @input="validateExtensions">
        </div>
      </div>
      <div class="field field-volume">
        <div class="heading">
          <label>{{ t('Volume') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select v-model="field.volume">
              <option v-for="volume in volumes" :key="volume.uid" :value="volume.uid">{{ volume.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div class="field field-tooltip-label">
        <div class="heading">
          <label>{{ t('Tooltip label') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.tooltipLabel">
        </div>
      </div>
      <div class="field field-tooltip">
        <div class="heading">
          <label>{{ t('Tooltip') }}</label>
        </div>
        <div class="input ltr">
          <textarea type="text" :class="'text fullwidth field-tooltip-value'+field.uid" v-model="field.tooltip" rows="1"></textarea>
        </div>
      </div>
      <div class="field field-size">
        <div class="heading">
          <label>{{ t('Max file size (Mb)') }}</label>
        </div>
        <div class="input ltr">
          <input type="number" min="0" step="0.5" class="text fullwidth" v-model="field.size">
        </div>
      </div>
    </template>
    <template v-slot:extraAdvanced>
      <div class="lightswitch-field field-attachToUserEmail field">
        <div class="heading">
          <label>{{ t('Attach to user email') }}</label>
        </div>
        <div class="input ltr">
          <div :class="'lightswitch ' + (field.attachToUserEmail ? 'on' : '')" tabindex="0" role="switch" :aria-checked="field.attachToUserEmail">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="attachToUserEmail" v-model="field.attachToUserEmail">
          </div>
        </div>
      </div>
      <div class="lightswitch-field field-attachToAdminEmail field">
        <div class="heading">
          <label>{{ t('Attach to admin email') }}</label>
        </div>
        <div class="input ltr">
          <div :class="'lightswitch ' + (field.attachToAdminEmail ? 'on' : '')" tabindex="0" role="switch" :aria-checked="field.attachToAdminEmail">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="attachToAdminEmail" v-model="field.attachToAdminEmail">
          </div>
        </div>
      </div>
    </template>
  </forms-field>
</template>

<script>
import { mapMutations, mapState } from 'vuex';

export default {
  props: {
    index: Number,
    field: Object,
    original: Object
  },
  data() {
    return {
      errors: {}
    }
  },
  computed: {
    ...mapState(['volumes'])
  },
  watch: {
    'field.labelHtml': function () {
      this.toggleRedactor();
    } 
  },
  mounted() {
    this.$nextTick(() => {
      Craft.initUiElements(this.$el);
      let _this = this;
      $(this.$el).find('.lightswitch').on('change', function(e){
        let input = $(e.target).find('input');
        let value = (input.val() == 1);
        _this.field[input.attr('name')] = value;
      });
    });
    if (this.field.labelHtml) {
      this.enableLabelRedactor();
    }
    this.enableTooltipRedactor();
  },
  methods: {
    enableTooltipRedactor: function () {
      $R('.field-tooltip-value'+this.field.uid, {
        callbacks: {
          changed: (html) => {
            this.field.tooltip = html;
          }
        }
      });
    },
    enableLabelRedactor: function () {
      $R('.field-label-value'+this.field.uid, {
        callbacks: {
          changed: (html) => {
            this.field.label = html;
          }
        }
      });
    },
    toggleRedactor: function() {
      if (this.field.labelHtml) {
        this.enableLabelRedactor();
      } else {
        $R('.field-label-value'+this.field.uid, 'destroy');
      }
    },
    validateExtensions() {
      if (this.field.extensions == '') {
        this.$set(this.errors, 'extensions', false);
        return;
      }
      for (let elem of this.field.extensions.split(',')) {
        if (!elem.match(/^[0-9a-z]+$/)) {
          this.$set(this.errors, 'extensions', true);
          return;
        }
      }
      this.$set(this.errors, 'extensions', false);
    }
  },
  created() {
    if (!this.field.id) {
      this.field.volume = this.volumes[0].id;
    }
  }
};

</script>