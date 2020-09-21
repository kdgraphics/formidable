<template>
  <forms-field v-on="$listeners"
    :field="field" 
    :index="index"
    :original="original"
    :errors="errors"
  >
    <template v-slot:extraFields>
      <div class="field field-label">
        <div class="heading">
          <label>{{ t('Label') }}</label>
        </div>
        <div class="input ltr">
          <input type="text" class="text fullwidth" v-model="field.label">
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
      <div class="lightswitch-field field-default field">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <div class="lightswitch on" tabindex="0" role="switch" :aria-checked="field.default">
            <div class="lightswitch-container">
              <div class="handle"></div>
            </div>
            <input type="hidden" name="default" v-model="field.default">
          </div>
        </div>
      </div>
    </template>
  </forms-field>
</template>

<script>
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
  mounted() {
    /**
     * Copies the lightswitch value on the field object
     */
    this.$nextTick(() => {
      let _this = this;
      $(this.$el).find('.lightswitch').on('change', function(e){
        let input = $(e.target).find('input');
        let value = (input.val() == 1);
        _this.field[input.attr('name')] = value;
      });
    });
  }
};
</script>