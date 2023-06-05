<template >
  <div :class="`lv-field col-${col}`">
    <div v-if="topLabel || $slots.topLabel" class="top-label" >
      <template v-if="$slots.topLabel"> <slot name="topLabel"></slot> </template>
      <template v-else> {{(label) ? label : ''}} </template>
    </div>
    <q-field :outlined="outlined" :borderless="borderless" dense
     :class="elClass" :readonly="isReadOnly"
    >
      <template v-slot:control="{ emitValue}">
        <q-toggle floating dense
          :size="size"
          :icon="icon"
          :unchecked-icon="uncheckedIcon"
          :checked-icon="checkedIcon"
          :icon-color="iconColor"
          :disable="isReadOnly"
          :color="color"
          :left-label="leftLabel"
          :class="mainClass"
          emit-value
          v-model="inputValue"
          :readonly="readonly"
          @update:model-value="val => emitters(val, emitValue)"
        >
          <span v-if="!topLabel" v-html="label"></span>
          <slot></slot>
        </q-toggle>
      </template>
    </q-field>
  </div>
</template>

<script>
import { ref, watch, watchEffect, onBeforeMount, computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvToggle',
  props: {
    modelValue: {
      type: [String, Boolean],
      default: '',
    },
    emit: {
      type: Function,
      default: () => {},
    },
    label: {
      type: String,
      default: null,
    },
    size: {
      type: String,
      default: 'md',
    },
    color: {
      type: String,
      default: 'primary',
    },
    icon: {
      type: String,
      default: null,
    },
    iconColor: {
      type: String,
      default: null,
    },
    checkedIcon: {
      type: String,
      default: null,
    },
    uncheckedIcon: {
      type: String,
      default: null,
    },
    block: {
      type: Boolean,
      default: false,
    },
    topLabel: {
      type: Boolean,
      default: false,
    },
    leftLabel: {
      type: Boolean,
      default: false,
    },
    flat: {
      type: Boolean,
      default: false,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    col: {
      type: String,
      default: '12',
    },
  },
  setup (props, { emit }) {
    const inputValue = ref(props.modelValue)

    onBeforeMount(()=> {
      if (props.modelValue) inputValue.value = props.modelValue
    })

    watchEffect(() => { // handle reactive for declared element
      inputValue.value = props.modelValue
    })

    const outlined = computed(() => {
      let res = true
      if (props.flat) res = !res
      return res
    })

    const borderless = computed(() => {
      let res = false
      if (props.flat) res = !res
      return res
    })

    const useInnerLabel = computed(() => {
      let res = (props.label) ? true : false
      if (props.topLabel) res = false
      return res
    })

    const isReadOnly = computed(() => {
      let res = false
      if (props.readonly) res = (props.readonly) ? true : props.readonly
      return res
    })

    const elClass = computed(() => {
      let res = `lv-toggle`
      if (isReadOnly.value) res = `${res} lv-field_readonly`
      return res
    })

    const mainClass = computed(() => {
      let res = (props.block) ? 'lv-toggle_block' : 'lv-toggle_vertical'
      if (props.leftLabel) res = `${res} lv-toggle_left`
      return res
    })

    function emitters (val, emitValueEvt) {
      emit('update:modelValue', inputValue.value)
      emit('input', inputValue.value)
      emitValueEvt(val)
    }
 
    return {
      inputValue,
      // computed
      outlined,
      borderless,
      useInnerLabel,
      elClass,
      isReadOnly,
      mainClass,
      //methods
      emitters,
    }
  }
})
</script>