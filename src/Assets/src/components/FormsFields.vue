<template>
  <div
    v-show="activeTab == 'fields'"
    id="form-fields"
    class="flex"
  >
    <div
      id="fields-sidebar"
      class="flex sidebar"
    >
      <div
        id="registered-fields-btn"
        class="btn menubtn add icon"
      >
        {{ t('Add field') }}
      </div>
      <div class="menu">
        <ul>
          <li>
            <a
              v-for="registered in registeredFields"
              :key="registered.type"
              href="#"
              @click.prevent="addFieldInternal(registered.type)"
            >{{ registered.friendlyName }}</a>
          </li>
        </ul>
      </div>
      <draggable
        id="fields-draggable"
        :list="sortableFields"
        group="fields"
        :move="checkMove"
        item-key="'uid'"
        @change="removeGroupId"
      >
        <template #item="{element}">
          <forms-field-item
            :field="element"
          />
        </template>
      </draggable>
    </div>
    <div id="fields-container">
      <component
        :is="selectedField.type + '-field'"
        v-if="selectedField"
      />
      <div
        v-if="fields.length == 0"
        class="no-fields"
      >
        {{ t('No fields found') }}
      </div>
      <div
        v-show="selectedField"
        class="actions"
      >
        <a @click.prevent="deleteFieldInternal()">{{ t('Delete') }}</a>&nbsp;
        <a @click.prevent="duplicateField()">{{ t('Duplicate') }}</a>&nbsp;
        <a
          v-if="selectedField && selectedField.type == 'group'"
          @click.prevent="duplicateGroup()"
        >{{ t('Duplicate with fields') }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { isEqual, cloneDeep, reduce, mapValues, get, filter, merge } from 'lodash';
import { v4 as uuidv4 } from 'uuid';
import Draggable from 'vuedraggable';
import FormsFieldItem from './FormsFieldItem.vue';
import CheckboxesField from '../fields/CheckboxesField';
import CheckboxField from '../fields/CheckboxField';
import DateField from '../fields/DateField';
import EmailField from '../fields/EmailField';
import FileField from '../fields/FileField';
import GroupField from '../fields/GroupField';
import HiddenField from '../fields/HiddenField';
import NumberField from '../fields/NumberField';
import RadioField from '../fields/RadioField';
import SelectField from '../fields/SelectField';
import TextareaField from '../fields/TextareaField';
import TextField from '../fields/TextField';

export default {
  components: {Draggable, FormsFieldItem, CheckboxField, CheckboxesField, DateField, FileField, GroupField, HiddenField, NumberField, RadioField, SelectField, TextareaField, TextField, EmailField},
  props: {
    registeredFields: {
      type: Object,
      default: () => {return {};}
    }
  },
  computed: {
    sortableFields: {
      get: function () {
        return this.fields;
      },
      set: function (fields) {
        this.setFields(fields);
      }
    },
    ...mapState(['activeTab', 'fields', 'form', 'selectedField']),
  },
  methods: {
    fieldComponent: function () {
      return 'field-' + this.selectedField.type;
    },
    checkMove: function (evt) {
      if ($(evt.dragged).data('type') == 'group' && $(evt.to).hasClass('group-fields')){
        return false;
      }
      return true;
    },
    addFieldInternal(type) {
      let field = merge({}, this.registeredFields[type]);
      field.order = this.fields.length - 1;
      field.active = true;
      field.id = null;
      field.uid = uuidv4();
      if (field.type == 'group') {
        field.fields = [];
      }
      this.addField(field);
      this.setSelectedField(field);
    },
    duplicateField() {
      let field = merge({}, this.selectedField);
      field.order = this.fields.length - 1;
      field.id = null;
      field.uid = uuidv4();
      if (field.type == 'group') {
        field.fields = [];
      }
      this.addField(field);
      this.setSelectedField(field);
    },
    duplicateGroup() {
      let field = merge({}, this.selectedField);
      field.order = this.fields.length - 1;
      field.id = null;
      field.uid = uuidv4();
      field.fields = [];
      for (let i in this.selectedField.fields) {
        let field2 = merge({}, this.selectedField.fields[i]);
        field2.id = null;
        field2.uid = uuidv4();
        field.fields.push(field2);
      }
      this.addField(field);
      this.setSelectedField(field);
    },
    deleteFieldInternal() {
      if (this.selectedField.type == 'group' && this.selectedField.fields.length ) {
        if (!confirm("This will also delete all children, continue ?")) {
          return;
        }
      }
      this.deleteField(this.selectedField);
      if (this.fields.length) {
        this.setSelectedField(this.fields[0]);
      } else {
        this.setSelectedField(null);
      }
    },
    removeGroupId(evt) {
      if (evt.added) {
        evt.added.element.group_id = null;
      }
    },
    ...mapMutations(['setFields', 'deleteField', 'addField', 'setSelectedField'])
  }
};
</script>

<style lang="scss">
@import "~craftcms-sass/mixins";

#fields-sidebar {
  width: calc(30% - 20px);
  background-color: white;
  padding: 0;
  margin-bottom: 0;
  font-size: 14px;
  flex-direction: column;
  align-items: start;
}

.chevron {
  display: inline-block;
  border-right: 2px solid grey;
  border-bottom: 2px solid grey;
  width: 7px; 
  height: 7px;
  transform: rotate(45deg);
  margin-top: 4px;
  margin-right: 5px;
}

#fields-draggable {
  width: 100%;
  max-height: calc(100vh - 300px);
  overflow-y: auto;
}

.draghelper .field-link, #fields-sidebar .field-link {
  color: #3f4d5a;
  font-size: 14px;
  padding: 7px !important;
  display: flex;
  flex-wrap: wrap;
  cursor: default;
  .type {
    text-align: right;
    flex-grow: 1;
  }
  .name {
    font-weight: bold;
  }
  .icon.move {
    &:before{
      margin-top: -4px;
    }
  }
}

.draghelper .field-link .name {
  padding-left: 13px;
}

.draghelper {
  border: 1px solid rgba(96, 125, 159, 0.25);
  box-shadow: inset 0 1px 4px -1px rgba(96, 125, 159, 0.25);
  background-color: #fbfcfe;
  border-radius: 3px;
  list-style-type:none;
}

#form-fields {
  position: relative;
  align-items: flex-start;
  justify-content: space-between;
  max-height: calc(100vh - 250px);
}

#fields-container {
  width: 70%;
  position: relative;
  .actions {
    text-align: right;
    margin-top: 10px;
  }
}

</style>