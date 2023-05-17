<template>
  <div :class="`text-center col-12 ${!noPadding ? 'q-py-lg q-my-lg' : ''}`">
    <q-spinner-facebook class="animated zoomIn"
        :color="color"
        :size="size"
    /> <br>
    <div class="text-grey-7 animated fadeInUp mt-1" >
      <span :class="`text-bold text-${color}`" v-html="(label) ? label : 'Loading...'"></span>
    </div>
  </div>
</template>

<script>
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvLoading',
  props: {
    color: { // path index API of module
      type: String,
      default: 'primary'
    },
    label: { // path index API of module
      type: String,
      default: 'Loading...'
    },
    size: { // path index API of module
      type: String,
      default: '7em'
    },
    noPadding: { // path index API of module
      type: Boolean,
      default: false
    },
  },
})
</script>
