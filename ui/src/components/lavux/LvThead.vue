<template v-if="column">
  <div>
    <template v-if="column.searchable === true || column.searchable === 'advance' ">
      <q-icon  name="filter_list" :class="`${column.searching.show ? 'text-primary column-filtered' : ''} link`" >
        <q-popup-proxy>
          <div class="q-pa-md row filter-thead bg-white" style="width: 360px;">
            <div class="text-primary text-bold col-12 q-pb-sm">
              Filter ({{ column.label }})
            </div>
            <lv-input :debounce="400" :mode="search_type" class="q-mb-sm" label="Search..." v-model="search" @input="emiters()">
              <template v-slot:after>
                <q-btn color="primary" class="bg-grey-2" flat unelevated :icon="getIconType(search_type)">
                  <q-menu transition-show="jump-down" transition-hide="jump-up"  >
                    <q-list style="min-width: 100px">
                      <q-item clickable @click="updateColumnType('text')">
                        <q-item-section>Text</q-item-section>
                      </q-item>
                      <q-item clickable @click="updateColumnType('date')">
                        <q-item-section>Date</q-item-section>
                      </q-item>
                      <q-item clickable @click="updateColumnType('datetime')">
                        <q-item-section>Date Time</q-item-section>
                      </q-item>
                      <q-separator />
                      <q-item clickable @click="updateColumnType('number')">
                        <q-item-section>Number</q-item-section>
                      </q-item>
                      <q-item clickable @click="updateColumnType('currency')">
                        <q-item-section>Currency</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                  <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">{{ search_type }}</q-tooltip>
                </q-btn>
              </template>
            </lv-input>
            <lv-select class="q-mb-sm" label="Operator" v-model="operator" :options="select.operator" @input="emiters()"/>

            <lv-toggle flat label="Multi Condition" v-model="multiCondition" />

            <lv-input v-if="multiCondition" :debounce="400" :mode="search2_type" class="q-my-sm" label="Search secondary..." v-model="search2" @input="emiters()">
              <template v-slot:after>
                <q-btn color="primary" class="bg-grey-2" flat unelevated :icon="getIconType(search2_type)">
                  <q-menu transition-show="jump-down" transition-hide="jump-up"  >
                    <q-list style="min-width: 100px">
                      <q-item clickable @click="updateColumnType('text', 2)">
                        <q-item-section>Text</q-item-section>
                      </q-item>
                      <q-item clickable @click="updateColumnType('date', 2)">
                        <q-item-section>Date</q-item-section>
                      </q-item>
                      <q-item clickable @click="updateColumnType('datetime', 2)">
                        <q-item-section>Date Time</q-item-section>
                      </q-item>
                      <q-separator />
                      <q-item clickable @click="updateColumnType('number', 2)">
                        <q-item-section>Number</q-item-section>
                      </q-item>
                      <q-item clickable @click="updateColumnType('currency', 2)">
                        <q-item-section>Currency</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                  <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">{{ search2_type }}</q-tooltip>
                </q-btn>
              </template>
            </lv-input>
            <lv-select v-if="multiCondition" class="q-mb-sm" label="Operator 2nd" v-model="operator2" :options="select.operator" @input="emiters()"/>

            <div class="col-12 text-right row">
              <lv-toggle flat class="col-5" label="Auto fetching" v-model="autoFetch">
                <q-tooltip>Will searching automated after typing or change some configuration</q-tooltip>
              </lv-toggle>

              <lv-btn class="q-mr-md" label="Reset" size="md"  @click="reset" />
              <lv-btn class="" label="Save & Search" size="md" color="primary" @click="emitFilter" />
            </div>

          </div>
        </q-popup-proxy>
        <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">
          Filter by {{ label }}
        </q-tooltip>
      </q-icon> 
      &nbsp; {{ label }}
    </template>

    <template v-else-if="column.searchable">
      <q-input v-if="column.searching.show" dense outlined :debounce="400" v-model="search" class="search-table-head animated fadeIn"
        :placeholder="`Search by ${label}...`" :type="column.searchable"
        @update:model-value="emitSearch">
        <template v-slot:append>
          &nbsp;
          <q-icon name="close" class="cursor-pointer" @click="closeSearch(table.columns.indexOf(column))" />
        </template>
      </q-input>
      <template v-else>
        <q-icon name="search" class="link" @click="showSearch(table.columns.indexOf(column))">
          <q-tooltip > Search by {{ label }} </q-tooltip>
        </q-icon>
        &nbsp; {{ label }}
      </template>
    </template>

    <template v-else>
      &nbsp; {{ label }}
    </template>

    <template v-if="column.sort && !column.searching.show"  >
      <template v-if="table.sortColumn" >
        <q-icon v-if="column.sort === 'asc'" name="arrow_upward" class="link hover-primary" @click="sortData(column)">
          <q-tooltip>Click to sort: {{column.sort == 'asc' ? 'desc' : 'asc'}}</q-tooltip>
        </q-icon>
        <q-icon v-if="column.sort === 'desc'" name="arrow_downward" class="link hover-primary" @click="sortData(column)">
          <q-tooltip>Click to sort: {{column.sort == 'asc' ? 'desc' : 'asc'}}</q-tooltip>
        </q-icon>
      </template>
    </template>
  </div>

