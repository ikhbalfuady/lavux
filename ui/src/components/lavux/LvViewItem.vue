<template>
  <div :class="`col-12 ${noBorder ? '' : ' bb-dotted'} ph`">
    <small v-if="fixLabel" class="text-grey-8 text-capitalize" v-html="fixLabel"></small><br>
    <span :class="`${!regularText ? 'text-bold' : 'text-semi-bold'}`">
      <template v-if="!$slots.default" >
        <lv-displayer v-if="optimizeDisplay" :data="display" />
        <span v-else v-html="display"></span>
      </template>
      <template v-else> <slot></slot> </template>
    </span>
  </div>
</template>

<script>
import useServices from './../../composables/Services'
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvViewItem',
  props: {
    label: {
      type: String,
      default: null
    },
    regularText: {
      type: Boolean,
      default: false
    },
    noBorder: {
      type: Boolean,
      default: false
    },
    optimizeDisplay: {
      type: Boolean,
      default: false
    },
    display: {}
  },
  setup (props) {
    const { Config, Handler, Helper, Api, GlobalStore, SetMetaPage } = useServices()

    const fixLabel = computed(() => {
      let res = props.label || ''
      if (props.optimizeDisplay) {
        res = Helper.replace(res, '_id', '') // takeout suffix _id
        res = Helper.replace(res, '_', ' ') // takeout underscore
        res = Helper.ucwords(res) // make capital
      }
      return res
    })

    return {
      fixLabel,
    }
  }
})
</script>