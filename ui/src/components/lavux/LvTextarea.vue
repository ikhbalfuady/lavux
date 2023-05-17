<template >
   <div class="col-12 lv-field ">
    <div v-if="topLabel || $slots.topLabel" class="top-label" >
      <template v-if="$slots.topLabel"> <slot name="topLabel"></slot> </template>
      <template v-else> {{(label) ? label : ''}} </template>
    </div>
    <q-input :class="elClass" :label-slot="useInnerLabel" :outlined="outlined" :borderless="borderless" dense
      type="textarea"
      v-model="inputValue"
      :readonly="readonly"
      @update:model-value="emitters()"
      :hint="hint"
      :placeholder="placeholderDefault"
      :rows="rows"
      :autogrow="autogrow"
      :rules="fixRules"
    >
      <template v-slot:label>
        {{(label) ? label : ''}}
      </template>

      <template v-if="$slots.prepend" v-slot:prepend>
        <slot name="prepend"></slot>
      </template>

      <template v-if="$slots.append" v-slot:append>
        <slot name="append"></slot>
      </template>

      <template v-if="$slots.hint" v-slot:hint>
        <slot name="hint"></slot>
      </template>
    </q-input>

  </div>
</template>

<script>
import { ref, watch, watchEffect, onBeforeMount, computed, useSlots, defineComponent } from 'vue'
import useServices from './../../composables/Services'
export default defineComponent({
  name: 'LvTextarea',
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
    rows: {
      type: String,
      default: '3',
    },
    autogrow: {
      type: Boolean,
      default: false,
    },
    topLabel: {
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
    placeholder: {
      type: String,
      default: null
    },
    hint: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
  },
  setup (props, { emit }) {
    const { Config, Handler, Helper} = useServices()
    const slots = useSlots()
    const inputValue = ref(props.modelValue)

    onBeforeMount(()=> {
      if (props.modelValue) inputValue.value = props.modelValue
    })

    watchEffect(() => { // handle reactive for declared element
      inputValue.value = props.modelValue;
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

    const placeholderDefault = computed(() => {
      let res = ''
      if (props.placeholder) res = props.placeholder
      if (isReadOnly.value) res = ''
      if (inputValue.value) res = ''
      return res
    })

    const useBottomSlot = computed(() => {
      let res = false
      if (props.hideBottomSpace) res = true
      if (props.hint) res = true
      if (props.rules) res = true
      if (slots.hint) res = true
      return true
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
      let res = `lv-textarea`
      if (props.clearable) res = `${res} lv-field_clearable`
      if (useBottomSlot.value) res = `${res} lv-field_fix_bottom`
      if (isReadOnly.value) res = `${res} lv-field_readonly`
      return res
    })

    const fixRules = computed(() => {
      let res = props.rules
      if (props.required) res = Handler.rules('required')
      return res
    })

    function emitters (val) {
      emit('update:modelValue', inputValue.value)
      emit('input', inputValue.value)
      emit('update', inputValue.value)
    }
 
    return {
      inputValue,
      // computed
      outlined,
      borderless,
      useInnerLabel,
      elClass,
      isReadOnly,
      placeholderDefault,
      useBottomSlot,
      fixRules,
      //methods
      emitters,
    }
  }
})
</script>