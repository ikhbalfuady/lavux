<template>
  <q-table v-if="showTable" dense class="table-data no-shadow" :style="`height: ${height};`"
    :rows="table.data" :columns="tableColumns" :loading="table.loading"
    row-key="id" virtual-scroll
    :virtual-scroll-item-size="itemSize" :virtual-scroll-sticky-size-start="itemSize"
    :pagination="table.pagination" :rows-per-page-options="[0]"
    @virtual-scroll="props => onScroll(props)"
    :selection="(table.action) ? 'multiple' : 'none'"
    :selected-rows-label="makeLabelSelected"
    v-model:selected="table.selected"
    :visible-columns="table.visibleColumns"
    @row-click="(evt, row, index) => rowClickHandler(evt, row, index, 'click')"
    @row-dblclick="(evt, row, index) => rowClickHandler(evt, row, index, 'dblclick')"
    @row-contextmenu="(evt, row, index) => rowClickHandler(evt, row, index, 'contextmenu')"
  >
    <template v-slot:top v-if="!hideTop">

      <template v-if="table.selected.length">
        <lv-btn @click="deleteRestoreSelected" v-if="actionBtn"
          :label="`${(table.isTrashed) ? 'Re-Activate' : 'Delete'} (${table.selected.length})`"
          :color="(table.isTrashed) ? 'green' : 'negative' "
          :icon="(table.isTrashed) ? 'check' : 'delete' "
        />
      </template>

      <template v-else >
        <q-btn size="md" flat dense color="primary" @click="onRefresh">
          <q-icon name="sync" :class="`${table.loading ? 'spin' :''}`"/>
          <q-tooltip>Refresh</q-tooltip>
        </q-btn>

        <q-toggle icon="delete" dense size="sm" label="Trash" 
          v-model="table.isTrashed" @click="onRefresh" class="q-pl-sm animated fadeIn label-toggle-sm"
        />

        <q-space />
        <q-input ref="focusSearch" debounce="500" :placeholder="`Search by ${table.searchBy.join(',')}...`" class="input-search-bar clearable no-shadow"
          v-model="table.search" @update:model-value="onRefresh" clearable dense standout="bg-primary"
        >
          <template v-slot:append>
            <q-icon v-if="!table.search" name="search" class="animated zoomIn" style="font-size: 18px;"/>
            <small v-if="!noShorthand" style="font-size: 13px;font-weight: 600;color: #a6a5a5;">(F1)</small>
          </template>
          <template v-slot:after v-if="setting">
            <q-btn unelevated flat icon="tune" color="primary" dense >
              <q-popup-proxy>
                <KeepAlive>
                  <lv-table-setting :config="table" @saved="updateSetting"/>
                </KeepAlive>
              </q-popup-proxy>
            </q-btn>
          </template>
        </q-input>
      </template>
    </template>

    <template v-slot:header="props">
      <q-tr v-if="table" :props="props">

        <q-th v-if="table.action" style="padding: 0px !important" >
          <q-checkbox v-if="table.selected.length > 0" @update:model-value="val => onSelectedAll(val)" v-model="checkAll" />
        </q-th>
        
        <template v-for="col in props.cols">
          <q-th v-if="showAction(col)" :key="col.name" :props="props" :style="`${col.width ? `width:${col.width}` : ''}`">
            <LvThead :table="table" :column="col" :label="col.label" @filter="searching" @sorted="sortData"/>
          </q-th>
        </template>
      </q-tr>
    </template>

    <!-- Custom Body -->
    <template v-slot:body-cell-action="props" v-if="table.action">
      <q-td class="lv-table-td" :props="props" :style="`${props.col.width ? `width:${props.col.width}` : ''}`">
        <slot name="col-action" 
          :row="props.row"
          :index="props.rowIndex"
          :value="props.value"
          :expand="props.expand"
          :selected="props.selected"
          :col="props.col"
        >
        </slot>
      </q-td>
    </template>

    <template v-for="(col, i) in tdList" v-slot:[`body-cell-${col.name}`]="props" :key="i">
      <q-td class="lv-table-td" :props="props" v-if="col.name !== 'action'" :style="`${props.col.width ? `width:${props.col.width};` : ''}`">
        <template v-if="!$slots[`col-${col.name}`]">
          <template v-if="col.type === 'index'">{{props.rowIndex+1}}</template>
          <template v-else>{{props.value}}</template>
        </template>
        <slot :name="`col-${col.name}`" 
          :row="props.row"
          :index="props.rowIndex"
          :value="props.value"
          :expand="props.expand"
          :selected="props.selected"
          :col="props.col"
        >
        </slot>
      </q-td>
    </template>

    <template v-slot:pagination>
      <span v-if="table">1 - {{table.data.length}} of {{table.pagination.rowsNumber}}</span>
    </template>

  </q-table>
