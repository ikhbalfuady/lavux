<template>
<div :class="`lv-field col-${col}`">
  <template v-if="label">
    <span class="text-bold text-grey-8 q-pr-sm">{{label}}</span><br v-if="!inlineLabel">
  </template>
  <q-btn-group unelevated>
    <template v-for="(opt, i) in select.options" :key="i">
      <lv-btn :soft="isActive(opt, solid) ? true : false" :color="isActive(opt)" :icon="opt?.icon || null"
        @click="emiters(opt)"
      >
        <span class="q-pl-xs" v-html="opt[optionLabel]"></span>
      </lv-btn>
    </template>
    </q-btn-group>
</div>
</template>

<script>

import { ref, watch, reactive, watchEffect, onBeforeMount, computed, useSlots, defineComponent } from 'vue'
import useServices from './../../composables/Services'
export default defineComponent({
  name: 'LvSegment',
  emits: [
    'input',
    'update:modelValue',
    'selected',
  ],
  props: {
    modelValue: {
      type: [String, Number, Object],
      default: null
    },
    col: {
      type: String,
      default: '12',
    },
    label: {
      type: String,
      default: null,
    },
    inlineLabel: {
      type: Boolean,
      default: false,
    },
    raw: {
      type: Boolean,
      default: false,
    },
    solid: {
      type: Boolean,
      default: false,
    },
    color: {
      type: String,
      default: 'primary',
    },
    options: {
      type: Array,
      default: () => { return [
        // {
        //   id: 1, // deafult value when selected
        //   name: 'Man', // default value on label
        //   icon: 'optional' // will show icon when you define that
        // }
      ]},
    },
    optionValue: {
      type: String,
      default: 'id',
    },
    optionLabel: {
      type: [String, Function],
      default: 'name',
    },
  },
  setup (props, { emit }) {
    const { Config, Handler, Helper, SetMetaPage, Api} = useServices()

    const inputValue = ref(props.modelValue)

    const select = reactive({
      options: [],
      tmp: [],
    })

    onBeforeMount(()=> {
      initOptions(props.options)
    })

    watchEffect(() => { // handle reactive for declared element
      inputValue.value = props.modelValue;
    })

    watch(() => props.defaultData, (newValue) => {
        select.options = []
        select.options.push(newValue)
      }
    )

    function initOptions (list, withTmp = true) {
      select.options = list
      if (withTmp) select.tmp = list
    }

    function isActive(opt, disableSoft = false) {
      if (opt.id && opt.id === inputValue.value) {
        if (!disableSoft) return props.color
      }
    }

    function emiters (val) {
      inputValue.value = val[props.optionValue]

      emit('update:modelValue', inputValue.value)
      emit('input', inputValue.value)
      emit('selected', val)
      // console.log(`emiters:(${refName.value})`, val, inputValue.value, selected)
    }

    return {
      select,
      isActive,
      inputValue,
      emiters,
    }
  }
})
</script>
