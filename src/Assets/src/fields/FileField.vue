<template>
  <forms-field
    v-bind="$attrs"
    :has-autocomplete="false"
  >
    <template #extraFields>
      <div class="lightswitch-field field">
        <div class="heading">
          <label>{{ t('Max files') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.maxFiles"
            type="text"
            min="2"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.maxFiles"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Extensions') }}</label>
        </div>
        <div class="instructions">
          <p>{{ t('Extensions allowed (no dot), comma separated. leave empty for all') }}</p>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.extensions"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.extensions"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Volume') }}</label>
        </div>
        <div class="input ltr">
          <div class="select">
            <select
              v-model="selectedField.volume"
              class="text"
              @input="validateVolume"
            >
              <option
                v-for="volume in volumes"
                :key="volume.uid"
                :value="volume.uid"
              >
                {{ volume.name }}
              </option>
            </select>
          </div>
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.volume"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Folder') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.folder"
            type="text"
            class="text fullwidth"
          >
        </div>
      </div>
      <div class="field">
        <div class="heading">
          <label>{{ t('Max file size (Mb)') }}</label>
        </div>
        <div class="input ltr">
          <input
            v-model="selectedField.size"
            type="text"
            class="text fullwidth"
          >
        </div>
        <ul class="errors">
          <li
            v-for="error, index in errors.size"
            :key="index"
            class="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>
    </template>
    <template #extraAdvanced>
      <div class="lightswitch-field field">
        <div class="heading">
          <label>{{ t('Attach to user email') }}</label>
        </div>
        <div class="input ltr">
          <div
            :class="'lightswitch ' + (selectedField.attachToUserEmail ? 'on' : '')"
            tabindex="0"
            role="switch"
            :aria-checked="selectedField.attachToUserEmail"
          >
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.attachToUserEmail"
              type="hidden"
              name="attachToUserEmail"
            >
          </div>
        </div>
      </div>
      <div class="lightswitch-field field">
        <div class="heading">
          <label>{{ t('Attach to admin email') }}</label>
        </div>
        <div class="input ltr">
          <div
            :class="'lightswitch ' + (selectedField.attachToAdminEmail ? 'on' : '')"
            tabindex="0"
            role="switch"
            :aria-checked="selectedField.attachToAdminEmail"
          >
            <div class="lightswitch-container">
              <div class="handle" />
            </div>
            <input
              v-model="selectedField.attachToAdminEmail"
              type="hidden"
              name="attachToAdminEmail"
            >
          </div>
        </div>
      </div>
    </template>
  </forms-field>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import FormsField from '../components/FormsField.vue';

export default {
  components: {FormsField},
  data() {
    return {
      volumes: []
    }
  },
  computed: {
    errors: function () {
      if (!this.selectedField.errors) {
        return {};
      }
      return this.selectedField.errors;
    },
    ...mapState(['selectedField'])
  },
  created() {
    let _this = this;
    if (typeof window.fileFieldVolumes == 'undefined') {
      let data = {};
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
      window.fileFieldVolumes = axios.post(Craft.getActionUrl('formidable/cp-utilities/volumes'), data)
      .catch((error) => {
        console.log(error);
      })
    }
    window.fileFieldVolumes.then(function (res) {
      _this.volumes = res.data.volumes;
    });
  }
};

</script>