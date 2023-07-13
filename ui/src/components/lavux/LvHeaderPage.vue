<template >
  <div class="col-12 row header-page">

    <div :class="`header-page-left row text-primary ${split && !single ? 'col-6' : 'col-12'} ${single ? '' : 'col-sm-5'}`">
      <!-- Back Button -->
      <template v-if="showBack">
        <lv-btn round class="back-btn bg-grey-2 no-border" size="xs" v-close-popup
          @v-on:click="handleBackEvent"
          :to="redirectTo"
        > <q-icon name="arrow_back"/>
        </lv-btn>
      </template>
      <slot name="left"></slot>
    </div>
    <div :class="`header-page-right row q-gutter-sm ${split && !single ? 'col-6' : 'col-12'} ${single ? '' : 'col-sm-7'}`">
      <slot name="right"></slot>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted, computed, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
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
    backToStrict: {
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
    const route = useRoute()
    const router = useRouter()

    const history = computed(() => {
      return router.options.history.state.back
    })

    const redirectTo = computed(() => {
      let res = null
      if (props.backTo) res = props.backTo
      if (history.value) res = history.value
      if (props.backToStrict) res = props.backToStrict
      if (props.preventBackTo) res = null
      return res
    })


    function handleBackEvent () {
      emit('click')
      if (props.backEvent) props.backEvent()
    }

    return {
      handleBackEvent,
      // computed
      redirectTo,
      history,
    }
  }
})
</script>
