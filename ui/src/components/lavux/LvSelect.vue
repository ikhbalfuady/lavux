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
      :clearable="(url) ? true : clearable"
      :options="select.options"
      :option-value="optionValueDefault"
      :option-label="(optionLabel) ? optionLabel : searchField"
      map-options
      :emit-value="!raw"
      @filter="(val, update) => filterSelect(val, update)"
      :use-input="isSearchable"
      :multiple="multiple"
      options-dense
      options-html
      :fill-input="fillInput"
      :hide-selected="hideSelected"
      @blur="onBlur"
      @focus="onFocus"
      :hide-bottom-space="!useBottomSlot"
      @popup-show="onPopupShow"
      @popup-hide="onPopupHide"
    >
      <template v-slot:label>
        {{(label) ? label : ''}}
      </template>

      <template v-slot:selected-item="scope">
        <div class="lv-select_display_value ellipsis"
          v-html="getSelectedView(optLabel(scope.opt), scope)"
        >
        </div>
      </template>

      <template v-slot:option="scope">
        <q-item v-bind="scope.itemProps" class="ph">
          <q-item-section>
            <q-item-label ><span v-html="optLabel(scope.opt)"></span></q-item-label>
          </q-item-section>
        </q-item>
        <q-separator/>
      </template>

      <template v-slot:no-option>
        <q-item>
          <q-item-section class="text-italic text-grey-7">
            No search results found for "{{ search }}"
          </q-item-section>
        </q-item>
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
  name: 'LvSelect',
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
    raw: {
      type: Boolean,
      default: false,
    },
    options: {
      type: Array,
      default: () => { return []},
    },
    optionValue: {
      type: String,
      default: 'id',
    },
    optionLabel: {
      type: [String, Function],
      default: 'name',
    },
    multiple: {
      type: Boolean,
      default: false,
    },
    url: { // only path url, if need add query params use "url-params"
      type: String,
      default: null,
    },
    urlParams: {
      type: [String, Array],
      default: null,
    },
    searchKey: { // it will be query params search ex: ?_contains=
      type: String,
      default: '_contains',
    },
    limit: { // it will be query params search ex: ?limit=8 
      type: [String, Number],
      default: '8',
    },
    searchBy: { // the value is avail column of modules 
      type: Array,
      default: () => { return ['name'] },
    },
    searchable: {
      type: Boolean,
      default: false,
    },
    defaultData: {
      type: [Object, String, Number],
      default: null,
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
    const search = ref(null) // for handle caching search data via ajax
    const select = reactive({
      options: [],
      tmp: [],
    })

    const forceHideSelected = ref(false)
    const focusEl = ref(false)

    onBeforeMount(()=> {
      initRefs(refName.value, 1)
      if (props.modelValue) inputValue.value = props.modelValue
      if (props.options) {
        select.options = props.options
        select.tmp = props.options
      }
      handleDefaultData()
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
      else {
        if (props.url) res = 'Type to search...'
        if (props.searchable) res = 'Type to search...'
      }

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

    const optionValueDefault = computed(() => {
      let res = 'id'
      if (props.optionValue) res = props.optionValue
      return res
    })

    const searchField = computed(() => {
      let col = 'name'
      if (props.optionLabel && typeof props.optionLabel !== 'function') col = props.optionLabel
      return col
    })

    const isSearchable = computed(() => {
      let res = false
      if (props.searchable) res = true
      if (props.url) res = true
      if (!focusEl.value) res = false
      return res
    })

    const urlPath = computed(() => {
      let res = props.url
      if (res.includes("?")) {
        const parts = props.url.split("?")
        res = parts[0] === str ? "" : parts[0]
      }
      return res
    })

    const hideSelected = computed(() => {
      let res = false
      if (props.url) res = true
      if (props.searchable) res = true
      if (inputValue.value) res = false
      if (forceHideSelected.value) res = true
      return res
    })

    const fillInput = computed(() => {
      let res = false
      if (props.searchable) res = true
      if (inputValue.value) res = false
      return res
    })

    const useInput = computed(() => {
      let res = false
      if (props.searchable) res = true
      if (focusEl.value) res = focusEl.value
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
      const selected = select.tmp.find(r => r[optionValueDefault.value] === inputValue.value) || null
      
      emit('update:modelValue', inputValue.value)
      emit('input', inputValue.value)
      emit('selected', selected)
      // console.log(`emiters:(${refName.value})`, val, inputValue.value, selected)
    }

    function initRefs(key, el) {
      refs[key] = ref(el);
    }

    function handleDefaultData () {
      if (props.url) {
        let val = inputValue.value
        if (props.defaultData) {
          var opts = [props.defaultData]
          if (props.multiple) opts = props.defaultData
          initOptions(opts)
        } else { // fetch to server if default data not defined
          if (val) {
            if (props.multiple) {
              getforDefaultValueMultiple(val)
            } else getforDefaultValue(val)
          }
        }
      }
    }

    function getforDefaultValue (val) {
      let endpoint = `${urlPath.value}?id=${val}`
      Api.get(endpoint, async (status, data, message) => {
        if (status === 200) {
          initOptions(data)
        }
      })
    }

    function getforDefaultValueMultiple (val) {
      let idx = null
      if (val.length) idx = val.join(',')
      if (!idx) return false

      let endpoint = `${urlPath.value}?id=in@${val}`
      Api.get(endpoint, async (status, data, message) => {
        if (status === 200) {
          select.options = data
          select.tmp = data
        }
      })
    }

    function filterSelect (val, update) {
      if (!isReadOnly.value) {
        if (props.url) {
          if (val !== search.value) fetchAjax(val, update)
          else update(() => initOptions(select.tmp)) // handling cache
        } else fetchLocal(val, update)
      }
      search.value = val
    }

    async function fetchLocal (val, update) {
      const options = {
        select: select.options,
        selectTmp: select.tmp
      }

      if (val) {
        const res = await Handler.filterSelect(val, update, 'select', options, searchField.value)
        await update(() => { 
          select.options = res.select 
        })
      } else {
        await update(() => {
          // trigger done
        })
      }
    }

    function fetchAjax (val, update) {
      val = val || ''
      const columns = props.searchBy ? props.searchBy.join(',') : ''
      const limit = props.limit || 8

      let endpoint = props.url
      if (!endpoint.includes("?")) endpoint = endpoint + '?'

      endpoint = `${endpoint}&_limit=${limit}`
      endpoint = `${endpoint}&${props.searchKey}=${columns}:${val}`
      endpoint = `${endpoint}&force` // agar bisa akses langsung module tanpa permission

      // handle url params
      console.log('urlparams', props.urlParams)
      let params = ''
      if (props.urlParams) {
        if (typeof(props.urlParams) === 'string') params = props.urlParams
        if (typeof(props.urlParams) === 'object') {
          // console.log('urlparams', props.urlParams)
          props.urlParams.map(r => {
            if (r.key) {
              const val = r.value || ''
              params += `&${r.key}=${val}`
            }
          })
        }
      }
      endpoint = `${endpoint}&${params}` // init params

      Api.get(endpoint, async (status, data, message) => {
        if (status === 200) {
          if (update) {
            await update(() => initOptions(data))
          } else {
            initOptions(data)
          }
        }
      })
    }

    function initOptions (list, withTmp = true) {
      if (
        props.defaultData &&
        inputValue.value === props.defaultData[optionValueDefault.value] &&
        !list.find(r => r[optionValueDefault.value] === props.defaultData[optionValueDefault.value])
      ) {
        list = [props.defaultData, ...list]
      }
      select.options = list
      if (withTmp) select.tmp = list
    }

    function optLabel (val) {
      var res = (props.optionLabel instanceof Function) ? props.optionLabel(val) : val[props.optionLabel || searchField.value]
      return res
    }

    function getSelectedView (data, raw) {
      if (data) {
        var totalData = inputValue.value.length
        var idx = 0
        if (raw.index) idx = raw.index + 1
        var res = data.split('||')
        if (props.multiple) {
          if (idx === totalData) return `${res[0]}`
          else return `${res[0]},&nbsp;`
        } else {
          return res[0] || '...'
        }
      } else return ''
    }

    function onPopupShow () {
      //
    }

    function onPopupHide () {
      //
    }
    
    function onBlur () {
      focusEl.value = false
      forceHideSelected.value = false
      initOptions(select.tmp)
    }
    
    function onFocus () {
      const ref = refs[refName.value].value
      
      if (ref) { // handle when click focus element
        ref.focus()
      }

      focusEl.value = true
      if (props.searchable || props.url) forceHideSelected.value = true
      initOptions(select.tmp)
    }


    return {
      inputValue,
      select,
      refs,
      search,
      // computed
      refName,
      outlined,
      borderless,
      isReadOnly,
      elClass,
      useInnerLabel,
      useBottomSlot,
      placeholderDefault,
      optionValueDefault,
      searchField,
      isSearchable,
      hideSelected,
      fillInput,
      useInput,
      fixRules,
      // methods
      filterSelect,
      emiters,
      onBlur,
      onFocus,
      optLabel,
      getSelectedView,
      onPopupShow,
      onPopupHide,
    }
  }
})
</script>