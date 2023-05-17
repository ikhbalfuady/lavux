
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
    <lv-container v-if="!loading" class="col-12" height="30vh">

      <lv-input col="4" label="Module" v-model="dataModel.module" />
      <lv-input col="4" label="Name" v-model="dataModel.name" />

    </lv-container>

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

    return {
      Meta,
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
    }
  }
})
</script>
