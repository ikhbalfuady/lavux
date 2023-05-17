<template >
  <div class="row bg-white" v-if="Meta.permission.browse">

    <!-- Header -->
    <lv-header-page v-if="controls.title" class="bg-white" split>
      <template v-slot:left>
        <span class="page-title animated zoomIn">{{Meta.name}}</span>
      </template>
      <template v-if="controls.action" v-slot:right>
        <lv-btn v-if="Meta.permission.create" soft icon="add" label="Add New" labelVisibility
          :to="action('form', Meta.model, true)" @click="action('form', Meta.model)" 
        />
      </template>
    </lv-header-page>

    <!-- List -->
    <lv-table class="col-12 " ref="refTable" :config="table" :hideTop="hideTop"
      :url-path="Meta.module" :height="height"
      :url-params="`_with=${Meta.withRelation.join(',')}`"
      :searchBy="Meta.searchBy"
      @deleted="onDeleted"
      @restored="onRestored"
      @row-clicked="onRowClicked"
      :noShorthand="noShorthand"
      :setting="setting"
      :allowTrash="Meta.permission.delete"
      :allowRestore="Meta.permission.restore"
    >

      <template v-slot:col-action="props">
        <lv-btn soft icon="more_horiz" color="primary">
          <lv-action-box label="Actions" width="120px">
            <lv-action-item v-if="Meta.permission.read" label="View" icon="visibility" color="blue" 
              :to="action('detail', props.row, true)" @click="action('detail', props.row)"
            />
            <lv-action-item v-if="Meta.permission.update" label="Edit" icon="edit" color="green"
              :to="action('form', props.row, true)" @click="action('form', props.row)"
            />
          </lv-action-box>
        </lv-btn>
      </template>

      <template v-slot:col-name="props">
        <q-avatar size="32px" class="bg-primary q-mr-sm" >
          <q-img v-if="props.row.picture" :src="props.row.picture" fit="cover" >
            <template v-slot:error>
              {{ $Helper.getFirstChar(props.row.username) }}
            </template>
          </q-img>
          <span v-else>{{ $Helper.getFirstChar(props.row.username) }}</span>
        </q-avatar>
        
        <span class="text-bold">{{ props.value }}</span>
        <q-badge v-if="$Config.userId() === props.row.id" class="bg-orange-1 q-ml-sm text-orange-9"><small>It's me</small></q-badge>
      </template>

      <template v-slot:col-log_data="props">
        <lv-log-data :data="props.value"/>
      </template>

    </lv-table>

    <q-dialog v-model="modal.show" @hide="modal.show = false" transition-show="jump-up" transition-hide="jump-down">
      <Form v-if="modal.target === 'form'" :data="modal.data" :onSubmit="onSubmitForm"/>
      <Detail v-if="modal.target === 'detail'" :data="modal.data"/>
    </q-dialog>
  </div>
</template>

<script>
import { ref, reactive, computed, watchEffect, onBeforeMount, defineComponent } from 'vue'
import { useRoute } from 'vue-router'
import useServices from './../../composables/Services'
import Meta from './meta'
import Form from './form'
import Detail from './detail'

export default defineComponent({
  name: Meta.moduleName,
  components: {
    Form,
    Detail
  },
  emits: [
    'row-clicked',
  ],
  props: {
    height: {
      type: String,
      default: '84.5vh',
    },
    hideTitle: {
      type: Boolean,
      default: false
    },
    hideAction: {
      type: Boolean,
      default: false
    },
    hideTop: {
      type: Boolean,
      default: false
    },
    noShorthand: { // disable search with F1 button
      type: Boolean,
      default: false
    },
    setting: { // disable column setting
      type: Boolean,
      default: true,
    },
    disableMeta: { // disable setPageMeta
      type: Boolean,
      default: false,
    }
  },
  setup (props, { emit }) {
    const { Config, Handler, Helper, SetMetaPage, Api, GlobalStore} = useServices()
    const route = useRoute()

    // Properties
    const refTable = ref(1)
    const modal = reactive(Handler.modalConfig())
    const controls = reactive({
      title: true,
      action: true,
    })
    const selectedAction = reactive({
      isDelete: false,
      data: [],
    })
    let topMenu = [
      { name: 'Refresh', event: onRefresh },
      { name: 'Tools', children:[
        {name: 'Export'},
        {name: 'Download'},
      ] },
    ]
    let table = reactive({
      ...Handler.table(Meta.columns, `${Meta.module}-columns`), // init default columns
      action: true
    })

    /* LIFECYCLE : all processes that are executed in a certain lifecycle are defined here */
    onBeforeMount(() => {
      if (!Meta.permission.browse) Handler._403()
      else{
        Handler.topMenu(topMenu)
        handleControls()
        if (!props.disableMeta) SetMetaPage(Meta.name)
      }
    })

    watchEffect(() => { // handle reactive for declared element
      handleControls()
    })

    /* COMPUTED : all computed variables are defined here */
    const actionModal = computed(() => { return GlobalStore.getActionModal })
    
    /* METHODS : all methods are defined here */
    function onRefresh () {
      console.log(refTable.value)
      if (refTable.value.onRefresh) refTable.value.onRefresh()
    }

    function action (target = 'form', data, returnLink = false) {
      const params = data.id ? { id: data.id } : null
      if (returnLink) return actionModal.value ? null : Handler.actionLinkPage(Meta, target, params, data)
      else {
        modal.target = target
        modal.data = data
        modal.show = true
      }
    }

    function handleControls () {
      controls.title = !props.hideTitle
      controls.action = !props.hideAction
      
      if (props.hideTop) controls.title = false
      else controls.title = true

      table.action = controls.action
    }

    function onDeleted (data) {
      selectedAction.isDelete = true
      selectedAction.data = data
      let hold = 0
      data.map(r => {
        if (Config.userId() === r.id) hold += 1
      })

      if (!hold) deleteRestoreHandler()
      else {
        Helper.showAlert('Opps!', 'You cannot delete your self data!')
      }
    }

    function onRestored (data) {
      selectedAction.isDelete = false
      selectedAction.data = data
      deleteRestoreHandler()
    }

    function deleteRestoreHandler () {
      const totalData = selectedAction.data.length
      const type = selectedAction.isDelete ? 'Delete' : 'Restore'
      const title = `${type} ${totalData} data selected`
      const message = `Are you sure want to ${title}?`
      Helper.showAlert(title, message, false, type, deleteRestoreSelected)
    }
 
    function deleteRestoreSelected () {
      const mode = selectedAction.isDelete ? 'delete' : 'restore'
      Helper.loadingOverlay(true, mode === 'delete' ? 'Deleting...' : 'Restoring...' )
      const data = { id: [] }
      selectedAction.data.map(r => {
        data.id.push(r.id)
      })
      
      Api.put(`${Meta.module}/${mode}`, data, (status, data, message, response) => {
        Helper.loadingOverlay(false)
        if (status === 200) {
          Helper.showToast(`${mode === 'delete' ? 'Delete' : 'Restore'} Successfully`, 5000, 'top-right', message, 'indigo')
          onRefresh()
        }
      })
    }

    function onRowClicked (evt, row, index, from) {
      emit('row-clicked', evt, row, index, from)
    }

    function onSubmitForm (data) {
      onRefresh()
    }
 
    return {
      Meta,
      modal,
      controls,
      table,
      refTable,
      // computed
      // methods
      action,
      onDeleted,
      onRestored,
      onRowClicked,
      onSubmitForm,
    }
  }
})
</script>
