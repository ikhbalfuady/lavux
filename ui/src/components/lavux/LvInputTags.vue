<template >
  <div :class="`lv-field col-${col} row`">
    <div v-if="topLabel || $slots.topLabel" class="top-label" >
      <template v-if="$slots.topLabel"> <slot name="topLabel"></slot> </template>
      <template v-else> {{(label) ? label : ''}} </template>
    </div>
    <q-select :ref="refs[refName]" :label-slot="useInnerLabel" :outlined="outlined" :borderless="borderless" dense :class="`col-12 ${elClass}`"
      v-model="inputValue"
      @update:model-value="val => emiters(val)"
      :rules="fixRules"
      :bottom-slots="useBottomSlot"
      :readonly="isReadOnly"
      :disable="isReadOnly"
      :placeholder="placeholderDefault"
      :clearable="clearable"
      options-dense
      options-html
      @blur="onBlur"
      @focus="onFocus"
      :hide-bottom-space="!useBottomSlot"
      @popup-show="onPopupShow"
      @popup-hide="onPopupHide"
      :error="error"
      :error-message="errorMessage"
      use-input use-chips multiple hide-dropdown-icon input-debounce="0"
      new-value-mode="add-unique"

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

      <template v-if="$slots.before" v-slot:before>
        <slot name="before"></slot>
      </template>

      <template v-if="$slots.after" v-slot:after>
        <slot name="after"></slot>
      </template>

      <template v-if="$slots.hint || hint" v-slot:hint>
        {{ hint }}
        <slot name="hint"></slot>
      </template>


    </q-select>

  </div>
</template>

<script>
import { ref, watch, reactive, watchEffect, onBeforeMount, computed, useSlots, defineComponent } from 'vue'
import useServices from './../../composables/Services'
export default defineComponent({
  name: 'LvInputTags',
  props: {
    modelValue: {
      type: Array,
      default: () => { return [] }
    },
    emit: {
      type: Function,
      default: () => {},
    },
    // design
    label: {
      type: String,
      default: null,
    },
    topLabel: {
      type: Boolean,
      default: false
    },
    clearable: {
      type: Boolean,
      default: false,
    },
    hideBottomSpace: {
      type: Boolean,
      default: true,
    },
    flat: {
      type: Boolean,
      default: false,
    },
    rules: {
      type: Object,
      default: null
    },
    stackLabel: {
      type: Boolean,
      default: false,
    },
    placeholder: {
      type: String,
      default: null
    },
    readonly: {
      type: Boolean,
      default: false
    },
    hint: {
      type: String,
      default: null
    },
    error: {
      type: Boolean,
      default: false
    },
    errorMessage: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    col: {
      type: String,
      default: '12',
    },
  },
  setup (props, { emit }) {
    const { Config, Handler, Helper, SetMetaPage, Api} = useServices()
    const slots = useSlots()
    const refs = {}
    const defaultValue = ref(props.defaultData)
    const inputValue = ref(props.modelValue)

    onBeforeMount(()=> {
      initRefs(refName.value, 1)
      if (props.modelValue) inputValue.value = props.modelValue
    })

    watchEffect(() => { // handle reactive for declared element
      inputValue.value = props.modelValue;
    })

    watch(defaultValue, () => {
      handleDefaultData()
    })

    const refName = computed(() => {
      let res = 'SLC_'
      if (props.label) res = props.label
      res = Helper.createUID(false) + '_' + res.toLowerCase()
      res = Helper.makeRef(res)
      return res
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
      if (props.hideBottomSpace) res = false
      if (props.hint) res = true
      if (fixRules.value) res = true
      if (slots.hint) res = true
      return res
    })

    const isReadOnly = computed(() => {
      let res = false
      if (props.readonly) {
        res = (props.readonly) ? true : props.readonly
      }
      return res
    })

    const useInnerLabel = computed(() => {
      let res = (props.label) ? true : false
      if (props.topLabel) res = false
      return res
    })

    const elClass = computed(() => {
      let res = `lv-select`
      if (props.clearable) res = `${res} lv-field_clearable`
      if (useBottomSlot.value) res = `${res} lv-field_fix_bottom`
      if (isReadOnly.value) res = `${res} lv-field_readonly`
      return res
    })

    const fixRules = computed(() => {
      let res = props.rules
      if (props.required) res = Handler.rules('required')
      if (props.required && props.multiple) res = [ val => !!val && val.length > 0 || 'Field is required' ]
      return res
    })

    /* Methods */

    function emiters (val) {
      emit('update:modelValue', inputValue.value)
      emit('input', inputValue.value)
    }

    function initRefs(key, el) {
      refs[key] = ref(el);
    }

    function onPopupShow () {
      emit('on-popup-hide', inputValue.value)
    }

    function onPopupHide () {
      emit('on-popup-hide', inputValue.value)
    }

    function onBlur () {
      emit('blur', inputValue.value)
    }

    function onFocus () {
      emit('focus', inputValue.value)
    }


    return {
      inputValue,
      refs,
      // computed
      refName,
      outlined,
      borderless,
      isReadOnly,
      elClass,
      useInnerLabel,
      useBottomSlot,
      placeholderDefault,
      fixRules,
      // methods
      emiters,
      onBlur,
      onFocus,
      onPopupShow,
      onPopupHide,
    }
  }
})
</script>
