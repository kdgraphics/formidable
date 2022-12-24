<template>
  <div
    :class="{opaque: !field.active, item: true, sel: selectedField && field.uid == selectedField.uid}"
    :data-uid="field.uid"
    :data-type="field.type"
  >
    <div class="field-link">
      <div class="icon move" />
      <span
        v-if="field.errors && Object.keys(field.errors).length"
        data-icon="alert"
      />
      <div class="name-wrapper">
        <a
          href="#"
          class="name"
          @click.prevent="setSelectedField(field)"
        >
          <span v-if="field.name">{{ field.name }}</span>
          <span v-if="!field.name"><i>No name</i></span>
        </a> <code>{{ field.handle }}</code>
      </div>
      <div class="type">
        <a
          v-if="field.type == 'group'"
          class="chevron"
          @click.prevent="toggleGroupFields"
        />{{ field.type }}
      </div>
    </div>
    <draggable
      v-if="field.type == 'group'"
      :list="field.fields"
      item-key="'uid'"
      group="fields"
      class="group-fields"
      @change="changeGroup"
    >
      <template #item="{element}">
        <forms-field-item
          :field="element"
        />
      </template>
    </draggable>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import Draggable from 'vuedraggable';

export default {
  components: {Draggable},
  props: {
    field: {
      type: Object,
      default: () => {return {};}
    }
  },
  computed: {
    ...mapState(['selectedField']),
  },
  methods: {
    toggleGroupFields: function (evt) {
      $(evt.target).parent().parent().next().slideToggle('fast');
    },
    changeGroup(evt) {
      if (evt.added) {
        evt.added.element.group_id = null;
      }
    },
    ...mapMutations(['setSelectedField'])
  }
}
</script>


<style lang="scss" scoped>

.item {
  margin-bottom: 5px;
  &.sel {
    & > .field-link {
      background-color: #e4edf6
    }
  }
  &.opaque {
    opacity: 0.7;
  }
  .field-link {
    border: 1px solid rgba(96, 125, 159, 0.25);
    box-shadow: inset 0 1px 4px -1px rgba(96, 125, 159, 0.25);
    background-color: #fbfcfe;
    border-radius: 3px;
    margin-bottom: 5px;
    .name-wrapper {
      padding: 0 5px;
    }
    [data-icon]::before {
      margin-top: -3px
    }
  }
  a {
    padding: 0;
    cursor: pointer !important;
  }
  .group-fields {
    padding-left: 20px;
  }
}

</style>