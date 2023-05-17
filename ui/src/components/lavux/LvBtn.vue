<template>
  <q-btn v-if="href" unelevated dense :class="`animated zoomIn lv-btn ${classTheme}`" :loading="loading"
    :round="round" :href="href" :size="size ? size : 'md'"  :style="styles" @v-on:click="$emit('click')"
  >
    <q-icon v-if="icon" :name="icon" style="font-size:1em !important; opacity: 0.7;"/>
    <span v-if="label" :class="labelVisibility ? 'gt-xs' : '' " :style="`${(icon) ? 'padding-left:5px; ' : ''}`" >{{ label }}</span>
    <q-tooltip v-if="labelVisibility" class="lt-sm">{{ label }}</q-tooltip>
    <slot></slot>
    <template v-slot:loading>
      <slot name="loading"></slot>
    </template>
  </q-btn>
  <q-btn v-else unelevated dense :class="`animated zoomIn lv-btn ${classTheme}`" :loading="loading"
    :round="round" :to="to" :size="size ? size : 'md'"  :style="styles" @v-on:click="$emit('click')"
  >
    <q-icon v-if="icon" :name="icon" style="font-size:1em !important; opacity: 0.7;"/>
    <span v-if="label" :class="labelVisibility ? 'gt-xs' : '' " :style="`${(icon) ? 'padding-left:5px; ' : ''}`" >{{ label }}</span>
    <q-tooltip v-if="labelVisibility" class="lt-sm">{{ label }}</q-tooltip>
    <slot></slot>
    <template v-slot:loading v-if="$slots.loading || loading" >
      <q-spinner-facebook  v-if="!$slots.loading && loading"/>
      <slot name="loading"></slot>
    </template>
  </q-btn>
</template>

<script>
import { ref, onMounted, computed, defineComponent } from 'vue'
import useServices from './../../composables/Services'
import { colors } from 'quasar'

export default defineComponent({
  name: 'LvBtn',
  props: {
    color: {
      type: String,
      default: null
    },
    size: {
      type: String,
      default: 'md'
    },
    icon: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: null
    },
    soft: {
      type: Boolean,
      default: false
    },
    outline: {
      type: Boolean,
      default: false
    },
    round: {
      type: Boolean,
      default: false
    },
    labelVisibility: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    to: {
      type: [String, Object],
      default: null
    },
    href: {
      type: String,
      default: ''
    },
  },
  setup (props) {
    const { Config, Helper, Handler } = useServices()
    const { getPaletteColor, hexToRgb } = colors

    onMounted(() => {
      // console.log(props.menu)
    })

    const getColor = computed(() => {
      return getPaletteColor(props.color || 'primary')
    })

    const classTheme = computed(() => {
      let res = 'bg-white text-dark lv-btn-default'
      let isColorBase = false
      if (props.color) isColorBase = (props.color.indexOf('-') > -1)  ? true : false

      if (props.color === 'primary' || props.color === 'secondary' || props.color === 'dark') {
        res = `bg-${props.color} text-white `
      }

      if (props.color) {
        res = `bg-${props.color} text-white `
      }

      if (props.color && props.soft) {
        res = `bg-${props.color}-1 text-${props.color}-9`
      }

      if (props.color && props.outline) {
        res = `bg-transparent text-${props.color}-9 lv-btn-outline`
      }

      return res
    })

    const styles = computed(() => {
      let res = []
      if (props.outline && props.color) {
        const color = getColor.value
        if (!props.soft) { 
          res.push({
           'border': `1px solid ${color} !important`,
          })
      }
      }

      if (props.soft) {
        res.push({ background: `${getRgbColor(0.1)} !important`})
        res.push({ color: `${getRgbColor(1)} !important`})
      }
      return res
    })

    function getRgbColor (opacity = 1) {
      let rgb = hexToRgb(getColor.value)
      rgb.o = opacity // set opacity
      return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${rgb.o})`
    }

    return {
      classTheme,
      getColor,
      styles,
    }
  }
})
</script>