
<template >
  <div :class="`${(fromModal) ? '' : 'q-pb-xs'} fix-content-modal row root`" style="min-width:75vw">
  

  <q-form @submit="submit" class="col-12 row animated fadeIn" >
    
    <!-- Header -->
    <lv-header-page class="bg-white" split showBack :backTo="{name: Meta.module}" :preventBackTo="fromModal ? true : false" >
      <template v-slot:left>
        <lv-breadcumb v-if="!fromModal" :title="Meta.name" :subtitle="type"/>
      </template>
      <template v-slot:right>
        <lv-btn labelVisibility icon="reply" label="Cancel" ref="closeForm" @click="backToRoot()" v-close-popup/>
        <lv-btn v-if="!loading" labelVisibility icon="check" label="Save" color="primary" :disable="disableSubmit" type="submit"/>
      </template>
    </lv-header-page>

    <!-- Content -->
    <lv-loading v-if="loading" />
    <div v-if="!loading" class="q-pa-md row col-12">

      <div :class="`col-12 col-sm-4 q-px-sm`" style="height: 320px;">
        <q-card class="shadow">
          <q-card-section class="row ">
            <lv-select top-label label="Group" col="12" v-model="dataModel.role_group_id" 
              url="role-groups" limit="20" :default-data="dataModel.role_group"
            />
            <lv-input top-label label="name" col="12" v-model="dataModel.name" :rules="$Handler.rules(dataModel.name, 'required')"/>
            <lv-input top-label label="slug" col="12" v-model="dataModel.slug" :rules="$Handler.rules(dataModel.slug, 'required')"/>
          
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-8 q-px-sm" >
        <q-table v-if="id" class="shadow "
          style="height: 80vh"
          :rows="dataModel.detail"
          :columns="table.columns"
          row-key="id"
          :filter="table.filter"
          :loading="table.loading"
          virtual-scroll
          :virtual-scroll-item-size="48"
          :pagination="table.pagination"
          :rows-per-page-options="[0]"
        >
          <template v-slot:top>
            <div v-if="dataModel.id" class="col-12">
              <lv-input filled dense debounce="300" color="dark" v-model="table.filter" placeholder="Search permission..">
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </lv-input>
            </div>
          </template>

          <template v-slot:header="props">
            <q-tr :props="props">
              <q-th v-for="col in props.cols" :key="col.name" :props="props" class="bg-dark">
                <span class="text-bold text-h6 text-white">{{ col.label }} ({{totalAllPermissions(true)}})</span>
              </q-th>
            </q-tr>
          </template>

          <template v-slot:body-cell-name="props">
            <q-td :props="props" class="no-padding">
              <q-expansion-item style="width:100%" class="text-bold"
                :label="props.row.name"
                :caption="totalPermissions(props.row.permissions)"
              >
                <div>
                  <q-list bordered separator class="bg-grey-1">

                    <q-item dense >
                      <q-item-section >
                        <div class="row">
                          <lv-btn @click="batchPermission(props.row.permissions, true)" class="col-6" size="xs" icon="fact_check" color="primary" label="Give all permission"/>
                          <lv-btn @click="batchPermission(props.row.permissions, false)" v-if="totalPermissions(props.row.permissions, true) > 0" class="col-6" size="xs" icon="delete_sweep" color="red" label="Remove all permission"/>
                        </div>
                      </q-item-section>
                    </q-item>
                    <q-separator />

                    <q-item dense v-for="(row, i) in props.row.permissions" :key="i+'tgl'+props.row.name">
                      <q-item-section avatar>
                        <q-toggle v-model="row.allow" color="green"
                          unchecked-icon="clear" checked-icon="check"
                        />
                      </q-item-section>
                      <q-item-section :class="row.allow ? 'text-green-9' : 'text-red-9' ">{{row.name}}</q-item-section>
                    </q-item>
                  </q-list>
                </div>
              </q-expansion-item>
            </q-td>
          </template>
        </q-table>
        <div v-else class="text-center">
          <lv-banner color="indigo">
            <q-icon name="info_outline" style="font-size: 52px;"/>
            <div class="text-bold">For first time creating Role, you cannot define the permissions</div>
            <div >if you want to define the permissions, after saving this data you can define by edit the data.</div>
          </lv-banner>
        </div>
      </div>

    </div>

  </q-form>

  </div>
</template>

<script>
import { ref, reactive, computed, onBeforeMount, defineComponent } from 'vue'
import useServices from './../../composables/Services'
import { useRouter, useRoute } from 'vue-router'
import Meta from './meta'

