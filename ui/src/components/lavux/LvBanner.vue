<template>
  <div v-if="show" :class="`${fade} lv-banner row justify-between`" :style="styles" >
    <div :class="`${isDismissable ? 'col-11' : 'col-12' }`">
      <slot></slot>
    </div>
    <div v-if="isDismissable" :class="`${isDismissable ? 'col-1' : '' } text-right`">
      <span v-if="$slots.dismiss" @click="onDismiss">
        <slot name="dismiss"></slot>
      </span>
      <q-btn v-else @click="onDismiss" icon="close" flat round dense size="xs" class="bg-overlay"/>
    </div>
  </div>
</template>

<script>
import { colors } from 'quasar'

import { ref, computed, useSlots, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvBanner',
  props: {
    solid: {
      type: Boolean,
      default: false
    },
    dense: { 
      type: Boolean,
      default: false
    },
    dismissable: { 
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: 'primary'
    },
    textColor: {
      type: String,
      default: 'primary'
    },
  },
  setup(props) {
    const { getPaletteColor, hexToRgb } = colors
    const slots = useSlots()

    const fade = ref('')
    const show = ref(true)

    const styles = computed (() => {
      const hexColor = getColor(props.color)
      let rgb = hexToRgb(hexColor)
      rgb.o = 0.1 // set opacity
      let rgbString = `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${rgb.o})`

      let bg = rgbString || '#fff'
      if (props.solid) bg = hexColor

      let txtColor = hexColor || '#333'
      if (props.solid && props.textColor === 'primary') txtColor = '#fff'
      if (props.textColor && props.textColor !== 'primary') txtColor = getColor(props.textColor)


      let res = `width:99.8%; border-radius: 5px;`
      res += `background-color: ${bg};` // bg
      res += `color: ${txtColor};` // bg
      res += `border: 1px dashed ${hexColor};` // border
      res += `${props.dense ? 'font-size: .8rem;' : 'font-size: 1rem;'}` // fontsize
      res += `${props.dense ? 'padding: .5rem;' : 'padding: 1rem;'}` // padding
      return res
    })

    const isDismissable = computed(() => {
      let res = props.dismissable
      if (slots.dismiss) res = true
      return res
    })

    function getColor(color) {
      return getPaletteColor(color)
    }

    function onDismiss () {
      fade.value = 'animated fadeOut'
      setTimeout(() => {
        show.value = false
      }, 400)
    }
    
    return {
      show,
      fade,
      styles,
      isDismissable,
      onDismiss,
    }
  }
})
</script>