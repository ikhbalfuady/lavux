<template>
  <q-card style="width: 350px">
    <q-card-actions >
      <div class="text-bold text-grey-7 col-6 q-pl-sm">Notifications ({{ summary.total_unread }})</div>
      <div class="col-6 text-right" >
        <q-btn size="sm" flat dense label="View All" class="bg-grey-2 text-capitalize" :to="{ name: 'notifications'}"/>
      </div>
    </q-card-actions>
    <q-tabs v-model="tab" dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="justify"
      narrow-indicator
    >
      <template v-for="(key, index) in Object.keys(list)" :key="index">
        <q-tab :name="key" :label="key" class="text-capitalize">
          <q-badge v-if="summary[`total_unread_${key}`]" color="red" floating>{{ summary[`total_unread_${key}`] }}</q-badge>
        </q-tab>
      </template>
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="tab" animated>
      <template v-for="(key, index) in Object.keys(list)" :key="index">
        <q-tab-panel :name="key" class="no-padding">
          <template v-for="(notif, i) in list[key]" :key="i+'sys'">
            <q-item clickable v-ripple :href="makeLink(notif)" target="_blank" @click="setRead(notif.id)">
              <q-item-section avatar>
                <q-avatar color="primary">
                  <q-img v-if="notif.creator_pic" :src="notif.creator_pic" fit="cover" >
                    <template v-slot:error>
                      <div class="absolute-full flex flex-center bg-primary text-white" >
                        <small style="font-size: 13px;">Cannot load image</small>
                      </div>
                    </template>
                  </q-img>
                  <b v-else class="text-white">{{ $Helper.getFirstChar(notif.creator) }}</b>
                </q-avatar>
              </q-item-section>

              <q-item-section>
                <q-item-label lines="2">{{ notif.title }}</q-item-label>
                <q-item-label  v-if="notif.category" lines="2">
                  <q-badge>{{ notif.category }}</q-badge>
                </q-item-label>
                <q-item-label caption lines="2">
                  {{ notif.content }}
                </q-item-label>
                <q-item-label caption >
                  <lv-user :username="notif.creator"/>
                  <span class="text-dark"> - {{ $Helper.beautyDate(notif.date, ' ', true) }}</span>
                </q-item-label>
              </q-item-section>
  
              <q-item-section side top>
                <small><i>{{ notif.time_ago}}</i></small>
              </q-item-section>
            </q-item>
            <q-separator inset="item" />
          </template>

          <lv-loading v-if="loading" noPadding label="..." size="3em"/>
         
          <q-item v-if="!list[key].length && !loading" clickable v-ripple>
            <q-item-section>
              <q-item-label lines="1">There's nothing</q-item-label>
              <q-item-label caption lines="2">
                There's no notification for you right now
              </q-item-label>
            </q-item-section>
          </q-item>

          <q-item v-if="summary[`total_unread_${key}`] > list[key].length" clickable v-ripple dense>
            <q-item-section>
              <q-item-label lines="1" align="center" class="text-grey-7">View All</q-item-label>
            </q-item-section>
          </q-item>

          </q-tab-panel>
      </template>
    </q-tab-panels>
  </q-card>

</template>

<script>
import { ref, reactive, onMounted, computed, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'NotificationList',
  setup (props, { emit }) {
    const { Config, Helper, Handler, Api } = useServices()
    const route = useRoute()
    const router = useRouter()

    const tab = ref('system')
    const loading = ref(false)
    const list = reactive({
      system: [],
      direct: [],
    })
    const summary = reactive({
      total: 0,
      total_unread: 0,
      total_unread_system: 0,
      total_unread_direct: 0,
    })

    onMounted(() => {
      getData()
    })

    function getData () {
      loading.value = true
      Api.get(`me/notifications?unread&grouping&_limit=8&max_group=4`, (status, data, message, full) => {
        loading.value = false
        if (status === 200 && data) {
          list.system = data.system
          list.direct = data.direct
          summary.total = data.total
          summary.total_unread = data.total_unread
          summary.total_unread_system = data.total_unread_system
          summary.total_unread_direct = data.total_unread_direct

          const cb = Helper.unReactive({ summary, list }) 
          emit('loaded', cb )
        }
      })
    }

    function makeLink (notif) {
      if (notif.link_source === 'external') return notif.link_url
      else if (notif.link_source === 'frontend') return notif.link_url
      else if (notif.link_source === 'api') return `${Config.getApiRoot()}${notif.link_url}`
      else return null
    }

    function setRead (id) {
      let send = []
      send.push(id)
      Api.put(`me/read-notifications`, send, (status, data, message, full) => {
        if (status === 200 && data) {
          //
        }
      })
    }

    return {
      tab,
      loading,
      list,
      summary,
      makeLink,
      setRead,
    }
  }
})
</script>