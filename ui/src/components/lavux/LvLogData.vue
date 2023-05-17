<template>
  <table class="log-data" >
    <tbody>
    <template v-for="(log, i) in data" :key="i">
      <tr v-if="log.by">
        <td>
          <q-badge :color="getColorBadge(i)">{{ $Helper.getFirstChar(i) }}</q-badge>
          {{ $Helper.beautyDate(log.at, ' ', true) }} 
          <q-tooltip>Info {{i}}</q-tooltip>
        </td>
        <td>
          <lv-user :username="log.by" style="position: relative;top: -1px;"/>
        </td>
        <td v-if="showIp">
          {{ log.ip }}
        </td>
      </tr>
    </template>
    </tbody>
  </table>
</template>

<script>
import useServices from './../../composables/Services'
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvLogData',
  props: {
    data: {
      type: Object,
      default: null
    },
    showIp: {
      type: Boolean,
      default: false
    },
  },
  setup (props) {
    const { Config, Handler, Helper, Api, GlobalStore, SetMetaPage } = useServices()

    function getColorBadge(type) {
      let res = 'primary'
      if (type === 'updated') res = 'green-7'
      if (type === 'deleted') res = 'red-7'
      return res
    }

    return {
      getColorBadge,
    }
  }
})
</script>
<style lang="scss">
.log-data {
  padding: 0px;

  tbody {

    tr {
      height: 16px !important;

      td {
        font-size: 10px !important;
        padding: 0px !important;
        border: 0px !important;
        height: 10px !important;

        .q-badge {
          font-size: 10px !important;
          font-weight: 600;
          padding: 1px 3px;
        }
      }
    }
  }
}
</style>