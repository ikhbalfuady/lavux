<template >
  <div :class="`lv-field col-${col}`">
    <div v-if="topLabel || $slots.topLabel" class="top-label" >
      <template v-if="$slots.topLabel"> <slot name="topLabel"></slot> </template>
      <template v-else> {{(label) ? label : ''}} </template>
    </div>
    <q-field :label-slot="useInnerLabel" :outlined="outlined" :borderless="borderless" dense :debouce="debouce"
      :class="elClass"
      v-model="inputValue"
      @input="$emit('update:value', $event)"
      :clearable="clearable"
      :stack-label="stackLabel"
      :hide-bottom-space="!useBottomSlot"
      :bottom-slots="useBottomSlot"
      :rules="fixRules"
      :error="error"
      :error-message="errorMessage"
      :readonly="isReadOnly"
      :disable="isReadOnly"
    >
      <template v-slot:control="{ id, floatingLabel, modelValue, emitValue}">
        <template v-if="mode === 'currency' || mode === 'number' " >
          <money3 :id="id" class="q-field__input text-right" v-model.number="inputValue" v-bind="currecyType"
            v-show="floatingLabel"
            @input="e => emitCurrency(e.target.value, emitValue)"
            @dblclick="e => e.target.select()"
            @focus="fixFocus($event)"
            v-bind:readonly="isReadOnly"
          />
        </template>

        <template v-else-if="mode === 'date' || mode === 'datetime' || mode === 'time'" >
          <template v-if="!isReadOnly" >
            <!-- Tanggal -->
            <q-popup-proxy v-if="mode !== 'time'" cover :ref="refs[refDate]" transition-show="jump-up" transition-hide="jump-down" >
              <q-date :mask="maskDate"
                v-bind:modelValue="modelValue"
                :options="optionDate"
                @update:model-value="val => handlerInputDate(refDate, refTime, val, emitValue)"
              >
              </q-date>
            </q-popup-proxy>
            <!-- Jam -->
            <q-popup-proxy  cover persistent :ref="refs[refTime]" transition-show="jump-up" transition-hide="jump-down">
              <q-time :mask="maskDate" format24h
                v-bind:modelValue="modelValue"
                @update:model-value="val => handlerInputDate(refDate, refTime, val, emitValue)"
              >
                <div class="row items-center justify-end"><q-btn v-close-popup label="Oke" color="primary" flat /></div>
              </q-time>
            </q-popup-proxy>
          </template>
          <div v-if="mode === 'time'" class="self-center full-width no-outline" tabindex="0">{{ modelValue }}</div>
          <div v-else class="self-center full-width no-outline q-field__input" tabindex="0">
            {{ inputValue ? $Helper.beautyDate(inputValue, '', (mode === 'datetime' ? true : false)) : ((placeholder) ? placeholderDefault : '') }}
          </div>
        </template>

        <template v-else-if="mode === 'daterange'" >
          <template v-if="!isReadOnly" >
            <!-- Tanggal -->
            <q-popup-proxy v-if="mode !== 'time'" cover :ref="refs[refDate]" transition-show="jump-up" transition-hide="jump-down" >
              <q-date mask="YYYY-MM-DD" range clearable
                v-bind:modelValue="modelValue"
                :options="optionDate"
                @update:model-value="val => handlerInputDate(refDate, null, val, emitValue)"
              >
              <div class="row items-center justify-end">
                <small class="text-grey-7">Double click on same day to set one day range</small>
                <q-btn v-if="inputValue" v-close-popup label="Reset" color="red" flat @click="emiters(null)" />
                <q-btn v-close-popup label="Close" color="primary" flat />
              </div>
              </q-date>
            </q-popup-proxy>
          </template>
          <div class="self-center full-width no-outline q-field__input" tabindex="0">{{ dateRangeLabel }}</div>
        </template>

        <template v-else>
          <input :id="id" class="q-field__input"
            :value="modelValue" style="text-transform: none;"
            @input="e => emitValue(e.target.value)"
            v-bind:readonly="isReadOnly"
            :placeholder="(placeholder) ? placeholderDefault : ''"
            :type="inputType.current"
          />
        </template>
      </template>

      <template v-slot:label>
        {{(label) ? label : ''}}
      </template>

      <template v-if="$slots.prepend" v-slot:prepend>
        <slot name="prepend"></slot>
      </template>

      <template v-if="showSlotAppend" v-slot:append>
        <q-icon v-if="type === 'password'" :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="visiblePwd" />
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
    </q-field>
  </div>
</template>

