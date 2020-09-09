<template>
    <div id="notifications-toolbar" class="flex flex-grow">
        <a href="admin/inspireforms/notifications/add" class="btn submit add icon" @click.prevent="add()">{{ t('New') }}</a>
    </div>
</template>

<script>
import { EventBus } from './../EventBus.js';

export default {
  methods: {
    add() {
      EventBus.$emit('add-notification-click');
    }
  }
};
</script>

<style lang="scss" scoped>
@import "~craftcms-sass/mixins";

#notifications-toolbar {
    justify-content: end;
}

</style>