<template>
	<div id="form-fields" v-show="selectedTab == 'fields'" class="flex">
    <div class="flex sidebar" id="fields-sidebar">
      <div id="registered-fields-btn" class="btn menubtn add icon">{{ t('Add field') }}</div>
        <div class="menu">
          <ul>
            <li><a v-for="registered in registeredFields" :key="registered.type" @click.prevent="addField(registered.type)" href="#">{{ registered.friendlyName }}</a></li>
          </ul>
        </div>
      <nav>
        <ul ref="draggable" id="fields-draggable">
          <li v-for="(field, index) in fields" :key="index" :class="{'sel': index == selectedField}">
            <a href="#" @click.prevent="setSelectedField(index)" class="field-link">
              <div class="icon move"></div>
              <div class="name">{{ field.name }}</div>
              <div class="type">{{ field.type }}</div>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div id="fields-container">
      <forms-field v-for="(field, index) in fields" v-show="index == selectedField"
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
import { EventBus } from './../EventBus.js';
import axios from 'axios';
import Vue from 'vue';

export default {
  computed: {
    ...mapState({
      fields: state => state.fields,
      selectedTab: state => state.selectedTab,
      selectedField: state => state.selectedField,
    })
  },
  props: {
    registeredFields: Object
  },
  created() {
    this.setRegisteredFields(this.registeredFields)
  },
  mounted() {
    new Garnish.DragSort($(this.$refs.draggable).find('li'), {
      axis: 'y',
      container: this.$refs.draggable
    });
  },
  methods: {
    ...mapActions({
      addField: 'addField',
      deleteField: 'deleteField',
      duplicateField: 'duplicateField',
    }),
    ...mapMutations({
      setSelectedField: 'setSelectedField',
      setRegisteredFields: 'setRegisteredFields'
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
  font-size: 13px;
  padding: 5px !important;
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