<script>
import { Money3Directive, Money3Component, format, unformat } from 'v-money3';
import { ref, reactive, watch, watchEffect, onBeforeMount, computed, useSlots, defineComponent } from 'vue'
import useServices from './../../composables/Services'
export default defineComponent({
  name: 'LvInput',
  directives: {
    money3: Money3Directive,
  },
  components: {
    money3: Money3Component,
  },
  props: {
    modelValue: {
      type: [String, Number, Object],
      default: null
    },
    emit: {
      type: Function,
      default: () => {},
    },
    // spec
    mode: { // enum( text, currency, number, date, datetime, time, daterange )
      type: String,
      default: 'text'
    },
    decimal: {
      type: Number,
      default: 2
    },
    minDate: {
      type: String,
      default: null
    },
    maxDate: {
      type: String,
      default: null
    },
    type: {
      type: String,
      default: 'text'
    },
    // design
    col: {
      type: String,
      default: '12',
    },
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
    debouce: {
      type: Number,
      default: 1
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
  },
  setup (props, { emit }) {
    const { Config, Handler, Helper, SetMetaPage, Api} = useServices()
    const slots = useSlots()

    const refs = {}
    const isPwd = ref(true)
    const inputType = reactive({
      temp: props.type,
      current: props.type,
    })
    const inputValue = ref(props.modelValue)

    onBeforeMount(()=> {
      if (props.modelValue) {
        if (props.mode === 'number' || props.mode === 'currency') firstInitDataNumber(props.modelValue)
        else inputValue.value = props.modelValue
      } else {
        if (props.mode === 'number' || props.mode === 'currency') inputValue.value = 0
      }
      initRefs(refDate.value, 1)
      initRefs(refTime.value, 1)

      if (props.type) {
        inputType.current = props.type
        inputType.temp = props.type
      }
    })

    watchEffect(() => { // handle reactive for declared element
      if (props.mode === 'number' || props.mode === 'currency') firstInitDataNumber(props.modelValue)
      else inputValue.value = props.modelValue

      inputType.temp = props.type
      inputType.current = props.type
    })

    watch(inputValue, () => {
      if (props.type === 'number') inputValue.value = Helper.toNumber(inputValue.value)
      emit('update:modelValue', inputValue.value)
      emit('input', inputValue.value)
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
      let res = 'Type something...'
      if (props.placeholder) res = props.placeholder
      return res
    })

    const decimalPlace = computed(() => {
      return (props.mode === 'currency') ? props.decimal : 0
    })

    const currecyType = computed(() => {
      return Config.currencyConfig(decimalPlace.value)
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

    const refDate = computed(() => {
      let res = 'DP_'
      if (props.label) res = props.label
      res = Helper.createUID(false) + '_' + res.toLowerCase()
      res = Helper.makeRef(res)
      return res
    })

    const refTime = computed(() => {
      let res = 'TP_'
      if (props.label) res = props.label
      res = Helper.createUID(false) + '_' + res.toLowerCase()
      res = Helper.makeRef(res)
      return res
    })

    const maskDate = computed(() => {
      let res = 'YYYY-MM-DD HH:mm'
      if (props.mode === 'date') res = 'YYYY-MM-DD'
      if (props.mode === 'time') res = 'HH:mm'
      return res
    })

    const dateRangeLabel = computed(() => {
      return Handler.labelFilterDate(inputValue.value)
    })

    const useInnerLabel = computed(() => {
      let res = (props.label) ? true : false
      if (props.topLabel) res = false
      return res
    })

    const elClass = computed(() => {
      let res = `lv-input`
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

    const showSlotAppend = computed(() => {
      let res = false
      if (props.type === 'password') res = true
      if (slots.append) res = true
      return res
    })

    /* Methods */

    function initRefs(key, el) {
      refs[key] = ref(el);
    }

    function firstInitDataNumber (val) {
      if (!val) val = 0
      inputValue.value = parseFloat(val)
    }

    function onBlur (e) {
      emit('blur', e)
    }

    function emiters (e) {
      inputValue.value = e
      emit('input', e)
    }

    function emitCurrency (val, evt) {
      const value = unformat(val, Config.currencyConfig(decimalPlace.value))
      evt(value)
    }

    function optionDate (date) {
      const val = new Date(date).getTime()
      if (props.minDate && props.maxDate) {
        const start = new Date(props.minDate).getTime() - (24 * 60 * 60 * 1000)
        const end = new Date(props.maxDate).getTime()

        return (val >= start) && (val <= end)
      }
      if (props.minDate) {
        const start = new Date(props.minDate).getTime() - (24 * 60 * 60 * 1000)
        return val >= start
      }
      if (props.maxDate) {
        const end = new Date(props.maxDate).getTime()
        return val <= end
      }
      return true
    }

    function handlerInputDate (refDate, refTime, val, emitValueEvt) {
      emitValueEvt(val)
      let refD = refs[refDate]
      let refT = refs[refTime]

      if (refDate && refD.value) refD?.value?.hide()
      if ((refTime && refT.value) && props.mode === 'datetime') refT?.value?.show()
    }

    function fixFocus (event) {
      event.stopImmediatePropagation()
    }

    function visiblePwd () {
      isPwd.value = !isPwd.value
      if (!isPwd.value) inputType.current = 'text'
      else inputType.current = inputType.temp
    }

    return {
      inputType,
      inputValue,
      refs,
      isPwd,
      // computed
      outlined,
      borderless,
      currecyType,
      placeholderDefault,
      useBottomSlot,
      isReadOnly,
      refDate,
      refTime,
      maskDate,
      dateRangeLabel,
      useInnerLabel,
      elClass,
      fixRules,
      showSlotAppend,

      // methods
      onBlur,
      emiters,
      firstInitDataNumber,
      emitCurrency,
      optionDate,
      handlerInputDate,
      fixFocus,
      visiblePwd,
    }
  }
})
</script>
