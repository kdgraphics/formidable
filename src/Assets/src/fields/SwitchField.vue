<template>
  <div>
    <slot name="name"></slot>
    <slot name="instructions"></slot>
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
    <slot name="advanced"></slot>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';

export default {
  props: {
    index: Number,
    field: Object
  },
  mounted() {
    this.$nextTick(() => {
      let _this = this;
      $(this.$el).find('.lightswitch').on('change', function(e){
        let input = $(e.target).find('input');
        let value = (input.val() == 1);
        _this.field[input.attr('name')] = value;
      });
    });
  },
};

</script>