</template>

<script>
import { ref, onMounted, reactive, computed, defineComponent } from 'vue'
import useServices from './../../composables/Services'
import { colors } from 'quasar'

export default defineComponent({
  name: 'LvThead',
  props: {
    label: {
      type: String,
      default: '-',
    },
    column: {
      type: Object,
      default: null,
    },
    table: {
      type: Object,
      default: null,
    },
  },
  setup (props, { emit }) {
    const { Config, Helper, Handler } = useServices()

    let autoFetch = ref(false)
    let multiCondition = ref(false)
    let search_type = ref('text')
    let search2_type = ref('text')
    let search = ref(null)
    let search2 = ref(null)
    let operator = ref('=')
    let operator2 = ref('=')
    let select = reactive({
      operator: [
        { id: '=', name: 'Equal (=)' },
        { id: 'not', name: 'Not Equal (!=)' },
        { id: 'like', name: 'Contain (like)' },
        { id: 'gt', name: 'Greater Than (>)' },
        { id: 'gte', name: 'Greater Than Equal (>=)' },
        { id: 'lt', name: 'Less Than (<)' },
        { id: 'lte', name: 'Less Than Equal (<=)' },
        { id: 'start', name: 'Start [Date] (>=)' },
        { id: 'end', name: 'End [Date] (<=)' },
        { id: 'is_null', name: 'Empty (IS NULL)' },
        { id: 'is_null', name: 'Not Empty (IS NOT NULL)' },
      ]
    })

    onMounted(() => {
      operator.value = props.column?.searching?.operator || 'like'
      // console.log(props.menu)
    })

    function setUpData () {
      let col = props.column
      col.searching.value = '' + search.value // convert to string, to fix searchable in lv-table
      col.searching.operator = operator.value
      if (multiCondition.value) {
        if (search2.value) col.searching.value2 = '' + search2.value // convert to string, to fix searchable in lv-table
        if (operator2.value) col.searching.operator2 = operator2.value
      } else {
        col.searching.value2 = null
        col.searching.operator2 = null
      }
      if (col.searching.value) col.searching.show = true
      return col 
    }

    function emiters () {
      const col = setUpData()
      
      emit('update', col)
      if (autoFetch.value) emit('filter', col)
    }

    function emitFilter () {
      const col = setUpData()

      emit('update', col)
      emit('filter', col)
    }

    function emitSearch () {
      const col = setUpData()
      // col.searching.operator = 'like'
      
      emit('update', col)
      emit('filter', col)
    }

    function reset () {
      let col = props.column
      col.searching.value = null
      col.searching.operator = 'like'
      col.searching.value2 = null
      col.searching.operator2 = null
      col.searching.show = false
      emit('update', col)
      emit('filter', col)
    }

    function sortData (col) {
      emit('sorted', col)
    }

    function getIconType (type) {
      let res = 'sort_by_alpha'
      if (type === 'number') res = 'pin'
      if (type === 'currency') res = 'money'
      if (type === 'date') res = 'event_note'
      if (type === 'datetime') res = 'date_range'
      return res
    }

    function updateColumnType (val, target = 1) {
      console.log('update', val)
      if (target === 2) {
        search.value = null
        search2_type.value = val
              console.log('search', search.value )
      }
      else {
        search2.value = null
        search_type.value = val
              console.log('search2', search2.value )
      }
      
    }
    
    function showSearch () {
      const col = setUpData()
      col.searching.value = null
      col.searching.show = true
      emit('update', col)
    }

    function closeSearch (index) {
      const col = setUpData()
      col.searching.value = null
      col.searching.show = false

      emit('update', col)
      if (search.value) {
        search.value = null
        emit('filter', col)
      }
        
    }

    return {
      search,
      search2,
      operator,
      operator2,
      autoFetch,
      multiCondition,
      select,
      search_type,
      search2_type,
      // methods
      emiters,
      emitFilter,
      emitSearch,
      showSearch,
      closeSearch,
      reset,
      sortData,
      getIconType,
      updateColumnType,
    }
  }
})
</script>