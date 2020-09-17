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
    <div class="field field-extensions" aria-describedby="field-extensions-instructions">
      <div class="heading">
        <label>{{ t('Extensions') }}</label>
      </div>
      <div id="field-extensions-instructions" class="instructions"><p>{{ t('Extensions allowed (no dot), empty for all') }}</p>
      </div>
      <div class="input ltr">
        <input type="text" class="text fullwidth" v-model="field.extensions">
      </div>
    </div>
    <div class="field field-size" aria-describedby="field-size-instructions">
      <div class="heading">
        <label>{{ t('Max size') }}</label>
      </div>
      <div id="field-size-instructions" class="instructions"><p>{{ t('In Mb, no value means unlimited') }}</p>
      </div>
      <div class="input ltr">
        <input type="number" class="text fullwidth" step="0.1" v-model="field.size">
      </div>
    </div>
    <div class="field field-volume">
      <div class="heading">
        <label>{{ t('Volume') }}</label>
      </div>
      <div class="input ltr">
        <div class="select">
          <select v-model="field.volume">
            <option v-for="volume in volumes" :key="volume.id" :value="volume.id">{{ volume.name }}</option>
          </select>
        </div>
      </div>
    </div>
    <slot name="advanced"></slot>
  </div>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { isEqual } from 'lodash';

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
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    ...mapState({
      volumes: state => state.volumes,
    })
  },
  watch: {
    field: {
      hasErrors: function () {
        this.$emit('error', {index: this.index, error: this.hasErrors});
      },
      handler: function () {
        this.validate();
        this.$emit('change', {index: this.index, change: !isEqual(this.field, this.original)});
      },
      deep: true
    }
  },
  created() {
    if (!this.field.id) {
      this.field.volume = this.volumes[0].id;
    }
  },
  methods: {
    /**
     * Sets errors here in the errors object
     */
    validate () {
      // this.$set(this.errors, 'field', value);
    }
  }
};

</script>