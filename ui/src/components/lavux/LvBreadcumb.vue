<template>
  <div class="breadcumb">
    <span class="page-title animated zoomIn" v-html="title"></span> 
    <q-icon class="separator-icon" name="keyboard_double_arrow_right" />
    <span class="page-subtitle animated zoomIn text-capitalize" v-html="subtitle"></span>
  </div>
</template>

<script>
import { ref, computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvBreadcumb',
  props: {
    title: {
      type: String,
      default: ''
    },
    subtitle: {
      type: [String, Number, Boolean],
      default: null
    },
  },
  setup(props) {
    
    return {
    }
  }
})
</script>
<style lang="scss">
.breadcumb {
  .separator-icon {
    position: relative;
    top: -3px;
    padding-left: 5px;
    padding-right: 5px;
    color: #7e7e7e;
  }
  .page-subtitle {
    position: relative;
    top: -1px;
    color: #707070;
    font-weight: 600;
  }
}
</style>
