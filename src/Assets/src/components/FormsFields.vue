<template>
	<div id="form-fields" v-show="activeTab == 'fields'" class="flex">
    <div class="flex sidebar" id="fields-sidebar">
      <div id="registered-fields-btn" class="btn menubtn add icon">
        {{ t('Add field') }}
      </div>
      <div class="menu">
        <ul>
          <li><a v-for="registered in registeredFields" :key="registered.type" @click.prevent="addFieldInternal(registered.type)" href="#">{{ registered.friendlyName }}</a></li>
        </ul>
      </div>
      <draggable group="fields" id="fields-draggable" v-model="sortableFields" :move="checkMove" @change="removeGroupId">
        <div v-for="field in fields" :key="field.uid" :class="{opaque: !field.active, item: true, sel: field.uid == selectedFieldUid}" :data-uid="field.uid" :data-type="field.type">
          <div class="field-link">
            <div class="icon move"></div>
            <div class="name-wrapper"><a href="#" @click.prevent="selectedFieldUid = field.uid" class="name">{{ field.name ? field.name : 'No name' }}</a> <code>{{ field.handle }}</code></div>
            <div class="type"><a class="chevron" v-if="field.type == 'group'" @click.prevent="toggleGroupFields"></a>{{ field.type }}</div>
            <div v-show="errors[field.uid]" class="red-span">!</div>
          </div>
          <draggable group="fields" class="group-fields" v-if="field.type == 'group'" v-model="field.fields" @change="changeGroup">
            <div v-for="field2 in field.fields" :key="field2.uid" :class="{opaque: !field.active, item: true, sel: field2.uid == selectedFieldUid}" :data-uid="field2.uid" :data-type="field2.type">
              <div class="field-link">
                <div class="icon move"></div>
                <div class="name-wrapper"><a href="#" @click.prevent="selectedFieldUid = field2.uid" class="name">{{ field2.name }}</a> <code>{{ field2.handle }}</code></div>
                <div class="type">{{ field2.type }}</div>
                <div v-show="errors[field2.uid]" class="red-span">!</div>
              </div>
            </div>
          </draggable>
        </div>
      </draggable>
    </div>
    <div id="fields-container">
      <component v-for="(field, index) in allFields" v-show="field.uid == selectedFieldUid" v-on:error="onError"
        :is="'field-'+field.type"
        :field="field" 
        :key="field.uid"
        >
      </component>
      <div class="no-fields" v-if="fields.length == 0">{{ t('No fields found') }}</div>
      <div class="actions" v-show="selectedField">
        <a @click.prevent="deleteFieldInternal()">{{ t('Delete') }}</a>
        <a @click.prevent="duplicateField()">{{ t('Duplicate') }}</a>
        <a @click.prevent="duplicateGroup()" v-if="selectedField && selectedField.type == 'group'">{{ t('Duplicate with fields') }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import Vue from 'vue';
import { isEqual, cloneDeep, reduce, mapValues, get, filter, merge } from 'lodash';
import { EventBus } from '../EventBus';
import { v4 as uuidv4 } from 'uuid';

export default {
  props: {
    registeredFields: Object
  },
  computed: {
    /**
     * @return {Boolean} global error status
     */
    hasErrors: function () {
      let _this = this;
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    selectedField: function () {
      for (let i in this.fields) {
        if (this.selectedFieldUid === this.fields[i].uid) {
          return this.fields[i];
        }
        if (this.fields[i].type == 'group') {
          for (let subfield of this.fields[i].fields) {
            if (subfield.uid == this.selectedFieldUid) {
              return subfield;
            }
          }
        }
      }
      return null;
    },
    allFields: function () {
      let fields = [];
      for(let field of this.fields) {
        fields.push(field);
        if (field.type == 'group') {
          for(let subfield of field.fields) {
            fields.push(subfield);
          }
        }
      }
      return fields;
    },
    sortableFields: {
      get: function () {
        return this.fields;
      },
      set: function (fields) {
        this.setFields(fields);
      }
    },
    ...mapState(['activeTab', 'fields', 'form', 'loadedTime']),
  },
  data: function () {
    return {
      selectedFieldUid: null,
      /**
       * @type {Object} Keyed by field uid
       */
      errors: {}
    }
  },
  watch: {
    loadedTime: function() {
      this.errors = {};
    },
    hasErrors: function () {
      this.fieldsHaveErrors(this.hasErrors);
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {
    toggleGroupFields: function (evt) {
      $(evt.target).parent().parent().next().slideToggle('fast');
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
      this.selectedFieldUid = field.uid;
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
      this.selectedFieldUid = field.uid;
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
      this.selectedFieldUid = field.uid;
    },
    onError(data) {
      this.$set(this.errors, data.uid, data.error);
    },
    deleteFieldInternal() {
      if (this.selectedField.type == 'group' && this.selectedField.fields.length ) {
        if (!confirm("This will also delete all children, continue ?")) {
          return;
        }
      }
      this.$delete(this.errors, this.selectedFieldUid);
      if (this.selectedField.type == 'group') {
        for (let i in this.selectedField.fields) {
          this.$delete(this.errors, this.selectedField.fields[i].uid);
        }
      }
      this.deleteField(this.selectedFieldUid);
      if (this.fields.length) {
        this.selectedFieldUid = this.fields[0].uid
      } else {
        this.selectedFieldUid = null;
      }
    },
    removeGroupId(evt) {
      if (evt.added) {
        evt.added.element.group_id = null;
      }
    },
    changeGroup(evt) {
      if (evt.added) {
        evt.added.element.group_id = null;
      }
    },
    ...mapActions(['fieldsHaveErrors']),
    ...mapMutations(['setFields', 'deleteField', 'addField'])
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
  .item {
    margin-bottom: 5px;
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
  }
  .item {
    &.sel {
      & > .field-link {
        background-color: #e4edf6
      }
    }
    &.opaque {
      opacity: 0.7;
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

.formidablefield-container {
  max-height: calc(100vh - 280px);
  overflow-y: auto;
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