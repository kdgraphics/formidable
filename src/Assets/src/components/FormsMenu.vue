<template>
  <nav id="forms-nav">
    <ul>
      <li v-for="form in forms" v-bind:key="form.id">
        <a :href="'/admin/inspireforms/form/edit' + form.id" :class="{'sel': form && form.id === form.id}" @click.prevent="edit(form.id)">{{ form.name }}</a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import { EventBus } from './../EventBus.js';
import axios from 'axios';

export default {
  computed: {
    ...mapState({
      forms: state => state.forms,
      form: state => state.form
    })
  },
  created() {
    
  },
  methods: {
    edit(id) {
      EventBus.$emit('edit-form-click', {id: id});
    },
    rerender() {
      this.$forceUpdate();
    }
  }
};
</script>


<style lang="scss">

#notification-nav {
    font-size: 14px;
}

.inspireforms #sidebar-container, .inspireforms #sidebar {
  width: 150px;
  flex: 0 0 150px;
}

.inspireforms #sidebar-container {
  flex: 0 0 150px;
}

.inspireforms #main-content.has-sidebar #content-container {
  width: calc(100% - 126px);
  max-width: calc(100% - 126px);
}

</style>
