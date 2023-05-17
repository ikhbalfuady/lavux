
<template >
  <div :class="`${(fromModal) ? '' : 'q-pb-xs'} fix-content-modal row root`" style="min-width:75vw" v-if="Meta.permission.read">
  
  <!-- Header -->
  <lv-header-page class="bg-white" split showBack :backTo="{name: Meta.module}" :preventBackTo="fromModal ? true : false" >
    <template v-slot:left>
      <lv-breadcumb v-if="!fromModal" :title="Meta.name" :subtitle="$Handler.keyLabelDisplay(dataModel, Meta.keyLabel)"/>
    </template>

    <template v-slot:right >
      <lv-btn soft v-if="!loading" label="Action" icon="more_horiz" color="indigo" labelVisibility>
        <lv-action-box label="Actions" width="120px">
          <lv-action-item v-if="Meta.permission.update" label="Edit" icon="edit" color="green" 
            :to="action('form', dataModel, true)" @click="action('form', dataModel)"
          />
        </lv-action-box>
      </lv-btn>
      <lv-open-link v-if="fromModal" :meta="Meta" :data="dataModel" page="detail" />
    </template>
  </lv-header-page>

  <!-- Content -->
  <lv-loading v-if="loading" />
  <lv-container v-if="!loading" class="col-12" height="30vh">
    <template v-for="(props, key) in viewList" :key="key">
      <lv-view-item optimize-display :label="key" :display="dataModel[key]" />
    </template>
    <!-- <lv-view-item label="polos">dengan slot</lv-view-item> -->
  </lv-container>

  <q-dialog v-model="modal.show" @hide="modal.show = false" transition-show="jump-up" transition-hide="jump-down">
    <Form v-if="modal.target === 'form'" :data="modal.data" :onSubmit="onRefresh"/>
  </q-dialog>
  </div>
</template>

<script>
import { ref, reactive, computed, onBeforeMount, defineComponent } from 'vue'
import useServices from './../../composables/Services'
import { useRoute } from 'vue-router'
import Meta from './meta'
import Form from './form'

export default defineComponent({
  name: Meta.moduleName+'Detail',
  components: {
    Form,
  },
  props: {
    data: {
      type: Object,
      default: null
    },
    disableMeta: { // disable setPageMeta
      type: Boolean,
      default: false,
    }
  },
  setup(props) {
    const { Config, Handler, Helper, Api, GlobalStore, SetMetaPage } = useServices()
    const route = useRoute()

    // Properties
    const modal = reactive(Handler.modalConfig())
    const loading = ref(false)
    const dataModel = ref(Meta.model)
    const topMenu = [{name: 'Refresh',  event: onRefresh }]
    const viewList = ref([])

    /* LIFECYCLE : all processes that are executed in a certain lifecycle are defined here */
    onBeforeMount(() => {
      if (!Meta.permission.read) Handler._403()
      else {
        if (!fromModal.value) {
          Handler.topMenu(topMenu)
          if (!props.disableMeta) SetMetaPage(`Detail ${Meta.name}`)
        }
        onRefresh()
      } 
    })

    /* COMPUTED : all computed variables are defined here */
    const actionModal = computed(() => { return GlobalStore.getActionModal })
    const fromModal = computed(() => { return (props.data) ? props.data : null })

    const id = computed(() => {
      return parseInt((fromModal.value && fromModal.value.id) ? fromModal.value.id : Handler.urlParams(route, 'id'))
    })

    /* METHODS : all methods are defined here */
    function onRefresh() {
      if (id.value) {
        getData()
      }
    }

    function getData() {
      loading.value = true
      Api.get(`${Meta.module}/${id.value}`, (status, data, message, full) => {
        loading.value = false
        if (status === 200 && data) {
          dataModel.value = data
          viewList.value = Handler.viewList(Meta.model)
        }
      })
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

    return {
      Meta,
      loading,
      dataModel,
      viewList,
      modal,
      // computed
      fromModal,
      id,
      // methods
      onRefresh,
      getData,
      action,
    }
  },
})
</script>
