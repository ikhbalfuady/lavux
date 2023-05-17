
<template >
<div class=" row root" style="min-width:75vw">

  <lv-loading class="col-12" v-if="loading" />
  <q-form @submit="submit" v-if="!loading" class="col-12 row animated fadeIn" >
    <lv-header-page class="bg-white" split>
      <template v-slot:left>
        <span class="page-title animated zoomIn">Application Settings</span>
      </template>
      <template v-slot:right >
      <lv-btn soft label="Meta" icon="folder_special" color="indigo" labelVisibility  :to="{ name: 'metas' }"/>
    </template>
    </lv-header-page>

    <div class="ph-1 col-12 q-pa-md">
      <lv-banner color="blue" dense>
        This form is auto save, you can change any setting without click button save.
      </lv-banner>
    </div>

    <div class="pv-1 ph-1 col-12">

      <q-card class="shadow scroll" >
        <q-tabs
          v-model="tab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
        <template v-for="(stg, i) in list" :key="i+'stab'">
          <q-tab :name="stg.name" :label="stg.name" />
        </template>
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="tab" animated>
          <template v-for="(stg, i) in list" :key="i+'span'">
            <q-tab-panel :name="stg.name" class="row">

              <div class="col-12 q-px-lg q-py-sm q-mb-lg bg-yellow-2 round quote">
                <span class="">{{stg.comment ?? ''}}</span>
              </div>

              <template v-for="(val, attr) in stg.list" :key="i+'sitem'+attr">

                <lv-input class="q-mb-md" v-if="val.raw.type === 'string' || val.raw.type === 'NULL'" @input="updateSetting(val.raw.id, val.value, stg.name, attr)" col="12" :label="$Helper.toLabel(attr)" v-model="val.value" />
                <lv-toggle class="q-mb-md" v-if="val.raw.type === 'boolean'" @input="updateSetting(val.raw.id, val.value, stg.name, attr)" col="6" :label="$Helper.toLabel(attr)" v-model="val.value" />
                <lv-input class="q-mb-md" v-if="val.raw.type === 'integer'" @input="updateSetting(val.raw.id, val.value, stg.name, attr)" mode="number" col="3" :label="$Helper.toLabel(attr)" v-model="val.value" />
                <lv-input class="q-mb-md" v-if="val.raw.type === 'double'" @input="updateSetting(val.raw.id, val.value, stg.name, attr)" mode="currency" col="3" :label="$Helper.toLabel(attr)" v-model="val.value" />
                <lv-input class="q-mb-md" v-if="val.raw.type === 'date'" @input="updateSetting(val.raw.id, val.value, stg.name, attr)" mode="date" col="3" :label="$Helper.toLabel(attr)" v-model="val.value" />
                <lv-textarea class="q-mb-md" v-if="val.raw.type === 'array'" @input="updateSetting(val.raw.id, val.raw_value, stg.name, attr)" col="12" :label="$Helper.toLabel(attr)" v-model="val.raw_value" />

              </template>
            </q-tab-panel>
          </template>
        </q-tab-panels>

      </q-card>
    </div>
  </q-form>

  </div>
</template>

<script>
import { ref, reactive, computed, onBeforeMount, defineComponent } from 'vue'
import useServices from './../composables/Services'
import { useRoute } from 'vue-router'
import { debounce } from 'quasar'

export default defineComponent({
  name: 'Settings',
  props: {
    data: {
      type: Object,
      default: null
    }
  },
  setup(props) {
    const { Config, Handler, Helper, Api, Store, SetMetaPage } = useServices()
    const route = useRoute()

    // Properties
    var splitterModel = ref(20)
    var loading = ref(false)
    var disableSubmit = ref(false)
    var topMenu = [{name: 'Refresh',  event: onRefresh }]
    var list = ref(null)
    var tab = ref('company')
    var Meta = {
      name: 'Settings',
      module: 'settings',
    }

    /* CONSTRUCTORS : the process of injecting into any variable do it here so that it is grouped neatly */
    updateSetting = debounce(updateSetting, 1000)

    /* LIFECYCLE : all processes that are executed in a certain lifecycle are defined here */
    onBeforeMount(() => {
      Handler.topMenu(topMenu)
      SetMetaPage(`Application Settings`)
      onRefresh()
    })

    /* COMPUTED : all computed variables are defined here */
 
    /* METHODS : all methods are defined here */
    function onRefresh() {
      getData()
    }

    function getData() {
      loading.value = true
      Api.get('app/settings', (status, data, message, full) => {
        loading.value = false
        if (status === 200 && data) {
          list.value = data
        }
      })
    }

    function submit () {

    }

    function updateSetting (id, value, module, config) {
      console.log('updateSetting', id, value)
      var send = {
        id: id,
        value: value,
        module: module,
        config: config,
      }
      Api.put('app/settings', send, (status, data, message, full) => {
        loading.value = false
        if (status === 200 && data) {
          var attr = Helper.toLabel(config)
          Helper.showToast(`Setting "${attr}" updated.`)
        }
      })
    }

    return {
      Meta,
      loading,
      list,
      disableSubmit,
      tab,
      splitterModel,
      // computed
      // methods
      onRefresh,
      getData,
      submit,
      updateSetting,
    }
  },
})
</script>
