<template >
  <div class="row q-pa-md bg-white" v-if="table" style="width: 420px;">

    <div class="col-12 q-mb-sm">
      <small class="text-indigo-8">
        <b>*</b> This setting will be saved in you local configuration
      </small>
      <q-select class="q-pt-xs" outlined dense v-model="columns" multiple :options="columnList" use-chips stack-label
        label="Column Visibility" emit-value map-options option-value="id"
      />
    </div>

    <div class="col-12 q-my-sm">
      <small class="text-indigo-8 ">
        <b>*</b> Configuration global search
      </small>
      <q-select class="q-pt-xs" outlined dense v-model="searchBy" multiple :options="searchByList" use-chips stack-label
        label="Search By" emit-value map-options option-value="id"
      />
    </div>

    <div class="col-12  q-my-sm">
      <lv-btn @click="saveSetting" color="primary" label="Save Setting"/>
    </div>
  </div>
</template>

<script>
import { ref, reactive, watch, onMounted, computed, defineComponent } from 'vue'
import useServices from './../../composables/Services'
export default defineComponent({
  name: 'LvTableSetting',
  props: {
    config: { // table object structure, use : Handler.table() for standard structure
      type: Object,
      default: null,
    }, 
    // emit: {
    //   type: Function,
    //   default: () => {},
    // },
  },
  setup (props, { emit }) {
    const { Config, Helper, Handler, Api } = useServices()

    let table = ref({
      ...Handler.table()
    })

    const columns = ref([])
    const searchBy = ref([])


    watch(props, () => {
      console.log('watch', props.config)
      table.value = props.config
      initColumns()
      initSearchBy()
    })

    onMounted(() => {
      table.value = props.config
      initColumns()
      initSearchBy()
    })

    const columnList = computed(() => {
      let res = []
      if (table.value && table.value.columns) {
        table.value.columns.map(r => {
          res.push({id: r.name, label: r.label})
        })
      }
      return res
    })

    const searchByList = computed(() => {
      let res = []
      if (table.value && table.value.columns) {
        table.value.columns.map(r => {
          if (r.field) res.push({id: r.name, label: r.label})
        })
      }
      return res
    })

    function initColumns () {
      if (table.value.visibleColumns && table.value.visibleColumns.length) columns.value = table.value.visibleColumns
      else columns.value = columnList.value
    }

    function initSearchBy () {
      if (table.value.searchBy && table.value.searchBy.length) searchBy.value = table.value.searchBy
      else searchBy.value = searchByList.value
    }

    function saveSetting () {
      const cacheName = table.value.cacheVisibilityColumn
      console.log('saveSetting', cacheName)
      Helper.saveLdb(cacheName, columns.value)

      // reset setting
      table.value = {
        ...Handler.table(table.value.columns, table.value.limit, table.value.cacheVisibilityColumn)
      }

      table.value.searchBy = searchBy
      emit('saved', table.value)
    }


    return {
      table,
      columns,
      searchBy,
      // computed
      columnList,
      searchByList,
      // methods
      initColumns,
      saveSetting,
    }
  }
})
</script>
