<template >
  <div class="col-12 row header-page">
    <div :class="`header-page-left row text-primary ${split && !single ? 'col-6' : 'col-12'} ${single ? '' : 'col-sm-5'}`">
      <!-- Back Button -->
      <lv-btn v-if="showBack" round class="back-btn bg-grey-2 no-border" size="xs" 
        @v-on:click="$emit('click')" :to="redirectTo" v-close-popup>
        <q-icon name="arrow_back"/>
      </lv-btn>

      <slot name="left"></slot>
    </div>
    <div :class="`header-page-right row q-gutter-sm ${split && !single ? 'col-6' : 'col-12'} ${single ? '' : 'col-sm-7'}`">
      <slot name="right"></slot>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted, computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvHeaderPage',
  props: {
    backEvent: {
      type: Function,
      default: null,
    },
    backTo: {
      type: [String, Object],
      default: null,
    },
    preventBackTo: {
      type: Boolean,
      default: false,
    },
    showBack: {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      default: null,
    },
    split: { // split area left & right always set col 6 
      type: Boolean,
      default: false,
    },
    single: { // split area will not effected, becase area will be col-12 as default
      type: Boolean,
      default: false,
    },

  },
  setup (props, { emit }) {

    const redirectTo = computed(() => {
      let res = null
      if (props.backTo) res = props.backTo
      if (props.preventBackTo) res = null
      return res
    })

    function handleBackEvent () {
      if (props.backEvent) props.backEvent()
    }

    return {
      handleBackEvent,
      // computed
      redirectTo
    }
  }
})
</script>