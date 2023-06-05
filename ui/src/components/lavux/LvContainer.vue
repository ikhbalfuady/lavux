<template>
  <div :class="`lv-container ${noPadding ? '' : 'q-pa-sm'} `">
    <q-scroll-area ref="refContainer" :class="`bg-${background} radius ${unelevated ? '' : 'shadow'} `" :thumb-style="$Config.scrollThumbStyle()"
      :bar-style="$Config.scrollBarStyle()" :style="`${contentStyle}`">
      <div :class="`row ${noPadding ? '' : 'q-pa-sm'} row-column-space`">
        <slot></slot>
      </div>
      <q-resize-observer @resize="onResize" />
    </q-scroll-area>
  </div>
</template>

<script>
import { ref, computed, onMounted, defineComponent } from 'vue'
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
      default: null
    },
    noPadding: { // path index API of module
      type: Boolean,
      default: false
    },
    unelevated: { // remove shadow
      type: Boolean,
      default: false
    },
  },
  setup(props) {

    const refContainer = ref(1)

    onMounted(() => {
      if (!props.height) {
        setAutoHeight(contentHeight.value)
      }
    })

    const contentHeight = computed(() => {
      const container = refContainer.value.$el
      const content = container.firstChild.firstChild
      return content.getBoundingClientRect().height + 'px'
    })

    function onResize (e) {
      // adjust and force height when in mobile
      if (e.width < 560) setAutoHeight(contentHeight.value)
      else setAutoHeight(props.height || contentHeight.value)
    }

    function setAutoHeight (height) {
      const container = refContainer.value.$el
      container.style.height = `${height}`
      container.style['max-height'] = `${height}`
    }

    return {
      refContainer,
      onResize
    }
  }
})
</script>
