<template>
  <forms-field>
    <template #extraFields>
      <div class="field">
        <div class="heading">
          <label>{{ t('Default value') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.default"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Format') }}</label>
        </div>
        <div class="instructions">
          {{ t('Flatpickr valid format, as seen ') }} <a
            href="https://flatpickr.js.org/formatting/"
            target="_blank"
          >here</a>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.format"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Disable on mobile') }}</label>
        </div>
        <div class="input ltr">
          <div :class="{lightswitch:true, on:selectedField.disableMobile}">
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.disableMobile"
              type="hidden"
              name="disableMobile"
            >
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-enableTime">{{ t('Enable time') }}</label>
        </div>
        <div class="input ltr">
          <div :class="{lightswitch:true, on:selectedField.enableTime}">
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.enableTime"
              type="hidden"
              name="enableTime"
            >
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-noCalendar">{{ t('Disable calendar') }}</label>
        </div>
        <div class="instructions">
          {{ t('Use it along with enable time to create a time picker.') }}
        </div>
        <div class="input ltr">
          <div :class="{lightswitch:true, on:selectedField.noCalendar}">
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.noCalendar"
              type="hidden"
              name="noCalendar"
            >
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label for="form-enableSeconds">{{ t('Enable seconds in the time picker') }}</label>
        </div>
        <div class="input ltr">
          <div :class="{lightswitch:true, on:selectedField.enableSeconds}">
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.enableSeconds"
              type="hidden"
              name="enableSeconds"
            >
          </div>
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Min date') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.minDate"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Max date') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.maxDate"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Minute increment') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.minuteIncrement"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.minuteIncrement"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
    </template>
  </forms-field>
</template>

<script>
import { mapState } from 'vuex';
import FormsField from '../components/FormsField.vue';

export default {
  components: {FormsField},
  computed: {
    errors: function () {
      if (!this.selectedField.errors) {
        return {};
      }
      return this.selectedField.errors;
    },
    ...mapState(['selectedField']),
  }
};
</script>