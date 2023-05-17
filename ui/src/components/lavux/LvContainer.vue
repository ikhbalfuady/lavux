<template>
  <div class="lv-container q-pa-sm ">
    <q-scroll-area ref="refContainer" :class="`bg-${background} radius shadow`" :thumb-style="$Config.scrollThumbStyle()"
      :bar-style="$Config.scrollBarStyle()" :style="`${contentStyle}`">
      <div class="row q-pa-sm row-column-space">
        <slot></slot>
      </div>
      <q-resize-observer @resize="onResize" />
    </q-scroll-area>
  </div>
</template>

<script>
import { ref, onBeforeMount, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvContainer',
  props: {
    background: { // path index API of module
      type: String,
      default: 'white'
    },
    contentStyle: { // path index API of module
      type: String,
      default: ''
    },
    height: { // path index API of module
      type: String,
      default: '40vh'
    },
  },
  setup(props) {

    const refContainer = ref(1)

    onBeforeMount(() => {
    })

    function onResize (e) {
      const container = refContainer.value.$el
      const content = container.firstChild.firstChild
      const contentHeight = content.getBoundingClientRect().height

      // apply default height by config
      container.style.height = props.height
      container.style['max-height'] = props.height

      if (e.width < 560) { // adjust and force height when in mobile
        container.style.height = `${contentHeight}px`
        container.style['max-height'] = `${contentHeight}px`
      }
    }

    return {
      refContainer,
      onResize
    }
  }
})
</script>
