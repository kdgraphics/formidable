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
          <li v-for="(field, index) in fields" :key="'' + form.id + index" :class="{'sel': index == selectedField}" :data-index="index">
            <a href="#" @click.prevent="selectedField = index" class="field-link">
              <div class="icon move"></div>
              <div class="name">{{ field.name }}</div>
              <div class="type">{{ field.type }}</div>
              <div v-show="changes[index] && !errors[index] && !nameErrors[index]" class="red-span">*</div>
              <div v-show="errors[index] || nameErrors[index]" class="red-span">!</div>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div id="fields-container">
      <component v-for="(field, index) in fields" v-show="index == selectedField" v-on:error="onError" v-on:change="onChange"
        :class="field.type+'-field' + ' inspirefield-container'"
        :is="'field-'+field.type"
        :field="field" 
        :index="index"
        :key="'' + form.id + index"
        :original="originals[index]"
        >
        <template v-slot:name>
          <div class="field first">
            <div class="heading">
              <label class="required" :for="'field-'+field.index+'-name'">{{ t('Name') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" :class="'text fullwidth' + (nameErrors[index] ? ' error' : '')" v-model="field.name" @input="validateName(index)">
            </div>
          </div>
        </template>
        <template v-slot:instructions>
          <div class="field field-instructions">
            <div class="heading">
              <label :for="'field-'+field.index+'-instructions'">{{ t('Instructions') }}</label>
            </div>
            <div class="input ltr">
              <input type="text" class="text fullwidth" :id="'field-'+field.index+'-instructions'" v-model="field.instructions">
            </div>
          </div>
        </template>
        <template v-slot:advanced>
          <div class="advanced">
            <div class="header" @click="toggleAdvanced(index)" :class="{'closed': !showAdvanced[index]}">{{ t('Advanced') }}</div>
            <div class="content" v-show="showAdvanced[index]">
              <div class="field field-fieldClass">
                <div class="heading">
                  <label>{{ t('Field class') }}</label>
                </div>
                <div class="input ltr">
                  <input type="text" class="text fullwidth" v-model="field.fieldClass">
                </div>
              </div>
              <div class="field field-containerClass">
                <div class="heading">
                  <label>{{ t('Container class') }}</label>
                </div>
                <div class="input ltr">
                  <input type="text" class="text fullwidth" v-model="field.containerClass">
                </div>
              </div>
              <div class="field field-labelClass">
                <div class="heading">
                  <label>{{ t('Label class') }}</label>
                </div>
                <div class="input ltr">
                  <input type="text" class="text fullwidth" v-model="field.labelClass">
                </div>
              </div>
            </div>
          </div>
        </template>
      </component>
      <div class="no-fields" v-if="fields.length == 0">{{ t('No fields found') }}</div>
      <div class="actions" v-show="selectedField != null">
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
      }, false) || reduce(this.nameErrors, function(result, value, key) {
        return (result || value);
      }, false);
    },
    ...mapState(['activeTab', 'fields', 'form', 'loadedTime']),
  },
  data: function () {
    return {
      deletedFields: 0,
      addedFields: 0,
      selectedField: null,
      /**
       * @type {Object} Keyed by field indexes
       */
      errors: {},
      /**
       * @type {Object} Keyed by field indexes
       */
      nameErrors: {},
      /**
       * @type {Object} Keyed by field indexes
       */
      changes: {},
      /**
       * @type {Object} Keyed by field indexes
       */
      originals: {},
      /**
       * @type {Object} Keyed by field indexes
       */
      showAdvanced: {}
    }
  },
  watch: {
    loadedTime: function() {
      this.init();
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
  },
  mounted() {
    /**
     * Initialize a garnish sort object on the field nav.
     * will recalculate the field orders on sort change
     */
    let _this = this;
    new Garnish.DragSort($(this.$refs.draggable).find('li'), {
      axis: 'y',
      container: this.$refs.draggable,
      onSortChange: function(){
        $('#fields-draggable li').each(function(index, elem) {
          _this.fields[$(elem).data('index')].order = index;
        });
      }
    });
  },
  methods: {
    /**
     * (re)initialise the fields
     */
    init(fields) {
      this.originals = cloneDeep(this.fields);
      this.changes = {};
      this.errors = {};
      this.nameErrors = {};
      this.showAdvanced = {};
      this.addedFields = 0;
      this.deletedFields = 0;
      if (this.fields.length) {
        this.selectedField = 0;
      }
    },
    /**
     * Show/hide the advanced section
     */
    toggleAdvanced (index) {
      if (typeof this.showAdvanced[index] == 'undefined') {
        this.$set(this.showAdvanced, index, false);
      }
      this.$set(this.showAdvanced, index, !this.showAdvanced[index]);
    },
    addField(type) {
      let field = cloneDeep(this.registeredFields[type]);
      this._addField(field);
    },
    duplicateField() {
      let field = cloneDeep(this.fields[this.selectedField]);
      this._addField(field);
    },
    _addField(field) {
      field.name = '';
      field.id = null;
      this.fields.push(field);
      let index = this.fields.length - 1;
      field.order = index;
      this.addedFields++;
      this.selectedField = index;
      this.validateName(index);
    },
    deleteField() {
      if (this.fields[this.selectedField].id) {
        this.deletedFields++;
      } else {
        this.addedFields--;
      }
      this.$delete(this.fields, this.selectedField);
      this.$delete(this.errors, this.selectedField);
      this.$delete(this.changes, this.selectedField);
      this.$delete(this.showAdvanced, this.selectedField);
      this.selectedField = (this.fields.length) ? 0 : null;
    },
    /**
     * Detects overall field changes
     */
    handleFieldsChanged() {
      let change = (this.deletedFields > 0 || this.addedFields > 0 || this.hasChanges);
      this.fieldsHaveChanged(change);
    },
    /**
     * Validates the field name
     */
    validateName(index) {
      let error = (!this.fields[index].name || this.otherFieldNames(index).includes(this.fields[index].name));
      this.$set(this.nameErrors, index, error);
    },
    /**
     * @return {Array} list of names for all the field except index
     */
    otherFieldNames: function (index) {
      let _this = this;
      return this.fields.filter(function (field) {
        return field.order != _this.fields[index].order;
      }).map((field) => field.name);
    },
    onError(data) {
      this.$set(this.errors, data.index, data.error);
    },
    onChange(data) {
      this.$set(this.changes, data.index, data.change);
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
  .field-link {
  }
}

.draghelper .field-link, #fields-sidebar .field-link {
  color: #3f4d5a;
  font-size: 14px;
  padding: 7px !important;
  display: flex;
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
  .inspirefield-container {
    display:flex;
    flex-wrap: wrap;
    width: 100%;
    .field {
      width: calc(50% - 10px);
      margin-top: 0;
      &:nth-child(2n) {
        margin-left: 10px;
      }
      &:nth-child(2n+1) {
        margin-right: 10px;
      }
    }
    .advanced {
      width: 100%;
      .header{
        box-shadow: none;
        border-radius: 0;
        padding: 5px 10px;
        cursor: pointer;
        border-top-right-radius: 5px;
        border-top-left-radius: 5px;
        &.closed {
          border-bottom-right-radius: 5px;
          border-bottom-left-radius: 5px;
        }
      }
      .content {
        padding: 10px;
        border: 1px solid #e4edf6;
        border-top: none;
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
        display:flex;
        flex-wrap: wrap;
      }
    }
  }
}

</style>