</template>

<script>
import LvThead from './LvThead.vue'
import { ref, reactive, onMounted, onBeforeMount, computed, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useServices from './../../composables/Services'

/**
 * LvTable
 * 
 * Columns structure: 
 * - searchable : advance / true (full feature) | <input-type> | false (hide searchable)
 */
export default defineComponent({
  name: 'LvTable',
  emits: [
    'deleted', 
    'restored',
    'row-click',
    'row-dblclick',
    'row-contextmenu',
    'row-clicked',
  ],
  props: {
    emit: {
      type: Function,
      default: () => {},
    },
    config: { // table object structure, use : Handler.table() for standard structure
      type: Object,
      default: null,
    }, 
    height: {
      type: String,
      default: '83vh',
    },
    itemSize: {
      type: Number,
      default: 11,
    },
    urlPath: { // custom endpoint path, need for fetch, delete & restore
      type: String,
      default: null,
    },
    /* USAGE :
      String          : < :url-params="`?score=gte:2`" />
      Array <Object>  : < :url-params="[{ key: 'score', value: 'gte:2' }]" />
    */
    urlParams: { // custom parameter for url queries
      type: [String, Array],
      default: null,
    },
    /* USAGE :
      < url-path="http://customer.endpoint.com/mockup" raw-endpoint />
    */
    rawEndpoint: { // for fetching outside sources
      type: Boolean,
      default: false,
    },
    allowTrash: {
      type: Boolean,
      default: true,
    },
    allowRestore: {
      type: Boolean,
      default: true,
    },
    searchBy: { // fill with column name on table module, ex : code, remarks
      type: Array,
      default: () => { return []},
    },
    hideTop: {
      type: Boolean,
      default: false,
    },
    noShorthand: { // disable search with F1 button
      type: Boolean,
      default: false,
    },
    setting: { // setting table visibility
      type: Boolean,
      default: true,
    }

  },
  components: {
    LvThead,
  },
  setup (props, { emit }) {
    const { Config, Helper, Handler, Api } = useServices()
    const route = useRoute()
    const router = useRouter()

    const focusSearch = ref(1)
    const checkAll = ref(false)
    const showTable = ref(false)
    let table = ref({
      ...Handler.table()
    })

    onBeforeMount(() => {
      window.addEventListener('keydown', keyDownHandler)
    })

    onMounted(() => {
      if (props.config) {
        table.value = props.config
        if (props.searchBy) table.value.searchBy = props.searchBy
        showTable.value = true
      }
      onRefresh()
    })

    const actionBtn = computed(() => {
      var res = true
      if (table.value.isTrashed) res = props.allowRestore
      else res = props.allowTrash
      return res
    })

    const tableColumns = computed(() => {
      var res = []
      if (table.value && table.value.columns) {
        table.value.columns.map(r => {
          if (r.name === 'action' && table.value.action) res.push(r)
          else if (r.name !== 'action') res.push(r)
        })
      }
      return res
    })

    const tdList = computed(() => {
      var res = []
      if (table.value && table.value.columns) {
        table.value.columns.map(r => {
          if (r.name !== 'action') res.push(r)
        })
      }
      return res
    })

    const paramUrl = computed(() => {
      return props.urlParams
    })

    function onRefresh () {
      if (props.urlPath) {
        setTimeout(() => { // delay for wait if parent has change some props
          getDataByUrl(true)
        }, 100)
      }
    }

    // Utils
    function makeLabelSelected () {
      const data = table.value.data
      const selected = table.value.selected
      return selected.length === 0 ? '' : `${selected.length} record${selected.length > 1 ? 's' : ''} selected of ${data.length}`
    }

    function onScroll (e) {
      if (!props.urlPath) return table

      const idx = e.index + 1
      const td = table.value.data.length
      if (idx === td) {
        if (!table.value.pagination.isLast) {
          table.value.pagination.page = table.value.pagination.page + 1
          if (table.value.hasLoaded) {
            getDataByUrl()
          }
        }
      }
      return table
    }

    function makeOperator (operator) {
      if (!operator) operator = ''
      else if (operator === '=') operator = ''
      else if (operator === 'eq') operator = ''
      else if (operator) operator = operator + ':'
      return operator
    }

    function compileSearchQuery(table) {
      let searchList = []
      let endpoint = ''

      // generate query from columns
      table.columns.forEach(column => {
        const { searching, search, field } = column
        if (searching.show && searching.value && searching.value.trim()) {
          const operator = makeOperator(searching.operator)
          const operator2 = makeOperator(searching.operator2)

          const obj = {
            name: search || field,
            value: searching.value,
            operator: operator,
            value2: searching.value2,
            operator2: operator2,
          }

          searchList.push(obj)

          if (obj.value2 && obj.operator2) { // handle multi condition
            endpoint += `&${obj.name}=${obj.operator}${obj.value}|${obj.operator2}${obj.value2}`
          } else {
            endpoint += `&${obj.name}=${obj.operator}${obj.value}`
          }
        }
      })

      // generate query for global search
      if (table.searchBy && table.searchBy.length) {
        if (table.search) endpoint += `${endpoint}&_contains=${table.searchBy.join(',')}:${table.search}`
      }

      return {
        endpoint,
        list: searchList,
      }
    }

    function prepareFetch () {
      const path = props.urlPath
      table.value.loading = true
      table.value.hasLoaded = false
      const page = table.value.pagination.page
      const rowsPerPage = table.value.pagination.rowsPerPage
      const perpage = rowsPerPage === 0 ? table.value.pagination.rowsNumber : rowsPerPage

      table.value.pagination.perPage = perpage

      let endpoint = `${path}?_table=true`
      endpoint += `&_page=${page}`
      endpoint += `&_limit=${perpage}`
      const customParams = getUrlParams()
      if (customParams) endpoint += `&${customParams}`

      let search = compileSearchQuery(table.value)
      endpoint += search.endpoint

      if (table.value.sort) endpoint = endpoint + `&_order=${table.value.sort.name}:${table.value.sort.type}`
      if (table.value.isTrashed) endpoint = endpoint + '&_trash=true'

      table.value.endpoint = endpoint
    }

    function getDataByUrl (reset = false) {
      if (reset) {
        table.value.data = []
        table.value.selected = []
        table.value.pagination.page = 1
      }
      prepareFetch()
      Api.get(table.value.endpoint, (status, data, message, full) => {
        table.value.loading = false
        if (status === 200 && data) {
          setData(data, reset)
          
        }
      })
    }

    function setData (data, reformat = false) {
      if (reformat) {
        table.value.data = data.data
        table.value.pagination.rowsNumber = data.total || data.data.length
        table.value.pagination.page = 1
        table.value.pagination.from = 1
        table.value.pagination.to = 1
        table.value.pagination.lastPage = 1
        table.value.hasLoaded = true
      } else {

        if (data.data.length > 0 && data.to <= data.total) {

          table.value.data = [
            ...table.value.data,
            ...data.data,
          ]

          if (table.value.data.length === data.total) table.value.pagination.isLast = true
          else table.value.pagination.isLast = false

          table.value.pagination.rowsNumber = data.total
          table.value.pagination.page = data.current_page
          table.value.pagination.from = data.from ? data.from : 0
          table.value.pagination.to = data.to ? data.to : 0
          table.value.pagination.lastPage = data.to ? data.last_page : 0
          table.value.hasLoaded = true
        }
      }

      table.value.tmp = table.value
      table.value.dataTmp = table.value.data
      table.value.paginationTmp = table.value.pagination

      return table
    }

    function getUrlParams () {
      const urlParams = props.urlParams
      if (Array.isArray(urlParams)) {
        let result = '';
        urlParams.forEach(param => {
          result += `${param.key}=${param.value}&`;
        });
        return result;
      } else if (typeof urlParams === 'object') {
        let result = '';
        for (let key in urlParams) {
          if (urlParams.hasOwnProperty(key)) {
            result += `${key}=${urlParams[key]}&`;
          }
        }
        return result;
      } else if (typeof urlParams === 'string') {
        return `${urlParams}&`;
      } else {
        return null;
      }
    }

    function onSelectedAll (val) {
      var selected = []
      table.value.data.map(r =>{
        selected.push(r)
      })
      if (val) table.value.selected = selected
      else table.value.selected = []
    }

    function showAction (col) {
      var res = true
      if (col.name === 'action' && !table.value.action) {
        res = false
      }
      return res
    }

    function makeSortData (col) {
      var f = Helper.findObjectByKey(table.value.columns, 'name', col.name, true)
      if (col.sort) {
        table.value = Handler.tableReset(table.value) // reset default
        var sort = 'asc'
        if (col.sort === 'asc') sort = 'desc'
        if (f >= 0) table.value.columns[f].sort = sort
        // sorting data
        table.value.sort = {
          name: col.field,
          type: sort
        }
      }
    }

    function sortData (col) {
      makeSortData(col)
      onRefresh()
    }

    function tableShowSearch (col) {
      if (table.value.searchColumn) {
        var f = Helper.findObjectByKey(table.value.columns, 'name', col.name, true)
        if (f >= 0) {
          var column = table.value.columns[f]
          if (column.searchable && column.name !== 'action') table.value.columns[f].searching.show = !column.searching.show
        }
      }
    }

    function closeSearch (idx) {
      table.value.columns[idx].searching.show = false
      var search = table.value.columns[idx].searching.value
      if (search) onRefresh()
      table.value.columns[idx].searching.value = null
    }

    function searching () {
      var search = compileSearchQuery(table.value)
      table.value.endpoint = search.endpoint
      if (search.list.length > 0) {
        table.value = Handler.tableReset(table.value)
      }
      onRefresh()
    }

    function updateSetting (tableUpdated) {
      table.value = tableUpdated
      showTable.value = false
      setTimeout(() => {
        showTable.value = true
      }, 100);
      onRefresh()
    }

    function deleteRestoreSelected () {
      if (!table.value.isTrashed) emit('deleted', table.value.selected)
      if (table.value.isTrashed) emit('restored', table.value.selected)
    }

    function keyDownHandler(e) {
      var key = e.key
      if (key === 'F1' && !props.noShorthand) {
        e.preventDefault()

        // set focus searchbar
        setTimeout(() => {
          focusSearch.value?.$el?.firstElementChild?.firstElementChild?.firstElementChild?.firstElementChild?.focus()
          if (table.value.search) table.value.search = null
        }, 200)
      }

    }

    function rowClickHandler (evt, row, index, from) {
      // specific click
      if (from === 'click') emit('row-click', evt, row, index)
      if (from === 'dblclick') emit('row-dblclick', evt, row, index)
      if (from === 'contextmenu') emit('row-contextmenu', evt, row, index) // right click
      // all click
      emit('row-clicked', evt, row, index, from)
    }

    return {
      table,
      checkAll,
      showTable,
      focusSearch,
      //computed
      actionBtn,
      tableColumns,
      tdList,
      // methods
      onRefresh,
      makeLabelSelected,
      onScroll,
      getDataByUrl,
      onSelectedAll,
      showAction,
      sortData,
      tableShowSearch,
      closeSearch,
      searching,
      deleteRestoreSelected,
      updateSetting,
      rowClickHandler,
    }
  }
})
</script>
<style lang="scss" scoped>
@media screen and (max-width: 480px) {
  .table-data {
    .q-table__top {
      .input-search-bar {
        width: 100%
      }
    }
  }
}

</style>
