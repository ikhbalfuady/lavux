<template>
  <pre class="text">{{ data }}</pre>
</template>

<script>

import { ref, computed, useSlots, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvText',
  props: {
    data: {}
  },
  setup(props) {
    
    return {
      
    }
  }
})
</script>
<style>
.text {
  padding: 0 !important;
  margin: 0 !important;
  font-size: 13px;
  font-family: inherit;
  color: #4d4c4c;
}
</style>