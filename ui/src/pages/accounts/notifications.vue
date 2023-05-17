
<template >
  <div class="row bg-white">

    <!-- Header -->
    <lv-header-page class="bg-white" split>
      <template v-slot:left>
        <div class="col-12 col-sm-6 col-md-4 page-title animated zoomIn q-pt-xs">Notifications</div>
        <lv-toggle flat class="col-sm-6" v-model="unRead" label="Unread" @input="onChangeUnread"/>
        <div class="col-12 text-indigo-9"><small>*Double click row for see detail notice</small></div>
      </template>
      <template v-slot:right>
        <template v-if="table.selected.length && unRead">
          <lv-btn soft icon="check" :label="`Mark read selected (${table.selected.length})`" labelVisibility @click="makeRead" />
        </template>
        <template v-else>
          <lv-btn color="primary" icon="send" label="Make Notification" labelVisibility  @click="modal.show = !modal.show"/>
        </template>
      </template>
    </lv-header-page>

    <!-- List -->
    <lv-table class="col-12 " ref="refTable" :config="table"
      :url-path="`me/notification-list`" height="83vh" hideTop
      :url-params="urlParams"
      :allowRestore="false"
      @row-dblclick="viewDetail"
    >

      <template v-slot:col-creator="props">
        <lv-user :username="props.value"/>
      </template>

      <template v-slot:col-content="props">
        <div :style="`${props.col.width ? `width:${props.col.width};` : ''}`">
          <div class="ellipsis">{{props.value}}</div>  
        </div>
      </template>

    </lv-table>

    <q-dialog v-model="modal.show" @hide="modal.show = false" persistent>
      <q-card>
        <q-bar class="bg-primary text-white">
          <q-icon name="error_outline" />
          <div class="text-bold">Send Notification</div>
          <q-space />
          <q-btn dense flat icon="close" v-close-popup>
            <q-tooltip>Close</q-tooltip>
          </q-btn>
        </q-bar>

        <q-card-section no-padding >
          <SendNotification class="bg-white col-12"/>
        </q-card-section>

      </q-card>
    </q-dialog>

    <q-dialog v-model="modalDetail.show" @hide="modalDetail.show = false" persistent>
      <q-card>
        <q-bar class="bg-primary text-white">
          <q-icon name="error_outline" />
          <div class="text-bold">Notification Detail</div>
          <q-space />
          <q-btn dense flat icon="close" v-close-popup>
            <q-tooltip>Close</q-tooltip>
          </q-btn>
        </q-bar>

        <q-card-section class="q-pa-sm" v-if="modalDetail.data">
          <div class="col-12 text-bold q-pb-sm">{{ modalDetail.data.title }}</div>
          <div class="col-12 q-pb-sm">
            <lv-text :data="modalDetail.data.content" />
          </div>

          <div class="col-12 text-bold q-pb-sm">
            <q-chip size="sm" square text-color="white" 
            :color="`${modalDetail.data.type === 'direct' ? 'teal' : 'orange-8'}`"
            :icon="`${modalDetail.data.type === 'direct' ? 'send' : 'error'}`"
            >
              {{ modalDetail.data.type }}
            </q-chip>
            <q-chip v-if="modalDetail.data.category " size="sm" square color="primary" text-color="white" >
              {{ modalDetail.data.category }}
            </q-chip>
          </div>

          <div class="col-12 q-pb-sm text-caption">
            <q-icon name="event"/>
            <lv-displayer :data="modalDetail.data.date"/>

            | @{{ modalDetail.data.creator }}
          </div>
          
        </q-card-section>

      </q-card>
    </q-dialog>
  
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, defineComponent } from 'vue'
import useServices from './../../composables/Services'
import SendNotification from './send-notification.vue'

export default defineComponent({
  name: 'Notifications',
  components: {
    SendNotification,
  },
  props: {
 //
  },
  setup () {
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()

    const refTable = ref(1)
    const loading = ref(false)
    const unRead = ref(true)
    const urlParams = ref([{ key: 'unread', value: 'true' }])
    const modal = reactive(Handler.modalConfig())
    const modalDetail = reactive(Handler.modalConfig())

    const txt = 'Nama saya jordan \n aliasn mansj'

    let table = reactive({
      ...Handler.table([
        { name: 'date', label: 'date', field: 'date', search: 'date', align: 'left', width: '180px', searchable: 'date', format: val => val ? Helper.beautyDate(val, ' ', true) : ''},
        { name: 'creator', label: 'From', field: 'creator', search: 'creator', align: 'center', searchable: 'simple', width: '180px', searching: { operator: '=' } },
        { name: 'title', label: 'title', field: 'title', search: 'title', align: 'left', width: '180px', searchable: 'simple'},
        { name: 'content', label: 'content', field: 'content', search: 'content', align: 'left', width: '320px', searchable: 'simple'},
        { name: 'type', label: 'type', field: 'type', search: 'type', align: 'center', searchable: 'simple', width: '180px',},
        { name: 'category', label: 'category', field: 'category', search: 'category', align: 'center', searchable: 'simple'},
        { name: 'link', label: 'link', field: 'link', search: 'link', align: 'left', searchable: 'simple'},
      ]), // init default columns
      action: true
    })

    onMounted(() => {
      SetMetaPage('Notifications')
      Handler.topMenu([
        { name: 'Refresh', event: onRefresh },
      ])
      onChangeUnread()
    })

    function onRefresh () {
      if (refTable.value.onRefresh) refTable.value.onRefresh()
    }

    function onChangeUnread () {
      if (unRead.value) urlParams.value = [{ key: 'unread', value: 'true' }]
      else urlParams.value =  [{ key: 'unread', value: 'false' }]
      // setTimeout(() => {
        onRefresh()
      // }, 200)
    }

    function makeRead () {
      const notif = table.selected.map(r => {
        return r.id
      })

      const send = {
        notif
      }

      Api.put(`me/read-notifications`, send, (status, data, message, full) => {
        if (status === 200 && data) {
          Helper.showToast(`${notif.length} has set read`)
          onRefresh()
        }
      })
    }

    function viewDetail (e, data) {
      modalDetail.show = true
      modalDetail.data = data
      console.log(data)
    }

    return {
      txt,
      loading,
      table,
      unRead,
      modal,
      modalDetail,
      refTable,
      urlParams,
      onRefresh,
      onChangeUnread,
      makeRead,
      viewDetail,
    }

  }
})
</script>
