<template>
	<div id="form-fields" v-show="activeTab == 'fields'" class="flex">
    <div class="flex sidebar" id="fields-sidebar">
      <div id="registered-fields-btn" class="btn menubtn add icon">{{ t('Add field') }}</div>
        <div class="menu">
          <ul>
            <li><a v-for="registered in registeredFields" :key="registered.type" @click.prevent="addField(registered.type)" href="#">{{ registered.friendlyName }}</a></li>
          </ul>
        </div>
      <nav>
        <ul ref="draggable" id="fields-draggable">
          <li v-for="(field, index) in fields" :key="field.uid" :class="{'sel': field.uid == selectedFieldUid}" :data-uid="field.uid">
            <a href="#" @click.prevent="selectedFieldUid = field.uid" class="field-link">
              <div class="icon move"></div>
              <div class="name">{{ field.name }}</div>
              <div class="type">{{ field.type }}</div>
              <div v-show="changes[field.uid] && !errors[field.uid]" class="red-span">*</div>
              <div v-show="errors[field.uid]" class="red-span">!</div>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div id="fields-container">
      <component v-for="(field, index) in fields" v-show="field.uid == selectedFieldUid" v-on:error="onError" v-on:change="onChange"
        :is="'field-'+field.type"
        :field="field" 
        :key="field.uid"
        :original="originals[index]"
        >
      </component>
      <div class="no-fields" v-if="fields.length == 0">{{ t('No fields found') }}</div>
      <div class="actions" v-show="selectedFieldUid != null">
        <a @click.prevent="deleteField()">{{ t('Delete') }}</a>
        <a @click.prevent="duplicateField()">{{ t('Duplicate') }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import Vue from 'vue';
import { isEqual, cloneDeep, reduce, mapValues, get } from 'lodash';
import { EventBus } from '../EventBus';
import { v4 as uuidv4 } from 'uuid';

export default {
  props: {
    registeredFields: Object,
    allFields: Array
  },
  computed: {
    /**
     * @return {Boolean} global change status
     */
    hasChanges: function () {
      return (reduce(this.changes, function(result, value, key) {
        return result || value;
      }, false) || this.deletedFields > 0 || this.addedFields > 0);
    },
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
      }
      return null;
    },
    ...mapState(['activeTab', 'fields', 'form', 'loadedTime']),
  },
  data: function () {
    return {
      deletedFields: 0,
      addedFields: 0,
      selectedFieldUid: null,
      dragger: null,
      /**
       * @type {Object} Keyed by field uid
       */
      errors: {},
      /**
       * @type {Object} Keyed by field uid
       */
      changes: {},
      /**
       * @type {Object} Keyed by field uid
       */
      originals: {},
    }
  },
  watch: {
    loadedTime: function() {
      this.init();
      this.initDragSort();
    },
    hasErrors: function () {
      this.fieldsHaveErrors(this.hasErrors);
    },
    hasChanges: function () {
      this.handleFieldsChanged();
    }
  },
  created() {
    this.setFields(this.allFields);
    this.originals = cloneDeep(this.fields);
  },
  mounted() {
    this.initDragSort();
  },
  methods: {
    initDragSort: function () {
      if (this.dragger) {
        this.dragger.destroy();
      }
      /**
       * Initialize a garnish sort object on the field nav.
       * will recalculate the field orders on sort change
       */
      this.$nextTick(() => {
        let _this = this;
        this.dragger = new Garnish.DragSort($(this.$refs.draggable).find('li'), {
          axis: 'y',
          container: this.$refs.draggable,
          onSortChange: function(){
            $('#fields-draggable li').each(function(index, elem) {
              _this.getFieldByUid($(elem).data('uid')).order = index;
            });
          }
        });
      });
    },
    getFieldByUid: function (uid) {
      for (let i in this.fields) {
        if (uid === this.fields[i].uid) {
          return this.fields[i];
        }
      }
      return null;
    },
    /**
     * (re)initialise the fields
     */
    init(fields) {
      this.originals = cloneDeep(this.fields);
      this.changes = {};
      this.errors = {};
      this.addedFields = 0;
      this.deletedFields = 0;
    },
    addField(type) {
      let field = cloneDeep(this.registeredFields[type]);
      this._addField(field);
    },
    duplicateField() {
      this._addField(cloneDeep(this.selectedField));
    },
    _addField(field) {
      field.name = '';
      field.id = null;
      field.uid = uuidv4();
      this.fields.push(field);
      let index = this.fields.length - 1;
      field.order = index;
      this.addedFields++;
      this.selectedFieldUid = field.uid;
      this.initDragSort();
    },
    deleteField() {
      if (this.selectedField.id) {
        this.deletedFields++;
      } else {
        this.addedFields--;
      }
      for (let i in this.fields) {
        if (this.fields[i].uid == this.selectedFieldUid) {
          this.$delete(this.fields, i);
          break;
        }
      }
      this.$delete(this.errors, this.selectedFieldUid);
      this.$delete(this.changes, this.selectedFieldUid);
      this.selectedFieldUid = (this.fields.length == 0) ? null : this.fields[0].uid;
      this.initDragSort();
    },
    /**
     * Detects overall field changes
     */
    handleFieldsChanged() {
      let change = (this.deletedFields > 0 || this.addedFields > 0 || this.hasChanges);
      this.fieldsHaveChanged(change);
    },
    onError(data) {
      this.$set(this.errors, data.uid, data.error);
    },
    onChange(data) {
      this.$set(this.changes, data.uid, data.change);
    },
    ...mapActions({
      fieldsHaveErrors: 'fieldsHaveErrors',
      fieldsHaveChanged: 'fieldsHaveChanged',
    }),
    ...mapMutations({
      setFields: 'setFields'
    })
  }
};
</script>
<style lang="scss">
@import "~craftcms-sass/mixins";

#fields-sidebar {
  width: calc(30% - 10px);
  background-color: white;
  padding: 0;
  margin-bottom: 0;
  font-size: 14px;
  nav {
    padding-left: 0;
    width: 100%;
    margin-bottom: 0;
  }
  li {
    border: 1px solid rgba(96, 125, 159, 0.25);
    box-shadow: inset 0 1px 4px -1px rgba(96, 125, 159, 0.25);
    background-color: #fbfcfe;
    border-radius: 3px;
    margin-bottom: 5px;
    &:last-child {
      margin:0;
    }
    &.sel {
      background-color: #e4edf6
    }
  }
  a {
    padding: 0;
    cursor: pointer !important;
  }
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