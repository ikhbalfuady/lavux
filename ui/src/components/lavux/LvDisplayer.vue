<template>
  <span v-if="type === 'boolean'">
    <q-chip size="sm">
      <q-avatar size="xs" :icon="value ? 'check' : 'close'" :color="value ? 'green-8' : 'red-8'" text-color="white" />
      {{value ? 'True' : 'False'}}
    </q-chip>
  </span>

  <span v-else-if="type === 'array'">
    <q-chip size="sm" v-for="val in value" :key="val">{{val}} </q-chip>
  </span>

  <span v-else-if="type === 'arrayObject'">
    <template v-for="(val, i) in value" :key="i">
      <q-badge :class="`link q-mr-sm ${ dense ? '' : 'q-pa-sm text-bold'}`" :size="dense ? 'xs' : 'sm'" v-if="Object.keys(val).length" >
        <small v-if="dense">{{val[$Helper.getKeyObject(val)]}}</small>
        <span v-else>{{val[$Helper.getKeyObject(val)]}}</span>
        <q-tooltip class="bg-white text-dark shadow-md border" style="padding:1px" ransition-show="scale" transition-hide="scale">
          <table class="table table-bordered" style="padding:3px !important; font-size: 12px;" >
            <tbody style="padding:1px !important" >
              <tr ><td colspan="2" class="text-center text-primary text-bold">Detail</td></tr>
              <tr v-for="i in Object.keys(val).length" :key="i" style="padding:1px !important" >
                <td style="padding:1px !important" class="text-left text-bold">{{ Object.keys(val)[i-1] }}</td>
                <td style="padding:1px !important" class="text-left ">: <lv-displayer :data="val[Object.keys(val)[i-1]] "/></td>
              </tr>
            </tbody>
          </table>
        </q-tooltip>
      </q-badge>
    </template>
  </span>

  <span v-else-if="type === 'object'">
    <table v-if="Object.keys(value).length"  class="table table-bordered" style="padding:3px !important; font-size: 12px;" >
      <tbody style="padding:1px !important" >
        <tr v-for="i in Object.keys(value).length" :key="i" style="padding:1px !important" >
          <td style="padding:1px !important" class="text-left text-bold">{{ Object.keys(value)[i-1] }}</td>
          <td style="padding:1px !important" class="text-left ">: <lv-displayer :data="value[Object.keys(value)[i-1]] "/></td>
        </tr>
      </tbody>
    </table>
  </span>

  <span v-else v-html="value"></span>
</template>

<script>
import useServices from './../../composables/Services'
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvDisplayer',
  props: {
    data: {}, // support any type
    dense: {
      type: Boolean,
      default: false,
    }
  },
  setup (props) {
    const { Config, Handler, Helper, Api, GlobalStore, SetMetaPage } = useServices()

    const type = computed(() => { return Helper._is(props.data) })

    const value = computed(() => {
      let res = props.data
      if (type.value === 'number' || type.value === 'float') res = Helper.currency(res)
      else if (type.value === 'date') res = Helper.beautyDate(res)
      else if (type.value === 'dateTime') res = Helper.beautyDate(res, ' ', true)
      return res
    })

    return {
      type,
      value,
    }
  }
})
</script>