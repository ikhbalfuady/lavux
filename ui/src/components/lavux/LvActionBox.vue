<template>
  <q-popup-proxy>
    <div :style="`width: ${width};`" class="q-py-sm q-px-xs bg-white">
      <div class="row">
        <div v-if="label" class="col-12 pb q-pb-sm q-pl-sm text-grey-7 text-bold bb-dotted" v-html="label"></div>
        <slot></slot>
      </div>
    </div>
  </q-popup-proxy>
</template>

<script>
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvActionBox',
  props: {
    className: {
      type: String
    },
    label: {
      type: String
    },
    width: {
      type: String,
      default: "270px"
    }
  },
  setup (props) {
    //
  }
})
</script>