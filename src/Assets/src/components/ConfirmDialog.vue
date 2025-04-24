<template>
    <vue-confirm-dialog class="popup"></vue-confirm-dialog>
</template>

<style lang="scss">
@import "~craftcms-sass/mixins";

.popup {
  .vc-container {
    border-radius: 5px;
  }
  .vc-text {
    color: $grey700;
    font-weight: bold
  }
  a, button {
    color: $linkColor;
  }
}
</style>
