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
              <option v-for="volume in volumes" :key="volume.id" :value="volume.id">{{ volume.name }}</option>
            </select>
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
  created() {
    if (!this.field.id) {
      this.field.volume = this.volumes[0].id;
    }
  },
  methods: {
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
  }
};

</script>