export default defineComponent({
  name: Meta.moduleName+'Form',
  props: {
    data: {
      type: Object,
      default: null
    },
    onSubmit: {
      type: Function,
      default: null
    },
    disableMeta: { // disable setPageMeta
      type: Boolean,
      default: false,
    }
  },
  setup (props) {
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()
    const router = useRouter()
    const route = useRoute()

    // Properties
    const closeForm = ref(1) // refs component
    const loading = ref(false)
    const disableSubmit = ref(false)
    const dataModel = ref({...Meta.model})
    const topMenu = [{ name: 'Refresh', event: onRefresh }]
    const select = reactive({ // select sources

    })

    var table = ref({
      loading: false,
      filter: '',
      selected: [],
      columns: [
        { name: 'name', label: 'Permission', field: 'name', align: 'left',  sortable: true },
      ],
      pagination: {
        rowsPerPage: 0
      }
    })

    /* LIFECYCLE : all processes that are executed in a certain lifecycle are defined here */
    onBeforeMount(() => {
      if (!Meta.permission[type.value]) Handler._403()
      else {
        if (!fromModal.value) {
          Handler.topMenu(topMenu)
          if (!props.disableMeta) SetMetaPage(`${type.value} ${Meta.name}`)
        }
        onRefresh()
        resetModel()
      } 
    })

    /* COMPUTED : all computed variables are defined here */
    const fromModal = computed(() => { return (props.data)? props.data : null })
    const id = computed(() => { 
      return (fromModal.value && fromModal.value.id) ? fromModal.value.id : Handler.urlParams(route, 'id', true) 
    })
    const type = computed(() => { return (id.value) ? 'update' : 'create' })
    
    /* METHODS : all methods are defined here */
    function onRefresh () {
      if (id.value) getData()
    }
    
    function backToRoot (data) {
      if (!fromModal.value) {
        console.log('br', data)
        if (data) router.push({ name: `${Meta.module}-detail`, params: data })
        else router.push({ name: Meta.module })
      } else if (closeForm.value) closeForm.value.$el.click()

    }
    
    function resetModel () {
      Object.assign(dataModel.value, Meta.model)
    }

    function validateSubmit () {
      // if (!dataModel.id) { // example validation
      //   Helper.showAlert('Opps!', 'id is rquired!')
      //   return false
      // } else return true // must set true on ending return
      return true
    }

    function submit () {
      if (validateSubmit()) {
        disableSubmit.value = true
        if (dataModel.value.id !== null) update()
        else save()
      }
    } 

    const callback  = (status, data, message) => {
      Helper.loadingOverlay(false)
      callbackFunc(data)
      if (status === 200) {
        Helper.showSuccess('Succesfully', message)
        backToRoot(data)
      } else disableSubmit.value = false
    }

    function save () {
      Helper.loadingOverlay(true, 'Saving..')
      Api.post(Meta.module, dataModel.value, callback)
    }

    function update () {
      Helper.loadingOverlay(true, 'Updating..')
      Api.put(`${Meta.module}/${dataModel.value.id}`, dataModel.value, callback)
    }

    function getData () {
      loading.value = true
      Api.get(`${Meta.module}/${id.value}`, (status, data, message, full) => {
        loading.value = false
        if (status === 200 && data) {
          dataModel.value = data
        }
      })
    }

    function callbackFunc (data) {
      if (props.onSubmit) props.onSubmit(data)
    }

    function totalAllPermissions (valueOnly = false) {
      var res = 0
      if (dataModel.value.detail) {
        dataModel.value.detail.map(r => {
          r.permissions.map(p => {
              res += p.allow ? 1 : 0
          })
        })
      }
      return  valueOnly ? res : res + ' permission active'
    }

    function totalPermissions (data, valueOnly = false) {
      var res = 0
      data.map(r => {
        res += r.allow ? 1 : 0
      })
      return  valueOnly ? res : res + ' permission active'
    }

    function batchPermission (data, allow = true) {
      data.map(r => {
        r.allow = allow
      })
      return data
    }


    return {
      Meta,
      table,
      closeForm,
      loading,
      disableSubmit,
      dataModel,
      select,
      // computed
      type,
      fromModal,
      id,
      // methods
      onRefresh,
      getData,
      submit,
      backToRoot,
      totalAllPermissions,
      totalPermissions,
      batchPermission,
    }
  }
})
</script>
<style scoped>
.table-data .q-table__top {
  padding: 3px 10px;
  background-color: #fff !important;
}
</style>
