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
          <a href="#" @click.prevent="selectedFieldUid = field.uid" class="field-link">
            <div class="icon move"></div>
            <div class="name-wrapper"><span class="name">{{ field.name }}</span> <code>{{ field.handle }}</code></div>
            <div class="type">{{ field.type }}</div>
            <div v-show="errors[field.uid]" class="red-span">!</div>
          </a>
          <draggable group="fields" class="group-fields" v-if="field.type == 'group'" v-model="field.fields" @change="removeGroupId">
            <div v-for="field2 in field.fields" :key="field2.uid" :class="{opaque: !field.active, item: true, sel: field2.uid == selectedFieldUid}" :data-uid="field2.uid" :data-type="field2.type">
              <a href="#" @click.prevent="selectedFieldUid = field2.uid" class="field-link">
                <div class="icon move"></div>
                <div class="name-wrapper"><span class="name">{{ field2.name }}</span> <code>{{ field2.handle }}</code></div>
                <div class="type">{{ field2.type }}</div>
                <div v-show="errors[field2.uid]" class="red-span">!</div>
              </a>
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
      <div class="actions" v-show="selectedFieldUid != null">
        <a @click.prevent="deleteFieldInternal()" v-if="!(selectedField && selectedField.type == 'group' && selectedField.fields.length > 0)">{{ t('Delete') }}</a>
        <a @click.prevent="duplicateField()">{{ t('Duplicate') }}</a>
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
    checkMove: function (evt) {
      if ($(evt.dragged).data('type') == 'group' && $(evt.to).hasClass('group-fields')){
        return false;
      }
      return true;
    },
    addFieldInternal(type) {
      let field = merge({}, this.registeredFields[type]);
      this._addField(field);
    },
    duplicateField() {
      this._addField(merge({}, this.selectedField));
    },
    _addField(field) {
      field.name = '';
      field.id = null;
      field.uid = uuidv4();
      let index = this.fields.length - 1;
      field.order = index;
      field.active = true;
      if (field.type == 'group') {
        field.fields = [];
      }
      this.selectedFieldUid = field.uid;
      this.addField(field);
    },
    onError(data) {
      this.$set(this.errors, data.uid, data.error);
    },
    deleteFieldInternal() {
      this.deleteField(this.selectedFieldUid);
      this.$delete(this.errors, this.selectedFieldUid);
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
  .item > a {
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
      & > a {
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

#fields-draggable {
  width: 100%;
}

.draghelper .field-link, #fields-sidebar .field-link {
  color: #3f4d5a;
  font-size: 14px;
  padding: 7px !important;
  display: flex;
  flex-wrap: wrap;
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