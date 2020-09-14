<template>
	<div id="form-fields" v-show="activeMenu == 'fields'" class="flex">
    <div class="flex sidebar" id="fields-sidebar">
      <div id="registered-fields-btn" class="btn menubtn add icon">{{ t('Add field') }}</div>
        <div class="menu">
          <ul>
            <li><a v-for="registered in registeredFields" :key="registered.type" @click.prevent="addField(registered.type)" href="#">{{ registered.friendlyName }}</a></li>
          </ul>
        </div>
      <nav>
        <ul ref="draggable" id="fields-draggable">
          <li v-for="(field, index) in fields" :key="index" :class="{'sel': index == selectedField}" :data-index="index">
            <a href="#" @click.prevent="selectField(index)" class="field-link">
              <div class="icon move"></div>
              <div class="name">{{ field.name }}</div>
              <div class="type">{{ field.type }}</div>
              <div v-show="changes[index] && !errors[index]" class="red-span">*</div>
              <div v-show="errors[index]" class="red-span">!</div>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div id="fields-container">
      <forms-field v-for="(field, index) in fields" v-show="index == selectedField" v-on:errors="setError" v-on:changed="setChanges"
        :class="field.type+'-field' + ' inspirefield-container'" 
        :key="index"
        :index="index"
        :field="field"
        >
      </forms-field>
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
import { isEqual, cloneDeep, reduce } from 'lodash';

export default {
  computed: {
    ...mapState(['activeMenu', 'fields']),
    hasChanges: function () {
      return reduce(this.changes, function(result, value, key) {
        return (result || value);
      }, false);
    },
    hasErrors: function () {
      return reduce(this.errors, function(result, value, key) {
        return (result || value);
      }, false);
    }
  },
  data: function () {
    return {
      deletedFields: 0,
      addedFields: 0,
      selectedField: null,
      errors: {},
      changes: {}
    }
  },
  props: {
    registeredFields: Object,
    allFields: Array
  },
  watch: {
    hasErrors: function () {
      this.fieldsHaveErrors(this.hasErrors);
    },
    hasChanges: function () {
      this.handleFieldsChanged();
    },
    deletedFields: function () {
      this.handleFieldsChanged();
    },
    addedFields: function () {
      this.handleFieldsChanged();
    }
  },
  created() {
    this.setFields(this.allFields);
    this.setFieldNames(this.fields.map((field) => field.name));
    if (this.fields.length) {
      this.selectedField = 0;
    }
  },
  mounted() {
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
     * Callback for setting a field error status
     */
    setError(data) {
      this.$set(this.errors, data.index, data.value);
    },
    /**
     * Callback for setting a field change status
     */
    setChanges(data) {
      this.$set(this.changes, data.index, data.value);
    },
    addField(type) {
      let field = cloneDeep(this.registeredFields[type]);
      field.order = this.fields.length;
      this.fields.push(field);
      this.selectedField = this.fields.length - 1;
      this.addedFields++;
    },
    deleteField() {
      if (this.fields[this.selectedField].id) {
        this.deletedFields++;
      } else {
        this.addedFields--;
      }
      Vue.delete(this.fields, this.selectedField);
      this.selectedField = (this.fields.length) ? 0 : null;
    },
    duplicateField() {
      let field = cloneDeep(this.fields[this.selectedField]);
      this.fields.push(field);
      this.addedFields++;
    },
    selectField (index) {
      this.selectedField = index;
    },
    handleFieldsChanged() {
      let change = (this.deletedFields > 0 || this.addedFields > 0 || this.hasChanges);
      this.setFieldNames(this.fields.map((field) => field.name));
      this.fieldsHaveChanged(change);
    },
    ...mapActions({
      fieldsHaveErrors: 'fieldsHaveErrors',
      fieldsHaveChanged: 'fieldsHaveChanged',
    }),
    ...mapMutations({
      setFields: 'setFields',
      setFieldNames: 'setFieldNames'
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
      width: calc(50% - 5px);
      margin-top: 0;
      &:nth-child(2n) {
        margin-left: 5px;
      }
      &:nth-child(2n+1) {
        margin-right: 5px;
      }
    }
  }
}

</style>